<?php

namespace common\models;

/**
 * Order price detail
 * @property integer $id
 * @property integer $order_id                  订单号
 * @property string $serial                     唯一序列号标记
 * @property integer $type                      类型（1:应缴 2:已缴）
 * @property integer $belong_office_id
 * @property integer $status                    状态
 * @property integer $relet_mark                  续租ID
 * @property integer $pay_source                支付方式
 * @property integer $deposit_pay_source        押金支付方式
 * @property integer $summary_amount            合计非押金金额
 * @property integer $summary_deposit           合计押金金额
 * @property integer $price_rent                租金费用
 * @property integer $price_overtime            超时费用
 * @property integer $price_overmileage         超超里程费用
 * @property integer $price_designated_driving  代驾费用
 * @property integer $price_designated_driving_overtime  代驾超时费用
 * @property integer $price_designated_driving_overmileage  代驾超里程费用
 * @property integer $price_oil                 加油费用
 * @property integer $price_oil_agency          加油代办
 * @property integer $price_car_damage          车损费用
 * @property integer $price_violation           违章费用
 * @property integer $price_poundage            基本手续费
 * @property integer $price_basic_insurance     基本服务费
 * @property integer $price_deposit             押金
 * @property integer $price_deposit_violation   违章押金
 * @property integer $price_optional_service    增值服务合计金额
 * @property integer $price_insurance_overtime  超时保费
 * @property integer $price_different_office    异店还车费用
 * @property integer $price_take_car            送车上门服务费
 * @property integer $price_return_car          上门取车服务费
 * @property integer $price_working_loss        误工费
 * @property integer $price_accessories         配件费
 * @property integer $price_agency              代办费
 * @property integer $price_other               其他费用
 * @property integer $time                      发生时间
 * @property string $remark                     备注
 * @property integer $edit_user_id              登记者管理员ID
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_vehicle_order_price_detail extends \common\helpers\ActiveRecordModel
{
    const STATUS_NORMAL = 0;
    const STATUS_REFUNDED = 300;
    const STATUS_CANCELED = 400;
    const STATUS_DISABLED = -10;
    
    const TYPE_SHOULD_PAY = 1;
    const TYPE_PAID = 2;
    
	
	
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
            ['serial', 'filter', 'filter' => 'trim'],
            ['serial', 'required'],
            //['serial', 'unique', 'targetClass' => '\common\models\Pro_vehicle_order_price_detail', 'filter'=>['<>', 'id', $this->id]],
            ['serial', 'string', 'min' => 2, 'max' => 64],

            ['status', 'required'],
            ['status', 'integer'],
            ['status', 'in', 'range' => [static::STATUS_NORMAL, static::STATUS_DISABLED]],
            
            ['type', 'required'],
            ['type', 'integer'],
            ['type', 'in', 'range' => [static::TYPE_SHOULD_PAY, static::TYPE_PAID]],
            
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
            'order_id' => \Yii::t('locale', '{name} No.', ['name'=>\Yii::t('locale', 'Order')]),
            'serial' => \Yii::t('locale', 'Serial No.'),
            'type' => \Yii::t('locale', 'Type'),
            'belong_office_id' => \Yii::t('carrental', 'Renting office'),
            'status' => \Yii::t('locale', 'Status'),
            'relet_mark' => \Yii::t('locale', 'Relet'),
            'pay_source' => \Yii::t('locale', '{name} payment method', ['name' => \Yii::t('locale', 'Rent')]),
            'deposit_pay_source' => \Yii::t('locale', '{name} payment method', ['name' => \Yii::t('locale', 'Deposit')]),
            'summary_amount' => \Yii::t('locale', 'Summary amount'),
            'summary_deposit' => \Yii::t('locale', 'Summary deposit'),
            'price_rent' => \Yii::t('locale', '{type} price', ['type'=>\Yii::t('locale', 'Rent')]),
            'price_left' => \Yii::t('locale', 'Left rent price'),
            'price_overtime' => \Yii::t('locale', '{type} overtime price', ['type'=>\Yii::t('locale', 'Personal driving')]),
            'price_overmileage' => \Yii::t('locale', '{type} overmileage price', ['type'=>\Yii::t('locale', 'Personal driving')]),
            'price_designated_driving' => \Yii::t('locale', '{type} price', ['type'=>\Yii::t('locale', 'Designated driving')]),
            'price_designated_driving_overtime' => \Yii::t('locale', '{type} overtime price', ['type'=>\Yii::t('locale', 'Designated driving')]),
            'price_designated_driving_overmileage' => \Yii::t('locale', '{type} overmileage price', ['type'=>\Yii::t('locale', 'Designated driving')]),
            'price_oil' => \Yii::t('carrental', 'Fuel cost'),
            'price_oil_agency' => \Yii::t('locale', '{type} price', ['type'=>\Yii::t('carrental', 'Fueling agent')]),
            'price_car_damage' => \Yii::t('locale', '{type} price', ['type'=>\Yii::t('carrental', 'Car damage')]),
            'price_violation' => \Yii::t('carrental', 'Violation price'),
            'price_poundage' => \Yii::t('locale', 'Poundage'),
            'price_basic_insurance' => \Yii::t('locale', 'Basic insurance'),
            'price_deposit' => \Yii::t('locale', 'Vehicle deposit'),
            'price_deposit_violation' => \Yii::t('locale', 'Violation deposit'),
            'price_optional_service' => \Yii::t('locale', '{type} price', ['type'=>\Yii::t('locale', 'Value-added services')]),
            'price_insurance_overtime' => \Yii::t('carrental', 'Overtime insurance price'),
            'price_different_office' => \Yii::t('carrental', 'Fee of different shop return car'),
            'price_take_car' => \Yii::t('carrental', 'Fee of delivery car to house'),
            'price_return_car' => \Yii::t('carrental', 'Fee of take car from house'),
            'price_working_loss' => \Yii::t('locale', '{type} price', ['type'=>\Yii::t('carrental', 'Working time loss')]),
            'price_accessories' => \Yii::t('locale', '{type} price', ['type'=>\Yii::t('carrental', 'Accessories')]),
            'price_agency' => \Yii::t('locale', '{type} price', ['type'=>\Yii::t('carrental', 'Agency')]),
            'price_other' => \Yii::t('locale', '{type} price', ['type'=>\Yii::t('locale', 'Other')]),
			'price_address_km' => \Yii::t('locale', 'Price address km'),
            'time' => \Yii::t('locale', 'Time'),
            'remark' => \Yii::t('locale', '{name} instruction', ['name'=>\Yii::t('locale', 'Remark')]),
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
        return array(
            'id' => array('data-options' => array('checkbox'=>'true'), 'key' => true),
            'order_id' => array('width' => 120, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.order_serial; }"),
            'serial' => array('width' => 100),
            'type' => array('width' => 60, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getTypesArray())." }"),
            'belong_office_id' => array('width' => 100, 'formatter' => "function(value,row){ return row.belong_office_disp; }"),
            'relet_mark' => array('width' => 120, 'sortable' => 'true', 'formatter' => "function(value,row){ if (value) { return '√'; } return ''; }"),
            'status' => array('width' => 60, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getStatusArray())." }"),
            'pay_source' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(\common\components\OrderModule::getOrderPayTypeArray())." }"),
            'deposit_pay_source' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(\common\components\OrderModule::getOrderPayTypeArray())." }"),
            'summary_amount' => array('width' => 100, 'sortable' => 'true'),
            'summary_deposit' => array('width' => 100, 'sortable' => 'true'),
            'price_rent' => array('width' => 100, 'sortable' => 'true'),
            'price_overtime' => array('width' => 100, 'sortable' => 'true'),
            'price_overmileage' => array('width' => 100, 'sortable' => 'true'),
            'price_designated_driving' => array('width' => 100, 'sortable' => 'true'),
            'price_designated_driving_overtime' => array('width' => 100, 'sortable' => 'true'),
            'price_designated_driving_overmileage' => array('width' => 100, 'sortable' => 'true'),
            'price_oil' => array('width' => 100),
            'price_oil_agency' => array('width' => 100),
            'price_car_damage' => array('width' => 100),
            'price_violation' => array('width' => 100),
            'price_poundage' => array('width' => 100),
            'price_basic_insurance' => array('width' => 100),
            'price_deposit' => array('width' => 100),
            'price_deposit_violation' => array('width' => 100),
            'price_optional_service' => array('width' => 100),
            'price_insurance_overtime' => array('width' => 100),
            'price_different_office' => array('width' => 100),
            'price_take_car' => array('width' => 100),
            'price_return_car' => array('width' => 100),
            'price_working_loss' => array('width' => 100),
            'price_accessories' => array('width' => 100),
            'price_agency' => array('width' => 100),
            'price_other' => array('width' => 100),
            'price_address_km' => array('width' => 100),
            'remark' => array('width' => 120),
            'time' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'edit_user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.edit_user_disp; }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 60, 
                'buttons' => array(
                    \Yii::$app->user->can('order/paymentdetail_delete') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['order/paymentdetail_delete', 'id'=>'']), 'condition'=>array("{field} == ".self::STATUS_NORMAL, array('{field}'=>'status')), 'name' => \Yii::t('locale', 'Delete'), 'title' => \Yii::t('locale', 'Are you sure to delete these records?'), 'paramField' => 'id', 'icon' => 'icon-delete', 'showText'=>true) : null,
                ),
            ),
        );
    }
    
    public static function getStatusArray()
    {
        return array(
            static::STATUS_NORMAL => \Yii::t('locale', 'Normal'),
            static::STATUS_DISABLED => \Yii::t('locale', 'Disabled'),
        );
    }

    public static function getTypesArray()
    {
        return array(
            static::TYPE_SHOULD_PAY => \Yii::t('locale', 'Normal'),
            static::TYPE_PAID => \Yii::t('locale', 'Disabled'),
        );
    }
    
    /**
     * 
     * @param \common\models\Pro_vehicle_order $model
     */
    public function load($objOrder, $priceAttributes=null)
    {
        $skipKeys = ['id'=>1, 'created_at'=>1, 'updated_at'=>1];
        foreach ($priceAttributes as $k => $v) {
            if (!isset($skipKeys[$k]) && $this->hasAttribute($k)) {
                $this->$k = floatval($v);
            }
        }
        $arrPreferencialFields = Pro_vehicle_order::getPreferentialPriceFields();
        $preferencialPrice = 0;
        foreach ($arrPreferencialFields as $f => $_x) {
            if (isset($priceAttributes[$f])) {
                $preferencialPrice += floatval($priceAttributes[$f]);
            }
        }
        $this->price_rent -= $preferencialPrice;
        $this->order_id = $objOrder->id;
        $this->belong_office_id = $objOrder->belong_office_id;
        $this->status = static::STATUS_NORMAL;
        $this->time = $objOrder->updated_at;
        $userId = (\Yii::$app->user->isGuest ? 0 : \Yii::$app->user->id);
        if ($userId && \Yii::$app->user->identityClass != 'backend\models\Rbac_admin') {
            $userId = 0;
        }
        $this->edit_user_id = $userId;
        $this->summary();
    }
    
    public function summary()
    {
        $summaryAmmount = 0;
        $summaryDeposit = 0;
        $attributes = $this->getAttributes();
        $arrPreferencialFields = Pro_vehicle_order::getPreferentialPriceFields();
        foreach ($attributes as $k => $v) {
            if (substr($k, 0, 6) == 'price_' && !isset($arrPreferencialFields[$k])) {
                if (substr($k, 0, 13) == 'price_deposit') {
                    $summaryDeposit += floatval($v);
                }
                else {
                    $summaryAmmount += floatval($v);
                }
            }
        }
        $this->summary_amount = $summaryAmmount;
        $this->summary_deposit = $summaryDeposit;
        return $this;
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
            return \Yii::createObject(\common\components\OfficeLimitedActiveQuery::className(), [get_called_class(), ['attribute'=>['belong_office_id']]]);
        }
    }
    
    /**
     * statistic all paid order price details and format to a same class object to store the statistic data.
     * @param integer $orderId
     * @return \common\models\Pro_vehicle_order_price_detail
     */
    public static function statisticPaidObject($orderId)
    {
        return static::statisticObject(static::TYPE_PAID, $orderId);
    }
    
    /**
     * 
     * @param integer $type
     * @param integer $orderId
     * @return \common\models\Pro_vehicle_order_price_detail
     */
    public static function statisticObject($type, $orderId)
    {
        $obj = new static();
        $obj->order_id = $orderId;
        $obj->type = $type;
        $obj->status = static::STATUS_NORMAL;
        $isLoadBaseData = true;
        $cdb = static::find(true);
        $cdb->where(['order_id' => $orderId, 'type' => $type, 'status' => static::STATUS_NORMAL]);
        $arrRows = $cdb->all();
        
        $priceKeys = static::getPriceKeys();
        foreach ($arrRows as $row) {
            if (!$isLoadBaseData) {
                $isLoadBaseData = false;
                $obj->belong_office_id = $row->belong_office_id;
                $obj->edit_user_id = $row->edit_user_id;
                $obj->pay_source = $row->pay_source;
                
            }
            
            foreach ($priceKeys as $k) {
                $v = floatval($obj->$k) + floatval($row->$k);
                $obj->$k = $v;
            }
        }
        $obj->summary();
        return $obj;
    }
    
    public static function generateSerial($orderId, $type, $time) {
        return $orderId.'-'.$type.'-'.$time;
    }
    
    public function autoSerial() {
        if (empty($this->serial)) {
            $this->serial = static::generateSerial($this->order_id, $this->type, $this->time);
        }
        return $this->serial;
    }
    
    /**
     * 
     * @param \common\models\Pro_purchase_order $objPurchaseOrder
     * @param \common\models\Pro_vehicle_order $objVehicleOrder
     * @return \common\models\Pro_vehicle_order_price_detail
     */
    public static function createPaidObjectByPurchaseOrder($objPurchaseOrder, $objVehicleOrder)
    {
        $obj = new static();
        $obj->order_id = $objVehicleOrder->id;
        $obj->type = static::TYPE_PAID;
        $obj->belong_office_id = $objVehicleOrder->belong_office_id;
        $obj->status = static::STATUS_NORMAL;
        $obj->pay_source = $objPurchaseOrder->pay_source;
        $obj->deposit_pay_source = Pro_vehicle_order::PAY_TYPE_NONE;
        $obj->time = $objPurchaseOrder->purchased_at;
        $obj->edit_user_id = 0;
        $obj->autoSerial();
        
        // prices
        $objOriginPaid = static::statisticObject(static::TYPE_PAID, $obj->order_id);
        $priceKeys = static::getPriceKeys();
        
        $leftamount = $objPurchaseOrder->amount;
        $obj->summary_amount = $leftamount;
        $obj->summary_deposit = 0;  // deposit would be paid when take kar in office.
        foreach ($priceKeys as $k) {
            if ($leftamount <= 0) {
                break;
            }
            if ($k == 'price_rent') {
                $obj->$k = $leftamount;
                break;
            }
            if (substr($k, 0, 13) == 'price_deposit') {
                continue;
            }
            $n = floatval($objVehicleOrder->$k) - floatval($objOriginPaid->$k);
            if ($n > 0) {
                if ($leftamount < $n) {
                    $n = $leftamount;
                }
                $leftamount -= $n;
                $obj->$k = $n;
            }
        }
        
        return $obj;
    }
    
    public static function getPriceKeys() {
        $obj = new \common\models\Pro_vehicle_order_price_detail();
        $keys = $obj->attributes();
        $arrPreferencialFields = Pro_vehicle_order::getPreferentialPriceFields();
        $priceKeys = [];
        foreach ($keys as $k) {
            if (substr($k, 0, 6) == 'price_' && $k != 'price_rent' && !isset($arrPreferencialFields[$k])) {
                $priceKeys[] = $k;
            }
        }
        $priceKeys[] = 'price_rent';
        return $priceKeys;
    }
    
    /**
     * create a object with giving model non-price data
     * @param array $model
     * @param array $priceFields
     * @return \common\models\Pro_vehicle_order_price_detail
     */
    public static function createObjectWithBaseData($model, $priceFields) {
        $obj = new static();
        foreach ($model as $k => $v) {
            if ($obj->hasAttribute($k)) {
                $obj->$k = $v;
            }
        }
        foreach ($priceFields as $k) {
            $obj->$k = 0;
        }
        $obj->summary_amount = 0;
        $obj->summary_deposit = 0;

        return $obj;
    }

    /**
     * add price rent related fields that skip deposit prices with given model
     * @param array|\common\models\Pro_vehicle_order_price_detail $model
     * @param array $priceFields
     */
    public function addAmountPricesWithData($model, $priceFields) {
        foreach ($priceFields as $k) {
            if (substr($k, 0, 13) != 'price_deposit') {
                $this->$k += floatval($model[$k]);
            }
        }
    }

    /**
     * add price deposit related fields with given model
     * @param array|\common\models\Pro_vehicle_order_price_detail $model
     * @param array $priceFields
     */
    public function addDepositPricesWithData($model, $priceFields) {
        foreach ($priceFields as $k) {
            if (substr($k, 0, 13) == 'price_deposit') {
                $this->$k += floatval($model[$k]);
            }
        }
    }

}

