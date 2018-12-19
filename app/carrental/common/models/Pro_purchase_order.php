<?php

namespace common\models;

/**
 *
 * @property integer $id
 * @property string $serial
 * @property integer $user_id
 * @property integer $type
 * @property integer $bind_id
 * @property integer $channel_type
 * @property string $channel_trade_no
 * @property integer $belong_office_id
 * @property integer $pay_source              支付方式
 * @property integer $sub_type
 * @property integer $amount
 * @property integer $receipt_amount
 * @property integer $purchased_at
 * @property integer $status
 * @property integer $bind_param
 * @property string $extra_info
 * @property integer $purchase_code
 * @property string $purchase_msg
 * @property integer $tried_count
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_purchase_order extends \common\helpers\ActiveRecordModel
{
    
    const TYPE_VEHICLE_ORDER = 5101;    // 车辆订单
    const TYPE_VEHICLE_RELET = 5102;    // 续租订单
    const TYPE_VIOLATION = 5301;        // 违章缴费
    
    const STATUS_SUCCEES = 200;         // 支付成功 
    const STATUS_REFUNDED = 300;        // 已退款 
    const STATUS_DELETED = 400;         // 已删除 
    const STATUS_FAILED = 1;

    const CHANNEL_TYPE_ALIPAY = 101;
    const CHANNEL_TYPE_WXPAY = 102;
    const CHANNEL_TYPE_KUAIQIAN = 103;
    const CHANNEL_TYPE_APPSTORE = 201;
    const CHANNEL_TYPE_OFFICE = 301;

    const SUB_TYPE_VEHICLE_ORDER_BOOK = 5101001;    // 租车定金
    const SUB_TYPE_VEHICLE_ORDER_RENT = 5101002;    // 租车租金
    const SUB_TYPE_VEHICLE_ORDER_RENT_RENEWAL = 5101003;  // 租车续交租金
    const SUB_TYPE_VEHICLE_ORDER_DEPOSIT = 5101005; // 租车押金
    const SUB_TYPE_VEHICLE_ORDER_RETURN = 5101007;  // 还车收费
    const SUB_TYPE_VEHICLE_ORDER_SETTLEMENT = 5101008;  // 租车结算收费
    const SUB_TYPE_VEHICLE_ORDER_OPTIONAL_SERVICE = 5101009;    // 增值服务费 
    const SUB_TYPE_VEHICLE_RELET = 5102001;         // 续租租金
    const SUB_TYPE_VEHICLE_VIOLATION = 5301001;     // 违章罚款收费
    const SUB_TYPE_VEHICLE_DAMAGE = 5301002;        // 车损费 
    const SUB_TYPE_VEHICLE_OIL = 5301003;           // 油费 
    const SUB_TYPE_VEHICLE_ACCESSORIES = 5301004;   // 配件费 
    const SUB_TYPE_DELAY_WASTE_WORKER = 5501001;    // 误工费 
    const SUB_TYPE_SERVICE = 5502001;               // 服务费 
    const SUB_TYPE_POUNDAGE = 5502002;              // 代办费 

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
            'serial' => \Yii::t('locale', '{name} No.', ['name'=>\Yii::t('locale', 'Order')]),
            'user_id' => \Yii::t('locale', 'User'),
            'type' => \Yii::t('locale', '{name} type', ['name'=>\Yii::t('locale', 'Order')]),
            'bind_id' => \Yii::t('carrental', 'Car rental order'),
            'channel_type' => \Yii::t('locale', 'Payment channel type'),
            'channel_trade_no' => \Yii::t('locale', 'Payment channel trade no'),
            'belong_office_id' => \Yii::t('locale', 'Belong office'),
            'pay_source' => \Yii::t('locale', '{name} payment method', ['name' => \Yii::t('locale', 'Order')]),
            'sub_type' => \Yii::t('locale', '{name} item', ['name' => \Yii::t('locale', 'Charge')]),
            'amount' => \Yii::t('locale', 'Amount'),
            'receipt_amount' => \Yii::t('locale', 'Payment amount'),
            'purchased_at' => \Yii::t('locale', 'Payment time'),
            'status' => \Yii::t('locale', '{name} status', ['name'=>\Yii::t('locale', 'Order')]),
            'bind_param' => \Yii::t('locale', 'Extra value'),
            'extra_info' => \Yii::t('locale', 'Extra info'),
            'purchase_code' => \Yii::t('locale', 'Purchase code'),
            'purchase_msg' => \Yii::t('locale', 'Purchase message'),
            'tried_count' => \Yii::t('locale', 'Tried times'),
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
            'serial' => array('width' => 120),
            'user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.user_disp; }"),
            'type' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getTypesArray())." }"
            ),
            'bind_id' => array('width' => 120, 'formatter' => "function(value,row){ return row.order_serial; }"),
            'channel_type' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getChannelTypesArray())." }"
            ),
            'channel_trade_no' => array('width' => 200),
            'belong_office_id' => array('width' => 100, 'formatter' => "function(value,row){ return row.belong_office_disp; }"),
            'pay_source' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(\common\components\OrderModule::getOrderPayTypeArray())." }"),
            'sub_type' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(\common\components\OrderModule::getOrderPayTypeArray())." }"),
            'amount' => array('width' => 100),
            'receipt_amount' => array('width' => 100),
            'purchased_at' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'status' => array('width' => 80, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getStatusArray())." }"
            ),
            'bind_param' => array('width' => 100),
            'extra_info' => array('width' => 100),
            'purchase_code' => array('width' => 100),
            'purchase_msg' => array('width' => 300),
            'tried_count' => array('width' => 100),
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
            static::STATUS_FAILED => \Yii::t('locale', 'Failed'),
            static::STATUS_REFUNDED => \Yii::t('locale', 'Refounded'),
            static::STATUS_DELETED => \Yii::t('locale', 'Deleted'),
        ];
    }
    
    public static function getTypesArray() {
        return [
            static::TYPE_VEHICLE_ORDER => \Yii::t('carrental', 'Rental order'),
            static::TYPE_VEHICLE_RELET => \Yii::t('carrental', 'Relet order'),
            static::TYPE_VIOLATION => \Yii::t('carrental', 'Violation penalty order'),
        ];
    }

    public static function getChannelTypesArray() {
        return [
            static::CHANNEL_TYPE_ALIPAY => \Yii::t('locale', 'Alipay'),
            static::CHANNEL_TYPE_WXPAY => \Yii::t('locale', 'Weixin pay'),
            static::CHANNEL_TYPE_APPSTORE => \Yii::t('locale', 'Appstore'),
            static::CHANNEL_TYPE_KUAIQIAN => \Yii::t('locale', 'Kuaiqian'),
        ];
    }
    
    public static function getSubTypesArray() {
        return [
            static::SUB_TYPE_VEHICLE_ORDER_BOOK => \Yii::t('carrental', 'Prepaid deposit'),
            static::SUB_TYPE_VEHICLE_ORDER_RENT => \Yii::t('carrental', 'Prepaid rent'),
            static::SUB_TYPE_VEHICLE_ORDER_RENT_RENEWAL => \Yii::t('carrental', 'Renewal rent'),
            static::SUB_TYPE_VEHICLE_ORDER_DEPOSIT => \Yii::t('carrental', 'Vehicle rent deposit'),
            static::SUB_TYPE_VEHICLE_ORDER_RETURN => \Yii::t('carrental', 'Return car charge'),
            static::SUB_TYPE_VEHICLE_ORDER_SETTLEMENT => \Yii::t('carrental', 'Settlement charge'),
            static::SUB_TYPE_VEHICLE_RELET => \Yii::t('carrental', 'Relet rent'),
            static::SUB_TYPE_VEHICLE_VIOLATION => \Yii::t('carrental', 'Violation charge'),
        ];
    }

    public function setSerialNo() {
        if (empty($this->serial)) {
            $id = $this->type * 100000000 + $this->channel_type * 100000 + static::getAutoIncreamentId();
            
            $this->serial = \common\components\Consts::PURCHANSE_TRADE_NO_PREFIX.$id;
        }
        return $this->serial;
    }
    
    public function getTypeText() {
        $arrTypes = static::getTypesArray();
        return (isset($arrTypes[$this->type]) ? $arrTypes[$this->type] : '');
    }
    
    public function getAbstract() {
        $arrSubTypes = static::getSubTypesArray();
        if (isset($arrSubTypes[$this->sub_type])) {
            return $arrSubTypes[$this->sub_type];
        }
        $arrTypes = static::getTypesArray();
        return \Yii::t('locale', '{name} payment', ['name'=>(isset($arrTypes[$this->type]) ? $arrTypes[$this->type] : '')]);
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

    public static function findByPaymentChannelTradeNo($channelType, $tradeNo) {
        $cdb = static::find(true);
        $cdb->where(['channel_type'=>intval($channelType), 'channel_trade_no'=>strval($tradeNo)]);
        return $cdb->one();
    }
    

    /*快钱支付*/
    public static function createWithVehicleOrderMNP($objVehicleOrder, $amount, $officeId, $subType, $purchaseTime = 0)
    {
        $obj = new static();
        $obj->user_id = $objVehicleOrder->user_id;
        $obj->type = static::TYPE_VEHICLE_ORDER;
        $obj->bind_id = $objVehicleOrder->id;
        $obj->channel_type = static::CHANNEL_TYPE_KUAIQIAN;
        $obj->belong_office_id = $officeId;
        $obj->pay_source = ($subType == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_DEPOSIT ? $objVehicleOrder->deposit_pay_source : $objVehicleOrder->pay_source);
        $obj->amount = $amount;
        $obj->receipt_amount = $amount;
        $obj->purchased_at = ($purchaseTime ? $purchaseTime : time());
        $obj->status = static::STATUS_SUCCEES;
        $obj->bind_param = 0;
        $obj->extra_info = '';
        $obj->purchase_code = 0;
        $obj->purchase_msg = '';
        $obj->tried_count = 1;
        $obj->edit_user_id = $objVehicleOrder->edit_user_id;
        $obj->setSerialNo();
        $obj->channel_trade_no = $obj->serial;
        
        if ($subType) {
            $obj->sub_type = $subType;
        }
        else {
            $obj->sub_type = static::SUB_TYPE_VEHICLE_ORDER_RENT;
        }
        
        return $obj;
    }
    public static function createWithVehicleOrder($objVehicleOrder, $amount, $officeId, $subType, $purchaseTime = 0) {
        $obj = new static();
        $obj->user_id = $objVehicleOrder->user_id;
        $obj->type = static::TYPE_VEHICLE_ORDER;
        $obj->bind_id = $objVehicleOrder->id;
        $obj->channel_type = static::CHANNEL_TYPE_OFFICE;
        $obj->belong_office_id = $officeId;
        $obj->pay_source = ($subType == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_DEPOSIT ? $objVehicleOrder->deposit_pay_source : $objVehicleOrder->pay_source);
        $obj->amount = $amount;
        $obj->receipt_amount = $amount;
        $obj->purchased_at = ($purchaseTime ? $purchaseTime : time());
        $obj->status = static::STATUS_SUCCEES;
        $obj->bind_param = 0;
        $obj->extra_info = '';
        $obj->purchase_code = 0;
        $obj->purchase_msg = '';
        $obj->tried_count = 1;
        $obj->edit_user_id = \Yii::$app->user->id;
        $obj->setSerialNo();
        $obj->channel_trade_no = $obj->serial;
        
        if ($subType) {
            $obj->sub_type = $subType;
        }
        else {
            $obj->sub_type = static::SUB_TYPE_VEHICLE_ORDER_RENT;
        }
        
        return $obj;
    }
    
    public static function createWithVehicleRelet($objVehicleOrder, $objReletOrder, $amount, $officeId, $subType = 0, $purchaseTime = 0) {
        $obj = new static();
        $obj->user_id = $objVehicleOrder->user_id;
        $obj->type = static::TYPE_VEHICLE_RELET;
        $obj->bind_id = $objVehicleOrder->id;
        $obj->channel_type = static::CHANNEL_TYPE_OFFICE;
        $obj->belong_office_id = $officeId;
        $obj->pay_source = $objVehicleOrder->pay_source;
        $obj->amount = $amount;
        $obj->receipt_amount = $amount;
        $obj->purchased_at = ($purchaseTime ? $purchaseTime : time());
        $obj->status = static::STATUS_SUCCEES;
        $obj->bind_param = 0;
        $obj->extra_info = '';
        $obj->purchase_code = 0;
        $obj->purchase_msg = '';
        $obj->tried_count = 1;
        $obj->edit_user_id = \Yii::$app->user->id;
        $obj->setSerialNo();
        $obj->channel_trade_no = $obj->serial;
        if ($subType) {
            $obj->sub_type = $subType;
        }
        else {
            $obj->sub_type = static::SUB_TYPE_VEHICLE_RELET;
        }
        
        return $obj;
    }


    /**
     *@author sjj
     *@desc PC端支付宝支付成功后添加的支付记录
     */
    public static function createWithVehiclePcOrder($objVehicleOrder, $amount, $officeId, $subType, $purchaseTime = 0) {
        $obj = new static();
        $obj->user_id = $objVehicleOrder->user_id;
        $obj->type = static::TYPE_VEHICLE_ORDER;
        $obj->bind_id = $objVehicleOrder->id;
        $obj->channel_type = static::CHANNEL_TYPE_ALIPAY;
        $obj->belong_office_id = $officeId;
        $obj->pay_source = ($subType == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_DEPOSIT ? $objVehicleOrder->deposit_pay_source : $objVehicleOrder->pay_source);
        $obj->amount = $amount;
        $obj->receipt_amount = $amount;
        $obj->purchased_at = ($purchaseTime ? $purchaseTime : time());
        $obj->status = static::STATUS_SUCCEES;
        $obj->bind_param = 0;
        $obj->extra_info = '';
        $obj->purchase_code = 0;
        $obj->purchase_msg = 'TRADE_SUCCESS';
        $obj->tried_count = 1;
        $obj->edit_user_id = \Yii::$app->user->id;
        $obj->setSerialNo();
        $obj->channel_trade_no = $objVehicleOrder->serial;
        
        if ($subType) {
            $obj->sub_type = $subType;
        }
        else {
            $obj->sub_type = static::SUB_TYPE_VEHICLE_ORDER_RENT;
        }
        
        return $obj;
    }
}