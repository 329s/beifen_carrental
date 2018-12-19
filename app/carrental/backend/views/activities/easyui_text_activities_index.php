<?php

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['activities/text_activities_list']),
    'deleteUrl' => \yii\helpers\Url::to(['activities/text_activities_delete']),
];

$toolbarArray = [
    Yii::$app->user->can('activities/text_activities_add') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', ['dialog'=>\yii\helpers\Url::to(['activities/text_activities_add'])]) : null,
    Yii::$app->user->can('activities/text_activities_edit') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', ['dialog'=>\yii\helpers\Url::to(['activities/text_activities_edit']), 'needSelect' => true]) : null,
    Yii::$app->user->can('activities/text_activities_delete') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', '') : null,
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_SINGLEMULTIPLE),
    
    // search areas
];

echo CMyHtml::datagrid('   ', // $title
    new \common\models\Pro_activity_info(),    // $model
    ['id', 'title', 'content', 'href', 'start_time', 'end_time', 'city_id', 'office_id', 'status', 'edit_user_id', 'created_at', 'operation'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
