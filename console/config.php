<?php
return [
    'id' => 'console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'aliases' => [
        '@console' => __DIR__
    ],
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=127.0.0.1;dbname=opencart',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8mb4',
            'tablePrefix' => 'mcc_',
        ],
        'db2' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=127.0.0.1;dbname=oc',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8mb4',
            'tablePrefix' => 'oc_',
        ]
    ]
];
