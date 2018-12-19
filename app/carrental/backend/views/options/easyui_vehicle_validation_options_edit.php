<?php

use common\helpers\CMyHtml;

$objForm = new \backend\models\Form_pro_vehicle_validation_config();
$formTitle = Yii::t('carrental', '{name} vehicle validation info', ['name' => ($action == 'update' ? Yii::t('locale', 'Edit') : Yii::t('locale', 'Add'))]);

$arrValueFlags = [];
$arrAllValueFlags = common\components\VehicleModule::getVehicleValidationOptionsValueFlagsArray();
if (!isset($objData) || !$objData) {
    $objData = new \common\models\Pro_vehicle_validation_config();
    $objData->belong_id = $belongId;
    foreach ($arrAllValueFlags as $k => $v) {
        $arrValueFlags[] = $k;
    }
}
else {
    foreach ($objData->getValueFlagNamesArray() as $k => $v) {
        $arrValueFlags[] = $k;
    }
}

$optionsType = $objData->type;
if ($action == 'insert') {
    if (isset($parentValueFlag)) {
        if ($parentValueFlag > 0) {
            $optionsType = \common\models\Pro_vehicle_validation_config::TYPE_OPTIONS;
        }
    }
}

$inputs = [
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('name'),
        'label' => $objData->getAttributeLabel('name'),
        'value' => $objData->name,
        'htmlOptions' => ['required' => true, 'style'=>'width:200px'],
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('type'),
        'label' => $objData->getAttributeLabel('type'),
        'value' => $optionsType,
        'data' => \common\components\VehicleModule::getVehicleValidationOptionsTypesArray(),
        'htmlOptions' => ['required' => true, 'editable' =>false, 'style'=>'width:200px'],
    ],
    ['type' => CMyHtml::INPUT_CHECKBOXLIST, 'name' => $objForm->fieldName('value_flag'),
        'label' => $objData->getAttributeLabel('value_flag'),
        'value' => $arrValueFlags,
        'data' => $arrAllValueFlags,
        'htmlOptions' => ['required' => true],
    ],
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => $action];

if (empty($objData->belong_id)) {
    $inputs[] = ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('belong_id'),
        'label' => $objData->getAttributeLabel('belong_id'),
        'value' => '',
        'data' => \common\components\VehicleModule::getVehecleValidationOptionsWithTypeLabelsArray(true),
        'htmlOptions' => ['required' => true, 'style'=>'width:200px'],
    ];
}
else {
    $hiddenFields[$objForm->fieldName('belong_id')] = $objData->belong_id;
}
if ($objData && $objData->id && $action == 'update') {
    $hiddenFields['id'] = $objData->id;
    $hiddenFields[$objForm->fieldName('id')] = $objData->id;
}

echo CMyHtml::form($formTitle, \yii\helpers\Url::to(['options/vehicle_validation_options_edit']), 'post', ['dialog'=>true], $inputs, $buttons, $hiddenFields);
