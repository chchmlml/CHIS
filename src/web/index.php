<?php

date_default_timezone_set('PRC');

defined('ROOT_PATH') or define('ROOT_PATH', dirname(__DIR__));

require(ROOT_PATH . '/vendor/autoload.php');
require(ROOT_PATH . '/vendor/yiisoft/yii2/Yii.php');

$dotenv = new \Dotenv\Dotenv(ROOT_PATH);
$dotenv->load();
defined('YII_DEBUG') or define('YII_DEBUG', getenv('YII_DEBUG'));
defined('YII_ENV') or define('YII_ENV', getenv('YII_ENV'));

$config = require(__DIR__ . '/../config/web.php');

(new yii\web\Application($config))->run();
