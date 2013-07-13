<?php

class GithubAutoloader
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Autoloader\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('GithubAutoloader', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Autoloader\Autoload\ClassLoader();
        spl_autoload_unregister(array('GithubAutoloader', 'loadClassLoader'));

        $libraryDir = dirname(__DIR__);
        $baseDir = dirname($libraryDir);

        $map = require __DIR__ . '/autoload_namespaces.php';
        foreach ($map as $namespace => $path) {
            $loader->set($namespace, $path);
        }

        $classMap = require __DIR__ . '/autoload_classmap.php';
        if ($classMap) {
            $loader->addClassMap($classMap);
        }

        $loader->register(true);

        return $loader;
    }
}
?>