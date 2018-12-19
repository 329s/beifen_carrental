<?php

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['activities/image_activities_list']),
    'deleteUrl' => \yii\helpers\Url::to(['activities/image_activities_delete']),
];

$toolbarArray = [
    Yii::$app->user->can('activities/image_activities_add') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', ['dialog'=>\yii\helpers\Url::to(['activities/image_activities_add'])]) : null,
    Yii::$app->user->can('activities/image_activities_edit') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', ['dialog'=>\yii\helpers\Url::to(['activities/image_activities_edit']), 'needSelect' => true]) : null,
    Yii::$app->user->can('activities/image_activities_delete') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', '') : null,
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_SINGLEMULTIPLE),
    
    // search areas
];

echo CMyHtml::datagrid('   ', // $title
    new \common\models\Pro_activity_image(),    // $model
    ['id', 'image', 'name', 'type', 'status', 'href', 'remark', 'edit_user_id', 'created_at', 'operation'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
