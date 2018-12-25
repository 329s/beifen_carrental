<?php

use common\helpers\CMyHtml;

$objForm = new \backend\models\Form_pro_vehicle_order_relet();
$formTitle = Yii::t('carrental', 'Vehicle relet order info');

$funcId = CMyHtml::genID();

$inputs = [
    ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => Yii::t('carrental', 'Vehicle basic info')],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => '',
        'label' => \Yii::t('locale', 'Plate number'),
        'value' => $objVehicle->plate_number,
        'htmlOptions' => ['editable'=>false, 'readonly'=>true],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => '',
        'label' => \Yii::t('locale', 'Vehicle model'),
        'value' => $objVehicleModel->vehicle_model,
        'htmlOptions' => ['editable'=>false, 'readonly'=>true],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => '',
        'label' => \Yii::t('locale', 'Vehicle color'),
        'value' => $objVehicle->getColorText(),
        'htmlOptions' => ['editable'=>false, 'readonly'=>true],
        'columnindex' => 2,
    ],
    
    ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => Yii::t('carrental', 'Vehicle relet order info')],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => '',
        'label' => $objData->getAttributeLabel('order_id'),
        'value' => $objData->getMainOrderSerial(),
        'htmlOptions' => ['editable'=>false, 'readonly'=>true],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => '',
        'label' => $objData->getAttributeLabel('origion_end_time'),
        'value' => date('Y-m-d H:i', $objData->origion_end_time),
        'htmlOptions' => ['editable'=>false, 'readonly'=>true],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => '',
        'label' => $objData->getAttributeLabel('new_end_time'),
        'value' => date('Y-m-d H:i', $objData->new_end_time),
        'htmlOptions' => ['editable'=>false, 'readonly'=>true],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => '',
        'label' => $objData->getAttributeLabel('pay_source'),
        'value' => \common\components\OrderModule::getOrderPayTypeText($objData->pay_source),
        'htmlOptions' => ['editable'=>false, 'readonly'=>true],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => '',
        'label' => $objData->getAttributeLabel('total_amount'),
        'value' => $objData->total_amount,
        'htmlOptions' => ['editable'=>false, 'readonly'=>true],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => '',
        'label' => $objData->getAttributeLabel('paid_amount'),
        'value' => $objData->paid_amount,
        'htmlOptions' => ['editable'=>false, 'readonly'=>true],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_TYPE_SUBGROUP],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => '',
        'label' => $objData->getAttributeLabel('remark'),
        'value' => $objData->remark,
        'htmlOptions' => ['editable'=>false, 'readonly'=>true],
        'columnindex' => 0,
    ],
];

$buttons = ['close' => Yii::t('locale', 'Cancel')];

$hiddenFields = [
    'action' => 'update',
    $objForm->fieldName('id') => $objData->id,
];

$arrScripts = [];

$htmlArray = [];

$headerId = CMyHtml::getIDPrefix()."form_header_{$funcId}";
$printerClass = CMyHtml::getIDPrefix()."_cls_printer_{$funcId}";
$formHtmlOptions = [
    'header'=>$headerId,
];
$printButtons = [
    ['href'=>\yii\helpers\Url::to(['print/relet_vehicle_order', 'id'=>$objData->id]), 'label'=>  \Yii::t('carrental', 'Print vehicle relet order')]
];

$htmlArray[] = CMyHtml::form($formTitle, \yii\helpers\Url::to(['order/order_relet_edit']), 'post', $formHtmlOptions, $inputs, $buttons, $hiddenFields);

if (!empty($printButtons)) {
    $htmlArray[] = CMyHtml::beginTag('div', ['id'=>$headerId, 'style'=>"text-align:right"]);
    foreach ($printButtons as $cfg) {
        $htmlArray[] = CMyHtml::tag('a', $cfg['label'], ['href'=>$cfg['href'], 'class'=>"easyui-linkbutton {$printerClass}", 'data-options'=>"iconCls:'icon-printer'", 'encode'=>false]);
    }
    $htmlArray[] = CMyHtml::endTag('div');
    
    $arrScripts[] = <<<EOD
$(document).ready(function() {
    $(".{$printerClass}").printPreview({
    });
});
EOD;
}
        
$htmlArray[] = yii\helpers\Html::script(implode("\n", $arrScripts));

echo implode("\n", $htmlArray);
