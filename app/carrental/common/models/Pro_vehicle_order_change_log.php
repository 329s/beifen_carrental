<?php

namespace common\models;

/**
 * Order change log
 * @property integer $id
 * @property string $serial
 * @property integer $vehicle_model_id
 * @property integer $vehicle_id
 * @property integer $user_id
 * @property integer $status                    订单状态
 * @property integer $type                      订单类型（个人业务，公司业务等）
 * @property integer $vehicle_color
 * @property integer $vehicle_oil_label
 * @property integer $vehicle_outbound_mileage  车辆出库里程(km)
 * @property integer $vehicle_inbound_mileage   车辆入库里程(km)
 * @property integer $origin_vehicle_id         原来分配的车辆
 * @property string $channel_serial             渠道方订单号
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $new_end_time
 * @property integer $rent_days
 * @property integer $belong_office_id
 * @property integer $office_id_rent
 * @property integer $office_id_return
 * @property integer $pay_type                  租车类型
 * @property integer $pay_source                租金支付方式
 * @property integer $total_amount              订单总计金额
 * @property integer $paid_amount               已缴费金额
 * @property integer $rent_per_day              每日租金
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
 * @property integer $price_preferential        优惠价格
 * @property integer $price_gift                优惠券抵用金额
 * @property integer $price_bonus_point_deduction  积分抵扣
 * @property integer $unit_price_overtime       超时费用标准（元/小时）
 * @property integer $unit_price_overmileage    超超里程费用标准（元/公里）
 * @property integer $unit_price_basic_insurance   基本服务费费标准（元/天）
 * @property integer $unit_price_designated_driving   代驾费用标准（元/天）
 * @property integer $unit_price_designated_driving_overtime  代驾超时费用标准（元/小时）
 * @property integer $unit_price_designated_driving_overmileage  代驾超里程费用标准（元/公里）
 * @property integer $settlement_status         结算状态
 * @property integer $preferential_type         优惠类型
 * @property string $preferential_info          优惠信息
 * @property integer $deposit_pay_source        押金支付方式
 * @property integer $settlement_pay_source     结算支付方式
 * @property integer $optional_service          已选增值服务
 * @property string $optional_service_info
 * @property string $daily_rent_detailed_info
 * @property string $used_gift_code             使用的优惠券
 * @property string $address_take_car
 * @property string $address_return_car
 * @property integer $source                    订单来源 1:门店来源 2:手机下单等
 * @property string $customer_name              客户名称
 * @property string $customer_telephone         客户电话
 * @property string $customer_fixedphone        客户固定电话
 * @property integer $customer_id_type
 * @property string $customer_id
 * @property string $customer_address
 * @property string $customer_postcode
 * @property string $customer_operator_name     经办人姓名
 * @property integer $customer_driver_license_time
 * @property string $customer_employer          客户单位名称
 * @property string $customer_employer_address  客户单位地址
 * @property string $customer_employer_phone
 * @property string $customer_employer_postcode
 * @property string $customer_employer_certificate_id
 * @property string $emergency_contact_name
 * @property string $emergency_contact_phone
 * @property string $refund_account_number
 * @property string $refund_account_name
 * @property string $refund_bank_name
 * @property string $refund_remark
 * @property string $inv_title                  发票抬头
 * @property string $inv_name
 * @property string $inv_tax_number
 * @property string $inv_phone
 * @property integer $inv_amount
 * @property string $inv_address
 * @property string $inv_postcode
 * @property string $remark                     订单备注
 * @property string $settlement_remark          结算说明
 * @property integer $validation_id_0           出车前验车信息
 * @property integer $validation_id_1           还车后验车信息
 * @property integer $edit_user_id              订单登记者管理员ID
 * @property integer $confirmed_at              订单确认时间
 * @property integer $car_dispatched_at         实际出车时间
 * @property integer $car_returned_at           实际还车时间
 * @property integer $settlemented_at           结算时间
 * @property integer $settlement_user_id        结算人员
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_vehicle_order_change_log extends \common\helpers\ActiveRecordModel
{
	public $price_address_km;           // 单程租车两地公里数油耗费用
	public $address_km;          		// 单程租车两地公里数油耗费用
	public $flag;        			   // 
    
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
        $model = new Pro_vehicle_order();
        $attributeLabels = $model->attributeLabels();
        $attributeLabels['created_at'] = \Yii::t('locale', 'Operation time');
        //$attributeLabels['flag'] = \Yii::t('locale', 'Vehicle flag');
        return $attributeLabels;
    }
    
    /**
     * Returns the attribute custom types.
     * Attribute custom types is used for display model data in datagrid specified display options.
     * @return array attribute custom types (name=>array('width'=>null, 'data-options'=>null, 'editor'=>null,'formatter'=>null,'sortable'=>true))
     */
    public function attributeCustomTypes()
    {
        $model = new Pro_vehicle_order();
        $attributeOptions = $model->attributeCustomTypes();
        return $attributeOptions;
    }
    
    /**
     * 
     * @param \common\models\Pro_vehicle_order $model
     */
    public function load($model,$formName = NULL)
    {
        $attributes = $model->getAttributes();
        $skipKeys = ['id'=>1, 'created_at'=>1, 'updated_at'=>1];
        foreach ($attributes as $k => $v) {
            if (!isset($skipKeys[$k])) {
                $this->$k = $v;
            }
        }
        $userId = (\Yii::$app->user->isGuest ? 0 : \Yii::$app->user->id);
        if ($userId && \Yii::$app->user->identityClass != 'backend\models\Rbac_admin') {
            $userId = 0;
        }
        $this->edit_user_id = $userId;
        $this->yuyue_time = $this->start_time;
        $this->yuyue_end_time = $this->end_time;
    }
    
    public function getTotalPreferentialPrice() {
        return $this->price_preferential + $this->price_bonus_point_deduction + $this->price_gift;
    }
    
    public function getTotalDepositPrice() {
        return $this->price_deposit + $this->price_deposit_violation;
    }
    
    /**
     * 
     * @param array $config
     * @return \common\helpers\ExtendActiveDataProvider
     */
    public static function createDataProvider($config = array()) {
        $config['query'] = static::find();
        return Pro_vehicle_order::createDataProvider($config);
    }
    
}
