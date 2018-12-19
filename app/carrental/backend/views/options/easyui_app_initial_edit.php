<?php

use common\helpers\CMyHtml;

$objData = isset($objInitial) ? $objInitial : null;

$objForm = new \backend\models\Form_pro_initial();
$formTitle = Yii::t('locale', '{operation} {name}', ['operation' => ($action == 'update' ? Yii::t('locale', 'Edit') : Yii::t('locale', 'Add')), 'name'=>($objData ? $objData->description : \Yii::t('locale', 'setting'))]);

if (!$objData) {
    $objData = new \common\models\Pro_initial();
    $objData->status = \common\components\Consts::STATUS_ENABLED;
}

$inputs = [
    ['type' => CMyHtml::INPUT_TEXTAREA, 'name' => $objForm->fieldName('value'),
        'label' => $objData->getAttributeLabel('value'),
        'value' => $objData->value,
        'htmlOptions' => ['required' => true, 'tailhtml' => $objData->tips],
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('description'),
        'label' => $objData->getAttributeLabel('description'),
        'value' => $objData->description,
        'htmlOptions' => ['required' => true, 'editable'=>false],
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('status'),
        'label' => $objData->getAttributeLabel('status'),
        'value' => $objData->status,
        'data' => \common\components\OptionsModule::getCommonStatusArray(),
        'htmlOptions' => ['editable'=>false],
    ],
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => $action];
if ($action == 'update' && $objInitial) {
    $hiddenFields['id'] = $objInitial->id;
    $hiddenFields[$objForm->fieldName('id')] = $objInitial->id;
    $hiddenFields[$objForm->fieldName('name')] = $objInitial->name;
}

echo CMyHtml::form($formTitle, \yii\helpers\Url::to(['options/app_initial_edit']), 'post', [], $inputs, $buttons, $hiddenFields);
