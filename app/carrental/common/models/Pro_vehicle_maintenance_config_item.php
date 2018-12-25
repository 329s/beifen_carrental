<?php

namespace common\models;

use Yii;

/**
 *
 * @property integer $id
 * @property integer $belong_id
 * @property integer $type
 * @property integer $value
 * @property integer $status
 * @property integer $reference_price
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_vehicle_maintenance_config_item extends \common\helpers\ActiveRecordModel
{
    const STATUS_DISABLED = 10;
    const STATUS_ENABLED = 0;
    
    const CHECKPOINT_TYPE_MILEAGE = 1;
    const CHECKPOINT_TYPE_TIME = 2;

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
            'belong_id' => Yii::t('carrental', 'Belong to maintenance list'),
            'type' => \Yii::t('carrental', 'Maintenance type'),
            'value' => Yii::t('carrental', 'Maintenance point'),
            'status' => Yii::t('locale', 'Status'),
            'reference_price' => Yii::t('locale', 'Reference price'),
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
        $flagEnabled = self::STATUS_ENABLED;
        return array(
            'id' => array('data-options' => array('checkbox'=>'true'), 'key' => true),
            'belong_id' => array('width' => 100, 'sortable' => 'true'),
            'type' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.type_disp; }",
                'editor' => array(
                    'type'=>'combobox',
                    'options' => array('valueField'=>'id', 'textField'=>'text', 'editable'=>'false', 'data'=>\common\helpers\CEasyUI::convertComboboxDataToString(\common\components\VehicleModule::getVehicleMaintenanceCheckPointTypesArray())),
                )),
            'value' => array('width' => 100, 'sortable' => 'true',
                'editor' => 'textbox'),
            'status' => array('width' => 80, 'sortable' => 'true',
                'formatter' => "function(value,row){ if (value == {$flagEnabled}) { return $.custom.lan.defaults.role.enabled; } else { return $.custom.lan.defaults.role.disabled; };}",
                'editor' => array(
                    'type'=>'combobox',
                    'options' => array('valueField'=>'id', 'textField'=>'text', 'editable'=>'false', 'data'=>\common\helpers\CEasyUI::convertComboboxDataToString(\common\components\VehicleModule::getCommonStatusArray())),
                )),
            'reference_price' => array('width' => 100, 'sortable' => 'true',
                'editor' => 'numberbox'),
            'edit_user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.edit_user_disp; }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 60, 
                'buttons' => array(
                ),
            ),
        );
    }
    
    public function getCheckPointText() {
        if ($this->type == self::CHECKPOINT_TYPE_MILEAGE) {
            return $this->value . \Yii::t('carrental', 'Kilometers');
        }
        else {
            return $this->value;    // TODO
        }
    }
    
}
