<?php
/**
 * @copyright   Copyright 2018, Citrus All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.besidesplus.net/
 */

$dir_base = dirname(__FILE__) . '/Sample';

$database = [
    'type'      => 'postgresql',
    'hostname'  => '192.168.0.1',
    'port'      => '5432',
    'database'  => 'cf-database',
    'schema'    => 'public',
    'username'  => 'citrus',
    'password'  => 'hogehoge',
    'options'   => [
        PDO::ATTR_PERSISTENT => true,
    ],
];


return [
    'default' => [
        'application' => [
            'id'        => 'Test\Sample',
            'path'      => $dir_base,
        ],
        'logger' => [
            'type'      => \Citrus\Logger\File::class,
            'rotate'    => [
                'type'  => 'date',
                'limit' => 'day',
            ],
        ],
        'database' => $database,
        'cache' => [
            'engine'    => 'memcached',
            'host'      => '127.0.0.1',
            'port'      => 11211,
            'prefix'    => 'cf',
            'expire'    => (60 * 60 * 6), // 6時間
        ],
        'router' => [
            'default_url' => 'home/index',
        ],
        'paths' => [
            'cache'             => $dir_base . '/Cache/{#domain#}',
            'compile'           => $dir_base . '/Compile/{#domain#}',
            'template'          => $dir_base . '/Template/{#domain#}',
            'smarty_plugin'     => $dir_base . '/Template/{#domain#}/Plug',
            'javascript'        => $dir_base . '/Javascript/{#domain#}',
            'javascript_library'=> $dir_base . '/Javascript/Library',
            'stylesheet'        => $dir_base . '/Stylesheet/{#domain#}',
            'stylesheet_library'=> $dir_base . '/Stylesheet/Library',
        ],
        'message' => [
            'enable_session' => true,
        ],
        'formmap' => [
            'path' => $dir_base . '/Business/Formmap',
            'cache' => false,
        ],
        'authentication' => [
            'type' => 'database',
            'database' => $database,
        ],
        'migration' => [
            'database' => $database,
            'mode' => 0755,
            'owner' => posix_getpwuid(posix_geteuid())['name'],
            'group' => posix_getgrgid(posix_getegid())['name'],
            'output_dir' => __DIR__ . '/.migration',
        ],
        'integration' => [
            'database' => $database,
            'mode' => 0755,
            'owner' => posix_getpwuid(posix_geteuid())['name'],
            'group' => posix_getgrgid(posix_getegid())['name'],
            'output_dir' => __DIR__ . '/src/Integration',
            'namespace' => 'Citrus',
        ],
        'slack' => [
            'hogehoge' => [
                'webhook_url' => 'https://hooks.slack.com/services/XXXXXXXXXXX',
            ],
            'fugafuga' => [
                'webhook_url' => 'https://hooks.slack.com/services/YYYYYYYYYYY',
            ],
        ],
        'aws' => [
            'cloudwatchlogs' => [
                'region' => 'ap-northeast-1',
                'version' => 'latest',
                'credentials' => [
                    'key' => 'hogehoge',
                    'secret' => 'fugafuga',
                ],
            ],
        ],
        'dmm' => [
            'api_id' => 'hogehoge',
            'affiliate_id' => 'fugafuga',
            'ssl' => true,
        ],
    ],
    'example.com' => [
        'application' => [
            'name'      => 'CitrusFramework Console.',
            'copyright' => 'Copyright 2019 CitrusFramework System, All Rights Reserved.',
            'domain'    => 'hoge.example.com',
        ],
        'logger' => [
            'directory' => $dir_base . '/log',
            'filename'  => '/hoge.example.com.system_log',
            'level'     => 'debug',
            'display'   => false,
            'owner'     => posix_getpwuid(posix_geteuid())['name'],
            'group'     => posix_getgrgid(posix_getegid())['name'],
            'mode'      => 0666,
        ],
        'routing' => [
            'default'   => 'home/login',
            'login'     => 'home/login',
            'error404'  => 'page/error404',
            'error503'  => 'page/error503',
        ],
    ],
];
