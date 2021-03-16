<?php
return [
    'cache_types' => [
        'compiled_config' => 1,
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'target_rule' => 1,
        'google_product' => 1,
        'full_page' => 1,
        'config_webservice' => 1,
        'translate' => 1,
        'vertex' => 1
    ],
    'backend' => [
        'frontName' => 'admin_7s38gi'
    ],
    'db' => [
        'connection' => [
            'indexer' => [
                'host' => '10.25.9.107',
                'dbname' => 'kera2',
                'username' => 'superuser',
                'password' => 'K4BZTt8tqmGwx6VC',
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'active' => '1',
                'persistent' => null
            ],
            'default' => [
                'host' => '10.25.9.107',
                'dbname' => 'kera2',
                'username' => 'superuser',
                'password' => 'K4BZTt8tqmGwx6VC',
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'active' => '1'
            ]
        ],
        'table_prefix' => ''
    ],
    'crypt' => [
        'key' => 'f6e2f117a64eae1319d3d1b9a75454ba'
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'default',
    'session' => [
        'save' => 'files'
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'id_prefix' => 'acf_'
            ],
            'page_cache' => [
                'id_prefix' => 'acf_'
            ]
        ]
    ],
    'lock' => [
        'provider' => 'db',
        'config' => [
            'prefix' => null
        ]
    ],
    'downloadable_domains' => [
        'ae.kerastase.com'
    ],
    'install' => [
        'date' => 'Wed, 03 Jun 2020 20:46:39 +0000'
    ]
];
