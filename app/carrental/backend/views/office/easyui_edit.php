<?php

use common\helpers\CMyHtml;

$action = 'insert';
$saveUrl = null;

if (isset($objData)) {
    if ($objData->id) {
        $action = 'update';
        $saveUrl = \yii\helpers\Url::to(['office/edit']);
    }
}
else {
    $objData = new common\models\Pro_office();
    $objData->status = \common\models\Pro_office::STATUS_NORMAL;
    $objData->isonewayoffice = \common\models\Pro_office::ONE_WAY_NO;
}

if (!$saveUrl) {
    $saveUrl = \yii\helpers\Url::to(['office/add']);
}

$objForm = new \backend\models\Form_pro_office();
$formTitle = Yii::t('locale', '{name} office', ['name' => ($action == 'update' ? \Yii::t('locale', 'Edit') : Yii::t('locale', 'Add'))]);
$idPrefix = CMyHtml::getIDPrefix();
$autoId = CMyHtml::genID();

$inputs = [
    ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => Yii::t('locale', '{name} info', ['name'=>\Yii::t('locale', 'Office')])],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('fullname'),
        'label' => $objData->getAttributeLabel('fullname'),
        'value' => $objData->fullname,
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('shortname'),
        'label' => $objData->getAttributeLabel('shortname'),
        'value' => $objData->shortname,
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('manager'),
        'label' => $objData->getAttributeLabel('manager'),
        'value' => $objData->manager,
        'htmlOptions' => [],
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('telephone'),
        'label' => $objData->getAttributeLabel('telephone'),
        'value' => $objData->telephone,
        'htmlOptions' => ['required' => true],
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('shopowner_tel'),
        'label' => $objData->getAttributeLabel('shopowner_tel'),
        'value' => $objData->shopowner_tel,
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_TIMEBOX, 'name' => $objForm->fieldName('open_time'),
        'label' => $objData->getAttributeLabel('open_time'),
        'value' => $objData->open_time,
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_TIMEBOX, 'name' => $objForm->fieldName('close_time'),
        'label' => $objData->getAttributeLabel('close_time'),
        'value' => $objData->close_time,
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('address'),
        'label' => $objData->getAttributeLabel('address'),
        'value' => $objData->address,
        'htmlOptions' => [
            'required' => true,
            'id' => "{$idPrefix}address{$autoId}",
            'onChange' => "funcOnAddressChanged{$autoId}",
        ],
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('geo_x'),
        'label' => $objData->getAttributeLabel('geo_x'),
        'value' => $objData->geo_x,
        'htmlOptions' => [
            'id' => "{$idPrefix}get_x{$autoId}",
        ],
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('geo_y'),
        'label' => $objData->getAttributeLabel('geo_y'),
        'value' => $objData->geo_y,
        'htmlOptions' => [
            'id' => "{$idPrefix}get_y{$autoId}",
            'tailhtml' => \yii\helpers\Html::button(\Yii::t('locale', 'Select coordinates'), [
                'class'=>'btn btn-default popover-toggle', 
                'title' => \Yii::t('locale', 'Select coordinates'),
                'type' => 'button',
                'id' => "{$idPrefix}gpscoordinatesbtn{$autoId}",
                'data-placement' => 'right',
                'data-toggle' => 'popover',
            ]),
        ],
    ],
    ['type' => CMyHtml::INPUT_COMBOTREE, 'name' => $objForm->fieldName('city_id'),
        'label' => $objData->getAttributeLabel('city_id'),
        'value' => $objData->city_id,
        'data' => \common\components\CityModule::getCityComboTreeData(),
        'htmlOptions' => ['required'=>true, 'editable'=>false],
    ],
    ['type' => CMyHtml::INPUT_COMBOTREE, 'name' => $objForm->fieldName('area_id'),
        'label' => $objData->getAttributeLabel('area_id'),
        'value' => $objData->area_id,
        'data' => \common\components\CityModule::getCityAreaComboTreeData(),
        'htmlOptions' => ['required'=>true, 'editable'=>false],
    ],
    ['type' => CMyHtml::INPUT_COMBOTREE, 'name' => $objForm->fieldName('parent_id'),
        'label' => $objData->getAttributeLabel('parent_id'),
        'value' => $objData->parent_id,
        'data' => \common\components\OfficeModule::getOfficeComboTreeData(['showUniversal' => \Yii::t('locale', 'None')]),
        'htmlOptions' => ['required'=>true, 'editable'=>false],
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('status'),
        'label' => $objData->getAttributeLabel('status'),
        'value' => $objData->status,
        'data' => \common\components\OfficeModule::getOfficeStatusArray(),
        'htmlOptions' => ['editable'=>false],
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('isonewayoffice'),
        'label' => $objData->getAttributeLabel('isonewayoffice'),
        'value' => $objData->isonewayoffice,
        'data' => \common\components\OfficeModule::getOfficeOneWayArray(),
        'htmlOptions' => ['editable'=>false],
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('transit_route'),
        'label' => $objData->getAttributeLabel('transit_route'),
        'value' => $objData->transit_route,
        'htmlOptions' => [],
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('landmark'),
        'label' => $objData->getAttributeLabel('landmark'),
        'value' => $objData->landmark,
        'data' => \common\components\OfficeModule::getOfficeLandmarksArray(),
        'htmlOptions' => ['editable'=>false],
    ],
];

$inputs[] = ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => \Yii::t('locale', '{name} photos', ['name'=>\Yii::t('locale', 'Office')]),
    'htmlOptions' => ['data-options'=>"collapsible:true,collapsed:false", 'style'=>"height:230px"], 
    'columnindex'=>0];
$arrImages = $objData->getImagesArray();
$curImgColIndex = 0;
foreach ($arrImages as $imgId => $imgInfo) {
    $inputs[] = [
        'type' => CMyHtml::INPUT_IMAGEFIELD,
        'name' => $objForm->fieldName('image_info')."[{$imgInfo->id}]",
        'label' => '',
        'htmlOptions' => ['readonly'=>false, 'editable'=>false, 
            'style'=>"width:200px", 'width'=>'200px', 'height'=>'140px', 
            'src'=>$imgInfo->getImageUrl(), 
            'fileSize'=>"600KB"],
        'columnindex' => $curImgColIndex++,
    ];
}
$addImgFieldName = $objForm->fieldName('image_info')."[addfiles][]";
$addImgFileIdx = 0;
if (empty($arrImages)) {
    $inputs[] = [
        'type' => CMyHtml::INPUT_IMAGEFIELD,
        'name' => $addImgFieldName,
        'label' => '',
        'htmlOptions' => ['readonly'=>false, 'editable'=>false, 
            'style'=>"width:200px", 'width'=>'200px', 'height'=>'140px', 
            'fileSize'=>"600KB",
            'src'=>''],
        'columnindex' => $curImgColIndex++,
    ];
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

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => $action];
if ($action == 'update') {
    $hiddenFields['id'] = $objData->id;
    $hiddenFields[$objForm->fieldName('id')] = $objData->id;
}

$htmlArray = [];

//$htmlArray[] = \yii\helpers\Html::jsFile("//webapi.amap.com/maps?v=1.3&key=".\Yii::$app->params['map.gaode.jsappkey']);
//$htmlArray[] = \yii\helpers\Html::jsFile("//webapi.amap.com/ui/1.0/main.js");

$htmlArray[] = CMyHtml::form($formTitle, $saveUrl, 'post', ['dialog'=>true, 'enctype' => 'multipart/form-data'], $inputs, $buttons, $hiddenFields);

$originLngLat = 'undefined';
$originAddress = $objData->address ? str_replace("'", "\\'", $objData->address) : '';
if ($objData->geo_x && $objData->geo_y) {
    $originLngLat = "[{$objData->geo_x},{$objData->geo_y}]";
}

$arrScripts = [];
$arrScripts[] = <<<EOD
var curLngLat{$autoId} = {$originLngLat};
var geocoder{$autoId};

AMap.service('AMap.Geocoder',function(){
    geocoder{$autoId} = new AMap.Geocoder({});
});

function funcSetOfficeGeoLocation{$autoId}(lnglat) {
    $('#{$idPrefix}get_x{$autoId}').textbox('setValue', lnglat.lng);
    $('#{$idPrefix}get_y{$autoId}').textbox('setValue', lnglat.lat);
    curLngLat{$autoId} = [lnglat.lng, lnglat.lat];
}

function funcOnAddressChanged{$autoId}(newValue, oldValue) {
    if (newValue != '' && (newValue != '{$originAddress}' || curLngLat{$autoId} == undefined)) {
        geocoder{$autoId}.getLocation(newValue, function(status, result) {
            if (status === 'complete' && result.info === 'OK') {
                var lnglat = result.geocodes[0].location;
                if (lnglat) {
                    curLngLat{$autoId} = [lnglat.lng, lnglat.lat];
                }
            }
        });
    }
}

function funcInitMapContainer{$autoId}(containerId) {
    $('#'+containerId).parent().parent().css('max-width', '530px');
    
    AMapUI.loadUI(['misc/PositionPicker'], function(PositionPicker) {
        var map = new AMap.Map(containerId, {
            resizeEnable: true,
            zoom: 16,
            center: curLngLat{$autoId}
        });
        
        var positionPicker = new PositionPicker({
            mode:'dragMarker',  //设定为拖拽地图模式，可选'dragMap'、'dragMarker'，默认为'dragMap'
            map:map             //依赖地图对象
        });

        positionPicker.on('success', function(positionResult) {
            var lnglat = positionResult.position;
            if (lnglat) {
                funcSetOfficeGeoLocation{$autoId}(lnglat);
            } else {
                geocoder{$autoId}.getLocation(positionResult.regeocode.formattedAddress, function(status, result) {
                    if (status === 'complete' && result.info === 'OK') {
                        var lnglat = result.geocodes[0].location;
                        if (lnglat) {
                            funcSetOfficeGeoLocation{$autoId}(lnglat);
                        }
                    }
                });
            }
        });
        positionPicker.start();
    });
}

$(function () {
    $('#{$idPrefix}gpscoordinatesbtn{$autoId}').popover({html:true, trigger:'click',
        content:'<div id=\"{$idPrefix}mapcontainer{$autoId}\" style=\"width:500px;height:300px\"></div>'
    });
    $('#{$idPrefix}gpscoordinatesbtn{$autoId}').on('shown.bs.popover', function(){ funcInitMapContainer{$autoId}('{$idPrefix}mapcontainer{$autoId}'); });
});
EOD;

$htmlArray[] = \yii\helpers\Html::script(implode("\n", $arrScripts), ['type'=>'text/javascript']);

echo implode("\n", $htmlArray);
