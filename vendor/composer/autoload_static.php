<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit8d24d6cd902d74a55699646136fad704
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
            $loader->prefixLengthsPsr4 = ComposerStaticInit8d24d6cd902d74a55699646136fad704::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit8d24d6cd902d74a55699646136fad704::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
