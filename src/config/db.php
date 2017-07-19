<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => getenv('dsn'),
    'username' => getenv('username'),
    'password' => getenv('password'),
    'charset' => 'utf8',
];
