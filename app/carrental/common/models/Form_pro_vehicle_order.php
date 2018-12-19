<?php

namespace common\models;

/**
 * This is the active form model class for table "pro_vehicle_order".
 */
class Form_pro_vehicle_order extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    //public $serial;
    public $vehicle_model_id;
    public $vehicle_id;
    public $user_id;
    public $status;                     // 订单状态
    public $type;                       // 订单类型（个人业务，公司业务等）
    public $vehicle_color;
    public $vehicle_oil_label;
    public $vehicle_outbound_mileage;   // 车辆出库里程(km)
    public $vehicle_inbound_mileage;    // 车辆入库里程(km)
    //public $origin_vehicle_id;          // 原来分配的车辆
    public $replace_vehicle_id;         // 替换车辆
    public $channel_serial;
    public $start_time;
    public $end_time;
    public $new_end_time;
    public $rent_days;
    public $belong_office_id;
    public $office_id_rent;
    public $office_id_return;
    public $pay_type;                   // 租车类型
    public $pay_source;                 // 租金支付方式
    public $total_amount;               // 订单总计金额
    //public $paid_amount;                // 已缴费金额
    public $rent_per_day;               // 每日租金价格
    public $price_rent;                 // 租金价格
    public $price_overtime;             // 超时价格标准
    public $price_overmileage;          // 超超里程费用
    public $price_designated_driving;   // 代驾费用
    public $price_designated_driving_overtime;  // 代驾超时费用
    public $price_designated_driving_overmileage;  // 代驾超里程费用
    public $price_oil;                  // 加油费用
    public $price_oil_agency;           // 加油代办
    public $price_car_damage;           // 车损费用
    public $price_violation;            // 违章费用
    public $price_poundage;             // 基本手续费
    public $price_basic_insurance;      // 基本服务费
    public $price_deposit;              // 押金
    public $price_deposit_violation;    // 违章押金
    //public $price_optional_service;     // 增值服务合计金额
    public $price_insurance_overtime;   // 超时保费
    public $price_different_office;     // 异店还车费用
    public $price_take_car;             // 送车上门服务费
    public $price_return_car;           // 上门取车服务费
    public $price_working_loss;           // 误工费
    public $price_accessories;           // 配件费
    public $price_agency;           // 代办费
    public $price_other;                // 其他费用
    public $price_preferential;         // 优惠价格
    public $price_bonus_point_deduction;   // 积分抵扣
    public $price_gift;                 // 优惠券抵用金额
    public $unit_price_overtime;        // 超时费用标准（元/小时）
    public $unit_price_overmileage;     // 超超里程费用标准（元/公里）
    public $unit_price_basic_insurance; // 基本服务费费用标准（元/天）
    public $unit_price_designated_driving;  // 代驾费用标准（元/天）
    public $unit_price_designated_driving_overtime; // 代驾超时费用标准（元/小时）
    public $unit_price_designated_driving_overmileage; // 代驾超里程费用标准（元/公里）
    public $settlement_status;          // 订单结算状态
    public $preferential_type;
    public $preferential_info;          // 优惠信息
    public $deposit_pay_source;         // 押金支付方式
    //public $paid_deposit;
    public $settlement_pay_source;      // 结算支付方式
    //public $optional_service;       // 已选增值服务
    //public $optional_service_info;
    public $used_gift_code;             // 使用的优惠券
    public $address_take_car;
    public $address_return_car;
    public $source;                 // 订单来源 1:门店来源 2:手机下单等
    public $customer_name;          // 客户名称
    public $customer_telephone;     // 客户电话
    public $customer_fixedphone;    // 客户电话
    public $customer_id_type;
    public $customer_id;
    public $customer_address;
    public $customer_postcode;
    public $customer_operator_name; // 经办人姓名
    public $customer_driver_license_time;
    public $customer_driver_license_expire_time;
    public $customer_employer;      // 客户单位名称
    public $customer_employer_address;  // 客户单位地址
    public $customer_employer_phone;
    public $customer_employer_postcode;
    public $customer_employer_certificate_id;
    public $emergency_contact_name;
    public $emergency_contact_phone;
    public $refund_account_number;
    public $refund_account_name;
    public $refund_bank_name;
    public $refund_remark;
    public $inv_title;              // 发票抬头
    public $inv_name;
    public $inv_tax_number;
    public $inv_phone;
    public $inv_amount;
    public $inv_address;
    public $inv_postcode;
    public $remark;                 // 订单备注
    public $settlement_remark;      // 结算说明
    public $car_dispatched_at;
    public $car_returned_at;
    
    //public $price_cur_payment;
    
    public $isUpdateSettlement = false; // 是否是更新结算信息模式

    //sjj
    public $yuyue_time;                 //预约租车时间
    public $yuyue_end_time;             //预约换车时间
    public $is_high_speed;
	public $price_address_km;           // 单程租车两地公里数油耗费用
	public $address_km;                 //两地之间的距离
	public $flag;                 		//车型标签
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vehicle_model_id', 'vehicle_id', 'user_id', 'status', 'start_time', 'end_time', 'price_rent'], 'required'],
            [['id', 'vehicle_model_id', 'vehicle_id', 'user_id', 'status', 'type', 'vehicle_color', 'vehicle_oil_label', 'vehicle_outbound_mileage', 'vehicle_inbound_mileage', 'rent_days', 'belong_office_id', 'office_id_rent', 'office_id_return', 'pay_type', 'pay_source', 'settlement_status', 'preferential_type', 'deposit_pay_source', 'settlement_pay_source', 'source', 'customer_id_type','is_high_speed','address_km'], 'integer'],
            [['total_amount', /*'paid_amount',*/ 'rent_per_day', 'price_rent', 'price_overtime', 'price_overmileage', 'price_designated_driving', 'price_designated_driving_overtime', 'price_designated_driving_overmileage', 'price_oil', 'price_oil_agency', 'price_car_damage', 'price_violation', 'price_poundage', 'price_basic_insurance', 'price_deposit', 'price_deposit_violation', 'price_insurance_overtime', 'price_different_office', 'price_take_car', 'price_return_car', 'price_working_loss', 'price_accessories', 'price_agency', 'price_other', 'price_preferential', 'price_bonus_point_deduction', 'price_gift', 'unit_price_overtime', 'unit_price_overmileage', 'unit_price_basic_insurance', 'unit_price_designated_driving', 'unit_price_designated_driving_overtime', 'unit_price_designated_driving_overmileage', /*'paid_deposit',*/ 'inv_amount','price_address_km','flag'], 'number'],
            [['remark', 'settlement_remark'], 'string'],
            [['channel_serial', 'used_gift_code', 'customer_name', 'customer_id', 'customer_operator_name', 'customer_employer_certificate_id', 'emergency_contact_name', 'refund_account_number', 'refund_account_name', 'refund_bank_name', 'inv_name', 'inv_tax_number'], 'string', 'max' => 64],
            [['preferential_info', 'customer_telephone', 'customer_fixedphone', 'customer_postcode', 'customer_employer_phone', 'customer_employer_postcode', 'emergency_contact_phone', 'inv_phone', 'inv_postcode'], 'string', 'max' => 32],
            [['address_take_car', 'address_return_car', 'customer_address', 'customer_employer', 'customer_employer_address', 'inv_title', 'inv_address'], 'string', 'max' => 255],
            [['refund_remark'], 'string', 'max' => 128],
            
            ['status', 'in', 'range' => [\common\models\Pro_vehicle_order::STATUS_WAITING, // 待确认
                \common\models\Pro_vehicle_order::STATUS_BOOKED, // 已预订
                \common\models\Pro_vehicle_order::STATUS_RENTING, // 已承租
                \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING, // 违章待查
                \common\models\Pro_vehicle_order::STATUS_COMPLETED, // 已完成
                \common\models\Pro_vehicle_order::STATUS_CANCELLED]],// 已取消
            ['type', 'in', 'range' => [\common\models\Pro_vehicle_order::TYPE_PERSONAL, \common\models\Pro_vehicle_order::TYPE_ENTERPRISE, \common\models\Pro_vehicle_order::TYPE_UNIVERSAL]],
            
            [['vehicle_id', 'replace_vehicle_id'], 'default', 'value'=>0],

            ['settlement_status', 'in', 'range' => [\common\models\Pro_vehicle_order::SETTLEMENT_TYPE_NONE, \common\models\Pro_vehicle_order::SETTLEMENT_TYPE_COMPLETED, \common\models\Pro_vehicle_order::SETTLEMENT_TYPE_INSTALLMENT, \common\models\Pro_vehicle_order::SETTLEMENT_TYPE_ONACCOUNT,4]],
            
            [['remark', 'settlement_remark', 'channel_serial', 'used_gift_code', 'customer_name', 'customer_id', 'customer_operator_name', 'customer_employer_certificate_id', 'emergency_contact_name', 'refund_account_number', 'refund_account_name', 'refund_bank_name', 'inv_name', 'inv_tax_number', 'preferential_info', 'customer_telephone', 'customer_fixedphone', 'customer_postcode', 'customer_employer_phone', 'customer_employer_postcode', 'emergency_contact_phone', 'inv_phone', 'inv_postcode', 'address_take_car', 'address_return_car', 'customer_address', 'customer_employer', 'customer_employer_address', 'inv_title', 'inv_address', 'refund_remark'], 'filter', 'filter'=>'trim'],
            
            [['start_time', 'end_time', 'car_dispatched_at', 'car_returned_at','yuyue_time','yuyue_end_time'], \common\helpers\validators\DatetimeValidator::className(), 'format'=>'php:Y-m-d H:i', 'adaptTimestamp'=>true],
            // sjj 删除驾照时间格式验证
            //[['customer_driver_license_time', 'customer_driver_license_expire_time'], \common\helpers\validators\DateValidator::className(), 'adaptTimestamp'=>true],
            // sjj
            
            //[['pay_source', 'deposit_pay_source', 'settlement_pay_source'], 'in', 'range' => array_keys(\common\components\OrderModule::getOrderPayTypeArray())],
            //[['pay_source', 'deposit_pay_source', 'settlement_pay_source'], 'default', 'value'=> \common\models\Pro_vehicle_order::PAY_TYPE_NONE],
        ];
        /*
        return [
            ['id', 'integer'],
            //['serial', 'filter', 'filter' => 'trim'],
            //['serial', 'required'],
            //['serial', 'string', 'min' => 12, 'max' => 64],
            
            ['vehicle_model_id', 'required'],
            ['vehicle_model_id', 'integer'],
            
            ['vehicle_id', 'integer'],
            ['vehicle_id', 'default', 'value'=>0],
            
            ['user_id', 'required'],
            ['user_id', 'integer'],
            
            ['status', 'required'],
            ['status', 'integer'],
            ['status', 'in', 'range' => [\common\models\Pro_vehicle_order::STATUS_WAITING, 
                \common\models\Pro_vehicle_order::STATUS_BOOKED, 
                \common\models\Pro_vehicle_order::STATUS_RENTING, 
                \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING, 
                \common\models\Pro_vehicle_order::STATUS_COMPLETED, 
                \common\models\Pro_vehicle_order::STATUS_CANCELLED]],
            
            ['type', 'required'],
            ['type', 'integer'],
            ['type', 'in', 'range' => [\common\models\Pro_vehicle_order::TYPE_PERSONAL, \common\models\Pro_vehicle_order::TYPE_ENTERPRISE, \common\models\Pro_vehicle_order::TYPE_UNIVERSAL]],
            
            ['vehicle_color', 'integer'],
            ['vehicle_color', 'default', 'value'=>0],
            
            ['vehicle_oil_label', 'integer'],
            ['vehicle_oil_label', 'default', 'value'=>0],
            
            ['vehicle_outbound_mileage', 'integer'],
            ['vehicle_outbound_mileage', 'default', 'value'=>0],
            
            ['vehicle_inbound_mileage', 'integer'],
            ['vehicle_inbound_mileage', 'default', 'value'=>0],
            
            ['replace_vehicle_id', 'integer'],
            ['replace_vehicle_id', 'default', 'value'=>0],
            
            ['start_time', 'required'],
            ['start_time', 'integer'],
            
            ['end_time', 'required'],
            ['end_time', 'integer'],
            
            ['new_end_time', 'integer'],
            
            ['rent_days', 'required'],
            ['rent_days', 'integer'],
            
            ['belong_office_id', 'integer'],
            ['belong_office_id', 'default', 'value'=>0],
            
            ['office_id_rent', 'integer'],
            ['office_id_rent', 'default', 'value'=>0],
            
            ['office_id_return', 'integer'],
            ['office_id_return', 'default', 'value'=>0],
            
            ['pay_type', 'integer'],
            ['pay_type', 'default', 'value'=>0],
            
            //['pay_source', 'integer'],
            //['pay_source', 'default', 'value'=>0],
            
            ['total_amount', 'double'],
            ['total_amount', 'default', 'value'=>0],
            
            //['paid_amount', 'double'],
            //['paid_amount', 'default', 'value'=>0],
            
            ['rent_per_day', 'double'],
            ['rent_per_day', 'default', 'value'=>0],
            
            ['price_rent', 'double'],
            ['price_rent', 'default', 'value'=>0],
            
            ['price_overtime', 'double'],
            ['price_overtime', 'default', 'value'=>0],
            
            ['price_overmileage', 'double'],
            ['price_overmileage', 'default', 'value'=>0],
            
            ['price_designated_driving', 'double'],
            ['price_designated_driving', 'default', 'value'=>0],
            
            ['price_designated_driving_overtime', 'double'],
            ['price_designated_driving_overtime', 'default', 'value'=>0],
            
            ['price_designated_driving_overtime', 'double'],
            ['price_designated_driving_overtime', 'default', 'value'=>0],
            
            ['price_designated_driving_overmileage', 'double'],
            ['price_designated_driving_overmileage', 'default', 'value'=>0],
            
            ['price_oil', 'double'],
            ['price_oil', 'default', 'value'=>0],
            
            ['price_oil_agency', 'double'],
            ['price_oil_agency', 'default', 'value'=>0],
            
            ['price_car_damage', 'double'],
            ['price_car_damage', 'default', 'value'=>0],
            
            ['price_violation', 'double'],
            ['price_violation', 'default', 'value'=>0],
            
            ['price_poundage', 'required'],
            ['price_poundage', 'double'],
            ['price_poundage', 'default', 'value'=>0],
            
            ['price_basic_insurance', 'double'],
            ['price_basic_insurance', 'default', 'value'=>0],
            
            ['price_deposit', 'double'],
            ['price_deposit', 'default', 'value'=>0],
            
            ['price_deposit_violation', 'required'],
            ['price_deposit_violation', 'double'],
            ['price_deposit_violation', 'default', 'value'=>0],
            
            //['price_optional_service', 'double'],
            
            ['price_insurance_overtime', 'double'],
            ['price_insurance_overtime', 'default', 'value'=>0],
            
            ['price_different_office', 'double'],
            ['price_different_office', 'default', 'value'=>0],
            
            ['price_take_car', 'double'],
            ['price_take_car', 'default', 'value'=>0],
            
            ['price_return_car', 'double'],
            ['price_return_car', 'default', 'value'=>0],
            
            ['price_working_loss', 'double'],
            ['price_working_loss', 'default', 'value'=>0],
            
            ['price_accessories', 'double'],
            ['price_accessories', 'default', 'value'=>0],
            
            ['price_agency', 'double'],
            ['price_agency', 'default', 'value'=>0],
            
            ['price_other', 'double'],
            ['price_other', 'default', 'value'=>0],
            
            ['price_preferential', 'double'],
            ['price_preferential', 'default', 'value'=>0],
            
            ['price_gift', 'double'],
            ['price_gift', 'default', 'value'=>0],
            
            ['price_bonus_point_deduction', 'double'],
            ['price_bonus_point_deduction', 'default', 'value'=>0],
            
            ['unit_price_overtime', 'double'],
            ['unit_price_overtime', 'default', 'value'=>0],
            
            ['unit_price_overmileage', 'double'],
            ['unit_price_overmileage', 'default', 'value'=>0],
            
            ['unit_price_basic_insurance', 'required'],
            ['unit_price_basic_insurance', 'double'],
            ['unit_price_basic_insurance', 'default', 'value'=>0],
            
            ['unit_price_designated_driving', 'double'],
            ['unit_price_designated_driving', 'default', 'value'=>0],
            
            ['unit_price_designated_driving_overtime', 'double'],
            ['unit_price_designated_driving_overtime', 'default', 'value'=>0],
            
            ['unit_price_designated_driving_overtime', 'double'],
            ['unit_price_designated_driving_overtime', 'default', 'value'=>0],
            
            ['unit_price_designated_driving_overmileage', 'double'],
            ['unit_price_designated_driving_overmileage', 'default', 'value'=>0],
            
            //['price_cur_payment', 'double'],
            //['price_cur_payment', 'default', 'value'=>0],
            
            ['settlement_status', 'integer'],
            ['settlement_status', 'default', 'value'=>\common\models\Pro_vehicle_order::SETTLEMENT_TYPE_NONE],
            ['settlement_status', 'in', 'range' => [\common\models\Pro_vehicle_order::SETTLEMENT_TYPE_NONE, \common\models\Pro_vehicle_order::SETTLEMENT_TYPE_COMPLETED, \common\models\Pro_vehicle_order::SETTLEMENT_TYPE_INSTALLMENT, \common\models\Pro_vehicle_order::SETTLEMENT_TYPE_ONACCOUNT]],
            
            ['preferential_type', 'integer'],

            ['preferential_info', 'default', 'value'=>''],
            ['preferential_info', 'filter', 'filter' => 'trim'],
            ['preferential_info', 'string', 'min' => 0, 'max' => 32],

            ['deposit_pay_source', 'integer'],
            ['deposit_pay_source', 'default', 'value'=>0],
            
            ['settlement_pay_source', 'integer'],
            ['settlement_pay_source', 'default', 'value'=>0],
            
            //['optional_service', 'integer'],
            
            //['optional_service_info', 'filter', 'filter' => 'trim'],
            //['optional_service_info', 'string'],
            
            ['used_gift_code', 'default', 'value'=>''],
            ['used_gift_code', 'filter', 'filter' => 'trim'],
            ['used_gift_code', 'string', 'min' => 0, 'max' => 64],

            ['address_take_car', 'default', 'value'=>''],
            ['address_take_car', 'filter', 'filter' => 'trim'],
            ['address_take_car', 'string', 'min' => 0, 'max' => 255],

            ['address_return_car', 'default', 'value'=>''],
            ['address_return_car', 'filter', 'filter' => 'trim'],
            ['address_return_car', 'string', 'min' => 0, 'max' => 255],

            ['source', 'integer'],
            ['source', 'default', 'value'=>0],
            
            ['customer_name', 'default', 'value'=>''],
            ['customer_name', 'filter', 'filter' => 'trim'],
            ['customer_name', 'string', 'min' => 0, 'max' => 64],

            ['customer_telephone', 'default', 'value'=>''],
            ['customer_telephone', 'filter', 'filter' => 'trim'],
            ['customer_telephone', 'string', 'min' => 0, 'max' => 64],

            ['customer_fixedphone', 'default', 'value'=>''],
            ['customer_fixedphone', 'filter', 'filter' => 'trim'],
            ['customer_fixedphone', 'string', 'min' => 0, 'max' => 64],

            ['customer_id_type', 'integer'],
            ['customer_id_type', 'default', 'value'=>0],
            
            ['customer_id', 'default', 'value'=>''],
            ['customer_id', 'filter', 'filter' => 'trim'],
            ['customer_id', 'string', 'min' => 0, 'max' => 64],

            ['customer_address', 'default', 'value'=>''],
            ['customer_address', 'filter', 'filter' => 'trim'],
            ['customer_address', 'string', 'min' => 0, 'max' => 255],

            ['customer_postcode', 'default', 'value'=>''],
            ['customer_postcode', 'filter', 'filter' => 'trim'],
            ['customer_postcode', 'string', 'min' => 0, 'max' => 64],

            ['customer_operator_name', 'default', 'value'=>''],
            ['customer_operator_name', 'filter', 'filter' => 'trim'],
            ['customer_operator_name', 'string', 'min' => 0, 'max' => 64],

            ['customer_driver_license_time', 'integer'],
            ['customer_driver_license_time', 'default', 'value'=>0],
            
            ['customer_driver_license_expire_time', 'integer'],
            ['customer_driver_license_expire_time', 'default', 'value'=>0],
            
            ['customer_employer', 'default', 'value'=>''],
            ['customer_employer', 'filter', 'filter' => 'trim'],
            ['customer_employer', 'string', 'min' => 0, 'max' => 64],

            ['customer_employer_address', 'default', 'value'=>''],
            ['customer_employer_address', 'filter', 'filter' => 'trim'],
            ['customer_employer_address', 'string', 'min' => 0, 'max' => 255],

            ['customer_employer_phone', 'default', 'value'=>''],
            ['customer_employer_phone', 'filter', 'filter' => 'trim'],
            ['customer_employer_phone', 'string', 'min' => 0, 'max' => 64],

            ['customer_employer_postcode', 'default', 'value'=>''],
            ['customer_employer_postcode', 'filter', 'filter' => 'trim'],
            ['customer_employer_postcode', 'string', 'min' => 0, 'max' => 32],

            ['customer_employer_certificate_id', 'default', 'value'=>''],
            ['customer_employer_certificate_id', 'filter', 'filter' => 'trim'],
            ['customer_employer_certificate_id', 'string', 'min' => 0, 'max' => 32],

            ['emergency_contact_name', 'default', 'value'=>''],
            ['emergency_contact_name', 'filter', 'filter' => 'trim'],
            ['emergency_contact_name', 'string', 'min' => 0, 'max' => 32],

            ['emergency_contact_phone', 'default', 'value'=>''],
            ['emergency_contact_phone', 'filter', 'filter' => 'trim'],
            ['emergency_contact_phone', 'string', 'min' => 0, 'max' => 32],

            ['refund_account_number', 'default', 'value'=>''],
            ['refund_account_number', 'filter', 'filter' => 'trim'],
            ['refund_account_number', 'string', 'min' => 0, 'max' => 64],

            ['refund_account_name', 'default', 'value'=>''],
            ['refund_account_name', 'filter', 'filter' => 'trim'],
            ['refund_account_name', 'string', 'min' => 0, 'max' => 64],

            ['refund_bank_name', 'default', 'value'=>''],
            ['refund_bank_name', 'filter', 'filter' => 'trim'],
            ['refund_bank_name', 'string', 'min' => 0, 'max' => 64],

            ['refund_remark', 'default', 'value'=>''],
            ['refund_remark', 'filter', 'filter' => 'trim'],
            ['refund_remark', 'string', 'min' => 0, 'max' => 128],

            ['inv_title', 'default', 'value'=>''],
            ['inv_title', 'filter', 'filter' => 'trim'],
            ['inv_title', 'string', 'min' => 0, 'max' => 255],

            ['inv_name', 'default', 'value'=>''],
            ['inv_name', 'filter', 'filter' => 'trim'],
            ['inv_name', 'string', 'min' => 0, 'max' => 64],

            ['inv_tax_number', 'default', 'value'=>''],
            ['inv_tax_number', 'filter', 'filter' => 'trim'],
            ['inv_tax_number', 'string', 'min' => 0, 'max' => 64],

            ['inv_phone', 'default', 'value'=>''],
            ['inv_phone', 'filter', 'filter' => 'trim'],
            ['inv_phone', 'string', 'min' => 0, 'max' => 32],

            ['inv_amount', 'double'],
            ['inv_amount', 'default', 'value'=>0],
            
            ['inv_address', 'default', 'value'=>''],
            ['inv_address', 'filter', 'filter' => 'trim'],
            ['inv_address', 'string', 'min' => 0, 'max' => 255],

            ['inv_postcode', 'default', 'value'=>''],
            ['inv_postcode', 'filter', 'filter' => 'trim'],
            ['inv_postcode', 'string', 'min' => 0, 'max' => 32],

            ['remark', 'default', 'value'=>''],
            ['remark', 'string'],
            
            ['settlement_remark', 'default', 'value'=>''],
            ['settlement_remark', 'string'],
            
            ['car_returned_at', 'integer'],
            
            ['edit_user_id', 'integer'],
            
        ];
         * 
         */
    }
    
    public function getActiveRecordModel() {
        $model = new Pro_vehicle_order();
        return $model;
    }
    /*
    public function savingFields() {
        if ($this->isUpdateSettlement) {
            return [
                'start_time',
                'end_time',
                'vehicle_inbound_mileage',
                'total_amount',
                'price_rent',
                //'paid_amount',
                'price_overtime',
                'price_overmileage',
                'price_designated_driving',
                'price_designated_driving_overtime',
                'price_designated_driving_overmileage',
                'price_oil',
                'price_oil_agency',
                'price_car_damage',
                'price_violation',
                'price_other',
                'price_poundage',
                'price_deposit',
                'price_deposit_violation',
                'price_insurance_overtime',
                'price_different_office',
                'price_take_car',
                'price_return_car',
                //'price_working_loss',
                //'price_accessories',
                //'price_agency',
                'price_preferential',
                'price_gift',
                'price_bonus_point_deduction',
                'preferential_type',
                'preferential_info',
                'settlement_status',
                'settlement_pay_source',
                'settlement_remark',
                'refund_account_number',
                'refund_account_name',
                'refund_bank_name',
                'refund_remark',
                //'edit_user_id',
                'car_returned_at',
                'used_gift_code',
            ];
        }
        return [
            //'serial',
            'vehicle_model_id',
            'vehicle_id',
            'user_id',
            'status',
            'type',
            'vehicle_color',
            'vehicle_oil_label',
            'vehicle_outbound_mileage',
            //'origin_vehicle_id',
            'start_time',
            'end_time',
            'rent_days',
            'office_id_rent',
            'office_id_return',
            'pay_type',
            //'pay_source',
            'total_amount',
            'price_rent',
            //'paid_amount',
            'rent_per_day',
            //'price_overtime',
            //'price_overmileage',
            //'price_designated_driving',
            //'price_designated_driving_overtime',
            //'price_designated_driving_overmileage',
            //'price_oil',
            //'price_oil_agency',
            //'price_car_damage',
            //'price_violation',
            //'price_other',
            'price_poundage',
            'price_basic_insurance',
            'price_deposit',
            'price_deposit_violation',
            //'price_optional_service',
            'price_preferential',
            //'price_insurance_overtime',
            //'price_bonus_point_deduction',
            'price_different_office',
            'price_take_car',
            'price_return_car',
            'price_gift',
            'unit_price_overtime',
            'unit_price_overmileage',
            'unit_price_basic_insurance',
            'unit_price_designated_driving',
            'unit_price_designated_driving_overtime',
            'unit_price_designated_driving_overmileage',
            //'settlement_status',
            'preferential_type',
            'preferential_info',
            //'deposit_pay_source',
            //'settlement_pay_source',
            //'optional_service',
            //'optional_service_info',
            'used_gift_code',
            'address_take_car',
            'address_return_car',
            'source',
            'customer_name',
            'customer_telephone',
            'customer_fixedphone',
            'customer_id_type',
            'customer_id',
            'customer_address',
            'customer_postcode',
            'customer_operator_name',
            'customer_driver_license_time',
            'customer_driver_license_expire_time',
            'customer_employer',
            'customer_employer_address',
            'customer_employer_phone',
            'customer_employer_postcode',
            'customer_employer_certificate_id',
            'emergency_contact_name',
            'emergency_contact_phone',
            'refund_account_number',
            'refund_account_name',
            'refund_bank_name',
            'refund_remark',
            'inv_title',
            'inv_name',
            'inv_tax_number',
            'inv_phone',
            'inv_amount',
            'inv_address',
            'inv_postcode',
            'remark',
            //'settlement_remark',
            //'edit_user_id',
        ];
    }
    
    public function load($data, $formName = null)
    {
        $scope = $formName === null ? $this->formName() : $formName;
        $formData = null;
        if ($scope === '' && !empty($data)) {
            $formData = $data;
        } elseif (isset($data[$scope])) {
            $formData = $data[$scope];
        }
        
        if ($formData) {
            // process
            $arrDateFields = ['start_time', 'end_time', 'confirmed_at', 'customer_driver_license_time', 'customer_driver_license_expire_time', 'car_returned_at'];
            foreach ($arrDateFields as $_field) {
                if (isset($formData[$_field])) {
                    $formData[$_field] = \common\helpers\Utils::toTimestamp($formData[$_field]);
                }
            }
            
            if (isset($formData['replace_vehicle_id']) && !empty($formData['replace_vehicle_id'])) {
                if (preg_match('/^\d+$/', $formData['replace_vehicle_id'])) {
                    $formData['vehicle_id'] = $formData['replace_vehicle_id'];
                }
                else {
                    $this->addError('replace_vehicle_id', \Yii::t('locale', 'Data does not fit the rule!'));
                    return false;
                }
            }
            
            $this->setAttributes($formData);
            
            //$this->edit_user_id = \Yii::$app->getUser()->id;
            
            if ($this->validate()) {
                //$curDayStart = strtotime(date('Y-m-d'));
                //if ($this->start_time < $curDayStart && $this->id == 0) {
                //    $this->addError('start_time', \Yii::t('carrental', 'Order start time should not earlier than today.'));
                //    return false;
                //}
                return true;
            }
        }
        return false;
    }
    */
    
    public function load($data, $formName = null) {
        if (parent::load($data, $formName)) {
            if ($this->replace_vehicle_id) {
                $this->vehicle_id = $this->replace_vehicle_id;
            }
            return true;
        }
        return false;
    }
    
    public function save($model) {
        if (parent::save($model)) {
            if ($this->belong_office_id) {
                $model->belong_office_id = $this->belong_office_id;
            }
            
            if ($model->new_end_time < $this->end_time) {
                $model->new_end_time = $this->end_time;
            }
            return true;
        }
        return false;
    }
    
    public function calculateCarDeliveryServicePrice() {
        $arrResult = [0, \Yii::t('locale', 'Success')];
        do
        {
            if (empty($this->price_different_office)) {
                $resultPriceDiffOffice = \common\components\OrderModule::getPriceByDistanceOfOffices($this->vehicle_model_id, $this->office_id_rent, $this->office_id_return);
                if ($resultPriceDiffOffice['result'] != 0) {
                    $arrResult[0] = $resultPriceDiffOffice['result'];
                    $arrResult[1] = $resultPriceDiffOffice['desc'];
                    break;
                }
                $this->price_different_office = $resultPriceDiffOffice['price'];
            }
            
            if (empty($this->price_take_car)) {
                if (!empty($this->address_take_car)) {
                    $resultPriceTakeCar = \common\components\OrderModule::getPriceByAddressToOffice($this->vehicle_model_id, $this->address_take_car, $this->office_id_rent);
                    if ($resultPriceTakeCar['result'] != 0) {
                        $arrResult[0] = $resultPriceTakeCar['result'];
                        $arrResult[1] = $resultPriceTakeCar['desc'];
                        break;
                    }
                    $this->price_take_car = $resultPriceTakeCar['price'];
                }
                else {
                    $this->price_take_car = 0;
                }
            }
            
            if (empty($this->price_return_car)) {
                if (!empty($this->address_return_car)) {
                    $resultPriceReturnCar = \common\components\OrderModule::getPriceByAddressToOffice($this->vehicle_model_id, $this->address_return_car, $this->office_id_return);
                    if ($resultPriceReturnCar['result'] != 0) {
                        $arrResult[0] = $resultPriceReturnCar['result'];
                        $arrResult[1] = $resultPriceReturnCar['desc'];
                        break;
                    }
                    $this->price_return_car = $resultPriceReturnCar['price'];
                }
                else {
                    $this->price_return_car = 0;
                }
            }
            
        } while(0);
        return $arrResult;
    }
    
}