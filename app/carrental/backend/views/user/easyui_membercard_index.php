<?php

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['user/membercard_list']),
    'deleteUrl' => \yii\helpers\Url::to(['user/membercard_delete']),
];

$toolbarArray = [
    Yii::$app->user->can('user/membercard_add') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', ['dialog'=>\yii\helpers\Url::to(['user/membercard_add'])]) : null,
    Yii::$app->user->can('user/membercard_edit') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', ['dialog'=>\yii\helpers\Url::to(['user/membercard_edit']), 'needSelect' => true]) : null,
    Yii::$app->user->can('user/membercard_delete') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', '') : null,
];

echo CMyHtml::datagrid('   ', // $title
    new common\models\Pro_member_card(),    // $model
    ['id', 'card_no', 'card_name', 'type', 'card_code', 'card_password', 'amount', 'recharged_amount', 'activated_at', 'status', 'edit_user_id', 'updated_at', 'operation'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
