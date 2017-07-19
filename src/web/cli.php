<?php

/**
 * cli模式
 * php cli.php hello index
 *
 */

defined('ROOT_PATH') or define('ROOT_PATH', dirname(__DIR__));

require(ROOT_PATH . '/vendor/autoload.php');
require(ROOT_PATH . '/vendor/yiisoft/yii2/Yii.php');

$dotenv = new \Dotenv\Dotenv(ROOT_PATH);
$dotenv->load();
defined('YII_DEBUG') or define('YII_DEBUG', getenv('YII_DEBUG'));
defined('YII_ENV') or define('YII_ENV', getenv('YII_ENV'));

$config = require(ROOT_PATH . '/config/console.php');
(new yii\console\Application($config))->run();
