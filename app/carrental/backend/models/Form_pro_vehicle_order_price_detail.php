<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_vehicle_order_price_detail".
 */
class Form_pro_vehicle_order_price_detail extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $order_id;
    public $serial = '';
    public $type;
    public $belong_office_id;
    public $status;
    public $relet_mark;
    public $pay_source;
    public $deposit_pay_source;
    public $summary_amount;
    public $summary_deposit;
    public $price_rent;
    public $price_overtime;
    public $price_overmileage;
    public $price_designated_driving;
    public $price_designated_driving_overtime;
    public $price_designated_driving_overmileage;
    public $price_oil;
    public $price_oil_agency;
    public $price_car_damage;
    public $price_violation;
    public $price_poundage;
    public $price_basic_insurance;
    public $price_deposit;
    public $price_deposit_violation;
    public $price_optional_service;
    public $price_insurance_overtime;
    public $price_different_office;
    public $price_take_car;
    public $price_return_car;
    public $price_working_loss;
    public $price_accessories;
    public $price_agency;
    public $price_other;
    public $time;
    public $remark;
      public $price_address_km;
      public $flag;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'serial', 'type', 'belong_office_id', 'status', 'time'], 'required'],
            [['id', 'order_id', 'type', 'belong_office_id', 'status', 'relet_mark', 'pay_source', 'deposit_pay_source','flag'], 'integer'],
            [['summary_amount', 'summary_deposit', 'price_rent', 'price_overtime', 'price_overmileage', 'price_designated_driving', 'price_designated_driving_overtime', 'price_designated_driving_overmileage', 'price_oil', 'price_oil_agency', 'price_car_damage', 'price_violation', 'price_poundage', 'price_basic_insurance', 'price_deposit', 'price_deposit_violation', 'price_optional_service', 'price_insurance_overtime', 'price_different_office', 'price_take_car', 'price_return_car', 'price_working_loss', 'price_accessories', 'price_agency', 'price_other','price_address_km'], 'number'],
            [['serial'], 'string', 'max' => 64],
            [['remark'], 'string', 'max' => 255],
            [['serial'], 'unique', 'targetClass' => 'common\models\Pro_vehicle_order_price_detail', 'filter'=>['<>', 'id', $this->id]],
        
            ['time', 'datetime', 'format'=>'php:Y-m-d H:i:s'],
            ['type', 'in', 'range' => [\common\models\Pro_vehicle_order_price_detail::TYPE_SHOULD_PAY, \common\models\Pro_vehicle_order_price_detail::TYPE_PAID]],
            ['status', 'in', 'range' => [\common\models\Pro_vehicle_order_price_detail::STATUS_NORMAL, \common\models\Pro_vehicle_order_price_detail::STATUS_DISABLED]],
            [['pay_source'], 'in', 'range' => array_keys(\common\components\OrderModule::getOrderPayTypeArray())],
            [['pay_source'], 'default', 'value'=> \common\models\Pro_vehicle_order::PAY_TYPE_NONE],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_vehicle_order_price_detail();
        return $model;
    }

}
