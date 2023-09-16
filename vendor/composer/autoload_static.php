<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit375b2f9da28c17138454b157cd0274c4
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'Carbon_Fields\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Carbon_Fields\\' => 
        array (
            0 => __DIR__ . '/..' . '/htmlburger/carbon-fields/core',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit375b2f9da28c17138454b157cd0274c4::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit375b2f9da28c17138454b157cd0274c4::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit375b2f9da28c17138454b157cd0274c4::$classMap;

        }, null, ClassLoader::class);
    }
}
