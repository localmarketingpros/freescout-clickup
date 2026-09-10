<?php

namespace Modules\ClickupIntegration\Extensions;

use Modules\ClickupIntegration\Extensions\Contracts\Registrable;
use FilesystemIterator;
use Error;

class Bootstrap
{
    /**
     * Register all extensions to provide either:
     *
     * 1. Inter-module communication
     * 2. Decoupled functionality
     * 3. Etc...
     *
     * @return void
     */
    public static function load()
    {
        $currentPath = dirname(__FILE__);

        $directories = array_map(function($file) use ($currentPath) {
            return "{$currentPath}/$file";
        }, array_diff(scandir($currentPath), ['.', '..']));

        $directories = array_filter($directories, function($file) {
            return is_dir($file);
        });

        $extensionClasses = [];
        foreach ($directories as $directory) {
            $extensionClasses += self::getExtensions($directory);
        }

        foreach ($extensionClasses as $extensionClass) {
            $extension = new $extensionClass;

            if (! $extension instanceof Registrable) {
                throw new Error("Extension ({$extensionClass}) does not implements Registrable interface");
            }

            // Registers extension functionality
            $extension->register();
        }
    }

    /**
     * Dynamically loads classes within a directory and return the names
     *
     * @param string $dir
     * @return array
     */
    private static function getExtensions($directory)
    {
        $predeclaredClasses = get_declared_classes();
        $iterator = new FilesystemIterator($directory, FileSystemIterator::SKIP_DOTS);
        $registeredClasses = [];

        foreach ($iterator as $file) {
            $pathName = $file->getPathname();
            $registeredClasses[] = pathinfo($pathName, PATHINFO_FILENAME);
            require_once $pathName; // NOSONAR
        }

        $classes = array_diff(get_declared_classes(), $predeclaredClasses);
        $classes = array_filter($classes, function ($class) use ($registeredClasses) {
            $pass = false;

            foreach ($registeredClasses as $registeredClass) {
                $pass = $pass || str_ends_with($class, $registeredClass);
            }

            return $pass;
        });
        sort($classes);

        return $classes;
    }
}
