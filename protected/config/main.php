<?php

Yii::setPathOfAlias('vendor', dirname(__FILE__) . '/../../vendor');

// This is the main Web application configuration.
return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name'       => 'Commit History',
    'preload'    => array('log'),
    'import'     => array(
        'application.helpers.*',
        'application.models.*',
        'application.components.*',
    ),
    'modules' => array(
        'api',
    ),
    'components' => array(
        'bitbucket'    => array(
            'class'   => 'application.components.bitbucket.Bitbucket',
            'baseUrl' => 'https://api.bitbucket.org/2.0',
            'authUrl' => 'https://bitbucket.org/site/oauth2/access_token',
        ),
        'mailer'       => array(
            'class'         => 'vendor.sobit.swiftmailer-component.SwiftMailerComponent',
            'swiftBasePath' => __DIR__ . '/../../vendor/swiftmailer/swiftmailer',
            'host'          => 'smtp.gmail.com',
            'port'          => 465,
            'username'      => 'commit.history123',
            'password'      => 'dg6h2dkj47d0dk3',
            'security'      => 'ssl',
            'fromEmail'     => 'noreply@commit-history.loc',
        ),
        'mailSender'   => array(
            'class'     => 'application.components.mailSender.MailSender',
            'viewPath'  => 'application.views.mail',
            'templates' => array(
                'commit-history' => array('subject' => 'Commit History'),
            ),
        ),
        'user'         => array(
            'allowAutoLogin' => true,
        ),
        'urlManager'   => array(
            'showScriptName' => false,
            'urlFormat'      => 'path',
            'rules'          => array(
                // REST patterns
                array('api/<resource>/list', 'pattern' => 'api/<resource:\w+>', 'verb' => 'GET'),
                array('api/<resource>/view', 'pattern' => 'api/<resource:\w+>/<id:\d+>', 'verb' => 'GET'),
                array('api/<resource>/update', 'pattern' => 'api/<resource:\w+>/<id:\d+>', 'verb' => 'PUT'),
                array('api/<resource>/delete', 'pattern' => 'api/<resource:\w+>/<id:\d+>', 'verb' => 'DELETE'),
                array('api/<resource>/create', 'pattern' => 'api/<resource:\w+>', 'verb' => 'POST'),
                // Other controllers
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ),
        ),
        'db' => require(dirname(__FILE__) . '/database.php'),
        'errorHandler' => array(
            'errorAction' => !YII_DEBUG ? 'site/error' : null,
        ),
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
    'params'     => array(
        'email' => array(
            'admin'   => 'webmaster@commit-history.loc',
        ),
    ),
);
