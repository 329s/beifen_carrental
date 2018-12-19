<?php

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['office/office_list']),
    'deleteUrl' => \yii\helpers\Url::to(['office/delete']),
];

$toolbarArray = [
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', ['dialog'=>\yii\helpers\Url::to(['office/add'])]),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', ['dialog'=>\yii\helpers\Url::to(['office/edit']), 'needSelect' => true]),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', ''),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_SINGLEMULTIPLE),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('locale', 'City management'), ['tab'=>\yii\helpers\Url::to(['city/index'])], 'icon-house_blue'),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('carrental', 'Office belong area management'), ['tab'=>\yii\helpers\Url::to(['city/index_area'])], 'icon-house_blue'),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('carrental', 'Office comments'), ['tab'=>\yii\helpers\Url::to(['office/usercomments_index'])], 'icon-feed'),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('carrental', 'Join application'), ['tab'=>\yii\helpers\Url::to(['office/joinapplying_index'])], 'icon-application_home'),
    
    // search areas
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOTREE, 'city_id', Yii::t('locale', 'Belong office'), \common\components\CityModule::getCityComboTreeData(), ['searchOnChange'=>true, 'style'=>'width:220px']),
];

echo CMyHtml::datagrid('   ', // $title
    new \common\models\Pro_office(),    // $model
    ['id', 'fullname', 'shortname', 'manager', 'telephone', 'status', 'open_time', 'close_time', 'landmark', 'city_id', 'area_id', 'parent_id', 'edit_user_id', 'updated_at', 'operation'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    ['method'=>'post'],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
