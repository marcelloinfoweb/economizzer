<?php

use app\models\Profile;
use app\controllers\AuthController;
use app\controllers\UserController;
use kartik\grid\Module;
use yii\authclient\widgets\AuthChoiceStyleAsset;
use yii\i18n\PhpMessageSource;
use yii\log\FileTarget;
use yii\swiftmailer\Mailer;
use amnah\yii2\user\components\User;
use yii\caching\FileCache;
use yii\web\UrlManager;
use app\components\LanguageSelector;

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'economizzer',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        'log',
        [
            'class' => LanguageSelector::class,
            'supportedLanguages' => ['en', 'pt', 'ru', 'ko', 'hu', 'fr', 'cn', 'de', 'es', 'ca', 'lt'],
        ],
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'sourceLanguage' => 'pt-BR',
    'components' => [
        // 'formatter' => [
        //     'class' => 'yii\i18n\formatter',
        //     'thousandSeparator' => '.',
        //     'decimalSeparator' => ',',
        // ],
        'urlManager' => [
            'class' => UrlManager::class,
            'showScriptName' => false,
            'enablePrettyUrl' => true,
            'rules' => array(
                    '<controller:\w+>/<id:\d+>' => '<controller>/view',
                    '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                    '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ),
        ],
        'session' => [
            'name' => '_economizzerSessionId',
            'savePath' => __DIR__ . '/../runtime',
        ],
        'request' => [
            'cookieValidationKey' => 'eco',
        ],
        'cache' => [
            'class' => FileCache::class,
        ],
        'user' => [
            'class' => User::class,
            'identityClass' => \app\models\User::class,
        ],
        'view' => [
                'theme' => [
                    'pathMap' => [
                        '@vendor/amnah/yii2-user/views/default' => '@app/views/user',
                    ],
                ],
            ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => Mailer::class,
            'useFileTransport' => true,
            'messageConfig' => [
                'from' => ['master@economizzer.com' => 'Admin'],
                'charset' => 'UTF-8',
            ]
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        'i18n' => [
        'translations' => [
                '*' => [
                        'class' => PhpMessageSource::class,
                        'basePath' => '@app/messages',
                ],
            ],
        ],
        // 'authClientCollection' => [
        //     'class' => 'yii\authclient\Collection',
        //     'clients' => [
        //         'google' => [
        //             'class' => 'yii\authclient\clients\Google',
        //             'clientId' => '',
        //             'clientSecret' => '',
        //         ],
        //         'facebook' => [
        //             'class' => 'yii\authclient\clients\Facebook',
        //             'clientId' => '',
        //             'clientSecret' => '',
        //             'scope' => 'email',
        //         ],
        //     ]
        // ],
        'assetManager' => [
            'bundles' => [
                AuthChoiceStyleAsset::class => [
                    'sourcePath' => '@app/widgets/authchoice/assets',
                ],
            ],
        ],
    ],
    'modules' => [
        'gridview' =>  [
            'class' => Module::class,
        ],
        'user' => [
            'class' => \amnah\yii2\user\Module::class,
            'controllerMap' => [
                'default' => UserController::class,
                'auth' => AuthController::class
            ],
            'modelClasses'  => [
                'Profile' => Profile::class,
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = \yii\debug\Module::class;

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = \yii\gii\Module::class;
}

return $config;
