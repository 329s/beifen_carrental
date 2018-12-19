<?php

namespace common\models;

use Yii;

/**
 *
 * @property integer $id
 * @property integer $vehicle_id
 * @property integer $type
 * @property integer $bind_id
 * @property string $name
 * @property integer $cost_time
 * @property integer $cost_price
 * @property string $remark
 * @property string $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_vehicle_cost extends \common\helpers\ActiveRecordModel
{
    const TYPE_UPKEEP = 1;        // 保养
    const TYPE_RENEWAL = 2;        // 续保
    const TYPE_VIOLATION = 3;        // 违章
    const TYPE_MAINTENANCE = 4;        // 维修
    const TYPE_DESIGNATING = 5;        // 代驾
    const TYPE_OIL = 6;             // 加油费
    const TYPE_VEHICLE_TAX = 7;     // 车船税
    const TYPE_SWIPE_CARD = 8;           // 刷卡费
    const TYPE_INVOICE = 9;         // 发票费
    const TYPE_OTHER = 10;          // 其他
    
    
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
        $nameTitle = Yii::t('locale', 'Cost item');
        $bindIdTitle = Yii::t('locale', 'Belong item');
        $costTimeTitle = \Yii::t('locale', '{name} time', ['name'=>Yii::t('locale', 'Cost')]);
        $costPriceTitle = \Yii::t('locale', '{type} price', ['type'=>Yii::t('locale', 'Cost')]);
        if ($this->type == self::TYPE_UPKEEP) {
            $bindIdTitle = \Yii::t('carrental', 'Maintenance mileage');
            $costTimeTitle = \Yii::t('carrental', 'Maintenance time');
            $costPriceTitle = \Yii::t('carrental', 'Upkeep cost');
        }
        elseif ($this->type == self::TYPE_VIOLATION) {
            $bindIdTitle = \Yii::t('carrental', 'Violation score');
            $costTimeTitle = \Yii::t('carrental', 'Violation time');
            $costPriceTitle = \Yii::t('carrental', 'Violation penalty');
        }
        return array(
            'id' => 'ID',
            'vehicle_id' => Yii::t('locale', 'Vehicle'),
            'type' => \Yii::t('locale', '{name} type', ['name'=>Yii::t('locale', 'Cost')]),
            'bind_id' => $bindIdTitle,
            'name' => $nameTitle,
            'cost_time' => $costTimeTitle,
            'cost_price' => $costPriceTitle,
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
        $bindIdCfg = array(
            'width'=>100, 'editor'=>'numberbox',
        );
        return array(
            'id' => array('data-options' => array('checkbox'=>'true'), 'key' => true),
            'vehicle_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.plate_number; }"),
            'type' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(\common\components\VehicleModule::getVehicleExpenditureTypesArray())." }"),
            'bind_id' => $bindIdCfg,
            'name' => array('width' => 140, 'sortable' => 'true',
                'editor' => 'textbox'),
            'cost_time' => array('width' => 180, 'sortable' => 'true', 'formatter' => "function(value,row){ return $.custom.utils.humanTime(value); }",
                'editor' => array('type'=>'datetimebox','options'=>array('editable'=>'false', 'width'=>180))),
            'cost_price' => array('width' => 100, 'sortable' => 'true',
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
            'bind_id' => $this->bind_id,
            'name' => $this->name,
            'cost_time' => $this->cost_time,
            'cost_price' => $this->cost_price,
            'remark' => $this->remark,
            'edit_user_id' => $this->edit_user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
    
    public static function getTypesArray() {
        return [
            static::TYPE_UPKEEP => \Yii::t('carrental', 'Upkeep cost'),
            static::TYPE_RENEWAL => \Yii::t('carrental', 'Renewal cost'),
            static::TYPE_VIOLATION => \Yii::t('carrental', 'Violation cost'),
            static::TYPE_MAINTENANCE => \Yii::t('carrental', 'Maintenance cost'),
            static::TYPE_DESIGNATING => \Yii::t('carrental', 'Designating cost'),
            static::TYPE_OIL => \Yii::t('carrental', 'Oil cost'),
            static::TYPE_VEHICLE_TAX => \Yii::t('carrental', 'Vehicle and vessel tax'),
            static::TYPE_SWIPE_CARD => \Yii::t('carrental', 'Swipe card fee'),
            static::TYPE_INVOICE => \Yii::t('carrental', 'Invoice fee'),
            static::TYPE_OTHER => \Yii::t('carrental', 'Other fee'),
        ];
    }
    
    public function getExpenditureTime() {
        return $this->cost_time;
    }
    
    public function getExpenditureAmount() {
        return $this->cost_price;
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