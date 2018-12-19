<?php

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['activities/gift_code_list']),
    'deleteUrl' => \yii\helpers\Url::to(['activities/gift_code_delete']),
];

$toolbarArray = [
    Yii::$app->user->can('activities/gift_code_batch_add') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, \Yii::t('locale', 'Batch add gift code'), ['dialog'=>\yii\helpers\Url::to(['activities/gift_code_batch_add'])], 'icon-car') : null,
    //Yii::$app->user->can('activities/gift_code_add') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', ['dialog'=>\yii\helpers\Url::to(['activities/gift_code_add'])]) : null,
    //Yii::$app->user->can('activities/gift_code_edit') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', ['dialog'=>\yii\helpers\Url::to(['activities/gift_code_edit']), 'needSelect' => true]) : null,
    Yii::$app->user->can('activities/gift_code_delete') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', '') : null,
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_SINGLEMULTIPLE),
    
    
    // search areas
];

echo CMyHtml::datagrid('   ', // $title
    new \common\models\Pro_gift_code(),    // $model
    ['id', 'sn', 'type', 'customer_id', 'amount', 'status', 'flag', 'activated_at', 'used_at', 'edit_user_id', 'created_at', 'operation'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    ['dialogWidth'=>'300px', 'dialogHeight'=>'280px'],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
