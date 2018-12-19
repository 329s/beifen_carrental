<?php

use common\helpers\CMyHtml;

$objForm = new \backend\models\Form_pro_vehicle_model();
$formTitle = Yii::t('locale', '{operation} vehicle model', ['operation' => ($action == 'update' ? Yii::t('locale', 'Edit') : Yii::t('locale', 'Add'))]);

$objData = isset($objVehicleModel) ? $objVehicleModel : null;
if (!$objData) {
    $objData = new \common\models\Pro_vehicle_model();
}

$brandUrl = \yii\helpers\Url::to(['vehicle/getvehiclebrands', 'enableadd'=>1]);
$subBrandUrl = \yii\helpers\Url::to(['vehicle/getvehiclesubbrands']);
$formBrandId = CMyHtml::getIDPrefix().'combobox_'.CMyHtml::genID();
$formSubBrandId = CMyHtml::getIDPrefix().'combobox_'.CMyHtml::genID();
$formVehicleModelId = CMyHtml::getIDPrefix().'textbox_'.CMyHtml::genID();

$objVehecleBrandForm = new \backend\models\Form_pro_vehicle_brand();
$vehicleBrandFlagEnabled = \common\models\Pro_vehicle_brand::FLAG_ENABLED;
$yiiCsrfKey = Yii::$app->request->csrfParam;
$yiiCsrfToken = Yii::$app->request->getCsrfToken();

$inputs = [
    ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => Yii::t('locale', 'Vehicle model info')],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('brand'),
        'label' => $objData->getAttributeLabel('brand'),
        'value' => $objData->brand,
        'data' => $brandUrl,
        'htmlOptions' => ['id' => $formBrandId, 'required' => true, 'editable'=>false, 'style'=>"width:200px", 
            'data-options'=>"valueField:'id',textField:'text', onSelect:function(record){\n".
                "    if (record.id == -128) {\n".
                "        $.messager.prompt($.custom.lan.defaults.vehicle.addVehicleBrand, $.custom.lan.defaults.vehicle.vehicleBrand+':', function(r){\n".
                "            if (r) {\n".
                "                var params = {'action':'insert', 'skiprefresh':1, '{$yiiCsrfKey}':'{$yiiCsrfToken}','{$objVehecleBrandForm->fieldName('name')}':r, '{$objVehecleBrandForm->fieldName('belong_brand')}':0, '{$objVehecleBrandForm->fieldName('flag')}':{$vehicleBrandFlagEnabled}};\n".
                "                easyuiFuncAjaxSendData('".\yii\helpers\Url::to(['vehicle/editbrand'])."', 'post', params,\n".
                "                    function(data) { $('#{$formBrandId}').combobox('clear'); $('#{$formBrandId}').combobox('reload'); },\n".
                "                    function(e) { $('#{$formBrandId}').combobox('clear'); $('#{$formBrandId}').combobox('reload'); }\n".
                "                );\n".
                "            } else {\n".
                "                $('#{$formBrandId}').combobox('clear');\n".
                "            }\n".
                "        });\n".
                "        return;\n".
                "    }\n".
                "    var opts = $('#{$formSubBrandId}').combobox('options');\n".
                "    opts.queryParams.brand = record.id;\n".
                "    opts.queryParams.enableadd = 1;\n".
                "    $('#{$formSubBrandId}').combobox('reload');\n".
                "    $('#{$formVehicleModelId}').textbox('setValue', record.text + $('#{$formSubBrandId}').combobox('getText'));\n}"
            ],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('model_series'),
        'label' => $objData->getAttributeLabel('model_series'),
        'value' => $objData->model_series,
        'data' => $subBrandUrl . (empty($objData->brand) ? '' : "?brand={$objData->brand}"),
        'htmlOptions' => ['id' => $formSubBrandId, 'required' => true, 'editable'=>false, 'style'=>"width:200px", 
            'data-options'=>"valueField:'id',textField:'text', onSelect:function(record){\n".
                "    if (record.id == -128) {\n".
                "        $.messager.prompt($.custom.lan.defaults.vehicle.addVehicleSeries, $.custom.lan.defaults.vehicle.vehicleSeries+':', function(r){\n".
                "            if (r) {\n".
                "                var opts = $('#{$formSubBrandId}').combobox('options');".
                "                var params = {'action':'insert', 'skiprefresh':1, '{$yiiCsrfKey}':'{$yiiCsrfToken}','{$objVehecleBrandForm->fieldName('name')}':r, '{$objVehecleBrandForm->fieldName('belong_brand')}':opts.queryParams.brand, '{$objVehecleBrandForm->fieldName('flag')}':{$vehicleBrandFlagEnabled}};\n".
                "                easyuiFuncAjaxSendData('".\yii\helpers\Url::to(['vehicle/editbrand'])."', 'post', params,\n".
                "                    function(data) { $('#{$formSubBrandId}').combobox('clear'); $('#{$formSubBrandId}').combobox('reload'); },\n".
                "                    function(e) { $('#{$formSubBrandId}').combobox('clear'); $('#{$formSubBrandId}').combobox('reload'); }\n".
                "                );\n".
                "            } else {\n".
                "                $('#{$formSubBrandId}').combobox('clear');\n".
                "            }\n".
                "        });\n".
                "        return;\n".
                "    }\n".
                "    $('#{$formVehicleModelId}').textbox('setValue', $('#{$formBrandId}').combobox('getText') + record.text);\n}"
            ],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('vehicle_model'),
        'label' => $objData->getAttributeLabel('vehicle_model'),
        'value' => $objData->vehicle_model,
        'htmlOptions' => ['id' => $formVehicleModelId, 'required' => true, 'style'=>"width:200px"],
        'columnindex' => 2,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('vehicle_type'),
        'label' => $objData->getAttributeLabel('vehicle_type'),
        'value' => $objData->vehicle_type,
        'data' => common\models\Pro_vehicle_model::getTypesArray(),
        'htmlOptions' => ['required' => true, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('vehicle_flag'),
        'label' => $objData->getAttributeLabel('vehicle_flag'),
        'value' => $objData->vehicleFlagArrayData(),
        'data' => common\models\Pro_vehicle_model::getVehicleFlagsArray(),
        'htmlOptions' => ['required' => true, 'editable'=>false, 'multiple' => true, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('carriage'),
        'label' => $objData->getAttributeLabel('carriage'),
        'value' => $objData->carriage,
        'data' => array_merge([0=>Yii::t('locale', 'No display')], common\components\VehicleModule::getVehicleCarriagesArray()),
        'htmlOptions' => ['required' => true, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('seat'),
        'label' => $objData->getAttributeLabel('seat'),
        'value' => $objData->seat,
        'data' => common\components\VehicleModule::getVehicleSeatsArray(),
        'htmlOptions' => ['required' => true, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('gearbox'),
        'label' => $objData->getAttributeLabel('gearbox'),
        'value' => $objData->gearbox,
        'data' => common\components\VehicleModule::getVehicleGearboxTypesArray(),
        'htmlOptions' => ['required' => true, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 2,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('emission'),
        'label' => $objData->getAttributeLabel('emission') . '(L)',
        'value' => (empty($objData->emission) ? '' :  $objData->vehicleEmissionDisplayValue()),
        'htmlOptions' => ['required' => true, 'precision'=>1, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('oil_capacity'),
        'label' => $objData->getAttributeLabel('oil_capacity') . '(L)',
        'value' => $objData->oil_capacity,
        'htmlOptions' => ['required' => true, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('oil_label'),
        'label' => $objData->getAttributeLabel('oil_label'),
        'value' => $objData->oil_label,
        'data' => \common\components\VehicleModule::getVehicleOilLabelsArray(),
        'htmlOptions' => ['required' => true, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 2,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('air_intake_mode'),
        'label' => $objData->getAttributeLabel('air_intake_mode'),
        'value' => $objData->air_intake_mode,
        'data' => \common\models\Pro_vehicle_model::getAirIntakeModesArray(),
        'htmlOptions' => ['required' => true, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('driving_mode'),
        'label' => $objData->getAttributeLabel('driving_mode'),
        'value' => $objData->driving_mode,
        'data' => \common\models\Pro_vehicle_model::getDrivingModesArray(),
        'htmlOptions' => ['required' => true, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('gps'),
        'label' => $objData->getAttributeLabel('gps'),
        'value' => $objData->gps,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('display_order'),
        'label' => $objData->getAttributeLabel('display_order'),
        'value' => $objData->display_order,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_CHECKBOXLIST, 'name' => $objForm->fieldName('limit_flag'),
        'label' => $objData->getAttributeLabel('limit_flag'),
        'value' => $objData->vehicleLimitFlagArrayData(),
        'data' => common\models\Pro_vehicle_model::getLimitFlagsArray(),
        'htmlOptions' => ['required' => true, 'style'=>"width:200px"],
        'columnindex' => 2,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('poundage'),
        'label' => $objData->getAttributeLabel('poundage'),
        'value' => $objData->poundage,
        'htmlOptions' => ['required' => true, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('basic_insurance'),
        'label' => $objData->getAttributeLabel('basic_insurance'),
        'value' => $objData->basic_insurance,
        'htmlOptions' => ['required' => true, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('rent_deposit'),
        'label' => $objData->getAttributeLabel('rent_deposit'),
        'value' => $objData->rent_deposit,
        'htmlOptions' => ['required' => true, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('designated_driving_price'),
        'label' => $objData->getAttributeLabel('designated_driving_price'),
        'value' => $objData->designated_driving_price,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('overtime_price_personal'),
        'label' => $objData->getAttributeLabel('overtime_price_personal'),
        'value' => $objData->overtime_price_personal,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('overtime_price_designated'),
        'label' => $objData->getAttributeLabel('overtime_price_designated'),
        'value' => $objData->overtime_price_designated,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('overmileage_price_personal'),
        'label' => $objData->getAttributeLabel('overmileage_price_personal'),
        'value' => $objData->overmileage_price_personal,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('overmileage_price_designated'),
        'label' => $objData->getAttributeLabel('overmileage_price_designated'),
        'value' => $objData->overmileage_price_designated,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('mileage_price'),
        'label' => $objData->getAttributeLabel('mileage_price'),
        'value' => $objData->mileage_price,
        'htmlOptions' => ['style'=>"width:200px", 'precision'=>2],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TEXTAREA, 'name' => $objForm->fieldName('description'),
        'label' => $objData->getAttributeLabel('description'),
        'value' => $objData->description,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 0,
    ],
    
    ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => Yii::t('locale', 'Vehicle image')],
    ['type' => CMyHtml::INPUT_IMAGEFIELD, 'name' => $objForm->fieldName('image_0_file'),
        'label' => $objData->getAttributeLabel('image_0'),
        'value' => '',
        'htmlOptions' => ['style'=>"width:200px", 'width'=>'200px', 'height'=>'140px', 
            'fileSize'=>"500KB",
            'src'=>\common\helpers\Utils::toFileUri($objData->image_0)],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_IMAGEFIELD, 'name' => $objForm->fieldName('image_a_file'),
        'label' => $objData->getAttributeLabel('image_a'),
        'value' => '',
        'htmlOptions' => ['style'=>"width:200px", 'width'=>'200px', 'height'=>'140px', 
            'fileSize'=>"500KB",
            'src'=>\common\helpers\Utils::toFileUri($objData->image_a)],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_IMAGEFIELD, 'name' => $objForm->fieldName('image_b_file'),
        'label' => $objData->getAttributeLabel('image_b'),
        'value' => '',
        'htmlOptions' => ['style'=>"width:200px", 'width'=>'200px', 'height'=>'140px', 
            'fileSize'=>"500KB",
            'src'=>\common\helpers\Utils::toFileUri($objData->image_b)],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_IMAGEFIELD, 'name' => $objForm->fieldName('image_d_file'),
        'label' => $objData->getAttributeLabel('image_d'),
        'value' => '',
        'htmlOptions' => ['style'=>"width:200px", 'width'=>'200px', 'height'=>'140px', 
            'fileSize'=>"500KB",
            'src'=>\common\helpers\Utils::toFileUri($objData->image_d)],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_IMAGEFIELD, 'name' => $objForm->fieldName('image_c_file'),
        'label' => $objData->getAttributeLabel('image_c'),
        'value' => '',
        'htmlOptions' => ['style'=>"width:200px", 'width'=>'200px', 'height'=>'140px', 
            'fileSize'=>"500KB",
            'src'=>\common\helpers\Utils::toFileUri($objData->image_c)],
        'columnindex' => 2,
    ],
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => $action, $objForm->fieldName('image_0') => $objData->image_0, 
    $objForm->fieldName('image_a') => $objData->image_a, 
    $objForm->fieldName('image_b') => $objData->image_b, 
    $objForm->fieldName('image_c') => $objData->image_c, 
    $objForm->fieldName('image_d') => $objData->image_d
];
if ($action == 'update' && $objVehicleModel) {
    $hiddenFields['id'] = $objVehicleModel->id;
    $hiddenFields[$objForm->fieldName('id')] = $objVehicleModel->id;
}

echo CMyHtml::form($formTitle, $saveUrl, 'post', ['enctype' => 'multipart/form-data', 'style' => 'height:460px'], $inputs, $buttons, $hiddenFields);