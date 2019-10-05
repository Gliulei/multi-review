<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9f307f67f0932cf8c841e95e1f2f8855
{
    public static $prefixLengthsPsr4 = array (
        'R' => 
        array (
            'Review\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Review\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9f307f67f0932cf8c841e95e1f2f8855::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9f307f67f0932cf8c841e95e1f2f8855::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
