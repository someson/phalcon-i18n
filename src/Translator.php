<?php

namespace Phalcon\I18n;

use Phalcon\Config;
use Phalcon\Di;
use Phalcon\I18n\Interfaces\DecoratorInterface;
use Phalcon\Translate\Exception as TException;
use ReflectionClass;
use ReflectionException;
use function call_user_func_array;

final class Translator
{
    /** @var self */
    private static $_instance;

    /** @var Config */
    private $_config;

    /** @var string */
    private $_lang;

    /** @var string */
    private $_scope;

    /** @var Handler\NativeArray[]|null */
    private $_translations;

    /** @var array */
    private $_missingTranslations;

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
     * @param array|null $userConfig
     */
    public function initialize(?array $userConfig = null): void
    {
        $this->_config = new Config\Adapter\Php('Config/Default.php');
        if ($newConfig = $userConfig ?? self::_getAppConfig('i18n')) {
            $this->_config = $this->_config->merge(new Config($newConfig));
        }
        $this->setLang($this->_config->get('defaultLang'));
        $this->setScope($this->_config->get('defaultScope'));
        $this->_translations = null;
        $this->_missingTranslations = [];
    }

    /**
     * @todo make it protected, no sense to set it globally
     * @param string $name
     * @return $this
     */
    public function setScope(string $name): self
    {
        $this->_scope = $name;
        return $this;
    }

    /**
     * @todo remove
     * @return string|null
     */
    public function getScopeName(): ?string
    {
        return $this->_scope;
    }

    /**
     * @param string $name
     * @return array
     * @throws ReflectionException|TException
     */
    public function getScope(string $name): array
    {
        $allByLang = $this->_loadTranslations();
        return $allByLang->getByScope($name) ?? [];
    }

    public function setLang(string $lang): self
    {
        $this->_lang = strtolower($lang);
        return $this;
    }

    public function getConfig(): Config
    {
        return $this->_config;
    }

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
        return $allByLang->exists($key);
    }

    /**
     * @param string $key 'scope:a.b.c'
     * @param array $params
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
        if (! $allByLang->exists($key)) {
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
        $loaderArgs = $this->_config->path('loader.arguments')->toArray();
        $dirPath = '';
        if (isset($loaderArgs['path'])) {
            $dirPath = rtrim($loaderArgs['path'], '/\\ ') . DIRECTORY_SEPARATOR;
            $loaderArgs['path'] = $dirPath . $this->_lang;
        }
        try {
            $content = call_user_func_array(sprintf('%s::load', $loader), array_merge([$adapter], $loaderArgs));
        } catch (\UnexpectedValueException $e) {
            $this->setLang($this->_config->get('defaultLang'));
            if (isset($loaderArgs['path'])) {
                $loaderArgs['path'] = $dirPath . $this->_lang;
            }
            try {
                $content = call_user_func_array(sprintf('%s::load', $loader), array_merge([$adapter], $loaderArgs));
            } catch (\Exception $e) {
                throw new TException(sprintf('i18n cannot be loaded: %s', $e->getMessage()));
            }
        }
        $interpolator = new ReflectionClass($this->_config->path('interpolator.className'));
        $interpolatorArgs = $this->_config->path('interpolator.arguments');

        return $this->_translations[$this->_lang] = new Handler\NativeArray(array_merge([
            'content' => $content->toArray(),
            'interpolator' => $interpolator->newInstanceArgs($interpolatorArgs->toArray()),
        ], $this->_config->path('handler.options')->toArray()));
    }

    /**
     * @param string $path 'i18n'
     * @return array
     */
    protected static function _getAppConfig(string $path): array
    {
        $di = Di::getDefault();
        if ($di && $di->has('config')) {
            /** @var Config $config */
            $config = $di->getShared('config');
            if ($found = $config->path($path, [])) {
                return $found->toArray();
            }
        }
        return [];
    }
}
