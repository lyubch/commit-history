<?php

// This is the configuration for yiic console application.
return array(
    'basePath'   => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name'       => 'Commit History Console Application',
    'preload'    => array('log'),
    'components' => array(
        'db'  => require(dirname(__FILE__) . '/database.php'),
        'log' => array(
            'class'  => 'CLogRouter',
            'routes' => array(
                array(
                    'class'  => 'CFileLogRoute',
                    'levels' => 'error, warning',
                ),
            ),
        ),
    ),
);
