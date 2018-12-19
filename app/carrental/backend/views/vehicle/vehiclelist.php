<?php

use common\helpers\CMyHtml;

$urlInfo = ['vehicle/vehicle_list'];

$urlsArray = [];
$toolbarArray = [];
$columnsArray = null;

$isFindStatus = true;

if (isset($vehicleStatus)) {
    $urlInfo['status'] = $vehicleStatus;
    
    $columnsArray = ['id', 'plate_number', 'model_id', 'status', 'engine_number', 'vehicle_number', 
        'color', 'baught_time', 'cur_kilometers', 'belong_office_id', 'stop_office_id', 
        'gps_id', 'annual_inspection_time', 'tci_renewal_time', 'vci_renewal_time', 
        'edit_user_id', 'updated_at', 'operation'];

    $isFindStatus = false;
}
else if(isset($action) && $action == 'find_vehicles') {
    $urlInfo['action'] = $action;
    
    $columnsArray = ['id', 'plate_number', 'model_id', 'status', 'engine_number', 'vehicle_number', 
        'color', 'baught_time', 'cur_kilometers', 'belong_office_id', 'stop_office_id', 
        'gps_id', 'annual_inspection_time', 'tci_renewal_time', 'vci_renewal_time', 
        'edit_user_id', 'updated_at'];
}
else {
    $arrCostTypes = \common\components\VehicleModule::getVehicleExpenditureTypesArray();
    $menuToolCostArray = [];
    foreach ($arrCostTypes as $k => $v) {
        $menuToolCostArray[] = ['event' => ['dialog' => \yii\helpers\Url::to(['vehicle/expenditure_index', 'type'=> $k]), 'needSelect' => true], 'name' => $v, 'title' => $v, 'icon' => 'icon-coins'];
    }
    
    
    $toolbarArray[] = Yii::$app->user->can('vehicle/add') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', ['dialog'=>\yii\helpers\Url::to(['vehicle/add'])]) : null;
    $toolbarArray[] = Yii::$app->user->can('vehicle/edit') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', ['dialog'=>\yii\helpers\Url::to(['vehicle/edit']), 'needSelect' => true]) : null;
    $toolbarArray[] = Yii::$app->user->can('vehicle/delete') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', '') : null;
    
    $toolbarArray[] = CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_SINGLEMULTIPLE);
    $toolbarArray[] = Yii::$app->user->can('vehicle/expenditure_index') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_MENU, Yii::t('carrental', 'Vehicle expenditure'), $menuToolCostArray, 'icon-coins') : null;
    
    $toolbarArray[] = Yii::$app->user->can('vehicle/index') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, \Yii::t('carrental', 'Find vehicles'), ['tab'=>\yii\helpers\Url::to(['vehicle/all-index', 'action'=>'find_vehicles'])]) : null;
    
    $urlsArray['deleteUrl'] = \yii\helpers\Url::to(['vehicle/delete']);
    $columnsArray = ['id', 'plate_number', 'model_id', 'status', 'engine_number', 'vehicle_number', 
        'color', 'baught_time', 'cur_kilometers', 'belong_office_id', 'stop_office_id', 
        'gps_id', 'annual_inspection_time', 'tci_renewal_time', 'vci_renewal_time', 
        'edit_user_id', 'updated_at', 'operation'];
}

$eleIdPrefix = CMyHtml::getIDPrefix();
$autoId = CMyHtml::genID();
$brandUrl = \yii\helpers\Url::to(['vehicle/getvehiclebrands', 'enablenone'=>1]);
$subBrandUrl = \yii\helpers\Url::to(['vehicle/getvehiclesubbrands', 'enablenone'=>1]);
$getVehicleModelsUrl = \yii\helpers\Url::to(['vehicle/getmodelnames', 'enablenone'=>1]);
$toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOTREE, 'office_id', Yii::t('locale', 'Office'), \common\components\OfficeModule::getOfficeComboTreeData(), ['searchOnChange'=>true, 'style'=>"width:160px"]);
$toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'plate_number', Yii::t('locale', 'Plate number'), '', ['searchOnChange'=>true]);
if ($isFindStatus) {
    $toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOBOX, 'status', Yii::t('locale', 'Status'), \common\components\VehicleModule::getVehicleStatusWithAllArray(), ['searchOnChange'=>true]);
}
$toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOBOX, 'vehicle_brand', Yii::t('locale', 'Brand'), $brandUrl, ['searchOnChange'=>true, 'id'=>"{$eleIdPrefix}vehicle_brand{$autoId}", 'onSelect'=>"funcOnSelectVehicleBrand{$autoId}"]);
$toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOBOX, 'vehicle_series', Yii::t('locale', 'Model series'), $subBrandUrl, ['searchOnChange'=>true, 'id'=>"{$eleIdPrefix}vehicle_series{$autoId}", 'onSelect'=>"funcOnSelectVehicleSeries{$autoId}"]);
$toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOBOX, 'vehicle_model', Yii::t('locale', 'Vehicle model'), $getVehicleModelsUrl, ['searchOnChange'=>true, 'id'=>"{$eleIdPrefix}vehicle_model{$autoId}"]);

if (\Yii::$app->user->can('vehicle/vehicle-export')) {
    $exportUrl = \yii\helpers\Url::to(['vehicle/vehicle-export', '_'=>time()]);
    $tabName = '车辆列表';
    $toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_BUTTON, \Yii::t('locale', 'Export'), '', '', [ 
            'onclick'=>"funcOpenExportLink{$autoId}('{$tabName}', '{$exportUrl}')"]);
}

$urlsArray['url'] = \yii\helpers\Url::to($urlInfo);

$scriptsContent = <<<EOD
function funcOnSelectVehicleBrand{$autoId}(record) {
    var opts0 = $('#{$eleIdPrefix}vehicle_brand{$autoId}').combobox('options');
    if (opts0.customData == undefined) {
        opts0.customData = {origionValue:record.id};
        return false;
    }
    else if (parseInt(opts0.customData.origionValue) == parseInt(record.id)) {
        return false;
    }
    opts0.customData.origionValue = record.id;

    var opts = $('#{$eleIdPrefix}vehicle_series{$autoId}').combobox('options');
    opts.queryParams.brand = record.id;
    $('#{$eleIdPrefix}vehicle_series{$autoId}').combobox('clear');
    $('#{$eleIdPrefix}vehicle_series{$autoId}').combobox('reload');
    $('#{$eleIdPrefix}vehicle_model{$autoId}').combobox('clear');
    return true;
}

function funcOnSelectVehicleSeries{$autoId}(record) {
    var opts0 = $('#{$eleIdPrefix}vehicle_series{$autoId}').combobox('options');
    if (opts0.customData == undefined) {
        opts0.customData = {origionValue:record.id};
        return false;
    }
    else if (parseInt(opts0.customData.origionValue) == parseInt(record.id)) {
        return false;
    }
    opts0.customData.origionValue = record.id;
    
    var opts = $('#{$eleIdPrefix}vehicle_model{$autoId}').combobox('options');
    var brandId = parseInt($('#{$eleIdPrefix}vehicle_brand{$autoId}').combobox('getValue'));
    opts.queryParams.brand = brandId;
    opts.queryParams.series = record.id;
    $('#{$eleIdPrefix}vehicle_model{$autoId}').combobox('clear');
    $('#{$eleIdPrefix}vehicle_model{$autoId}').combobox('reload');
    
    return true;
}

function funcOpenExportLink{$autoId}(tabName, url) {
    var params = $('#{$eleIdPrefix}vehicle_list{$autoId}').datagrid('options').queryParams;
    var form = $('<form>');
    form.attr('style', 'display:none');
    form.attr('target', '');
    form.attr('method', 'get');
    form.attr('action', url);
    for (var i in params) {
        var input = $('<input>');
        input.attr('type', 'hidden');
        input.attr('name', i);
        input.attr('value', params[i]);
        form.append(input);
    }
    $('body').append(form);
    form.submit();
    setTimeout(function(){ form.remove(); }, 500);
}

$(function () {
    $.parser.parse('#{$eleIdPrefix}vehiclewrapper{$autoId}');
});
EOD;

$htmlArray = [];
$htmlArray[] = \yii\bootstrap\Html::beginTag('div', ['id'=>"{$eleIdPrefix}vehiclewrapper{$autoId}", 'style'=>"width:100%;height:100%"]);
$htmlArray[] = CMyHtml::datagrid('   ', // $title
    new \common\models\Pro_vehicle(),    // $model
    $columnsArray,            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    ['id'=>"{$eleIdPrefix}vehicle_list{$autoId}"],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
$htmlArray[] = \yii\bootstrap\Html::endTag('div');
$htmlArray[] = yii\helpers\Html::script($scriptsContent);

echo implode("\n", $htmlArray);
