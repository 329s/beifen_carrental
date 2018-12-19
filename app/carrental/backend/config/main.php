<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'language' => 'zh-CN',
    'defaultRoute' => 'site/index',
    'modules' => [
        'sysmaintenance' => [
            'class' => 'backend\modules\sysmaintenance\Module',
        ],
        'rbac' => [
            'class' => 'backend\modules\rbac\Module',
        ],
    ],
    'components' => [
        'user' => [
            'identityClass' => 'backend\models\Rbac_admin',
            'enableAutoLogin' => true,
            'loginUrl' => ['site/login'],
            'idParam' => '__admin',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    //'logVars' => ['_GET', '_POST', '_FILES'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'request' => [
            'class' => 'common\helpers\Request',
            'web' => '/backend/web',
            'adminUrl' => '/admin',
            //'cookieValidationKey' => '9351e27712bbceda088ed91dfcdefe7d',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
    ],
    'params' => $params,
];
