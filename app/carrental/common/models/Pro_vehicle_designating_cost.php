<?php

namespace common\models;

use Yii;

/**
 *
 * @property integer $id
 * @property integer $vehicle_id
 * @property integer $type
 * @property integer $time
 * @property string $driver
 * @property integer $driver_fee
 * @property integer $road_fee
 * @property integer $parking_fee
 * @property integer $fuel_fee
 * @property string $remark
 * @property string $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_vehicle_designating_cost extends \common\helpers\ActiveRecordModel
{
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
            \common\helpers\behaviors\EditorBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
        ];
    }

    /**
     * Returns the attribute labels.
     * Attribute labels are mainly used in error messages of validation.
     * By default an attribute label is generated using {@link generateAttributeLabel}.
     * This method allows you to explicitly specify attribute labels.
     *
     * Note, in order to inherit labels defined in the parent class, a child class needs to
     * merge the parent labels with child labels using functions like array_merge().
     *
     * @return array attribute labels (name=>label)
     * @see generateAttributeLabel
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'vehicle_id' => Yii::t('locale', 'Vehicle'),
            'type' => \Yii::t('locale', '{name} type', ['name'=>Yii::t('locale', 'Cost')]),
            'time' => Yii::t('locale', '{name} time', ['name'=>Yii::t('locale', 'Cost')]),
            'driver' => Yii::t('carrental', 'Driver'),
            'driver_fee' => Yii::t('carrental', 'Driver fee'),
            'road_fee' => \Yii::t('carrental', 'Road fee'),
            'parking_fee' => Yii::t('carrental', 'Parking fee'),
            'fuel_fee' => Yii::t('carrental', 'Fuel fee'),
            'remark' => Yii::t('locale', 'Remark'),
            'edit_user_id' => Yii::t('locale', 'Edit user'),
            'created_at' => Yii::t('locale', 'Create time'),
            'updated_at' => Yii::t('locale', 'Update time'),
            'operation' => Yii::t('locale', 'Operation'),
        );
    }
    
    /**
     * Returns the attribute custom types.
     * Attribute custom types is used for display model data in datagrid specified display options.
     * @return array attribute custom types (name=>array('width'=>null, 'data-options'=>null, 'editor'=>null,'formatter'=>null,'sortable'=>true))
     */
    public function attributeCustomTypes()
    {
        return array(
            'id' => array('data-options' => array('checkbox'=>'true'), 'key' => true),
            'vehicle_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.plate_number; }"),
            'type' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(\common\components\VehicleModule::getVehicleExpenditureTypesArray())." }"),
            'time' => array('width' => 180, 'sortable' => 'true', 'formatter' => "function(value,row){ return $.custom.utils.humanTime(value); }",
                'editor' => array('type'=>'datetimebox','options'=>array('editable'=>'false'))),
            'driver' => array('width' => 100,
                'editor' => 'textbox'),
            'driver_fee' => array('width' => 100,
                'editor' => 'numberbox'),
            'road_fee' => array('width' => 100,
                'editor' => 'numberbox'),
            'parking_fee' => array('width' => 100,
                'editor' => 'numberbox'),
            'fuel_fee' => array('width' => 100,
                'editor' => 'numberbox'),
            'remark' => array('width' => 180,
                'editor' => 'textarea'),
            'edit_user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.edit_user_disp; }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 60, 
                'buttons' => array(
                    \Yii::$app->user->can('vehicle/expenditure_edit') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['vehicle/expenditure_edit', 'id'=>'']), 'name' => Yii::t('locale', 'Edit'), 'title' => Yii::t('locale', 'Edit'), 'paramField' => 'id', 'icon' => 'icon-edit') : null,
                    \Yii::$app->user->can('vehicle/expenditure_delete') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['vehicle/expenditure_delete', 'id'=>'']), 'name' => Yii::t('locale', 'Delete'), 'title' => Yii::t('locale', 'Are you sure to delete these records?'), 'paramField' => 'id', 'icon' => 'icon-delete') : null,
                ),
            ),
        );
    }
    
    public function getAttributeValues()
    {
        return [
            'id' => $this->id,
            'vehicle_id' => $this->vehicle_id,
            'type' => $this->type,
            'time' => $this->time,
            'driver' => $this->driver,
            'driver_fee' => $this->driver_fee,
            'road_fee' => $this->road_fee,
            'parking_fee' => $this->parking_fee,
            'fuel_fee' => $this->fuel_fee,
            'remark' => $this->remark,
            'edit_user_id' => $this->edit_user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
    
    public function getExpenditureTime() {
        return $this->time;
    }
    
    public function getExpenditureAmount() {
        return $this->driver_fee + $this->road_fee + $this->parking_fee + $this->fuel_fee;
    }
    
    public function getRemark() {
        return $this->remark;
    }
    
    public function getVehicleBelongOfficeId() {
        $objVehicle = Pro_vehicle::findById($this->vehicle_id);
        if ($objVehicle) {
            return $objVehicle->belong_office_id;
        }
        return 0;
    }
    
}
