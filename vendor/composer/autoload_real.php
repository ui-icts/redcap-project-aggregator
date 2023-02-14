<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit32988ac30f13b7cc0672cabb7aca653e
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInit32988ac30f13b7cc0672cabb7aca653e', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit32988ac30f13b7cc0672cabb7aca653e', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit32988ac30f13b7cc0672cabb7aca653e::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
