<?php

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['options/app_initial_list']),
];

$toolbarArray = [
    Yii::$app->user->can('options/app_initial_edit') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', ['dialog'=>\yii\helpers\Url::to(['options/app_initial_edit']), 'needSelect' => true]) : null,
    //Yii::$app->user->can('options/app_initial_delete') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', '') : null,
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_SINGLEMULTIPLE),
    
    // search areas
];

echo CMyHtml::datagrid('   ', // $title
    new \common\models\Pro_initial(),    // $model
    ['id', 'name', 'value', 'description','tips', 'status', 'edit_user_id', 'updated_at', 'operation'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);

