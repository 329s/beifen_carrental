<?php

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['user/user_list', 'maxstatus'=>\common\models\Pub_user_info::CREDIT_LEVEL_WARNING]),
    'deleteUrl' => \yii\helpers\Url::to(['user/blacklist-delete']),
];

$toolbarArray = [
    Yii::$app->user->can('user/blacklist-add') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', ['tab'=>\yii\helpers\Url::to(['user/blacklist-add'])]) : null,
    Yii::$app->user->can('user/blacklist-edit') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', ['tab'=>\yii\helpers\Url::to(['user/blacklist-edit']), 'needSelect' => true]) : null,
    Yii::$app->user->can('user/blacklist-delete') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', '') : null,
];

echo CMyHtml::datagrid('   ', // $title
    new common\models\Pub_user_info(),    // $model
    ['id', 'name', 'telephone', 'member_id', 'vip_level', 'credit_level', 'member_card_amount', 'total_consumption', 'cur_integration', 'violation_records', 'accident_records', 'unfreeze_at', 'operation'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);