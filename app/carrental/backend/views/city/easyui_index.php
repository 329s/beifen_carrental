<?php

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['city/city_list']),
    'deleteUrl' => \yii\helpers\Url::to(['city/delete']),
];

$tblTitle = '    ';
$tblWidth = '100%';
$tblHeight = '100%';
$tblColumns = ['id', 'name', 'status', 'flag', 'operation'];

$toolbarArray = [
    Yii::$app->user->can('city/add') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, Yii::t('locale', 'Add city'), ['dialog'=>\yii\helpers\Url::to(['city/add', 'type'=>common\models\Pro_city::TYPE_CITY])]) : null,
    Yii::$app->user->can('city/edit') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', ['dialog'=>\yii\helpers\Url::to(['city/edit', 'type'=>common\models\Pro_city::TYPE_CITY]), 'needSelect' => true]) : null,
    Yii::$app->user->can('city/delete') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', '') : null,
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_SINGLEMULTIPLE),
    Yii::$app->user->can('city/province_index') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('locale', 'City enable/disable management'), ['tab'=>\yii\helpers\Url::to(['city/province_index'])], 'icon-house_blue') : null,

    // search areas
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOBOX, 'region', Yii::t('locale', 'Province'), \common\components\CityModule::getAllProvincesArray(true), ['searchOnChange'=>true, 'style'=>'width:120px']),
];

echo CMyHtml::datagrid($tblTitle, // $title
    new \common\models\Pro_city(),    // $model
    $tblColumns,            // $columns
    [],            // $dataArray
    $tblWidth, $tblHeight,     // $width, $height
    ['class'=>'easyui-treegrid', 'data-options' => ['treeField'=>'name']],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);