<?php

declare(strict_types=1);

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'name' => 'Affiliate Platform',
    'language' => 'en',
    'basePath' => dirname(__DIR__),
    'defaultRoute' => 'back-office/site/index',
    'bootstrap' => ['monolog', 'log', 'esb', 'esbListener', 'queue', 'event', 'back-office'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'components' => [

        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => yii\i18n\PhpMessageSource::class,
                    //'basePath' => '@app/messages',
                    //'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'app'       => 'app.php',
                        //'app/error' => 'error.php',
                    ],
                ],
            ],
        ],

        'exchanger' => [
            'class' => \app\services\exchanger\ExchangeService::class,
        ],

        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'iGTfwFlaZA8tNlsZR_12kq2Sw0Wr8d7J',
            'parsers' => [
                'application/json' => yii\web\JsonParser::class,
            ],
        ],
        'cache' => [
            'class' => yii\caching\DummyCache::class,
        ],
        'redis' => (env('REDIS_CONNECTION','local') === 'local') ? require 'redis.php' : require 'redis-local.php',

        'formatter' => [
            'class' => yii\i18n\Formatter::class,
            'defaultTimeZone' => 'UTC',
        ],

        // authorization to admin panel of microservice
        'auth' => [
            'class' => yii\web\User::class,
            'identityClass' => \app\models\User::class,
            'enableAutoLogin' => false,
            'loginUrl' => ['back-office/site/login'],
        ],

        // authorization to API
        'user' => [
            'identityClass' => \app\models\Token::class,
            'enableAutoLogin' => false,
            'loginUrl' => null,
        ],
        'errorHandler' => [
            'errorAction' => 'back-office/site/error',
        ],
        'monolog' => require 'monolog.php',
        'log' => require 'logger.php',
        'db' => $db,

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                new \yii\web\GroupUrlRule([
                    'prefix' => 'api/v1',
                    'routePrefix' => '',
                    'rules' => require 'routes/v1.php',
                ]),
            ],
        ],
        'queue_esb'     => require 'queue-esb.php',
        'esb'           => require 'esb.php',
        'esbListener' => [
            'class' => \app\components\EsbListener::class,
        ],
        'event' => ['class' => \app\components\EventComponent::class],
        'queue' => (env('QUEUE_DRIVER','sync') === 'amqp') ? require 'queue.php' : require 'queue-local.php',
        'filesystem' => (env('FILESYSTEM_DRIVER','local') === 's3') ? require 'filesystem-aws.php' : require 'filesystem-local.php',
    ],


    'modules' => [
        'back-office' => [
            'class' => app\modules\back_office\BackOffice::class,
        ],
    ],

    'container'     => require 'container.php',
    'controllerMap' => require 'controller-map.php',

    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug']['class'] = yii\debug\Module::class;
    $config['modules']['debug']['allowedIPs'] = ['*'];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii']['class'] = yii\gii\Module::class;
    $config['modules']['gii']['allowedIPs'] = ['*'];
}

return $config;
