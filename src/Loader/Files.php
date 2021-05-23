<?php

namespace Phalcon\I18n\Loader;

use DirectoryIterator;
use Phalcon\Config as Collection;
use Phalcon\I18n\Interfaces\{ AdapterInterface, FileLoaderInterface };

class Files implements FileLoaderInterface
{
    /**
     * {@inheritDoc}
     */
    public static function load(string $adapterClass, string $dir): Collection
    {
        $output = [];
        /** @var DirectoryIterator $file */
        foreach (new DirectoryIterator($dir) as $file) {
            if ($file->isFile()) {
                $ext = $file->getExtension();
                /** @var AdapterInterface $adapterClass */
                if (in_array(strtolower($ext), $adapterClass::getFileExtensions(), true)) {
                    $namespace = $file->getBasename('.' . $ext);
                    $output[$namespace] = new $adapterClass($file->getRealPath());
                }
            }
        }
        return new Collection($output);
    }
}
