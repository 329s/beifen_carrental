<?php

namespace common\models;

use Yii;

/**
 *
 * @property integer $id
 * @property integer $name
 * @property integer $belong_brand
 * @property integer $status
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_vehicle_maintenance_config extends \common\helpers\ActiveRecordModel
{
    const STATUS_DISABLED = 10;
    const STATUS_ENABLED = 0;
    
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
            'name' => \Yii::t('locale', 'Name'),
            'belong_brand' => Yii::t('carrental', 'Belong to brand'),
            'status' => Yii::t('locale', 'Status'),
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
            'name' => array('width' => 340, 'sortable' => 'true',
                'editor' => 'textbox'),
            'belong_brand' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.belong_brand_disp; }",
                'editor' => array(
                    'type'=>'combobox',
                    'options' => array('valueField'=>'id', 'textField'=>'text', 'editable'=>'false', 'data'=>\common\helpers\CEasyUI::convertComboboxDataToString(\common\components\VehicleModule::getVehicleBrandsArray())),
                )),
            'status' => array('width' => 80, 'sortable' => 'true',
                'formatter' => "function(value,row){ if (value == {$flagEnabled}) { return $.custom.lan.defaults.role.enabled; } else { return $.custom.lan.defaults.role.disabled; };}",
                'editor' => array(
                    'type'=>'combobox',
                    'options' => array('valueField'=>'id', 'textField'=>'text', 'editable'=>'false', 'data'=>\common\helpers\CEasyUI::convertComboboxDataToString(\common\components\VehicleModule::getCommonStatusArray())),
                )),
            'edit_user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.edit_user_disp; }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 60, 
                'buttons' => array(
                ),
            ),

            'detailed_info' => array('detailed' => true),
        );
    }
    
    public function getMaintenanceItems() {
        $cdb = \common\models\Pro_vehicle_maintenance_config_item::find();
        $cdb->where(['belong_id' => $this->id]);
        $arrRows = $cdb->all();
        $arrData = ['mileage'=>[], 'time'=>[]];
        foreach ($arrRows as $row) {
            if ($row->type == \common\models\Pro_vehicle_maintenance_config_item::CHECKPOINT_TYPE_MILEAGE) {
                $arrData['mileage'][$row->value] = $row->reference_price;
            }
            else {
                $arrData['time'][$row->value] = $row->reference_price;
            }
        }
        ksort($arrData['mileage']);
        ksort($arrData['time']);
        return $arrData;
    }
}