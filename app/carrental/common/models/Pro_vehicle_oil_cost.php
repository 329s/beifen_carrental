<?php

namespace common\models;

use Yii;

/**
 *
 * @property integer $id
 * @property integer $vehicle_id
 * @property integer $type
 * @property integer $time
 * @property integer $oil_label
 * @property integer $oil_volume
 * @property integer $amount
 * @property integer $pay_type
 * @property string $purpose
 * @property integer $mileage
 * @property string $oil_tanker
 * @property string $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_vehicle_oil_cost extends \common\helpers\ActiveRecordModel
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
            'time' => Yii::t('carrental', 'Fuel time'),
            'oil_label' => \Yii::t('locale', 'Oil label'),
            'oil_volume' => Yii::t('carrental', 'Oil volume'),
            'amount' => Yii::t('locale', 'Amount'),
            'pay_type' => Yii::t('locale', 'Payment method'),
            'purpose' => Yii::t('carrental', 'Fuel purpose'),
            'mileage' => Yii::t('carrental', 'Mileage'),
            'oil_tanker' => Yii::t('carrental', 'Fuel tanker'),
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
            'time' => array('width' => 180, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}",
                'editor' => array('type'=>'datetimebox','options'=>array('editable'=>'false'))),
            'oil_label' => array('width' => 100,
                'editor' => 'numberbox'
            ),
            'oil_volume' => array('width' => 100,
                'editor' => 'numberbox'
            ),
            'amount' => array('width' => 100,
                'editor' => 'numberbox'
            ),
            'pay_type' => array('width' => 100, 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(\common\components\OrderModule::getOrderPayTypeArray())." }",
                'editor' => array(
                    'type'=>'combobox',
                    'options' => array('valueField'=>'id', 'textField'=>'text', 'editable'=>'false', 'data'=>\common\helpers\CEasyUI::convertComboboxDataToString(\common\components\OrderModule::getOrderPayTypeArray())),
                )
            ),
            'purpose' => array('width' => 140,
                'editor' => 'textbox'),
            'mileage' => array('width' => 100,
                'editor' => 'numberbox'),
            'oil_tanker' => array('width' => 100,
                'editor' => 'textbox'),
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
            'oil_label' => $this->oil_label,
            'oil_volume' => $this->oil_volume,
            'amount' => $this->amount,
            'pay_type' => $this->pay_type,
            'purpose' => $this->purpose,
            'mileage' => $this->mileage,
            'oil_tanker' => $this->oil_tanker,
            'edit_user_id' => $this->edit_user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
    
    public function getExpenditureTime() {
        return $this->time;
    }
    
    public function getExpenditureAmount() {
        return $this->amount;
    }
    
    public function getRemark() {
        return $this->purpose;
    }
    
    public function getVehicleBelongOfficeId() {
        $objVehicle = Pro_vehicle::findById($this->vehicle_id);
        if ($objVehicle) {
            return $objVehicle->belong_office_id;
        }
        return 0;
    }
    
}
