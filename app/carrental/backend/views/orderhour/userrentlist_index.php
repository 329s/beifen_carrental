<?php

use common\helpers\CMyHtml;

$urlInfo = ['order/order_with_vehicle_list', 'user_id'=>$userId];

$toolbarArray = [
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'serial', Yii::t('locale', '{name} No.', ['name'=>Yii::t('locale', 'Order')]), '', []),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'plate_number', Yii::t('locale', 'Plate number'), '', []),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOBOX, 'vehicle_model_id', Yii::t('locale', 'Vehicle model'), \yii\helpers\Url::to(['vehicle/getmodelnames', 'enableadd'=>'0', 'enableall'=>'1']), ['searchOnChange'=>true, 'style'=>'width:120px']),
    //CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOTREE, 'office_id', Yii::t('locale', 'Office'), \common\components\OfficeModule::getOfficeComboTreeData(), ['searchOnChange'=>true, 'style'=>'width:120px']),
];

$urlsArray = [
    'url' => \yii\helpers\Url::to($urlInfo),
];

$columnFields = ['id', 'serial', 'belong_office_id', 'vehicle_id', 'vehicle_model_id', 'type', 'status', 'customer_name', 'customer_telephone', 
    'customer_vip_level', 'total_amount', 'paid_amount', 'price_rent', 'rent_per_day', 'start_time', 'new_end_time', 'rent_days',
    'price_left'];

$sortName = 'start_time';
$sortOrder = 'desc';

$toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_BUTTON, Yii::t('locale', 'Retrieval'), '', '', []);

$editOrderTitle = \Yii::t('locale', '{name} order', ['name'=> \Yii::t('locale', 'Edit')]);
$urlEditOrder = yii\helpers\Url::to(['order/edit', '_'=>time()]);

$dgId = CMyHtml::getIDPrefix().'dg_'.CMyHtml::genID();

echo CMyHtml::datagrid('   ', // $title
    new \common\models\Pro_vehicle_order(),    // $model
    $columnFields,            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    ['id'=>$dgId, 
        'data-options'=>[
            'onClickCell'=>"function(index,field,value) { if (field.substring(0, 9) == 'operation') { return; } var rowsData = $('#{$dgId}').datagrid('getRows'); var row = rowsData[index]; easyuiFuncNavTabAddDoNotKnownId('{$editOrderTitle}', '{$urlEditOrder}&id='+row.id+'&vehicle_id='+row.vehicle_id); }",
            'sortName' => $sortName,
            'sortOrder' => $sortOrder,
        ],
    ],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
