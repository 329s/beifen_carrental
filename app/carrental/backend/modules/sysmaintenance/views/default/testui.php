<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
echo \yii\bootstrap\Html::label("OFFICE"). \common\helpers\BootstrapHtml::combotree('test', '21', \common\components\OfficeModule::getOfficeComboTreeData(), ['style'=>'width:200px;']);

$modelData = common\models\Pro_vehicle::findById(18);

echo \common\widgets\AutoLayoutFormWidget::widget([
    'formModel' => new backend\models\Form_pro_vehicle(),
    'data' => $modelData,
    'columnCount' => 2,
    'attributes' => [
        ['type'=>\common\helpers\InputTypesHelper::TYPE_GROUP, 'label'=>\Yii::t('locale', '{name} info', ['name' => \Yii::t('locale', 'Vehicle')])],
        'id',
        'plate_number',
        \common\helpers\InputTypesHelper::TYPE_NOP,
        'model_id',
        'status',
        'engine_number',
        'vehicle_number',
        'certificate_number',
        'color',
        'baught_price',
        'baught_tax',
        'baught_time',
        'baught_insurance',
        'decoration_fee',
        'license_plate_fee',
        'baught_kilometers',
        'cur_kilometers',
        'belong_office_id',
        'stop_office_id',
        'vehicle_property',
        'gps_id',
        'remark',
        ['type' => \common\helpers\InputTypesHelper::TYPE_GROUP, 'label' => \Yii::t('locale', '{name} info', ['name' => \Yii::t('locale', 'Upkeep')])],
        'annual_inspection_time',
        'tci_renewal_time',
        'vci_renewal_time',
        'upkeep_mileage_interval',
        'upkeep_time_interval',
        'next_upkeep_mileage',
        'next_upkeep_time',
        ['type' => \common\helpers\InputTypesHelper::TYPE_GROUP, 'label' => \Yii::t('locale', '{name} info', ['name' => \Yii::t('locale', 'Image')])],
        'vehicle_image',
        'certificate_image',
        'validation_id',
    ],
    'attributeTypes' => [
        'id' => \common\helpers\InputTypesHelper::TYPE_HIDDEN,
        'model_id' => ['type'=>\common\helpers\InputTypesHelper::TYPE_DROPDOWN_LIST, 'data'=> \common\components\VehicleModule::getVehicleModelNamesArray()],
        'belong_office_id,stop_office_id' => ['type'=> \common\helpers\InputTypesHelper::TYPE_DROPDOWN_TREE, 'data'=> \common\components\OfficeModule::getOfficeComboTreeData()],
        'baught_time,annual_inspection_time,tci_renewal_time,vci_renewal_time,next_upkeep_mileage' => \common\helpers\InputTypesHelper::TYPE_DATE,
        'vehicle_image,certificate_image' => \common\helpers\InputTypesHelper::TYPE_IMAGE,
        'validation_id' => \common\helpers\InputTypesHelper::TYPE_HIDDEN,
    ],
    'hiddenValues' => [
        'id' => $modelData->id,
    ]
]);
