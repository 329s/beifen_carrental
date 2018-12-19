<?php

use common\helpers\CMyHtml;

$objForm = new \backend\models\Form_pro_city_area();

$action = 'insert';
if (isset($objData) && $objData) {
    if ($objData->id) {
        $action = 'update';
    }
}
else {
    $objData = new \common\models\Pro_city_area();
    if (isset($cityId)) {
        $objData->city_id = $cityId;
    }
}

$inputs = [
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('name'),
        'label' => $objData->getAttributeLabel('name'),
        'value' => $objData->name,
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_COMBOTREE, 'name' => $objForm->fieldName('city_id'),
        'label' => $objData->getAttributeLabel('city_id'),
        'value' => $objData->city_id,
        'data' => \common\components\CityModule::getCityComboTreeData(true),
        'htmlOptions' => ['required' => true, 'editable'=>false],
    ],
    ['type' => CMyHtml::INPUT_RATIOBUTTONLIST, 'name' => $objForm->fieldName('status'),
        'label' => $objData->getAttributeLabel('status'),
        'value' => $objData->status,
        'data' => \common\components\CityModule::getCityStatusArray(),
        'htmlOptions' => ['required' => true],
    ],
];

$formTitle = Yii::t('locale', '{name} area', ['name' => ($action == 'update' ? \Yii::t('locale', 'Edit') : Yii::t('locale', 'Add'))]);

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => $action];
if ($action == 'update') {
    $hiddenFields['id'] = $objData->id;
    $hiddenFields[$objForm->fieldName('id')] = $objData->id;
}

echo CMyHtml::form($formTitle, $saveUrl, 'post', ['dialog'=>true], $inputs, $buttons, $hiddenFields);
