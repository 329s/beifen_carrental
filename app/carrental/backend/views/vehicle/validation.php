<?php

use common\helpers\CMyHtml;

$objForm = new \backend\models\Form_pro_vehicle_validation_order();
$formTitle = Yii::t('carrental', 'Vehicle validation info');

$arrAllValidationOptions = \common\components\VehicleModule::getVehicleValidationOptionsArray();

if (!isset($objData)) {
    $objData = new \common\models\Pro_vehicle_validation_order();
    $objData->mileage = $vehicleMileage;
}

$funcId = CMyHtml::genID();

$inputs = [
    ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => Yii::t('carrental', 'Vehicle basic info')],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => '',
        'label' => \Yii::t('locale', 'Plate number'),
        'value' => $vehiclePlateNo,
        'htmlOptions' => ['editable'=>false, 'readonly'=>true],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => '',
        'label' => \Yii::t('locale', 'Vehicle model'),
        'value' => $vehicleModelName,
        'htmlOptions' => ['editable'=>false, 'readonly'=>true],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => '',
        'label' => \Yii::t('locale', 'Vehicle color'),
        'value' => $vehicleColorText,
        'htmlOptions' => ['editable'=>false, 'readonly'=>true],
        'columnindex' => 2,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => '',
        'label' => \Yii::t('locale', 'Vehicle status'),
        'value' => $vehicleStatusText,
        'htmlOptions' => ['editable'=>false, 'readonly'=>true],
        'columnindex' => 3,
    ],
    
];

function _appendValidationFields($objForm, $objData, $groupLabelEndfix = '', $groupColIndex = 0, $isReadony = false) {
    return [
        ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => Yii::t('carrental', 'Vehicle validation data').$groupLabelEndfix, 
            'columnindex'=>$groupColIndex],
        ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => ($isReadony ? '' : $objForm->fieldName('oil')),
            'label' => $objData->getAttributeLabel('oil'),
            'value' => $objData->oil,
            'data' => \common\components\VehicleModule::getOilVolumeLevesArray(),
            'htmlOptions' => ['required' => true, 'style'=>'width:140px', 'editable'=>false, 'readonly'=>$isReadony],
            'columnindex' => 0,
        ],
        ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => ($isReadony ? '' : $objForm->fieldName('mileage')),
            'label' => $objData->getAttributeLabel('mileage'),
            'value' => $objData->mileage,
            'htmlOptions' => ['required' => true, 'style'=>'width:170px', 'readonly'=>$isReadony],
            'columnindex' => 1,
        ],
        ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => ($isReadony ? '' : $objForm->fieldName('validator')),
            'label' => $objData->getAttributeLabel('validator'),
            'value' => $objData->validator,
            'htmlOptions' => ['required' => false, 'style'=>'width:140px', 'readonly'=>$isReadony],
            'columnindex' => 0,
        ],
        ['type' => CMyHtml::INPUT_DATETIMEBOX, 'name' => ($isReadony ? '' : $objForm->fieldName('validated_at')),
            'label' => $objData->getAttributeLabel('validated_at'),
            'value' => date('Y-m-d H:i:s', ($isReadony ? ($objData->validated_at ? $objData->validated_at : time()) : time())),
            'htmlOptions' => ['required' => true, 'style'=>'width:170px', 'readonly'=>$isReadony],
            'columnindex' => 1,
        ],
        ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => ($isReadony ? '' : $objForm->fieldName('validate_summary')),
            'label' => $objData->getAttributeLabel('validate_summary'),
            'value' => $objData->validate_summary,
            'data' => \common\components\VehicleModule::getVehicleValidationSummaryArray(),
            'htmlOptions' => ['required' => true, 'style'=>'width:140px', 'readonly'=>$isReadony],
        ],
    ];
}

$labelPrev = '';
$labelCur = '';
if ($objPrevValidation) {
    if ($purpose == 'vehicle_validation') {
        $labelPrev = CMyHtml::tag('span', "(". Yii::t('carrental', 'before vehicle dispath').")", []);
        $labelCur = CMyHtml::tag('span', "(". Yii::t('carrental', 'after vehicle returned').")", []);
    }
    else {
        $labelPrev = CMyHtml::tag('span', "(". Yii::t('carrental', 'before vehicle validation').")", []);
        $labelCur = CMyHtml::tag('span', "(". Yii::t('carrental', 'after vehicle validation').")", []);
    }
    $arr1 = _appendValidationFields($objForm, $objPrevValidation, $labelPrev, 0, true);
    $arr2 = _appendValidationFields($objForm, $objData, $labelCur, 1, false);
    foreach ($arr1 as $o) {
        $inputs[] = $o;
    }
    foreach ($arr2 as $o) {
        $inputs[] = $o;
    }
}
else {
    $arr1 = _appendValidationFields($objForm, $objData);
    foreach ($arr1 as $o) {
        $inputs[] = $o;
    }
}

foreach ($arrAllValidationOptions as $k => $group) {
    $groupColIndex = 0;
    $groupName = $group['name'];
    $groupType = $group['type'];
    $groupArr = [];
    if (isset($group['children'])) {
        $groupArr = &$group['children'];
    }
    if ($objPrevValidation) {
        if ($groupType == \common\models\Pro_vehicle_validation_config::TYPE_IMAGES) {
            $inputs[] = ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => $groupName.$labelPrev,
                'htmlOptions' => ['data-options'=>"collapsible:true,collapsed:false", 'style'=>"height:230px"], 
                'columnindex'=>$groupColIndex++];
            $arrImages = $objPrevValidation->getValidationImagesByValidationOptionsId($k);
            foreach ($arrImages as $imgIdx => $imgInfo) {
                $inputs[] = [
                    'type' => CMyHtml::INPUT_IMAGEFIELD,
                    'name' => '',
                    'label' => '',
                    'htmlOptions' => ['readonly'=>true, 'editable'=>false, 
                        'style'=>"width:200px", 'width'=>'200px', 'height'=>'140px', 
                        'fileSize'=>"4MB",
                        'src'=>$imgInfo->getImageUrl()],
                    'columnindex' => $imgIdx,
                ];
            }
        }
        else {
            $inputs[] = ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => $groupName.$labelPrev,
            'htmlOptions' => ['data-options'=>"collapsible:true,collapsed:false"],
            'columnindex'=>$groupColIndex++];
            foreach ($groupArr as $row) {
                if ($row->type == \common\models\Pro_vehicle_validation_config::TYPE_OPTIONS) {
                    $inputs[] = [
                        'type' => CMyHtml::INPUT_RATIOBUTTONLIST,
                        'name' => '',
                        'label' => $row->name,
                        'value' => $objPrevValidation->getValueByValidationOptionsId($row->id),
                        'data' => $row->getValueFlagNamesArray(),
                        'htmlOptions' => ['readonly'=>true, 'editable'=>false],
                    ];
                }
            }
        }
    }
    
    if ($groupType == \common\models\Pro_vehicle_validation_config::TYPE_IMAGES) {
        $inputs[] = ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => $groupName.$labelCur,
            'htmlOptions' => ['data-options'=>"collapsible:true,collapsed:false", 'style'=>"height:230px"], 
            'columnindex'=>$groupColIndex];
        //$arrImages = $objData->getValidationImagesByValidationOptionsId($k);
        $arrImages = [];
        $curImgColIndex = 0;
        foreach ($arrImages as $imgInfo) {
            $inputs[] = [
                'type' => CMyHtml::INPUT_IMAGEFIELD,
                'name' => $objForm->fieldName('image_info')."[{$k}][{$imgInfo->id}]",
                'label' => '',
                'htmlOptions' => ['readonly'=>false, 'editable'=>false, 
                    'style'=>"width:200px", 'width'=>'200px', 'height'=>'140px', 
                    'fileSize'=>"4MB",
                    'src'=>$imgInfo->getImageUrl(), 
                    'hiddenField'=>$objForm->fieldName('image_info')."[{$k}][{$imgInfo->id}]"],
                'columnindex' => $curImgColIndex,
            ];
            $curImgColIndex++;
        }
        $addImgFieldName = $objForm->fieldName('image_info')."[{$k}][addfiles][]";
        $addImgFileIdx = 0;
        if (empty($arrImages)) {
            $inputs[] = [
                'type' => CMyHtml::INPUT_IMAGEFIELD,
                'name' => $addImgFieldName,
                'label' => '',
                'htmlOptions' => ['readonly'=>false, 'editable'=>false, 
                    'style'=>"width:200px", 'width'=>'200px', 'height'=>'140px', 
                    'fileSize'=>"4MB",
                    'src'=>''],
                'columnindex' => $curImgColIndex,
            ];
            $curImgColIndex++;
        }
        $getImageFieldHtmlUrl = \yii\helpers\Url::to(['api/get_imagefield_html', 'field'=>$addImgFieldName]);
        $inputs[] = [
            'type' => CMyHtml::INPUT_TYPE_APPENDELEMENTBUTTON,
            'name' => $addImgFieldName,
            'label' => Yii::t('locale', 'Add'),
            'value' => $addImgFileIdx,
            'data' => ['url'=>$getImageFieldHtmlUrl],
            'htmlOptions' => [],
            'columnindex' => $curImgColIndex,
        ];
    }
    else {
        $inputs[] = ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => $groupName.$labelCur,
            'htmlOptions' => ['data-options'=>"collapsible:true,collapsed:false"],
            'columnindex'=>$groupColIndex,
            'htm'];
        foreach ($groupArr as $row) {
            if ($row->type == \common\models\Pro_vehicle_validation_config::TYPE_OPTIONS) {
                $inputs[] = [
                    'type' => CMyHtml::INPUT_RATIOBUTTONLIST,
                    'name' => $objForm->fieldName('validate_info')."[{$row->id}]",
                    'label' => $row->name,
                    'value' => $objData->getValueByValidationOptionsId($row->id),
                    'data' => $row->getValueFlagNamesArray(),
                    'htmlOptions' => ['readonly'=>false],
                ];
            }
        }
    }
}

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = [
    'action' => $action,
    'purpose' => $purpose,
    $objForm->fieldName('vehicle_id') => $vehicleId,
    $objForm->fieldName('order_id') => $orderId,
];

if ($objData && $objData->id && $action == 'update') {
    $hiddenFields['id'] = $objData->id;
}

$arrScripts = [];

$htmlArray = [];

$headerId = CMyHtml::getIDPrefix()."form_header_{$funcId}";
$printerClass = CMyHtml::getIDPrefix()."_cls_printer_{$funcId}";
$formHtmlOptions = ['enctype' => 'multipart/form-data'];
$printButtons = [];
if ($purpose == 'vehicle_dispatch' || $purpose == 'vehicle_validation') {
    $formHtmlOptions['header'] = $headerId;
    $printButtons[] = ['href'=>\yii\helpers\Url::to(['print/validation_vehicle_order', 'id'=>$orderId]), 'label'=>  \Yii::t('carrental', 'Print validation vehicle order')];
    if ($purpose == 'vehicle_validation') {
        $printButtons[] = ['href'=>\yii\helpers\Url::to(['print/settlement_vehicle_order', 'id'=>$orderId]), 'label'=>  \Yii::t('carrental', 'Print settlement vehicle order')];
    }
}

$htmlArray[] = CMyHtml::form($formTitle, \yii\helpers\Url::to(['vehicle/validation_edit']), 'post', $formHtmlOptions, $inputs, $buttons, $hiddenFields);

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
