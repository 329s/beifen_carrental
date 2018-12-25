<?php

use common\helpers\CMyHtml;

$urlInfo = ['orderhour/order_with_vehicle_list'];

$toolbarArray = [
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'serial', Yii::t('locale', '{name} No.', ['name'=>Yii::t('locale', 'Order')]), '', []),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'plate_number', Yii::t('locale', 'Plate number'), '', []),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOBOX, 'vehicle_model_id', Yii::t('locale', 'Vehicle model'), \yii\helpers\Url::to(['vehicle/getmodelnames', 'enableadd'=>'0', 'enableall'=>'1']), ['searchOnChange'=>true, 'style'=>'width:120px']),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'customer_name', \Yii::t('locale', 'Customer name'), '', []),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'customer_telephone', \Yii::t('locale', 'Contact number'), '', []),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOTREE, 'office_id', Yii::t('locale', 'Office'), \common\components\OfficeModule::getOfficeComboTreeData(), ['searchOnChange'=>true, 'style'=>'width:120px']),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOBOX, 'settlement_status', '结算状态', \common\components\VehicleModule::getVehicleSettlementStatusArray(), ['searchOnChange'=>true, 'style'=>'width:120px']),
];


if (isset($status)) {
    $urlInfo['status'] = $status;
}
else {
    $status = 0; 
}
//租车类型
$urlInfo['pay_type'] = 6;

$urlsArray = [
    'url' => \yii\helpers\Url::to($urlInfo),
];
// print_r($urlsArray);exit;
$columnFields = ['id', 'serial', 'belong_office_id', 'vehicle_id', 'vehicle_model_id', 'type', 'customer_name', 'customer_telephone', 
    'customer_vip_level', 'total_amount', 'paid_amount', 'price_rent', 'rent_per_day', 'start_time', 'new_end_time', 'rent_days','price_address_km',
    'price_left'];

$columnFieldsSettlement = ['id', 'serial', 'belong_office_id', 'vehicle_id', 'vehicle_model_id', 'type', 'customer_name', 'customer_telephone', 
    'customer_vip_level', 'total_amount', 'paid_amount', 'price_rent', 'rent_per_day', 'start_time', 'new_end_time', 'rent_days','price_address_km',
    'price_left'
];

$dlgWidth = '1006px';
$dlgHeight = '520px';

$sortName = 'start_time';
$sortOrder = 'desc';

if ($status == \common\models\Pro_vehicle_order::STATUS_WAITING) {
    array_splice($columnFields, 2, 0, ['booking_time']);
    $columnFields[] = 'booking_left_time';
    $columnFields[] = 'operation_waiting_oneway';
    $sortName = 'start_time';
    $sortOrder = 'asc';
}
else if ($status == \common\models\Pro_vehicle_order::STATUS_BOOKED) {
    array_splice($columnFields, 2, 0, ['booking_time']);
    $columnFields[] = 'booking_left_time';
    $columnFields[] = 'operation_booking_oneway';
    $sortName = 'start_time';
    $sortOrder = 'asc';
}
else if ($status == \common\models\Pro_vehicle_order::STATUS_RENTING) {
    $columnFields[] = 'renting_left_time';
    $columnFields[] = 'operation_renting_oneway';
    $dlgWidth = '902px';
    $dlgHeight = '490px';
    $sortName = 'new_end_time';
    $sortOrder = 'asc';
}
else if ($status == \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING) {
    $columnFields[] = 'violation_left_time';
    $columnFields[] = 'operation_violation_oneway';
    $dlgWidth = '1006px';
    $dlgHeight = '520px';
    $sortName = 'new_end_time';
    $sortOrder = 'asc';
}
else if ($status == \common\models\Pro_vehicle_order::STATUS_COMPLETED) {
    $columnFields[] = 'operation_complete_oneway';
    $sortName = 'new_end_time';
    $sortOrder = 'desc';
}

$toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_BUTTON, \Yii::t('locale', 'Retrieval'), '', '', []);

$autoId = CMyHtml::genID();
$dgId = CMyHtml::getIDPrefix().'dg_'.$autoId;

$arrScripts = [];

if ($status == \common\models\Pro_vehicle_order::STATUS_COMPLETED
    || $status == \common\models\Pro_vehicle_order::STATUS_RENTING || $status == 2) {
    if (false) {
        $exportUrl = \yii\helpers\Url::to(['orderhour/export_order_data', 'status'=>$status]);
        $arrScripts[] = "function funcExportVehicleOrder{$autoId}(){ var url = '{$exportUrl}'; var queryParams = $('#{$dgId}').datagrid('options').queryParams; for (var k in queryParams) { url += '&'+k+'='+encodeURI(queryParams[k]); } window.open(url); }";
        $toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_BUTTON, \Yii::t('locale', 'Export'), '', '', [ 
            'onclick'=>"funcExportVehicleOrder{$autoId}()"]);
    }
    else if (\Yii::$app->user->can('statement/orderbymonthly')) {
        $exportUrl = \yii\helpers\Url::to(['statement/orderbymonthly', 'status'=>$status,'pay_type'=>6]);
        $arrStatusText = \common\components\OrderModule::getOrderStatusArray();
        $arrStatusText[\common\models\Pro_vehicle_order::STATUS_COMPLETED] = '历史结算';
        $arrStatusText[\common\models\Pro_vehicle_order::STATUS_RENTING] = '在租';
        $tabName = \Yii::t('locale', '{type} order list', ['type'=>(isset($arrStatusText[$status]) ? $arrStatusText[$status] : '')]);
        $toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_BUTTON, \Yii::t('locale', 'Export'), '', '', [ 
            'onclick'=>"easyuiFuncNavTabAddDoNotKnownId('{$tabName}', '{$exportUrl}', '')"]);
    }
}
// print_r($columnFields);exit;
$editOrderTitle = \Yii::t('locale', '{name} order', ['name'=> \Yii::t('locale', 'Edit')]);
$urlEditOrder = yii\helpers\Url::to(['orderhour/edit', '_'=>time()]);

echo CMyHtml::datagrid('   ', // $title
    new \common\models\Pro_vehicle_order(),    // $model
    $columnFields,            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    ['id'=>$dgId, 'dialogWidth'=>$dlgWidth, 'dialogHeight'=>$dlgHeight, 
        'data-options'=>[
            'onClickCell'=>"function(index,field,value) { if (field.substring(0, 9) == 'operation') { return; } var rowsData = $('#{$dgId}').datagrid('getRows'); var row = rowsData[index]; easyuiFuncNavTabAddDoNotKnownId('{$editOrderTitle}', '{$urlEditOrder}&id='+row.id+'&vehicle_id='+row.vehicle_id); }",
            'sortName' => $sortName,
            'sortOrder' => $sortOrder,
        ],
    ],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);

if (!empty($arrScripts)) {
    echo \yii\helpers\Html::script(implode("\n", $arrScripts));
}
