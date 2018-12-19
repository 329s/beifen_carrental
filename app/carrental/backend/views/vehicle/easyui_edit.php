<?php

use common\helpers\CMyHtml;

$objForm = new \backend\models\Form_pro_vehicle();
$formTitle = Yii::t('locale', '{operation} vehicle', ['operation' => ($action == 'update' ? Yii::t('locale', 'Edit') : Yii::t('locale', 'Add'))]);

$objData = isset($objVehicle) ? $objVehicle : null;
if (!$objData) {
    $objData = new \common\models\Pro_vehicle();
    $objData->status = \common\models\Pro_vehicle::STATUS_NORMAL;
}

$isAdministrator = \backend\components\AdminModule::isAuthorizedHeadOffice();

$idPrefix = CMyHtml::getIDPrefix();
$autoId = CMyHtml::genID();
$dlgId = $idPrefix.'dlg_'.$autoId;

$inputs = [
    ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => Yii::t('locale', '{name} info', ['name' => Yii::t('locale', 'Vehicle')])],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('plate_number'),
        'label' => $objData->getAttributeLabel('plate_number'),
        'value' => $objData->plate_number,
        'htmlOptions' => ['required' => true, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('model_id'),
        'label' => $objData->getAttributeLabel('model_id'),
        'value' => $objData->model_id,
        //'data' => \yii\helpers\Url::to(['vehicle/getmodelnames', 'enableadd'=>'1']),
        'data' => \common\helpers\CEasyUI::convertComboTreeDataToString(\common\components\VehicleModule::getVehicleModelNamesWithPriceArray(['enableadd'=>true])),    // use convertComboTreeDataToString that to support extra parameters.
        'htmlOptions' => ['required' => true, 'editable'=>false, 'style'=>"width:200px",
            'data-options' => "valueField:'id',textField:'text', onSelect:function(record){\n".
                "    if (record.id == -128) {\n".
                "        easyuiFuncOpenDialog('{$dlgId}', '".\yii\helpers\Url::to(['vehicle/addmodel'])."', '".Yii::t('locale', '{operation} vehicle model', ['operation' => Yii::t('locale', 'Add')])."');\n".
                "        $(this).combobox('clear');\n".
                "    }\n}"
        ],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('color'),
        'label' => $objData->getAttributeLabel('color'),
        'value' => $objData->color,
        'data' => \common\components\VehicleModule::getVehicleColorsArray(),
        'htmlOptions' => ['required' => true, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 2,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('engine_number'),
        'label' => $objData->getAttributeLabel('engine_number'),
        'value' => $objData->engine_number,
        'htmlOptions' => [ 'required' => true, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('vehicle_number'),
        'label' => $objData->getAttributeLabel('vehicle_number'),
        'value' => $objData->vehicle_number,
        'htmlOptions' => [ 'required' => true, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('certificate_number'),
        'label' => $objData->getAttributeLabel('certificate_number'),
        'value' => $objData->certificate_number,
        'htmlOptions' => [ 'required' => true, 'style'=>"width:200px"],
        'columnindex' => 2,
    ],
    ['type' => CMyHtml::INPUT_DATEBOX, 'name' => $objForm->fieldName('baught_time'),
        'label' => $objData->getAttributeLabel('baught_time'),
        'value' => (empty($objData->baught_time) ? '' : date('Y-m-d', $objData->baught_time)),
        'htmlOptions' => ['required' => true, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('baught_price'),
        'label' => $objData->getAttributeLabel('baught_price'),
        'value' => $objData->baught_price,
        'htmlOptions' => ['required' => true, 'precision'=>0, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('baught_tax'),
        'label' => $objData->getAttributeLabel('baught_tax'),
        'value' => $objData->baught_tax,
        'htmlOptions' => ['required' => true, 'style'=>"width:200px"],
        'columnindex' => 2,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('baught_insurance'),
        'label' => $objData->getAttributeLabel('baught_insurance'),
        'value' => $objData->baught_insurance,
        'htmlOptions' => ['required' => false, 'precision'=>0, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('decoration_fee'),
        'label' => $objData->getAttributeLabel('decoration_fee'),
        'value' => $objData->decoration_fee,
        'htmlOptions' => ['required' => false, 'precision'=>0, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('license_plate_fee'),
        'label' => $objData->getAttributeLabel('license_plate_fee'),
        'value' => $objData->license_plate_fee,
        'htmlOptions' => ['required' => false, 'precision'=>0, 'style'=>"width:200px"],
        'columnindex' => 2,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('baught_kilometers'),
        'label' => $objData->getAttributeLabel('baught_kilometers'),
        'value' => $objData->baught_kilometers,
        'htmlOptions' => ['required' => true, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('status'),
        'label' => $objData->getAttributeLabel('status'),
        'value' => $objData->status,
        'data' => common\components\VehicleModule::getVehicleStatusArray(),
        'htmlOptions' => ['required' => true, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('isoneway'),
        'label' => $objData->getAttributeLabel('isoneway'),
        'value' => $objData->isoneway,
        'data' => common\components\VehicleModule::getVehicleIsOneWayArray(),
        'htmlOptions' => ['required' => true, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 2,
    ],
    ['type' => CMyHtml::INPUT_COMBOTREE, 'name' => $objForm->fieldName('belong_office_id'),
        'label' => $objData->getAttributeLabel('belong_office_id'),
        'value' => $objData->belong_office_id,
        'data' => common\components\OfficeModule::getOfficeComboTreeData(), 
        'htmlOptions' => ['required' => true, 'editable'=>false, /*'readonly'=>!$isAdministrator,*/ 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_COMBOTREE, 'name' => $objForm->fieldName('stop_office_id'),
        'label' => $objData->getAttributeLabel('stop_office_id'),
        'value' => $objData->stop_office_id,
        'data' => common\components\OfficeModule::getOfficeComboTreeData(), 
        'htmlOptions' => ['required' => true, 'editable'=>false, /*'readonly'=>!$isAdministrator,*/ 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('vehicle_property'),
        'label' => $objData->getAttributeLabel('vehicle_property'),
        'value' => $objData->vehicle_property,
        'data' => common\components\VehicleModule::getVehiclePropertiesArray(),
        'htmlOptions' => ['required' => true, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('gps_id'),
        'label' => $objData->getAttributeLabel('gps_id'),
        'value' => $objData->gps_id,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('remark'),
        'label' => $objData->getAttributeLabel('remark'),
        'value' => $objData->remark,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 0,
    ],
                    
    ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => Yii::t('locale', '{name} info', ['name' => Yii::t('locale', 'Upkeep')])],
    ['type' => CMyHtml::INPUT_DATEBOX, 'name' => $objForm->fieldName('annual_inspection_time'),
        'label' => $objData->getAttributeLabel('annual_inspection_time'),
        'value' => (empty($objData->annual_inspection_time) ? '' : date('Y-m-d', $objData->annual_inspection_time)),
        'htmlOptions' => ['style'=>"width:200px", 'editable'=>false,
            'onChange'=>"updateAnnualInspectionTimeDesc{$autoId}",
            'tailhtml' => \yii\bootstrap\Html::tag('span', '', ['style'=>"color:#007500", 'id' => "{$idPrefix}annual_inspection_time_desc{$autoId}"]),
            ],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_DATEBOX, 'name' => $objForm->fieldName('tci_renewal_time'),
        'label' => $objData->getAttributeLabel('tci_renewal_time'),
        'value' => (empty($objData->tci_renewal_time) ? '' : date('Y-m-d', $objData->tci_renewal_time)),
        'htmlOptions' => ['style'=>"width:200px", 'editable'=>false,
            'onChange'=>"updateTciRenewalTimeDesc{$autoId}",
            'tailhtml' => \yii\bootstrap\Html::tag('span', '', ['style'=>"color:#007500", 'id' => "{$idPrefix}tci_renewal_time_desc{$autoId}"]),
            ],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_DATEBOX, 'name' => $objForm->fieldName('vci_renewal_time'),
        'label' => $objData->getAttributeLabel('vci_renewal_time'),
        'value' => (empty($objData->vci_renewal_time) ? '' : date('Y-m-d', $objData->vci_renewal_time)),
        'htmlOptions' => ['style'=>"width:200px", 'editable'=>false,
            'onChange'=>"updateVciRenewalTimeDesc{$autoId}",
            'tailhtml' => \yii\bootstrap\Html::tag('span', '', ['style'=>"color:#007500", 'id' => "{$idPrefix}vci_renewal_time_desc{$autoId}"]),
            ],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TYPE_SUBGROUP],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('upkeep_mileage_interval'),
        'label' => $objData->getAttributeLabel('upkeep_mileage_interval'),
        'value' => $objData->upkeep_mileage_interval,
        'htmlOptions' => ['style'=>"width:200px",
            'id' => "{$idPrefix}upkeep_mileage_interval{$autoId}",
            'onChange'=>"updateNextUpkeepMileage{$autoId}",
            ],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('next_upkeep_mileage'),
        'label' => $objData->getAttributeLabel('next_upkeep_mileage'),
        'value' => $objData->next_upkeep_mileage,
        'htmlOptions' => ['style'=>"width:200px",
            'id' => "{$idPrefix}next_upkeep_mileage{$autoId}",
            'tailhtml' => \yii\bootstrap\Html::tag('span', '', ['style'=>"color:#007500", 'id' => "{$idPrefix}next_upkeep_mileage_desc{$autoId}"]),
            ],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('upkeep_time_interval'),
        'label' => $objData->getAttributeLabel('upkeep_time_interval'),
        'value' => (empty($objData->upkeep_time_interval) ? '' : intval($objData->upkeep_time_interval) / (86400*30)),
        'htmlOptions' => ['style'=>"width:200px",
            'id' => "{$idPrefix}upkeep_time_interval{$autoId}",
            'onChange'=>"updateNextUpkeepTime{$autoId}",
            'tailhtml' => \Yii::t('locale', 'Month'),
            ],
        'columnindex' => 0,
    ],
    /*['type' => CMyHtml::INPUT_DATEBOX, 'name' => $objForm->fieldName('next_upkeep_time'),
        'label' => $objData->getAttributeLabel('next_upkeep_time'),
        'value' => (empty($objData->next_upkeep_time) ? '' : date('Y-m-d', $objData->next_upkeep_time)),
        'htmlOptions' => ['style'=>"width:200px", 'editable'=>false,
            'id' => "{$idPrefix}next_upkeep_time{$autoId}",
            'tailhtml' => \yii\bootstrap\Html::tag('span', '', ['style'=>"color:#007500", 'id' => "{$idPrefix}next_upkeep_time_desc{$autoId}"]),
            ],
        'columnindex' => 1,
    ],*/
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('cur_kilometers'),
        'label' => $objData->getAttributeLabel('cur_kilometers'),
        'value' => $objData->cur_kilometers,
        'htmlOptions' => ['required' => true, 'style'=>"width:200px",
            'id'=>"{$idPrefix}cur_kilometers{$autoId}",
            'tailhtml' => \yii\bootstrap\Html::tag('label', 
                \yii\bootstrap\Html::checkbox($objForm->fieldName('finishUpkeepOpened'), $objForm->finishUpkeepOpened,
                    [
                        'id' => "{$idPrefix}finish_upkeep_opened{$autoId}",
                        'value' => '1',
                        'uncheck' => '0',
                    ]
                ).\Yii::t('carrental', 'Finish upkeep to enter next period (check this item to effect on save)'), ['style'=>"color:#007500"]),
        ],
        'columnindex' => 0,
    ],
    
    ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => Yii::t('locale', '{name} info', ['name' => Yii::t('locale', 'Image')])],
    ['type' => CMyHtml::INPUT_IMAGEFIELD, 'name' => $objForm->fieldName('vehicle_image'),
        'label' => $objData->getAttributeLabel('vehicle_image'),
        'value' => '',
        'htmlOptions' => ['style'=>"width:200px", 'width'=>'200px', 'height'=>'140px', 
            'fileSize'=>"2MB",
            'src'=>\common\helpers\Utils::toFileUri($objData->vehicle_image)],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_IMAGEFIELD, 'name' => $objForm->fieldName('certificate_image'),
        'label' => $objData->getAttributeLabel('certificate_image'),
        'value' => '',
        'htmlOptions' => ['style'=>"width:200px", 'width'=>'200px', 'height'=>'140px', 
            'fileSize'=>"3MB",
            'src'=>\common\helpers\Utils::toFileUri($objData->certificate_image)],
        'columnindex' => 1,
    ],
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => $action];
if ($action == 'update' && $objVehicle) {
    $hiddenFields['id'] = $objVehicle->id;
    $hiddenFields[$objForm->fieldName('id')] = $objVehicle->id;
    //$hiddenFields[$objForm->fieldName('status')] = $objVehicle->status;
}
else {
    //$hiddenFields[$objForm->fieldName('status')] = \common\models\Pro_vehicle::STATUS_NORMAL;
}

$htmlArray = [];

$htmlArray[] = CMyHtml::form($formTitle, $saveUrl, 'post', ['enctype' => 'multipart/form-data', 'height'=>'500px'], $inputs, $buttons, $hiddenFields);

$htmlArray[] = \common\helpers\CEasyUI::dialog('  ', ['id' => $dlgId, 'style' => "width:1040px;height:610px", 'closed'=>true]);

$lastUpkeepMileage = intval($objData->last_upkeep_mileage);
$lastUpkeepTime = intval($objData->last_upkeep_time);
$arrScripts = [];
$arrScripts[] = <<<EOD
function updateNextUpkeepMileage{$autoId}() {
    var interval = parseInt($('#{$idPrefix}upkeep_mileage_interval{$autoId}').numberbox('getValue'));
    if (isNaN(interval)) { interval = 0; }
    var nextMileage = {$lastUpkeepMileage} + interval;
    $('#{$idPrefix}next_upkeep_mileage{$autoId}').numberbox('setValue', nextMileage);
    updateNextUpkeepMileageDesc{$autoId}(nextMileage);
}
function updateNextUpkeepTime{$autoId}() {
    var interval = parseInt($('#{$idPrefix}upkeep_time_interval{$autoId}').numberbox('getValue'));
    if (isNaN(interval)) { interval = 0; }
    
    var nextTime = {$lastUpkeepTime} + (interval * 86400*30);
    var nextDate = $.custom.utils.formatTime('yyyy-MM-dd', nextTime);
    $('#{$idPrefix}next_upkeep_time{$autoId}').datebox('setValue', nextDate);
    updateNextUpkeepTimeDesc{$autoId}(nextDate);
}

function updateLeftDaysDescriptionText{$autoId}(objId, text, date) {
    var curTime = Math.ceil(new Date().getTime() / 1000);
    var tim = $.custom.utils.toTimestamp(date);
    var days = Math.round((tim - curTime) / 86400);
    
    var oTarget = $('#'+objId);
    var color = '#007500';
    if (days <= 30) {
        color = '#750000';
    }
    oTarget.html(text+' '+days+' 天');
    oTarget.css({color:color});
}

function updateAnnualInspectionTimeDesc{$autoId}(newValue, oldValue) {
    updateLeftDaysDescriptionText{$autoId}('{$idPrefix}annual_inspection_time_desc{$autoId}', '当前离年检还有', newValue);
}

function updateTciRenewalTimeDesc{$autoId}(newValue, oldValue) {
    updateLeftDaysDescriptionText{$autoId}('{$idPrefix}tci_renewal_time_desc{$autoId}', '离交强险续保还有', newValue);
}

function updateVciRenewalTimeDesc{$autoId}(newValue, oldValue) {
    updateLeftDaysDescriptionText{$autoId}('{$idPrefix}vci_renewal_time_desc{$autoId}', '离商业险续保还有', newValue);
}

function updateNextUpkeepMileageDescX{$autoId}(nextMileage, curMileage) {
    var leftMileage = nextMileage - curMileage;
    var oTarget = $('#{$idPrefix}next_upkeep_mileage_desc{$autoId}');
    var color = '#007500';
    if (leftMileage <= 1000) {
        color = '#750000';
    }
    oTarget.html('距离下次保养剩余 '+leftMileage+' 公里');
    oTarget.css({color:color});
}

function updateNextUpkeepMileageDesc{$autoId}(newValue, oldValue) {
    var curMileage = parseInt($('#{$idPrefix}cur_kilometers{$autoId}').numberbox('getValue'));
    var nextMileage = parseInt(newValue);
    if (isNaN(curMileage)) { curMileage = 0; }
    if (isNaN(nextMileage)) { nextMileage = 0; }
    if (nextMileage && curMileage) {
        updateNextUpkeepMileageDescX{$autoId}(nextMileage, curMileage);
    }
}

function updateNextUpkeepTimeDesc{$autoId}(newValue, oldValue) {
    updateLeftDaysDescriptionText{$autoId}('{$idPrefix}next_upkeep_time_desc{$autoId}', '距离下次保养还有', newValue);
}

EOD;

$arrScripts[] = "$(document).ready(function() {";
if (!empty($objData->annual_inspection_time)) {
    $arrScripts[] = "updateAnnualInspectionTimeDesc{$autoId}('".date('Y-m-d',$objData->annual_inspection_time)."');";
}
if (!empty($objData->tci_renewal_time)) {
    $arrScripts[] = "updateTciRenewalTimeDesc{$autoId}('".date('Y-m-d',$objData->tci_renewal_time)."');";
}
if (!empty($objData->vci_renewal_time)) {
    $arrScripts[] = "updateVciRenewalTimeDesc{$autoId}('".date('Y-m-d',$objData->vci_renewal_time)."');";
}
if (!empty($objData->next_upkeep_mileage) && !empty($objData->cur_kilometers)) {
    $arrScripts[] = "updateNextUpkeepMileageDescX{$autoId}({$objData->next_upkeep_mileage}, {$objData->cur_kilometers});";
}
if (!empty($objData->next_upkeep_time)) {
    $arrScripts[] = "updateNextUpkeepTimeDesc{$autoId}('".date('Y-m-d',$objData->next_upkeep_time)."');";
}
$arrScripts[] = "});";

$htmlArray[] = yii\helpers\Html::script(implode("\n", $arrScripts), ['type'=>'text/javascript']);

echo implode("\n", $htmlArray);
