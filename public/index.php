<?php

defined('YII_DEBUG') or define('YII_DEBUG', 1);
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);

$yii = __DIR__ . '/../vendor/yiisoft/yii/framework/yii.php';
require_once($yii);

$configPath = __DIR__ . '/../protected/config';
$config     = file_exists($configPath . '/main-local.php') ? $configPath . '/main-local.php' : $configPath . '/main.php';
Yii::createWebApplication($config)->run();
