<?php

use common\helpers\CMyHtml;

$objForm = new \backend\models\Form_pro_inner_applying();
$formTitle = \Yii::t('carrental', 'Inner applying');

$authOfficeId = backend\components\AdminModule::getAuthorizedOfficeId();
$editable = true;

$idPrefix = CMyHtml::getIDPrefix();
$autoId = CMyHtml::genID();
$searchVehicleUrl = \yii\helpers\Url::to(['/vehicle/search_plates', 'plate'=>'']);

$objData = isset($objItem) ? $objItem : null;
if (!$objData) {
    $objData = new \backend\models\Pro_inner_applying();
    $objData->type = \backend\models\Pro_inner_applying::TYPE_VEHICLE_INNER_USE;
    $objData->status = \backend\models\Pro_inner_applying::STATUS_APPLYING;
    $statusArray = [backend\models\Pro_inner_applying::STATUS_APPLYING => \Yii::t('carrental', 'Waiting approval'), backend\models\Pro_inner_applying::STATUS_CANCELED => \Yii::t('locale', 'Canceled')];
}
else {
    if ($objItem->status != backend\models\Pro_inner_applying::STATUS_APPLYING 
            && $authOfficeId != common\components\OfficeModule::HEAD_OFFICE_ID
            && $authOfficeId != $objItem->approval_office_id) {
        $editable = false;
        $statusArray = backend\models\Pro_inner_applying::getStatusArray();
    }
    else {
        if ($objItem->status == backend\models\Pro_inner_applying::STATUS_APPLYING
                || $objItem->status == backend\models\Pro_inner_applying::STATUS_CANCELED) {
            $statusArray = [backend\models\Pro_inner_applying::STATUS_APPLYING => \Yii::t('carrental', 'Waiting approval'), backend\models\Pro_inner_applying::STATUS_CANCELED => \Yii::t('locale', 'Canceled')];
        }
        else {
            $statusArray = [backend\models\Pro_inner_applying::STATUS_APPROVED=>\Yii::t('carrental', 'Approved'), backend\models\Pro_inner_applying::STATUS_REJECTED=>\Yii::t('locale', 'Rejected')];
        }
    }
}

$inputs = [
    ['type' => CMyHtml::INPUT_COMBOTREE, 'name' => $objForm->fieldName('office_id'),
        'label' => $objData->getAttributeLabel('office_id'),
        'value' => $objData->office_id,
        'data' => \common\components\OfficeModule::getOfficeComboTreeData(),
        'htmlOptions' => ['required' => true, 'style'=>'width:200px', 'editable'=>false, 'readonly'=>(!$editable || $objData->status != backend\models\Pro_inner_applying::STATUS_APPLYING)],
    ],
    ['type' => CMyHtml::INPUT_TYPE_HTML, 'name' => $objForm->fieldName('plate_number'),
        'label' => $objData->getAttributeLabel('plate_number'),
        'html' => \yii\bootstrap\Html::beginTag('div', ['class'=>'input-group input-group-sm']). \yii\bootstrap\Html::input('text', 
                $objForm->fieldName('plate_number'), 
                $objData->plate_number, 
                ['class'=>'form-control',
                    'id'=>"{$idPrefix}plate_number{$autoId}",
                    'autocomplete' => 'off',
                    'data-provide' => 'typeahead',
                ]).
                \yii\bootstrap\Html::endTag('div'),
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('status'),
        'label' => $objData->getAttributeLabel('status'),
        'value' => $objData->status,
        'data' => $statusArray,
        'htmlOptions' => ['required' => true, 'style'=>'width:200px', 'editable'=>false, 'readonly'=>((!$editable) || $objData->status != backend\models\Pro_inner_applying::STATUS_APPLYING)],
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('applyer'),
        'label' => $objData->getAttributeLabel('applyer'),
        'value' => $objData->applyer,
        'htmlOptions' => ['required' => true, 'style'=>'width:200px', 'editable'=>$editable],
    ],
    ['type' => CMyHtml::INPUT_TEXTAREA, 'name' => $objForm->fieldName('content'),
        'label' => $objData->getAttributeLabel('content'),
        'value' => $objData->content,
        'htmlOptions' => ['required' => true, 'style'=>'width:200px', 'editable'=>$editable],
    ],
    ['type' => CMyHtml::INPUT_DATETIMEBOX, 'name' => $objForm->fieldName('start_time'),
        'label' => $objData->getAttributeLabel('start_time'),
        'value' => empty($objData->start_time) ? '' : date('Y-m-d H:i:s', $objData->start_time),
        'htmlOptions' => ['required' => false, 'style'=>'width:200px', 'editable'=>false, 'readonly'=>!$editable],
    ],
    ['type' => CMyHtml::INPUT_DATETIMEBOX, 'name' => $objForm->fieldName('end_time'),
        'label' => $objData->getAttributeLabel('end_time'),
        'value' => empty($objData->end_time) ? '' : date('Y-m-d H:i:s', $objData->end_time),
        'htmlOptions' => ['required' => false, 'style'=>'width:200px', 'editable'=>false, 'readonly'=>!$editable],
    ],
];

if ($objItem) {
    if (!$editable || $authOfficeId == \common\components\OfficeModule::HEAD_OFFICE_ID || $authOfficeId == $objItem->approval_office_id) {
        $inputs[] = ['type' => CMyHtml::INPUT_TEXTAREA, 'name' => $objForm->fieldName('approval_content'),
            'label' => $objData->getAttributeLabel('approval_content'),
            'value' => $objData->approval_content,
            'htmlOptions' => ['required' => true, 'style'=>'width:200px', 'editable'=>$editable],
        ];
        
        if ($objData->type == backend\models\Pro_inner_applying::TYPE_VEHICLE_BELONG_OFFICE
                || $objData->type == backend\models\Pro_inner_applying::TYPE_VEHICLE_STOP_OFFICE
                || $objData->type == backend\models\Pro_inner_applying::TYPE_VEHICLE_INNER_USE) {
            $inputs[] = ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('vehicle_outbound_mileage'),
                'label' => $objData->getAttributeLabel('vehicle_outbound_mileage'),
                'value' => $objData->vehicle_outbound_mileage,
                'htmlOptions' => ['required' => false, 'style'=>'width:200px', 'editable'=>$editable],
            ];
            $inputs[] = ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('vehicle_inbound_mileage'),
                'label' => $objData->getAttributeLabel('vehicle_inbound_mileage'),
                'value' => $objData->vehicle_inbound_mileage,
                'htmlOptions' => ['required' => false, 'style'=>'width:200px', 'editable'=>$editable],
            ];
        }
    }
}

$buttons = ['close' => Yii::t('locale', 'Cancel')];

if ($editable) {
    $buttons['submit'] = Yii::t('locale', 'Submit');
}

$hiddenFields = ['action' => $action, $objForm->fieldName('type')=>$objData->type];
if ($objItem) {
    $hiddenFields['id'] = $objItem->id;
    $hiddenFields[$objForm->fieldName('id')] = $objItem->id;
}

echo CMyHtml::form($formTitle, $saveUrl, 'post', [], $inputs, $buttons, $hiddenFields);
?>
<script type="text/javascript">
$(document).ready(function() {
    $.fn.typeahead.Constructor.prototype.blur = function() {
        var that = this;
        setTimeout(function () { that.hide() }, 250);
    };
    $('<?= "#{$idPrefix}plate_number{$autoId}" ?>').typeahead({
        source: function (query, process) {
            $.get('<?= $searchVehicleUrl ?>'+encodeURI(query), undefined, function (data) {
                var obj = eval('(' + data + ')');
                if (obj.length) {
                    var results = _.map(obj, function(item) {
                        return item.plate;
                    });
                    process(results);
                }
            });
        }
    });
});
</script>
