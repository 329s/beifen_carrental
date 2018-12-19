<?php

namespace common\models;

use Yii;

/**
 *
 * @property integer $id
 * @property string $name
 * @property integer $type
 * @property integer $belong_id
 * @property integer $status
 * @property integer $value_flag
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_vehicle_validation_config extends \common\helpers\ActiveRecordModel
{
    const STATUS_DISABLED = 10;
    const STATUS_ENABLED = 0;
    
    const TYPE_ROOT = 0;
    const TYPE_OPTIONS= 1;
    const TYPE_IMAGES = 2;
    
    const VALUE_FLAG_NONE = 0x01;
    const VALUE_FLAG_GOOD = 0x02;
    const VALUE_FLAG_BROKEN = 0x04;
    const VALUE_FLAG_LOST = 0x08;
    
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
            'name' => Yii::t('locale', 'Name'),
            'type' => \Yii::t('locale', 'Type'),
            'belong_id' => Yii::t('locale', 'Belong item'),
            'status' => Yii::t('locale', 'Status'),
            'value_flag' => Yii::t('locale', 'Opening flags'),
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
            'id' => array('data-options' => array('checkbox'=>'true'), 'key' => true, 'sortable' => 'true'),
            'name' => array('width' => 160, 'sortable' => 'true'),
            'type' => array('width' => 100, 'sortable' => 'true'),
            'belong_id' => array('width' => 100, 'sortable' => 'true'),
            'value_flag' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.value_flag_disp; }"),
            'status' => array('width' => 80, 'sortable' => 'true',
                'formatter' => "function(value,row){ if (value == {$flagEnabled}) { return $.custom.lan.defaults.role.enabled; } else { return $.custom.lan.defaults.role.disabled; };}"),
            'edit_user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.edit_user_disp; }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 90, 
                'buttons' => array(
                    \Yii::$app->user->can('options/vehicle_validation_options_add') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['options/vehicle_validation_options_add', 'belong_id'=>'']), 'condition'=>array("{field} == 0", array('{field}'=>'belong_id')), 'name' => Yii::t('carrental', '{name} vehicle validation info', ['name' => Yii::t('locale', 'Add')]), 'title' => Yii::t('carrental', '{name} vehicle validation info', ['name' => Yii::t('locale', 'Add')]), 'paramField' => 'id', 'icon' => 'icon-add') : null,
                    \Yii::$app->user->can('options/vehicle_validation_options_add') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['options/vehicle_validation_options_add', 'belong_id'=>'']), 'condition'=>array("{field} > 0", array('{field}'=>'belong_id')), 'name' => Yii::t('carrental', '{name} vehicle validation info', ['name' => Yii::t('locale', 'Add')]), 'title' => Yii::t('carrental', '{name} vehicle validation info', ['name' => Yii::t('locale', 'Add')]), 'paramField' => 'belong_id', 'icon' => 'icon-add') : null,
                    \Yii::$app->user->can('options/vehicle_validation_options_edit') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['options/vehicle_validation_options_edit', 'id'=>'']), 'name' => Yii::t('carrental', '{name} vehicle validation info', ['name' => Yii::t('locale', 'Edit')]), 'title' => Yii::t('carrental', '{name} vehicle validation info', ['name' => Yii::t('locale', 'Edit')]), 'paramField' => 'id', 'icon' => 'icon-edit') : null,
                    \Yii::$app->user->can('options/vehicle_validation_options_delete') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['options/vehicle_validation_options_delete', 'id'=>'']), 'name' => Yii::t('locale', 'Delete'), 'title' => Yii::t('locale', 'Are you sure to delete these records?'), 'paramField' => 'id', 'icon' => 'icon-delete') : null,
                ),
            ),
            'detailed_info' => array('detailed' => true, ),
        );
    }
    
    public static function getValueFlagsArray() {
        return [
            \common\models\Pro_vehicle_validation_config::VALUE_FLAG_NONE => \Yii::t('carrental', 'None'),
            \common\models\Pro_vehicle_validation_config::VALUE_FLAG_GOOD => \Yii::t('carrental', 'Good'),
            \common\models\Pro_vehicle_validation_config::VALUE_FLAG_BROKEN => \Yii::t('carrental', 'Broken'),
            \common\models\Pro_vehicle_validation_config::VALUE_FLAG_LOST => \Yii::t('carrental', 'Lost'),
        ];
    }
    
    public function getValueFlagNamesArray() {
        $arrAllNames = \common\components\VehicleModule::getVehicleValidationOptionsValueFlagsArray();
        $arrData = [];
        foreach ($arrAllNames as $k => $v) {
            if (($this->value_flag & $k) > 0) {
                $arrData[$k] = $v;
            }
        }
        return $arrData;
    }
    
}
