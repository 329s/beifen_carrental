<?php

use common\helpers\CMyHtml;

if (!isset($belongId)) {
    $belongId = 0;
}

$tblTitle = '    ';
$tblWidth = '100%';
$tblHeight = '100%';
$tblColumns = ['id', 'name', 'status', 'value_flag', 'edit_user_id', 'created_at', 'operation'];
//$toolbarArray = [];

$urlsArray = [
    'url' => \yii\helpers\Url::to(['options/vehicle_validation_options_list', 'belong_id'=>$belongId]),
];

//if (!isset($isChildren) || !$isChildren) {
//    $urlsArray['detailUrl'] = \yii\helpers\Url::to(['options/vehicle_validation_options_index', 'is_children'=>'1']);
//    $tblColumns[] = 'detialed_info';
    $toolbarArray = [
        CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', ['dialog'=>\yii\helpers\Url::to(['options/vehicle_validation_options_add'])]),
        CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', ['dialog'=>\yii\helpers\Url::to(['options/vehicle_validation_options_edit']), 'needSelect' => true]),
        CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', ['dialog'=>\yii\helpers\Url::to(['options/vehicle_validation_options_delete']), 'needSelect' => true]),
        CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_SINGLEMULTIPLE),

        // search areas
    ];
//}
//else {
//    $tblTitle = Yii::t('locale', 'Belongs to {name}', ['name' => (isset($name) ? $name : '')]);
//}

echo CMyHtml::datagrid($tblTitle, // $title
    new \common\models\Pro_vehicle_validation_config(),    // $model
    $tblColumns,            // $columns
    [],            // $dataArray
    $tblWidth, $tblHeight,     // $width, $height
    ['class'=>'easyui-treegrid', 'data-options' => [ 'treeField'=>'name']],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
