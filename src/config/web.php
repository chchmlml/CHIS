<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'app\\library\\Bootstrap'],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'OC9aNqn9z3z44pvkgnpF9-DURrGtEaGP',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'errorHandler' => [
            'errorAction' => 'error/handle',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info', 'error', 'warning'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        'urlManager' => [
            // 路由路径化
            'enablePrettyUrl' => true,
            // 隐藏入口脚本
            'showScriptName' => false,
            // 假后缀
            'suffix'=>'',
            'rules' => [
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
            ],
        ],
    ],
    'defaultRoute' => 'tag/index',
    'params' => $params,
];
return $config;
