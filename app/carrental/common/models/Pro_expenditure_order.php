<?php

namespace common\models;

/**
 *
 * @property integer $id
 * @property string $serial
 * @property integer $type
 * @property integer $bind_id
 * @property integer $belong_office_id
 * @property integer $sub_type
 * @property integer $amount
 * @property integer $expenditured_at
 * @property integer $status
 * @property integer $bind_param
 * @property string $extra_info
 * @property string $remark
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_expenditure_order extends \common\helpers\ActiveRecordModel
{
    const STATUS_SUCCEES = 0;

    const TYPE_VEHICLE = 7101;              // 车辆通用花费
    const TYPE_VEHICLE_OIL = 7102;          // 加油花费
    const TYPE_VEHICLE_INSURANCE = 7103;    // 投保花费
    const TYPE_VEHICLE_DESIGNATING = 7104;  // 代驾花费

    const SUB_TYPE_VEHICLE_COST_FACTOR = 7100000;
    const SUB_TYPE_VEHICLE_ORDER_BOOKING_RETURNS = 7301001;             // 退预定金
    const SUB_TYPE_VEHICLE_ORDER_RENTING_RETURNS = 7301002;             // 退预租金
    const SUB_TYPE_VEHICLE_ORDER_DEPOSIT_RETURNS = 7302001;             // 清退押金
    const SUB_TYPE_VEHICLE_ORDER_VIOLATION_DEPOSIT_RETURNS = 7302002;   // 清退违章押金
    const SUB_TYPE_VEHICLE_ORDER_SETTLEMENT_RETURNS = 7303001;          // 结算退费
    const SUB_TYPE_VEHICLE_ORDER_VIOLATION_RETURNS = 7303002;           // 违章退费

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
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
            'serial' => \Yii::t('locale', 'Expenditure No.'),
            'type' => \Yii::t('locale', '{name} type', ['name'=>\Yii::t('locale', 'Expenditure')]),
            'bind_id' => \Yii::t('locale', 'Item'),
            'belong_office_id' => \Yii::t('locale', 'Belong office'),
            'sub_type' => \Yii::t('locale', '{name} item', ['name' => \Yii::t('locale', 'Expenditure')]),
            'amount' => \Yii::t('locale', 'Amount'),
            'expenditured_at' => \Yii::t('locale', '{name} time', ['name'=>\Yii::t('locale', 'Expenditure')]),
            'status' => \Yii::t('locale', '{name} status', ['name'=>\Yii::t('locale', 'Expenditure')]),
            'bind_param' => \Yii::t('locale', 'Extra value'),
            'extra_info' => \Yii::t('locale', 'Extra info'),
            'remark' => \Yii::t('locale', 'Remark'),
            'edit_user_id' => \Yii::t('locale', 'Edit user'),
            'created_at' => \Yii::t('locale', 'Create time'),
            'updated_at' => \Yii::t('locale', 'Update time'),
            'operation' => \Yii::t('locale', 'Operation'),
        );
    }
    
    /**
     * Returns the attribute custom types.
     * Attribute custom types is used for display model data in datagrid specified display options.
     * @return array attribute custom types (name=>array('width'=>null, 'data-options'=>null, 'editor'=>null,'formatter'=>null,'sortable'=>true))
     */
    public function attributeCustomTypes()
    {
        $flagEnabled = \common\components\Consts::STATUS_ENABLED;
        return array(
            'id' => array('data-options' => array('checkbox'=>'true'), 'key' => true),
            'serial' => array('width' => 200),
            'type' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getTypesArray())." }"
            ),
            'bind_id' => array('width' => 100),
            'belong_office_id' => array('width' => 100, 'formatter' => "function(value,row){ return row.belong_office_disp; }"),
            'sub_type' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getSubTypesArray())." }"),
            'amount' => array('width' => 100),
            'expenditured_at' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'status' => array('width' => 80, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getStatusArray())." }"
            ),
            'bind_param' => array('width' => 100),
            'extra_info' => array('width' => 100),
            'remark' => array('width' => 100),
            'edit_user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.edit_user_disp; }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 160, 
                'buttons' => array(
                ),
            ),
        );
    }
    
    public static function getStatusArray() {
        return [
            static::STATUS_SUCCEES => \Yii::t('locale', 'Succeed'),
        ];
    }
    
    public static function getTypesArray() {
        return [
            static::TYPE_VEHICLE => \Yii::t('carrental', 'Vehicle common expenditure'),
            static::TYPE_VEHICLE_OIL => \Yii::t('carrental', 'Fuel fee'),
            static::TYPE_VEHICLE_INSURANCE => \Yii::t('carrental', 'Renewal cost'),
            static::TYPE_VEHICLE_DESIGNATING => \Yii::t('carrental', 'Designating cost'),
        ];
    }

    public static function getSubTypesArray() {
        $arrTypes = [];
        $arrVehicleCostTypes = \common\models\Pro_vehicle_cost::getTypesArray();
        foreach($arrVehicleCostTypes as $k => $v) {
            $arrTypes[static::SUB_TYPE_VEHICLE_COST_FACTOR + $k] = $v;
        }
        $arrTypes2 = [
            static::SUB_TYPE_VEHICLE_ORDER_BOOKING_RETURNS => \Yii::t('carrental', 'Pre-booking fee returns'),            // 退预定金
            static::SUB_TYPE_VEHICLE_ORDER_RENTING_RETURNS => \Yii::t('carrental', 'Pre-renting fee returns'),            // 退预租金
            static::SUB_TYPE_VEHICLE_ORDER_DEPOSIT_RETURNS => \Yii::t('carrental', 'Vehicle deposit returns'),             // 清退押金
            static::SUB_TYPE_VEHICLE_ORDER_VIOLATION_DEPOSIT_RETURNS => \Yii::t('carrental', 'Violation deposit returns'),   // 清退违章押金
            static::SUB_TYPE_VEHICLE_ORDER_SETTLEMENT_RETURNS => \Yii::t('carrental', 'Settlement returns'),          // 结算退费
            static::SUB_TYPE_VEHICLE_ORDER_VIOLATION_RETURNS => \Yii::t('carrental', 'Violation returns'),           // 违章退费
        ];

        return array_merge($arrTypes, $arrTypes2);
    }

    public function setSerialNo() {
        if (empty($this->serial)) {
            $id = $this->type * 100000000 + static::getAutoIncreamentId();
            
            $this->serial = 'EX'.$id;
        }
        return $this->serial;
    }
    
    public function getTypeText() {
        $arrTypes = self::getTypesArray();
        return (isset($arrTypes[$this->type]) ? $arrTypes[$this->type] : '');
    }
    
    public function getAbstract() {
        $arrTypes = self::getSubTypesArray();
        if (isset($arrTypes[$this->sub_type])) {
            return $arrTypes[$this->sub_type];
        }
        return $this->getTypeText();
    }
    
    /**
     * @inheritdoc
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function find($skipOfficeLimit = false)
    {
        if ($skipOfficeLimit) {
            return \Yii::createObject(\yii\db\ActiveQuery::className(), [get_called_class()]);
        }
        else {
            return \Yii::createObject(\common\components\OfficeLimitedActiveQuery::className(), [get_called_class(), ['attribute'=>'belong_office_id']]);
        }
    }

    public static function findByTypeAndBindId($type, $bindId) {
        $cdb = static::find(true);
        $cdb->where(['type'=>$type, 'bind_id'=>strval($bindId)]);
        return $cdb->one();
    }
    
    public static function createWithCostOrder($objCostOrder, $expenditureType, $officeId) {
        $obj = new static();
        $obj->type = $expenditureType;
        $obj->belong_office_id = $officeId;
        $obj->status = static::STATUS_SUCCEES;
        $obj->bind_param = 0;
        $obj->extra_info = '';
        
        $obj->updateWithCostOrder($objCostOrder);
        
        return $obj;
    }
    
    public function updateWithCostOrder($objCostOrder) {
        $this->bind_id = $objCostOrder->id;
        $this->expenditured_at = $objCostOrder->getExpenditureTime();
        $this->amount = $objCostOrder->getExpenditureAmount();
        if (empty($this->remark)) {
            $this->remark = $objCostOrder->getRemark();
        }
        $this->edit_user_id = \Yii::$app->user->id;
        $this->setSerialNo();
    }
    
}
