<?php

use common\helpers\CMyHtml;

$objForm = new \backend\models\Form_pro_festival();
$formTitle = Yii::t('locale', '{operation} festival', ['operation' => ($action == 'update' ? Yii::t('locale', 'Edit') : Yii::t('locale', 'Add'))]);

$objData = isset($objFesitval) ? $objFesitval : null;
if (!$objData) {
    $objData = new \common\models\Pro_festival();
    $objData->status = \common\models\Pro_festival::STATUS_NORMAL;
}

$inputs = [
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('name'),
        'label' => $objData->getAttributeLabel('name'),
        'value' => $objData->name,
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_DATEBOX, 'name' => $objForm->fieldName('start_time'),
        'label' => $objData->getAttributeLabel('start_time'),
        'value' => (empty($objData->start_time) ? '' : date('Y-m-d', $objData->start_time)),
        'htmlOptions' => ['required' => true, 'editable'=>false],
    ],
    ['type' => CMyHtml::INPUT_DATEBOX, 'name' => $objForm->fieldName('end_time'),
        'label' => $objData->getAttributeLabel('end_time'),
        'value' => (empty($objData->end_time) ? '' : date('Y-m-d', $objData->end_time)),
        'htmlOptions' => ['required' => true, 'editable'=>false],
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('status'),
        'label' => $objData->getAttributeLabel('status'),
        'value' => $objData->status,
        'data' => \common\components\OptionsModule::getFestivalStatusArray(),
        'htmlOptions' => ['editable'=>false],
    ],
    ['type' => CMyHtml::INPUT_TYPE_HTML,
        'label' => '',
        'html' => yii\bootstrap\Html::checkbox($objForm->fieldName('alldays_required'), intval($objData->alldays_required) ? true : false, ['label'=>$objData->getAttributeLabel('alldays_required')]),
    ],
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => $action];
if ($action == 'update' && $objFesitval) {
    $hiddenFields['id'] = $objFesitval->id;
    $hiddenFields[$objForm->fieldName('id')] = $objFesitval->id;
}

echo CMyHtml::form($formTitle, $saveUrl, 'post', [], $inputs, $buttons, $hiddenFields);
