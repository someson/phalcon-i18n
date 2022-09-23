<?php

namespace Phalcon\I18n;

use Phalcon\Config\Adapter\Php;
use Phalcon\Config\ConfigInterface;
use Phalcon\Config\Exception;
use Phalcon\Di\Di;
use Phalcon\I18n\Interfaces\DecoratorInterface;
use Phalcon\Translate\Exception as TException;
use Phalcon\Translate\Interpolator\InterpolatorInterface;
use ReflectionClass, ReflectionException, UnexpectedValueException;

final class Translator
{
    private static self $_instance;
    private ConfigInterface $_config;
    private string $_lang;
    private string $_scope;

    /** @var Handler\NativeArray[]|null */
    private ?array $_translations;

    /** @var array<string, int> */
    private array $_missingTranslations;

    private function __clone() {}
    private function __construct()
    {
        $this->initialize();
    }

    public static function instance(): self
    {
        if (! isset(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * @param array<string, mixed> $userConfig
     * @return void
     * @throws Exception
     */
    public function initialize(array $userConfig = []): void
    {
        $this->_config = new Php('Config/Default.php');
        if ($newConfig = $userConfig ?: self::_getAppConfig('i18n')) {
            $this->_config = $this->_config->merge($newConfig);
        }
        $this->setLang($this->_config->get('defaultLang'));
        $this->setScope($this->_config->get('defaultScope'));
        $this->_translations = null;
        $this->_missingTranslations = [];
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setScope(string $name): self
    {
        $this->_scope = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getScopeName(): ?string
    {
        return $this->_scope;
    }

    /**
     * @param string $name
     * @return array<string, mixed>
     * @throws ReflectionException|TException
     */
    public function getScope(string $name): array
    {
        return $this->_loadTranslations()->getByScope($name);
    }

    public function setLang(string $lang): self
    {
        $this->_lang = strtolower($lang);
        return $this;
    }

    public function getConfig(): ConfigInterface
    {
        return $this->_config;
    }

    /**
     * @return array<string, int>
     */
    public function getMissingTranslations(): array
    {
        return $this->_missingTranslations;
    }

    /**
     * @param string $key 'scope:a.b.c'
     * @return bool
     * @throws ReflectionException|TException
     */
    public function exists(string $key): bool
    {
        $allByLang = $this->_loadTranslations();
        $scope = $this->_scope;
        $parts = explode(':', $key);
        if (count($parts) > 1) {
            [$scope, $key] = $parts;
        }
        $allByLang->shiftKeys($scope);
        return $allByLang->has($key);
    }

    /**
     * @param string $key 'scope:a.b.c'
     * @param array<string, string> $params
     * @param bool $pluralize
     * @return string
     * @throws ReflectionException|TException
     */
    public function _(string $key, array $params = [], bool $pluralize = true): string
    {
        $scope = $this->_scope;
        $parts = explode(':', $key);
        if (count($parts) > 1) {
            [$scope, $key] = $parts;
        }
        if (isset($params['context']) && $params['context']) {
            $key = sprintf('%s_%s', $key, $params['context']);
        }
        if ($pluralize && isset($params['count']) && (int) $params['count'] > 1) {
            $key = sprintf('%s_plural', $key);
        }
        $allByLang = $this->_loadTranslations();
        $allByLang->shiftKeys($scope);
        if (! $allByLang->has($key)) {
            if ($this->_config->get('collectMissingTranslations', false)) {
                if (isset($this->_missingTranslations[$key])) {
                    ++$this->_missingTranslations[$key];
                } else {
                    $this->_missingTranslations[$key] = 1;
                }
            }
            if (! $decorator = $this->_config->get('decorateMissingTranslations', false)) {
                return $key;
            }
            if ($decorator instanceof DecoratorInterface) {
                return (new $decorator())->decorate($key);
            }
            if (is_string($decorator)) {
                return sprintf($decorator, $key);
            }
        }
        return $allByLang->query($key, $params);
    }

    /**
     * @return Handler\NativeArray
     * @throws ReflectionException|TException
     */
    public function _loadTranslations(): Handler\NativeArray
    {
        if (isset($this->_translations[$this->_lang])) {
            return $this->_translations[$this->_lang];
        }
        $loader = $this->_config->path('loader.className');
        $adapter = $this->_config->path('adapter.className');
        $loaderArgs = $this->_config->path('loader.arguments')?->toArray();

        $dirPath = isset($loaderArgs['path']) ? rtrim($loaderArgs['path'], '/\\ ') . DIRECTORY_SEPARATOR : '';
        $loaderArgs['path'] = $dirPath . $this->_lang;
        try {
            $content = $loader::load($adapter, $loaderArgs['path']);
        } catch (UnexpectedValueException) {
            $this->setLang($this->_config->get('defaultLang'));
            $loaderArgs['path'] = $dirPath . $this->_lang;
            try {
                $content = $loader::load($adapter, $loaderArgs['path']);
            } catch (\Exception $e) {
                throw new TException(sprintf('i18n cannot be loaded: %s', $e->getMessage()));
            }
        }
        $interpolatorObj = new ReflectionClass($this->_config->path('interpolator.className'));
        $interpolatorArgs = $this->_config->path('interpolator.arguments');
        $handlerOptions = $this->_config->path('handler.options');

        /** @var InterpolatorInterface $interpolator */
        $interpolator = $interpolatorObj->newInstanceArgs($interpolatorArgs->toArray());
        return $this->_translations[$this->_lang] = new Handler\NativeArray($interpolator, array_merge(
            ['content' => $content->toArray()], $handlerOptions->toArray()
        ));
    }

    /**
     * @param string $path 'i18n'
     * @return array<string, mixed>
     */
    protected static function _getAppConfig(string $path): array
    {
        $di = Di::getDefault();
        if ($di && $di->has('config')) {
            /** @var ConfigInterface $config */
            $config = $di->getShared('config');
            if ($found = $config->path($path, [])) {
                return $found->toArray();
            }
        }
        return [];
    }
}
