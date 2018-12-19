<?php

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['options/app_initial_list']),
];

$toolbarArray = [
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', ['dialog'=>\yii\helpers\Url::to(['options/app_initial_edit']), 'needSelect' => true]),
    //CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', ''),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_SINGLEMULTIPLE),
    
    // search areas
];

echo CMyHtml::datagrid('   ', // $title
    new \common\models\Pro_initial(),    // $model
    ['id', 'name', 'value', 'description', 'status', 'edit_user_id', 'updated_at', 'operation'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);

