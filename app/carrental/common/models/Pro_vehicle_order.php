<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * Order model
 *
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
 * @property integer $paid_deposit              已付押金
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
 * @property integer $customer_driver_license_expire_time
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
class Pro_vehicle_order extends \common\helpers\ActiveRecordModel
{
    const STATUS_WAITING = 1;           // 待确认
    const STATUS_BOOKED = 2;            // 已预订
    const STATUS_PAID = 3;              // 虚拟状态 已支付 用于客户端
    const STATUS_RENTING = 10;          // 已承租
    const STATUS_VIOLATION_CHECKING = 100;// 违章待查
    const STATUS_COMPLETED = 101;       // 已完成
    const STATUS_CANCELLED = 400;       // 已取消
    
    const TYPE_PERSONAL = 1;            // 个人订单
    const TYPE_ENTERPRISE = 2;          // 公司订单
    const TYPE_UNIVERSAL = 3;           // 通用业务
    
    const PRICE_TYPE_OFFICE = 1;        // 门店价格
    const PRICE_TYPE_MULTIDAYS = 2;     // 打包价
    const PRICE_TYPE_ONLINE = 3;        // 在线支付价格
    const PRICE_TYPE_WEEK = 4;          // 7天打包价
    const PRICE_TYPE_MONTH = 5;         // 月租打包价
    const PRICE_TYPE_HOUR = 6;         // 单程租车小时价
    
    const PAY_TYPE_NONE = 0;            // 未交
    const PAY_TYPE_CASH = 1;            // 现金
    const PAY_TYPE_SWIPE_CARD = 2;      // 刷卡
    const PAY_TYPE_CHEQUE = 3;          // 支票
    const PAY_TYPE_ONLINE_BANKING = 4;  // 网银
    const PAY_TYPE_ALIPAY = 5;          // 支付宝
    const PAY_TYPE_WEIXIN = 6;          // 微信支付
    const PAY_TYPE_PRE_LICENSING = 7;   // 预授权
    const PAY_TYPE_MEMBER_CARD = 8;     // 会员卡
    const PAY_TYPE_KUAIQIAN = 9;     // 快钱
    const PAY_TYPE_ABC = 10;     // 农业银行扫码支付
    
    const ORDER_SOURCE_APP = 1;         // 手机APP下单
    const ORDER_SOURCE_WEBSITE = 2;     // 网站下单
    const ORDER_SOURCE_OFFICE = 3;      // 门店下单
    const ORDER_SOURCE_TELEPHONE = 4;   // 电话下单
    const ORDER_SOURCE_PROXY = 5;       // 代理下单
    const ORDER_SOURCE_OTHER = 6;       // 其他来源
    const ORDER_SOURCE_CTRIP = 7;       // 携程来源
    const ORDER_SOURCE_ZHIZUN = 8;       // 至尊来源
    const ORDER_SOURCE_TUANGOU = 9;       // 团购订单百度糯米
    const ORDER_SOURCE_XIAOCHENGXU = 10;       // 小程序
    
    const SETTLEMENT_TYPE_NONE = 0;
    const SETTLEMENT_TYPE_COMPLETED = 1;// 还车终结
    const SETTLEMENT_TYPE_INSTALLMENT = 2;// 分期结算
    const SETTLEMENT_TYPE_ONACCOUNT = 3;   // 还车挂账
	
    const ORDER_CODE_BUTTON_HIDDEN  = 0;//订单支付二维码按钮不显示
    const ORDER_CODE_BUTTON_SHOW    = 1;//订单支付二维码按钮显示
	
    
    private $_optionalServicePricesArray = null;

    private $_dailyRentDetailedPricesArray = null;
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
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
            'vehicle_model_id' => \Yii::t('locale', 'Vehicle model'),
            'vehicle_id' => \Yii::t('locale', 'Plate number'),
            'flag' => \Yii::t('locale', 'Plate flag'),
            'user_id' => \Yii::t('locale', 'User'),
            'status' => \Yii::t('locale', '{name} status', ['name'=>\Yii::t('locale', 'Order')]),
            'type' => \Yii::t('locale', '{name} type', ['name'=>\Yii::t('locale', 'Order')]),
            'vehicle_color' => \Yii::t('locale', 'Vehicle color'),
            'vehicle_oil_label' => \Yii::t('locale', 'Oil label'),
            'vehicle_outbound_mileage' => \Yii::t('locale', 'Outbound mileage'),
            'vehicle_inbound_mileage' => \Yii::t('locale', 'Inbound mileage'),
            'origin_vehicle_id' => \Yii::t('locale', 'Plate number'),
            'channel_serial' => \Yii::t('locale', '{name} No.', ['name'=>\Yii::t('locale', 'Channel order')]),
            'replace_vehicle_id' => \Yii::t('locale', 'Replace vehicle'),
            'start_time' => \Yii::t('locale', '{name} time', ['name'=>\Yii::t('carrental', 'Start rent car')]),
            'end_time' => \Yii::t('locale', '{name} time', ['name'=>\Yii::t('carrental', 'End rent car')]),
            'new_end_time' => \Yii::t('locale', '{name} time', ['name'=>\Yii::t('carrental', 'End rent car')]),
            'rent_days' => \Yii::t('carrental', 'Lease(day)'),
            'rent_hour' => \Yii::t('carrental', 'Lease(hour)'),
            'belong_office_id' => \Yii::t('carrental', 'Renting office'),
            'office_id_rent' => \Yii::t('locale', '{name} office', ['name'=>\Yii::t('carrental', 'Take car')]),
            'office_id_return' => \Yii::t('locale', '{name} office', ['name'=>\Yii::t('carrental', 'Return car')]),
            'pay_type' => \Yii::t('locale', '{name} type', ['name'=>\Yii::t('locale', 'Price')]),
            'pay_source' => \Yii::t('locale', '{name} payment method', ['name' => \Yii::t('locale', 'Rent')]),
            'total_amount' => \Yii::t('locale', '{type} price', ['type'=>\Yii::t('locale', 'Total')]),
            'paid_amount' => \Yii::t('locale', 'Paid amount'),
            'rent_per_day' => \Yii::t('locale', '{type} price', ['type'=>\Yii::t('locale', 'Per day')]),
            'rent_per_hour' => \Yii::t('locale', '{type} price', ['type'=>\Yii::t('locale', 'Per hour')]),
            'price_address_km' => \Yii::t('locale', 'Price address km'),
            'address_km' => \Yii::t('locale', 'Address km'),
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
            'price_preferential' => \Yii::t('locale', '{type} price', ['type'=>\Yii::t('locale', 'Preferential')]),
            'price_gift' => \Yii::t('locale', 'Gift amount'),
            'price_bonus_point_deduction' => \Yii::t('carrental', 'Bonus points deduction'),
            'unit_price_overtime' => \Yii::t('locale', '{type} overtime price standard', ['type'=>\Yii::t('locale', 'Personal driving')]),
            'unit_price_overmileage' => \Yii::t('locale', '{type} overmileage price standard', ['type'=>\Yii::t('locale', 'Personal driving')]),
            'unit_price_basic_insurance' => \Yii::t('locale', 'Basic insurance'),
            'unit_price_designated_driving' => \Yii::t('locale', '{type} price', ['type'=>\Yii::t('locale', 'Designated driving')]),
            'unit_price_designated_driving_overtime' => \Yii::t('locale', '{type} overtime price standard', ['type'=>\Yii::t('locale', 'Designated driving')]),
            'unit_price_designated_driving_overmileage' => \Yii::t('locale', '{type} overmileage price standard', ['type'=>\Yii::t('locale', 'Designated driving')]),
            'settlement_status' => \Yii::t('locale', '{name} status', ['name'=>\Yii::t('locale', 'Settlement')]),
            'preferential_type' => \Yii::t('locale', '{name} type', ['name'=>\Yii::t('locale', 'Preferential')]),
            'preferential_info' => \Yii::t('locale', '{name} info', ['name'=>\Yii::t('locale', 'Preferential')]),
            'deposit_pay_source' => \Yii::t('locale', '{name} payment method', ['name' => \Yii::t('locale', 'Deposit')]),
            'paid_deposit' => \Yii::t('locale', 'Paid {name}', ['name' => \Yii::t('locale', 'deposit')]),
            'settlement_pay_source' => \Yii::t('locale', '{name} payment method', ['name' => \Yii::t('locale', 'Settlement')]),
            'optional_service' => \Yii::t('locale', 'Value-added services'),
            'optional_service_info' => \Yii::t('locale', '{name} info', ['name'=>\Yii::t('locale', 'Value-added services')]),
            'used_gift_code' => \Yii::t('locale', 'Used preferential code'),
            'address_take_car' => \Yii::t('locale', '{name} address', ['name'=>\Yii::t('carrental', 'Delivery car to house')]),
            'address_return_car' => \Yii::t('locale', '{name} address', ['name'=>\Yii::t('carrental', 'Take car from house')]),
            'source' => \Yii::t('locale', 'Order source'),
            'customer_name' => \Yii::t('locale', 'Customer name'),
            'customer_telephone' => \Yii::t('locale', 'Contact number'),
            'customer_fixedphone' => \Yii::t('locale', 'Fixed phone number'),
            'customer_id_type' => \Yii::t('locale', 'ID type'),
            'customer_id' => \Yii::t('locale', 'ID number'),
            'customer_address' => \Yii::t('locale', '{name} address', ['name'=>\Yii::t('locale', 'Customer')]),
            'customer_postcode' => \Yii::t('locale', '{name} postcode', ['name'=>\Yii::t('locale', 'Customer')]),
            'customer_operator_name' => \Yii::t('locale', 'Customer operator name'),
            'customer_driver_license_time' => \Yii::t('carrental', 'Got driving license time'),
            'customer_driver_license_expire_time' => \Yii::t('carrental', 'Driver license expire date'),
            'customer_vip_level' => \Yii::t('locale', 'Member type'),
            'customer_employer' => \Yii::t('locale', 'Employer'),
            'customer_employer_address' => \Yii::t('locale', '{name} address', ['name'=>\Yii::t('locale', 'Employer')]),
            'customer_employer_phone' => \Yii::t('locale', 'Employer contact number'),
            'customer_employer_postcode' => \Yii::t('locale', '{name} postcode', ['name'=>\Yii::t('locale', 'Employer')]),
            'customer_employer_certificate_id' => \Yii::t('locale', 'Business license no.'),
            'emergency_contact_name' => \Yii::t('locale', 'Emergency contact'),
            'emergency_contact_phone' => \Yii::t('locale', 'Emergency contact telephone'),
            'refund_account_number' => \Yii::t('locale', 'Refund account number'),
            'refund_account_name' => \Yii::t('locale', 'Refund account name'),
            'refund_bank_name' => \Yii::t('locale', 'Refund bank name'),
            'refund_remark' => \Yii::t('locale', '{name} instruction', ['name'=>\Yii::t('locale', 'Refund')]),
            'inv_title' => \Yii::t('locale', 'Invoice title'),
            'inv_name' => \Yii::t('locale', 'Invoice receiver name'),
            'inv_tax_number' => \Yii::t('locale', 'Invoice tax number'),
            'inv_phone' => \Yii::t('locale', 'Invoice receiver phone'),
            'inv_amount' => \Yii::t('locale', 'Invoice amount'),
            'inv_address' => \Yii::t('locale', 'Invoice receiver address'),
            'inv_postcode' => \Yii::t('locale', 'Invoice receiver postcode'),
            'remark' => \Yii::t('locale', '{name} instruction', ['name'=>\Yii::t('locale', 'Remark')]),
            'settlement_remark' => \Yii::t('locale', '{name} instruction', ['name'=>\Yii::t('locale', 'Settlement')]),
            'edit_user_id' => \Yii::t('locale', 'Edit user'),
            'validation_id_0' => \Yii::t('carrental', 'Vehicle validation info').'('.\Yii::t('carrental', 'before vehicle dispath').')',
            'validation_id_1' => \Yii::t('carrental', 'Vehicle validation info').'('.\Yii::t('carrental', 'after vehicle returned').')',
            'booking_time' => \Yii::t('locale', 'Booking time'),
            'booking_left_time' => \Yii::t('locale', 'Left time'),
            'renting_left_time' => \Yii::t('locale', 'Left time'),
            'violation_left_time' => \Yii::t('locale', 'Left time'),
            'confirmed_at' => \Yii::t('locale', '{name} time', ['name' => \Yii::t('locale', 'Confirm')]),
            'car_dispatched_at' => \Yii::t('carrental', 'Vehicle dispathed time'),
            'car_returned_at' => \Yii::t('carrental', 'Vehicle returned time'),
            'settlemented_at' => \Yii::t('carrental', 'Settlemented time'),
            'settlement_user_id' => \Yii::t('carrental', 'Settlemented operator'),
            'created_at' => \Yii::t('locale', 'Create time'),
            'updated_at' => \Yii::t('locale', 'Update time'),
            'operation_waiting' => \Yii::t('locale', 'Operation'),
            'operation_waiting_oneway' => \Yii::t('locale', 'Operation'),
            'operation_booking' => \Yii::t('locale', 'Operation'),
            'operation_booking_oneway' => \Yii::t('locale', 'Operation'),
            'operation_renting_oneway' => \Yii::t('locale', 'Operation'),
            'operation_renting' => \Yii::t('locale', 'Operation'),
            'operation_violation' => \Yii::t('locale', 'Operation'),
            'operation_violation_oneway' => \Yii::t('locale', 'Operation'),
            'operation_complete' => \Yii::t('locale', 'Operation'),
            'operation_complete_oneway' => \Yii::t('locale', 'Operation'),
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
            'serial' => array('width' => 180, 'sortable' => 'true'),
            'vehicle_model_id' => array('width' => 120, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.vehicle_model_name; }"),
            'vehicle_id' => array('width' => 120, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.plate_number; }"),
            'flag' => array('width' => 120),
            'user_id' => array('width' => 120, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.user_name; }"),
            'status' => array('width' => 60, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(\common\components\OrderModule::getOrderStatusArray())." }"),
            'type' => array('width' => 60, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(\common\components\OrderModule::getOrderTypeArray())." }"),
            'vehicle_color' => array('width' => 80, 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(\common\components\VehicleModule::getVehicleColorsArray())." }"),
            'vehicle_oil_label' => array('width' => 100, 'sortable' => 'true'),
            'vehicle_outbound_mileage' => array('width' => 100),
            'vehicle_inbound_mileage' => array('width' => 100),
            'origin_vehicle_id' => array('width' => 100, 'sortable' => 'true'),
            'channel_serial' => array('width' => 180, 'sortable' => 'true'),
            'start_time' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'end_time' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'new_end_time' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'rent_days' => array('width' => 60, 'sortable' => 'true'),
            'belong_office_id' => array('width' => 100, 'formatter' => "function(value,row){ return row.belong_office_disp; }"),
            'office_id_rent' => array('width' => 100, 'formatter' => "function(value,row){ return row.rent_office_disp; }"),
            'office_id_return' => array('width' => 100, 'formatter' => "function(value,row){ return row.return_office_disp; }"),
            'pay_type' => array('width' => 80, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(\common\components\OrderModule::getPriceTypeArray())." }"),
            'pay_source' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(\common\components\OrderModule::getOrderPayTypeArray())." }"),
            'total_amount' => array('width' => 100, 'sortable' => 'true'),
            'price_rent' => array('width' => 100, 'sortable' => 'true'),
            'paid_amount' => array('width' => 100, 'sortable' => 'true'),
            'rent_per_day' => array('width' => 100, 'sortable' => 'true'),
            'rent_per_hour' => array('width' => 100, 'sortable' => 'true'),
            'price_left' => array('width' => 100, 'formatter' => "function(value,row){return row.total_amount - row.paid_amount;}"),
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
            'price_preferential' => array('width' => 100),
            'price_gift' => array('width' => 100),
            'price_bonus_point_deduction' => array('width' => 100),
            'unit_price_overtime' => array('width' => 100, 'sortable' => 'true'),
            'unit_price_overmileage' => array('width' => 100, 'sortable' => 'true'),
            'unit_price_basic_insurance' => array('width' => 100, 'sortable' => 'true'),
            'unit_price_designated_driving' => array('width' => 100, 'sortable' => 'true'),
            'unit_price_designated_driving_overtime' => array('width' => 100, 'sortable' => 'true'),
            'unit_price_designated_driving_overmileage' => array('width' => 100, 'sortable' => 'true'),
            'settlement_status' => array('width' => 80, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(\common\components\OrderModule::getSettlementTypeArray())." }"),
            'preferential_type' => array('width' => 100),
            'preferential_info' => array('width' => 100),
            'deposit_pay_source' => array('width' => 100, 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(\common\components\OrderModule::getOrderPayTypeArray())." }"),
            'paid_deposit' => array('width' => 100, 'sortable' => 'true'),
            'settlement_pay_source' => array('width' => 100, 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(\common\components\OrderModule::getOrderPayTypeArray())." }"),
            'optional_service' => array('width' => 100),
            'optional_service_info' => array('width' => 100),
            'used_gift_code' => array('width' => 100),
            'address_take_car' => array('width' => 100),
            'address_return_car' => array('width' => 100),
            'source' => array('width' => 80, 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(\common\components\OrderModule::getOrderSourceArray())." }"),
            'customer_name' => array('width' => 80),
            'customer_telephone' => array('width' => 80),
            'customer_fixedphone' => array('width' => 80),
            'customer_id_type' => array('width' => 80),
            'customer_id' => array('width' => 80),
            'customer_address' => array('width' => 80),
            'customer_postcode' => array('width' => 80),
            'customer_operator_name' => array('width' => 80),
            'customer_driver_license_time' => array('width' => 80),
            'customer_driver_license_expire_time' => array('width' => 80),
            'customer_vip_level' => array('width' => 80),
            'customer_employer' => array('width' => 80),
            'customer_employer_address' => array('width' => 80),
            'customer_employer_phone' => array('width' => 80),
            'customer_employer_postcode' => array('width' => 80),
            'customer_employer_certificate_id' => array('width' => 80),
            'emergency_contact_name' => array('width' => 80),
            'emergency_contact_phone' => array('width' => 80),
            'refund_account_number' => array('width' => 80),
            'refund_account_name' => array('width' => 80),
            'refund_bank_name' => array('width' => 80),
            'refund_remark' => array('width' => 80),
            'inv_title' => array('width' => 120),
            'inv_name' => array('width' => 80),
            'inv_tax_number' => array('width' => 80),
            'inv_phone' => array('width' => 80),
            'inv_amount' => array('width' => 80),
            'inv_address' => array('width' => 120),
            'inv_postcode' => array('width' => 80),
            'remark' => array('width' => 120),
            'settlement_remark' => array('width' => 120),
            'edit_user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.edit_user_disp; }"),
            'booking_time' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(row.created_at);}"),
            'booking_left_time' => array('width' => 100, 'formatter' => "function(value,row){ return $.custom.utils.secondsToHuman(".\common\components\OrderModule::ORDER_MAX_WAITING_SECONDS." - (Math.floor($.now()/1000) - parseInt(row.created_at)));}"),
            'renting_left_time' => array('width' => 100, 'formatter' => "function(value,row){return $.custom.utils.secondsToHuman(parseInt(row.new_end_time) - Math.floor($.now()/1000));}"),
            'violation_left_time' => array('width' => 100, 'formatter' => "function(value,row){return $.custom.utils.secondsToHuman(".\common\components\OrderModule::ORDER_VIOLATION_CHECKING_DURATION." - (Math.floor($.now()/1000) - parseInt(row.car_returned_at)));}"),
            'confirmed_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'car_dispatched_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'car_returned_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'settlemented_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'settlement_user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.settlement_user_disp; }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation_waiting' => array('width' => 250, 
                'buttons' => array(
                    \Yii::$app->user->can('order/edit') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['order/edit', 'id'=>'']), 'name' => Yii::t('locale', 'Confirm order'), 'title' => Yii::t('locale', 'Confirm order'), 'paramField' => 'id', 'icon' => 'icon-ok', 'showText'=>true) : null,
                    \Yii::$app->user->can('order/cancel') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['order/cancel', 'id'=>'']), 'name' => Yii::t('locale', 'Cancel order'), 'title' => Yii::t('locale', 'Cancel order'), 'paramField' => 'id', 'icon' => 'icon-delete', 'showText'=>true) : null,
                    \Yii::$app->user->can('order/paymentdetail_index') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['order/paymentdetail_index', 'serial'=>'']), 'name' => Yii::t('locale', 'Payment details'), 'title' => Yii::t('locale', 'Order payment details'), 'paramField' => 'serial', 'icon' => 'icon-book_tabs', 'showText'=>true) : null,
                ),
            ),
			
		
            'operation_booking' => array('width' => 290, 
                'buttons' => array(
                    \Yii::$app->user->can('order/confirm_booked') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['order/confirm_booked', 'id'=>'']), 'condition'=>array("{field} == 0", array('{field}'=>'confirmed_at')), 'name' => Yii::t('locale', 'Confirm'), 'title' => Yii::t('locale', 'Confirm order'), 'paramField' => 'id', 'icon' => '', 'showText'=>true) : null,
                    array('type' => 'ajax', 'condition'=>array("{field} != 0", array('{field}'=>'confirmed_at')), 'name' => Yii::t('carrental', 'Confirmed'), 'title' => Yii::t('carrental', 'Confirmed'), 'icon' => '', 'showText'=>true),
                    \Yii::$app->user->can('order/edit') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['order/edit', 'id'=>'']), 'name' => \Yii::t('locale', '{name} order', ['name'=>Yii::t('locale', 'Edit')]), 'title' => \Yii::t('locale', '{name} order', ['name'=>Yii::t('locale', 'Edit')]), 'paramField' => 'id', 'icon' => 'icon-edit', 'showText' => true) : null,
                    //\Yii::$app->user->can('vehicle/validation') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['vehicle/validation', 'purpose'=>'vehicle_dispatch'])."&order_id=", 'name' => Yii::t('carrental', 'Dispatch vehicle'), 'title' => Yii::t('carrental', 'Dispatch vehicle'), 'paramField' => 'id', 'icon' => 'icon-ok', 'showText'=>true) : null,
                    \Yii::$app->user->can('order/cancel') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['order/cancel', 'id'=>'']), 'name' => Yii::t('locale', 'Cancel order'), 'title' => Yii::t('locale', 'Cancel order'), 'paramField' => 'id', 'icon' => 'icon-delete', 'showText'=>true) : null,
                    \Yii::$app->user->can('order/paymentdetail_index') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['order/paymentdetail_index', 'serial'=>'']), 'name' => Yii::t('locale', 'Payment details'), 'title' => Yii::t('locale', 'Order payment details'), 'paramField' => 'serial', 'icon' => 'icon-book_tabs', 'showText'=>true) : null,
                ),
            ),
            'operation_renting' => array('width' => 200, 
                'buttons' => array(
                    \Yii::$app->user->can('order/order_relet') ? array('type' => 'window', 'url' => \yii\helpers\Url::to(['order/order_relet', 'id'=>'']), 'name' => Yii::t('locale', 'Relet'), 'title' => Yii::t('locale', 'Relet'), 'paramField' => 'id', 'icon' => '', 'showText' => true) : null,
                    \Yii::$app->user->can('order/settlement') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['order/settlement', 'id'=>'']), 'name' => Yii::t('locale', 'Settlement'), 'title' => Yii::t('locale', 'Settlement'), 'paramField' => 'id', 'icon' => '', 'showText' => true) : null,
                    \Yii::$app->user->can('order/paymentdetail_index') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['order/paymentdetail_index', 'serial'=>'']), 'name' => Yii::t('locale', 'Payment details'), 'title' => Yii::t('locale', 'Order payment details'), 'paramField' => 'serial', 'icon' => 'icon-book_tabs', 'showText'=>true) : null,
                ),
            ),
            'operation_violation' => array('width' => 210, 
                'buttons' => array(
                    \Yii::$app->user->can('vehicle/violation_info') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['vehicle/violation_info', 'purpose'=>'order'])."&order_id=", 'name' => Yii::t('carrental', 'Violation input'), 'title' => Yii::t('carrental', 'Violation input'), 'paramField' => 'id', 'icon' => '', 'showText' => true) : null,
                    \Yii::$app->user->can('order/complete') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['order/complete', 'id'=>'']), 'name' => Yii::t('locale', 'Order completed'), 'title' => Yii::t('locale', 'Order completed'), 'paramField' => 'id', 'icon' => '', 'showText'=>true) : null,
                    \Yii::$app->user->can('order/paymentdetail_index') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['order/paymentdetail_index', 'serial'=>'']), 'name' => Yii::t('locale', 'Payment details'), 'title' => Yii::t('locale', 'Order payment details'), 'paramField' => 'serial', 'icon' => 'icon-book_tabs', 'showText'=>true) : null,
                ),
            ),
            'operation_complete' => array('width' => 150, 
                'buttons' => array(
                    \Yii::$app->user->can('vehicle/violation_info') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['vehicle/violation_info', 'purpose'=>'order'])."&order_id=", 'name' => Yii::t('carrental', 'Violation input'), 'title' => Yii::t('carrental', 'Violation input'), 'paramField' => 'id', 'icon' => '', 'showText' => true) : null,
                    \Yii::$app->user->can('order/paymentdetail_index') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['order/paymentdetail_index', 'serial'=>'']), 'name' => Yii::t('locale', 'Payment details'), 'title' => Yii::t('locale', 'Order payment details'), 'paramField' => 'serial', 'icon' => 'icon-book_tabs', 'showText'=>true) : null,
                ),
            ), 
			
			
			//单程往返
			'operation_waiting_oneway' => array('width' => 250, 
                'buttons' => array(
                    \Yii::$app->user->can('order/edit') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['orderhour/edit', 'id'=>'']), 'name' => Yii::t('locale', 'Confirm order'), 'title' => Yii::t('locale', 'Confirm order'), 'paramField' => 'id', 'icon' => 'icon-ok', 'showText'=>true) : null,
                    \Yii::$app->user->can('order/cancel') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['orderhour/cancel', 'id'=>'']), 'name' => Yii::t('locale', 'Cancel order'), 'title' => Yii::t('locale', 'Cancel order'), 'paramField' => 'id', 'icon' => 'icon-delete', 'showText'=>true) : null,
                    \Yii::$app->user->can('order/paymentdetail_index') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['orderhour/paymentdetail_index', 'serial'=>'']), 'name' => Yii::t('locale', 'Payment details'), 'title' => Yii::t('locale', 'Order payment details'), 'paramField' => 'serial', 'icon' => 'icon-book_tabs', 'showText'=>true) : null,
                ),
            ),
			'operation_booking_oneway' => array('width' => 290, 
                'buttons' => array(
                    \Yii::$app->user->can('order/confirm_booked') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['orderhour/confirm_booked', 'id'=>'']), 'condition'=>array("{field} == 0", array('{field}'=>'confirmed_at')), 'name' => Yii::t('locale', 'Confirm'), 'title' => Yii::t('locale', 'Confirm order'), 'paramField' => 'id', 'icon' => '', 'showText'=>true) : null,
                    array('type' => 'ajax', 'condition'=>array("{field} != 0", array('{field}'=>'confirmed_at')), 'name' => Yii::t('carrental', 'Confirmed'), 'title' => Yii::t('carrental', 'Confirmed'), 'icon' => '', 'showText'=>true),
                    \Yii::$app->user->can('order/edit') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['orderhour/edit', 'id'=>'']), 'name' => \Yii::t('locale', '{name} order', ['name'=>Yii::t('locale', 'Edit')]), 'title' => \Yii::t('locale', '{name} order', ['name'=>Yii::t('locale', 'Edit')]), 'paramField' => 'id', 'icon' => 'icon-edit', 'showText' => true) : null,
            
                    \Yii::$app->user->can('order/cancel') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['orderhour/cancel', 'id'=>'']), 'name' => Yii::t('locale', 'Cancel order'), 'title' => Yii::t('locale', 'Cancel order'), 'paramField' => 'id', 'icon' => 'icon-delete', 'showText'=>true) : null,
                    \Yii::$app->user->can('order/paymentdetail_index') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['orderhour/paymentdetail_index', 'serial'=>'']), 'name' => Yii::t('locale', 'Payment details'), 'title' => Yii::t('locale', 'Order payment details'), 'paramField' => 'serial', 'icon' => 'icon-book_tabs', 'showText'=>true) : null,
                ),
            ),
			'operation_renting_oneway' => array('width' => 200, 
                'buttons' => array(
                    \Yii::$app->user->can('order/settlement') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['orderhour/settlement', 'id'=>'']), 'name' => Yii::t('locale', 'Settlement'), 'title' => Yii::t('locale', 'Settlement'), 'paramField' => 'id', 'icon' => '', 'showText' => true) : null,
                    \Yii::$app->user->can('order/paymentdetail_index') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['orderhour/paymentdetail_index', 'serial'=>'']), 'name' => Yii::t('locale', 'Payment details'), 'title' => Yii::t('locale', 'Order payment details'), 'paramField' => 'serial', 'icon' => 'icon-book_tabs', 'showText'=>true) : null,
                ),
            ),
			
			'operation_violation_oneway' => array('width' => 210, 
                'buttons' => array(
                    \Yii::$app->user->can('vehicle/violation_info') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['vehicle/violation_info', 'purpose'=>'order'])."&order_id=", 'name' => Yii::t('carrental', 'Violation input'), 'title' => Yii::t('carrental', 'Violation input'), 'paramField' => 'id', 'icon' => '', 'showText' => true) : null,
                    \Yii::$app->user->can('order/complete') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['orderhour/complete', 'id'=>'']), 'name' => Yii::t('locale', 'Order completed'), 'title' => Yii::t('locale', 'Order completed'), 'paramField' => 'id', 'icon' => '', 'showText'=>true) : null,
                    \Yii::$app->user->can('order/paymentdetail_index') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['orderhour/paymentdetail_index', 'serial'=>'']), 'name' => Yii::t('locale', 'Payment details'), 'title' => Yii::t('locale', 'Order payment details'), 'paramField' => 'serial', 'icon' => 'icon-book_tabs', 'showText'=>true) : null,
                ),
            ),
			
			'operation_complete_oneway' => array('width' => 150, 
                'buttons' => array(
                    \Yii::$app->user->can('vehicle/violation_info') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['vehicle/violation_info', 'purpose'=>'order'])."&order_id=", 'name' => Yii::t('carrental', 'Violation input'), 'title' => Yii::t('carrental', 'Violation input'), 'paramField' => 'id', 'icon' => '', 'showText' => true) : null,
                    \Yii::$app->user->can('order/paymentdetail_index') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['orderhour/paymentdetail_index', 'serial'=>'']), 'name' => Yii::t('locale', 'Payment details'), 'title' => Yii::t('locale', 'Order payment details'), 'paramField' => 'serial', 'icon' => 'icon-book_tabs', 'showText'=>true) : null,
                ),
            ),
			
        );
    }
    
    public function setSerialNo() {
        if (empty($this->serial)) {
            $id = $this->getSerialNoHighPart() + static::getAutoIncreamentId();
            
            $this->serial = \common\components\Consts::VEHICLE_TRADE_NO_PREFIX.$id;
        }
        return $this->serial;
    }
    
    public function getSerialNoHighPart() {
        return $this->type * 100000000000 + $this->source * 10000000000 + ($this->vehicle_model_id%10000)*1000000;
    }
    
    public function setOptionalServices($arrServices) {
        $flags = 0;
        $totalPrice = 0;
        $arrInfoTexts = [];
        $this->_optionalServicePricesArray = [];
        $rentTimeData = \common\models\Pri_renttime_data::create($this->start_time, $this->new_end_time,$this->pay_type);
        foreach ($arrServices as $row) {
            $flags |= $row->type;
            $price = $row->getActualUnitPrice();
            $count = $row->getActualCount($rentTimeData);
            
            $arrInfoTexts[] = "{$row->id}:{$price}:{$count}";
            $totalPrice += $price * $count;
            
            $this->_optionalServicePricesArray[$row->id] = ['price'=>$price, 'count'=>$count];
        }
        
        $this->optional_service = $flags;
        $this->price_optional_service = $totalPrice;
        $this->optional_service_info = implode(";", $arrInfoTexts);
    }
    
    public function getOptionalServicePriceArray() {
        if ($this->_optionalServicePricesArray === null) {
            
            $arr = explode(';', $this->optional_service_info);
            $this->_optionalServicePricesArray = [];
            foreach ($arr as $v0) {
                $v1 = explode(':', $v0);
                if (count($v1) > 2) {
                    $this->_optionalServicePricesArray[intval($v1[0])] = ['price'=>floatval($v1[1]), 'count'=>intval($v1[2])];
                }
            }
        }
        return $this->_optionalServicePricesArray;
    }
    
    public function getDailyRentDetailedPriceArray() {
        if ($this->_dailyRentDetailedPricesArray === null) {
            $this->_dailyRentDetailedPricesArray = [];
            if (!empty($this->daily_rent_detailed_info)) {
                $arr = explode(',', $this->daily_rent_detailed_info);
                foreach ($arr as $v) {
                    $v1 = explode(':', $v);
                    $v2 = floatval($v1[0]);
                    if (count($v1) > 1) {
                        $c = intval($v1[1]);
                        for($_i = 0; $_i < $c; $_i++) {
                            $this->_dailyRentDetailedPricesArray[] = $v2;
                        }
                    }
                    else {
                        $this->_dailyRentDetailedPricesArray[] = $v2;
                    }
                }
            }
            // support for older version of orders that do not have daily rent details
            if (count($this->_dailyRentDetailedPricesArray) < $this->rent_days) {
                $arrPriceInfo = \common\components\OrderModule::calculateVehicleModelRentPriceData($this->vehicle_model_id, $this->start_time, $this->new_end_time, $this->belong_office_id, $this->source, $this->pay_type, $this->getCustomerBirthday());
                $arrDetails = $arrPriceInfo['details'];
                for($i = 0; $i < $this->rent_days; $i++) {
                    if (isset($arrDetails[$i])) {
                        if (!isset($this->_dailyRentDetailedPricesArray[$i])) {
                            $this->_dailyRentDetailedPricesArray[$i] = $arrDetails[$i];
                        }
                    }
                }
                   /* // sjj
                    $d=array('vehicle_model_id'=>$this->vehicle_model_id, 'start_time'=>$this->start_time, 'new_end_time'=>$this->new_end_time, 'belong_office_id'=>$this->belong_office_id, 'source'=>$this->source, 'pay_type'=>$this->pay_type, 'getCustomerBirthday'=>$this->getCustomerBirthday());
                    $D=json_encode($d);
                    $f=json_encode($this->_dailyRentDetailedPricesArray);
                    file_put_contents('preview.txt',"D=>$D\ntotal_amount2=>$f\n",FILE_APPEND);
                    // sjj*/
                
                $this->_setDailyRentDetailedPriceArray($this->_dailyRentDetailedPricesArray);
            }
        }
        return $this->_dailyRentDetailedPricesArray;
    }
    
    public function appendDailyRentDetailedPriceArray($offset, $arrPrices) {
        $arr = $this->getDailyRentDetailedPriceArray();
        for ($i = $offset; $i < $this->rent_days; $i++) {
            if (isset($arrPrices[$i - $offset])) {
                $arr[$i] = $arrPrices[$i - $offset];
            }
        }
        $this->_setDailyRentDetailedPriceArray($arr);
    }
    
    public function resetDailyRentDetailedPriceArrayInfoWithData($strRentDetailedInfo, $reFormatPriceInfo = false) {
        $this->_dailyRentDetailedPricesArray = null;
        $this->daily_rent_detailed_info = $strRentDetailedInfo;
        $arr = $this->getDailyRentDetailedPriceArray();
        if ($reFormatPriceInfo) {
            $this->_setDailyRentDetailedPriceArray($arr);
        }
        return $arr;
    }
    
    private function _setDailyRentDetailedPriceArray($arr) {
        $this->_dailyRentDetailedPricesArray = $arr;
        $tmpArr = [];
        $lastValue = -1;
        $count = 0;
        foreach ($this->_dailyRentDetailedPricesArray as $v) {
            if ($v != $lastValue) {
                if ($lastValue >= 0 && $count > 0) {
                    $tmpArr[] = (($count==1) ? $lastValue : $lastValue.':'.$count);
                }
                
                $lastValue = $v;
                $count = 0;
            }
            
            $count++;
        }
        if ($count > 0 && $lastValue >= 0) {
            $tmpArr[] = (($count==1) ? $lastValue : $lastValue.':'.$count);
        }
        $this->daily_rent_detailed_info = implode(',', $tmpArr);
    }

    public function calculateTotalPrice() {
        $rentTimeData = \common\models\Pri_renttime_data::create($this->start_time, $this->new_end_time,$this->pay_type);
		
		if($this->pay_type == 6){
			if($this->flag > 0){
				$oneWayTimePrice = \common\models\Pro_vehicle_fee_plan::getOneWayTimePrice($this->flag);
				$rentMinute = $this->new_end_time - $this->end_time;
				if($rentMinute <= 600){
					
				}else if($rentMinute >600 && $rentMinute<= 1800){
					$this->price_overtime = $this->rent_per_day + $oneWayTimePrice['oneWayOverTimeMinutePrice'] * $oneWayTimePrice['oneWayOilByKm'];
				}else if($rentMinute >1800 && $rentMinute<= 3600){
					$this->price_overtime = $this->rent_per_day + $oneWayTimePrice['oneWayOverTimeHoursPrice'] * $oneWayTimePrice['oneWayOilByKm'];
				}else if($rentMinute > 3600){
					$this->price_overtime = $this->rent_per_day + $oneWayTimePrice['oneWayOverTimeHoursPrice'] * $oneWayTimePrice['oneWayOilByKm'] * ceil($rentMinute / 3600);
				}
			
			}
		}else{
			$this->price_overtime = $this->unit_price_overtime * $rentTimeData->hours;
		}
		
        $this->price_basic_insurance = $this->unit_price_basic_insurance * $rentTimeData->days;
        
        $arrOptionalServices = $this->getOptionalServicePriceArray();
        if (isset($arrOptionalServices[Pro_service_price::ID_DESIGNATED_DRIVING])) {
            $this->price_designated_driving = $this->unit_price_designated_driving * $rentTimeData->days;
        }
        
        $totalPrice = $this->price_rent + $this->price_designated_driving + $this->price_optional_service + $this->price_poundage;
        $totalPrice += $this->price_overtime + $this->price_overmileage + $this->price_designated_driving_overtime + $this->price_designated_driving_overmileage;
        $totalPrice += $this->price_oil + $this->price_oil_agency + $this->price_car_damage + $this->price_violation + $this->price_other;
        $totalPrice += $this->price_basic_insurance + $this->price_insurance_overtime;
        $totalPrice += $this->price_different_office + $this->price_take_car + $this->price_return_car;
        $totalPrice -= $this->getTotalPreferentialPrice();
		if($this->pay_type == 6){//单程出租加公里油耗
			$totalPrice += $this->price_address_km;
		}
        $this->total_amount = $totalPrice;
        $this->rent_per_day = round($rentTimeData->days ? $this->price_rent / $rentTimeData->days : 0, 2);
        return $totalPrice;
    }
	
	public function calculateOneWayTotalPrice(){
	
        $rentTimeData = \common\models\Pri_renttime_data::create($this->start_time, $this->end_time,$this->pay_type);
		
		if($this->pay_type == 6){
			if($this->flag > 0){
				$oneWayTimePrice = \common\models\Pro_vehicle_fee_plan::getOneWayTimePrice($this->flag);
				//car_returned_at new_end_time
				$rentMinute = $this->car_returned_at - $this->end_time;
				if($rentMinute <= 600){
					$this->price_overtime = 0;
				}else if($rentMinute >600 && $rentMinute<= 1800){
					$this->price_overtime = $this->rent_per_day + $oneWayTimePrice['oneWayOverTimeMinutePrice'] * $oneWayTimePrice['oneWayOilByKm'];
				}else if($rentMinute >1800 && $rentMinute<= 3600){
					$this->price_overtime = $this->rent_per_day + $oneWayTimePrice['oneWayOverTimeHoursPrice'] * $oneWayTimePrice['oneWayOilByKm'];
				}else if($rentMinute > 3600){
					$this->price_overtime = $this->rent_per_day + $oneWayTimePrice['oneWayOverTimeHoursPrice'] * $oneWayTimePrice['oneWayOilByKm'] * ceil($rentMinute / 3600);
				}
			}
		}
		
        $this->price_basic_insurance = $this->unit_price_basic_insurance * $rentTimeData->days;
        
        $arrOptionalServices = $this->getOptionalServicePriceArray();
        if (isset($arrOptionalServices[Pro_service_price::ID_DESIGNATED_DRIVING])) {
            $this->price_designated_driving = $this->unit_price_designated_driving * $rentTimeData->days;
        }
		
        
        $totalPrice = $this->price_rent + $this->price_designated_driving + $this->price_optional_service + $this->price_poundage;
        $totalPrice += $this->price_overtime + $this->price_overmileage + $this->price_designated_driving_overtime + $this->price_designated_driving_overmileage;
        $totalPrice += $this->price_oil + $this->price_oil_agency + $this->price_car_damage + $this->price_violation + $this->price_other;
        $totalPrice += $this->price_basic_insurance + $this->price_insurance_overtime;
        $totalPrice += $this->price_different_office + $this->price_take_car + $this->price_return_car;
        $totalPrice -= $this->getTotalPreferentialPrice();
		if($this->pay_type == 6){//单程出租加公里油耗
			$totalPrice += $this->price_address_km;
		}
        $this->total_amount = $totalPrice;
        $this->rent_per_day = round($rentTimeData->days ? $this->price_rent / $rentTimeData->days : 0, 2);
        // print_r($totalPrice);exit;
        return $totalPrice;
	}
    
    public function onUpdateEndTime($endTime) {
        if ($endTime < $this->start_time) {
            \Yii::error("order:{$this->id} serial:{$this->serial} on update to end_time:{$endTime} while the time less than order start_time:{$this->start_time}", 'order');
            return false;
        }
        
        $preferentialType = $this->preferential_type;
        //if ($this->preferential_type > 0) {
        //    $objPreferential = Pro_preferential_info::findById($this->preferential_type);
        //    if ($objPreferential) {
        //        $preferentialType = $objPreferential->process_type;
        //    }
        //}
        
        //\Yii::warning("order:{$this->id} serial:{$this->serial} on relete to time:{$endTime} recalculate.", 'order');
        $originEndtime = $this->new_end_time;
        $originTotalPrice = $this->total_amount;
        $originServicePrice = $this->price_optional_service;
        $originOvertimePrice = $this->price_overtime;
        
        $arrDeltaData = ['price'=>0, 'details'=>[], 'origin_overtime_price'=>$originOvertimePrice, 'now_overtime_price'=>0, 'optional_service'=>0];
        
        $originRentTimeData = \common\models\Pri_renttime_data::create($this->start_time, $originEndtime);
        $rentTimeData = \common\models\Pri_renttime_data::create($this->start_time, $endTime);
        
        if (static::isMultidaysPackagePriceType($this->pay_type)) {
            if ($rentTimeData->days < $originRentTimeData->days) {
                \Yii::warning("order:{$this->serial} update end time:{$rentTimeData->endTime} earliear than origin end time:{$originRentTimeData->endTime}, this is not allowed.", 'order');
                $rentTimeData->days = $originRentTimeData->days;
                $rentTimeData->hours = $originRentTimeData->hours;
                $endTime = $originRentTimeData->endTime;
            }
        }
        //\Yii::error("######## [{$originRentTimeData->days}, {$originRentTimeData->hours}] [{$rentTimeData->days}, {$rentTimeData->hours}]", 'order');
        if ($preferentialType == \common\components\Consts::PROCESS_TYPE_FIRST_RENTAL_GIFT_ONE_DAY && $this->price_preferential > 0) {
            if ($rentTimeData->days < 2) {
                $rentTimeData->days = 2;
                $rentTimeData->hours = 0;
                $endTime = $rentTimeData->startTime + 86400*2;
            }
        }
        elseif ($rentTimeData->days < $originRentTimeData->days) {
            // 提前还车，若提前还车时间有余超时时间，按一天算。
            if ($this->status >= static::STATUS_RENTING && ($rentTimeData->hours > 0 || ($rentTimeData->startTime + 86400*($rentTimeData->days)) < ($rentTimeData->endTime - \common\components\Consts::AHEAD_RETURN_CAR_ALLOW_DELTA_SECONDS))) {
                $rentTimeData->days++;
                $rentTimeData->hours = 0;
                $endTime = $rentTimeData->startTime + 86400*$rentTimeData->days;
            }
        }
        
        $this->new_end_time = $endTime;
        $this->rent_days = $rentTimeData->days;
        //sjj
        $old_source=$this->source;
        // file_put_contents('preview.txt',"source=>$d\n",FILE_APPEND);
        if($this->pay_type == 3){//在线价格
            $this->source = 1;//1是手机订单
        }else{
            $this->source = 3;//3是门店订单
        }
        //sjj
        if ($rentTimeData->days == $originRentTimeData->days) {
            if ($rentTimeData->hours == $originRentTimeData->hours) {
                $arr = $this->getDailyRentDetailedPriceArray();
                    // sjj
                    // $d=$this->price_rent;
                    // $f=json_encode($arr);
                    // file_put_contents('preview.txt',"D=>$d\ntotal_amount2=>$f\n",FILE_APPEND);
                    // sjj
                if (count($arr) < $rentTimeData->days) {
                    $arrPriceInfo = \common\components\OrderModule::calculateVehicleModelRentPriceData($this->vehicle_model_id, $this->start_time, $endTime, $this->belong_office_id, $this->source, $this->pay_type, $this->getCustomerBirthday());
                    if ($arrPriceInfo) {
                        $arr = $arrPriceInfo['details'];
                        $this->price_rent = $arrPriceInfo['price'];
                        $this->_setDailyRentDetailedPriceArray($arr);
                    }
                }
                else {
                    $price = 0;
                    for ($i = 0; $i < $rentTimeData->days; $i++) {
                        $price += $arr[$i];
                    }
                    $this->price_rent = $price;
                    $this->_dailyRentDetailedPricesArray = array_slice($arr, 0, $rentTimeData->days);
                    $this->_setDailyRentDetailedPriceArray($this->_dailyRentDetailedPricesArray);
                }
                //return $arrDeltaData;
            }
            else {
                $this->price_overtime = $this->unit_price_overtime * $rentTimeData->hours;
            }
        }
        else {
            if ($rentTimeData->days > $originRentTimeData->days) {
                $startTime = $this->start_time + $originRentTimeData->days * 86400;
                if ($startTime < $endTime) {
                    $arrPriceInfo = \common\components\OrderModule::calculateVehicleModelRentPriceData($this->vehicle_model_id, $startTime, $endTime, $this->belong_office_id, $this->source, $this->pay_type, $this->getCustomerBirthday());
                    if ($arrPriceInfo) {
                        $this->price_rent += $arrPriceInfo['price'];
                        $this->appendDailyRentDetailedPriceArray($originRentTimeData->days, $arrPriceInfo['details']);

                        foreach ($arrPriceInfo['details'] as $v) {
                            $arrDeltaData['details'][] = $v;
                        }
                    }
                }

                $arrDeltaData['calc_start_time'] = $startTime;
            }
            else {
                $arr = $this->getDailyRentDetailedPriceArray();
                if (count($arr) < $rentTimeData->days) {
                    $arrPriceInfo = \common\components\OrderModule::calculateVehicleModelRentPriceData($this->vehicle_model_id, $this->start_time, $endTime, $this->belong_office_id, $this->source, $this->pay_type, $this->getCustomerBirthday());
                    if ($arrPriceInfo) {
                        $arr = $arrPriceInfo['details'];
                        $this->price_rent = $arrPriceInfo['price'];
                        $this->_setDailyRentDetailedPriceArray($arr);
                    }
                }
                else {
                    $price = 0;
                    for ($i = 0; $i < $rentTimeData->days; $i++) {
                        $price += $arr[$i];
                    }
                    $this->price_rent = $price;
                    $this->_dailyRentDetailedPricesArray = array_slice($arr, 0, $rentTimeData->days);
                    $this->_setDailyRentDetailedPriceArray($this->_dailyRentDetailedPricesArray);
                }
            }
            
            $this->price_overtime = $this->unit_price_overtime * $rentTimeData->hours;
            
            $arrOptionalServices = $this->getOptionalServicePriceArray();
            if (!empty($arrOptionalServices)) {
                $arrRows = Pro_service_price::findAllServicePrices($this->belong_office_id, array_keys($arrOptionalServices));
                if ($arrRows) {
                    $this->setOptionalServices($arrRows);
                }
            }
        }
        
        $this->calculateTotalPrice();
        
        $arrDeltaData['price'] = $this->total_amount - $originTotalPrice;
        $arrDeltaData['now_overtime_price'] = $this->price_overtime;
        // $arrDeltaData['optional_service'] = $this->price_optional_service - $originServicePrice;
        $arrDeltaData['optional_service'] = $this->price_optional_service ;

        //sjj其他费用:加油费+车损费+违章费用+超时保费+加油代办价格+其他价格+个人自驾超时费用
        $arrDeltaData['other_price'] = $this->price_oil + $this->price_car_damage + $this->price_violation + $this->price_insurance_overtime + $this->price_oil_agency + $this->price_other/* + $this->price_overtime*/;
        $arrDeltaData['price_preferential'] = $this->price_preferential ;
        $arrDeltaData['price_poundage'] = $this->price_poundage ;//手续费
        $arrDeltaData['unit_price_basic_insurance'] = $this->unit_price_basic_insurance ;//基本服务费费
        $arrDeltaData['price_different_office'] = $this->price_different_office ;//异店还车费
        $arrDeltaData['price_take_car'] = $this->price_take_car ;//送车上门服务费
        $arrDeltaData['price_return_car'] = $this->price_return_car ;//上门取车服务费

        if($this->pay_type == 3){//在线价格
            $this->source = $old_source;//1是手机订单
        }else{
            $this->source = $old_source;//3是门店订单
        }
        // sjj
        
        return $arrDeltaData;
    }

    /*单程租车总价格计算等*/
    public function onOneWayUpdateEndTime($endTime) {
        if ($endTime < $this->start_time) {
            \Yii::error("order:{$this->id} serial:{$this->serial} on update to end_time:{$endTime} while the time less than order start_time:{$this->start_time}", 'order');
            return false;
        }
        
        $preferentialType = $this->preferential_type;
        $originEndtime = $this->end_time;
        $originTotalPrice = $this->total_amount;
        $originServicePrice = $this->price_optional_service;
        $originOvertimePrice = $this->price_overtime;
        
        $arrDeltaData = ['price'=>0, 'details'=>[], 'origin_overtime_price'=>$originOvertimePrice, 'now_overtime_price'=>0, 'optional_service'=>0];
        
        $originRentTimeData = \common\models\Pri_renttime_data::create($this->start_time, $originEndtime,$this->pay_type);
        $rentTimeData = \common\models\Pri_renttime_data::create($this->start_time, $endTime,$this->pay_type);
      
        if (static::isMultidaysPackagePriceType($this->pay_type)) {
			
            if ($rentTimeData->days < $originRentTimeData->days) {
                \Yii::warning("order:{$this->serial} update end time:{$rentTimeData->endTime} earliear than origin end time:{$originRentTimeData->endTime}, this is not allowed.", 'order');
                $rentTimeData->days = $originRentTimeData->days;
                $rentTimeData->hours = $originRentTimeData->hours;
                $endTime = $originRentTimeData->endTime;
            }
        }
		
        //\Yii::error("######## [{$originRentTimeData->days}, {$originRentTimeData->hours}] [{$rentTimeData->days}, {$rentTimeData->hours}]", 'order');
        if ($preferentialType == \common\components\Consts::PROCESS_TYPE_FIRST_RENTAL_GIFT_ONE_DAY && $this->price_preferential > 0) {
            if ($rentTimeData->days < 2) {
                $rentTimeData->days = 2;
                $rentTimeData->hours = 0;
                $endTime = $rentTimeData->startTime + 3600*2;
            }
        }
        elseif ($rentTimeData->days < $originRentTimeData->days) {
            // 提前还车，若提前还车时间有余超时时间，按一天算。
            if ($this->status >= static::STATUS_RENTING && ($rentTimeData->hours > 0 || ($rentTimeData->startTime + 3600*($rentTimeData->days)) < ($rentTimeData->endTime - \common\components\Consts::AHEAD_RETURN_CAR_ALLOW_DELTA_SECONDS))) {
                $rentTimeData->days++;
                $rentTimeData->hours = 0;
                $endTime = $rentTimeData->startTime + 3600*$rentTimeData->days;
            }
        }
        // print_r($rentTimeData);exit;
        $this->new_end_time = $this->end_time;
        $this->rent_days = $originRentTimeData->days;
        //sjj
        $old_source=$this->source;
        // file_put_contents('preview.txt',"source=>$d\n",FILE_APPEND);
        if($this->pay_type == 3){//在线价格
            $this->source = 1;//1是手机订单
        }else{
            $this->source = 3;//3是门店订单
        }
        //sjj
		
		if($this->flag > 0){
			$oneWayTimePrice = \common\models\Pro_vehicle_fee_plan::getOneWayTimePrice($this->flag);
			$rentMinute = $rentTimeData->endTime - $originRentTimeData->endTime;
			if($rentMinute <= 600){
				$this->price_overtime = 0;
			}else if($rentMinute >600 && $rentMinute<= 1800){
				$this->price_overtime = $this->rent_per_day + $oneWayTimePrice['oneWayOverTimeMinutePrice'] * $oneWayTimePrice['oneWayOilByKm'];
			}else if($rentMinute >1800 && $rentMinute<= 3600){
				$this->price_overtime = $this->rent_per_day + $oneWayTimePrice['oneWayOverTimeHoursPrice'] * $oneWayTimePrice['oneWayOilByKm'];
			}else if($rentMinute > 3600){
				$this->price_overtime = $this->rent_per_day + $oneWayTimePrice['oneWayOverTimeHoursPrice'] * $oneWayTimePrice['oneWayOilByKm'] * ceil($rentMinute / 3600);
			}
		}
		
        $this->calculateOneWayTotalPrice();
       
        $arrDeltaData['price'] = $this->total_amount - $originTotalPrice;
        $arrDeltaData['now_overtime_price'] = $this->price_overtime;
        // $arrDeltaData['optional_service'] = $this->price_optional_service - $originServicePrice;
        $arrDeltaData['optional_service'] = $this->price_optional_service ;

        //sjj其他费用:加油费+车损费+违章费用+超时保费+加油代办价格+其他价格+个人自驾超时费用
        $arrDeltaData['other_price'] = $this->price_oil + $this->price_car_damage + $this->price_violation + $this->price_insurance_overtime + $this->price_oil_agency + $this->price_other/* + $this->price_overtime*/;
        $arrDeltaData['price_preferential'] = $this->price_preferential ;
        $arrDeltaData['price_poundage'] = $this->price_poundage ;//手续费
        $arrDeltaData['unit_price_basic_insurance'] = $this->unit_price_basic_insurance ;//基本服务费费
        $arrDeltaData['price_different_office'] = $this->price_different_office ;//异店还车费
        $arrDeltaData['price_take_car'] = $this->price_take_car ;//送车上门服务费
        $arrDeltaData['price_return_car'] = $this->price_return_car ;//上门取车服务费
		
        if($this->pay_type == 3){//在线价格
            $this->source = $old_source;//1是手机订单
        }else{
            $this->source = $old_source;//3是门店订单
        }
        // sjj
       // print_r($arrDeltaData);exit;
        return $arrDeltaData;
    }
    public function onUpdateOneWayEndTime($endTime) {
        if ($endTime < $this->start_time) {
            \Yii::error("order:{$this->id} serial:{$this->serial} on update to end_time:{$endTime} while the time less than order start_time:{$this->start_time}", 'order');
            return false;
        }
        $preferentialType   = $this->preferential_type;

        $originEndtime      = $this->new_end_time;
        $originTotalPrice   = $this->total_amount;
        $originServicePrice = $this->price_optional_service;
        $originOvertimePrice= $this->price_overtime;
        
        $arrDeltaData = [
            'price'                =>0,
            'details'              =>[],
            'origin_overtime_price'=>$originOvertimePrice,
            'now_overtime_price'   =>0,
            'optional_service'     =>0
        ];

        $originRentTimeData = \common\models\Pri_renttime_data::createTime($this->start_time, $originEndtime);
        $rentTimeData       = \common\models\Pri_renttime_data::createTime($this->start_time, $endTime);

        $arr = $this->getDailyRentDetailedPriceArray();
        // echo "<pre>";
        // print_r($arr);
        // echo "</pre>";
        // die;


        $this->new_end_time = $endTime;
        $this->rent_days = $rentTimeData->hours/24;
        //sjj
        $old_source=$this->source;
        if($this->pay_type == 3){//在线价格
            $this->source = 1;//1是手机订单
        }else{
            $this->source = 3;//3是门店订单
        }
        //sjj
        if ($rentTimeData->days == $originRentTimeData->days) {
            if ($rentTimeData->hours == $originRentTimeData->hours) {
                $arr = $this->getDailyRentDetailedPriceArray();
                if (count($arr) < $rentTimeData->days) {
                    $arrPriceInfo = \common\components\OrderModule::calculateVehicleModelRentPriceData($this->vehicle_model_id, $this->start_time, $endTime, $this->belong_office_id, $this->source, $this->pay_type, $this->getCustomerBirthday());
                    if ($arrPriceInfo) {
                        $arr = $arrPriceInfo['details'];
                        $this->price_rent = $arrPriceInfo['price'];
                        $this->_setDailyRentDetailedPriceArray($arr);
                    }
                }
                else {
                    $price = 0;
                    for ($i = 0; $i < $rentTimeData->days; $i++) {
                        $price += $arr[$i];
                    }
                    $this->price_rent = $price;
                    $this->_dailyRentDetailedPricesArray = array_slice($arr, 0, $rentTimeData->days);
                    $this->_setDailyRentDetailedPriceArray($this->_dailyRentDetailedPricesArray);
                }
                //return $arrDeltaData;
            }
            else {
                $this->price_overtime = $this->unit_price_overtime * $rentTimeData->hours;
            }
        }
        else {
            if ($rentTimeData->days > $originRentTimeData->days) {
                $startTime = $this->start_time + $originRentTimeData->days * 86400;
                if ($startTime < $endTime) {
                    $arrPriceInfo = \common\components\OrderModule::calculateVehicleModelRentPriceData($this->vehicle_model_id, $startTime, $endTime, $this->belong_office_id, $this->source, $this->pay_type, $this->getCustomerBirthday());
                    if ($arrPriceInfo) {
                        $this->price_rent += $arrPriceInfo['price'];
                        $this->appendDailyRentDetailedPriceArray($originRentTimeData->days, $arrPriceInfo['details']);

                        foreach ($arrPriceInfo['details'] as $v) {
                            $arrDeltaData['details'][] = $v;
                        }
                    }
                }

                $arrDeltaData['calc_start_time'] = $startTime;
            }
            else {
                $arr = $this->getDailyRentDetailedPriceArray();
                if (count($arr) < $rentTimeData->days) {
                    $arrPriceInfo = \common\components\OrderModule::calculateVehicleModelRentPriceData($this->vehicle_model_id, $this->start_time, $endTime, $this->belong_office_id, $this->source, $this->pay_type, $this->getCustomerBirthday());
                    if ($arrPriceInfo) {
                        $arr = $arrPriceInfo['details'];
                        $this->price_rent = $arrPriceInfo['price'];
                        $this->_setDailyRentDetailedPriceArray($arr);
                    }
                }
                else {
                    $price = 0;
                    for ($i = 0; $i < $rentTimeData->days; $i++) {
                        $price += $arr[$i];
                    }
                    $this->price_rent = $price;
                    $this->_dailyRentDetailedPricesArray = array_slice($arr, 0, $rentTimeData->days);
                    $this->_setDailyRentDetailedPriceArray($this->_dailyRentDetailedPricesArray);
                }
            }
            
            $this->price_overtime = $this->unit_price_overtime * $rentTimeData->hours;
            
            $arrOptionalServices = $this->getOptionalServicePriceArray();
            if (!empty($arrOptionalServices)) {
                $arrRows = Pro_service_price::findAllServicePrices($this->belong_office_id, array_keys($arrOptionalServices));
                if ($arrRows) {
                    $this->setOptionalServices($arrRows);
                }
            }
        }
        
        $this->calculateTotalPrice();
        
        $arrDeltaData['price'] = $this->total_amount - $originTotalPrice;
        $arrDeltaData['now_overtime_price'] = $this->price_overtime;
        // $arrDeltaData['optional_service'] = $this->price_optional_service - $originServicePrice;
        $arrDeltaData['optional_service'] = $this->price_optional_service;

        //sjj其他费用:加油费+车损费+违章费用+超时保费+加油代办价格+其他价格+个人自驾超时费用
        $arrDeltaData['other_price'] = $this->price_oil + $this->price_car_damage + $this->price_violation + $this->price_insurance_overtime + $this->price_oil_agency + $this->price_other/* + $this->price_overtime*/;
        $arrDeltaData['price_preferential'] = $this->price_preferential ;
        $arrDeltaData['price_poundage'] = $this->price_poundage ;//手续费
        $arrDeltaData['unit_price_basic_insurance'] = $this->unit_price_basic_insurance ;//基本保险费
        $arrDeltaData['price_different_office'] = $this->price_different_office ;//异店还车费
        $arrDeltaData['price_take_car'] = $this->price_take_car ;//送车上门服务费
        $arrDeltaData['price_return_car'] = $this->price_return_car ;//上门取车服务费

        if($this->pay_type == 3){//在线价格
            $this->source = $old_source;//1是手机订单
        }else{
            $this->source = $old_source;//3是门店订单
        }
        return $arrDeltaData;
    }


    public function getStatusText() {
        $arrStatus = \common\components\OrderModule::getOrderStatusArray();
        if (isset($arrStatus[$this->status])) {
            return $arrStatus[$this->status];
        }
        return Yii::t('locale', 'Unknown');
    }
    
    public function getIdentityTypeText() {
        $arr = Pub_user_info::getIdentityTypesArray();
        return (isset($arr[$this->customer_id_type]) ? $arr[$this->customer_id_type] : '');
    }
    
    public function getPlateNumber() {
        $objVehicle = Pro_vehicle::findById($this->vehicle_id);
        if ($objVehicle) {
            return $objVehicle->plate_number;
        }
        return '';
    }
    
    public function getTakeCarAddressText() {
        if (!empty($this->address_take_car)) {
            return $this->address_take_car;
        }
        $objOffice = Pro_office::findById($this->office_id_rent);
        if ($objOffice) {
            return $objOffice->address;
        }
        return '';
    }
    
    public function getReturnCarAddressText() {
        if (!empty($this->address_return_car)) {
            return $this->address_return_car;
        }
        $objOffice = Pro_office::findById($this->office_id_return);
        if ($objOffice) {
            return $objOffice->address;
        }
        return '';
    }
    
    public function getTakeCarOfficeText() {
        $objOffice = Pro_office::findById($this->office_id_rent);
        if ($objOffice) {
            return $objOffice->shortname;
        }
        return '';
    }
    
    public function getReturnCarOfficeText() {
        $objOffice = Pro_office::findById($this->office_id_return);
        if ($objOffice) {
            return $objOffice->shortname;
        }
        return '';
    }
    
    public function getTakeCarCityText() {
        return \common\components\OrderModule::getCityTextByOfficeId($this->office_id_rent);
    }
    
    public function getReturnCarCityText() {
        return \common\components\OrderModule::getCityTextByOfficeId($this->office_id_return);
    }
    
    public function getTakeCarCityAndOfficeText() {
        return \common\components\OrderModule::getCityAndOfficeTextByOfficeId($this->office_id_rent);
    }
    
    public function getReturnCarCityAndOfficeText() {
        return \common\components\OrderModule::getCityAndOfficeTextByOfficeId($this->office_id_return);
    }
    
    public function getOrderSourceText() {
        $arr = \common\components\OrderModule::getOrderSourceArray();
        return (isset($arr[$this->source]) ? $arr[$this->source] : '');
    }
    
    public function getEditUserName() {
        return \backend\components\AdminModule::getAdminNameById($this->edit_user_id);
    }
    
    public function getSettlementUserName() {
        return \backend\components\AdminModule::getAdminNameById($this->settlement_user_id);
    }
    
    public function getDepositPayTypeText() {
        $arr = \common\components\OrderModule::getOrderPayTypeArray();
        return (isset($arr[$this->deposit_pay_source]) ? $arr[$this->deposit_pay_source] : '');
    }

    public function isValidForOfficeId($officeId) {
        if ($officeId == \common\components\OfficeModule::HEAD_OFFICE_ID) {
            return true;
        }
        $arrOfficeIds = [$this->belong_office_id=>$this->belong_office_id];
        $arrOfficeIds[$this->office_id_rent] = $this->office_id_rent;
        $arrOfficeIds[$this->office_id_return] = $this->office_id_return;
        if (isset($arrOfficeIds[$officeId])) {
            return true;
        }
        else {
            $objOffice = Pro_office::findById($officeId);
            if ($objOffice) {
                $arrRows = Pro_office::findAll(['area_id'=>$objOffice->area_id]);
                foreach ($arrRows as $row) {
                    if (isset($arrOfficeIds[$row->id])) {
                        return true;
                    }
                }
            }
        }
        return false;
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
            return \Yii::createObject(\common\components\OfficeLimitedActiveQuery::className(), [get_called_class(), ['attribute'=>['belong_office_id'/*,'office_id_rent','office_id_return'*/]]]);
        }
    }
    
    /**
     * 
     * @return \common\helpers\ExtendActiveDataProvider
     */
    public static function createDataProvider($config = []) {
        return parent::createDataProvider(\yii\helpers\ArrayHelper::merge([
            'formattingAttributes' => [
                'status' => \common\components\OrderModule::getOrderStatusArray(),
                'type' => \common\components\OrderModule::getOrderTypeArray(),
                'vehicle_color' => \common\components\VehicleModule::getVehicleColorsArray(),
                'pay_type' => \common\components\OrderModule::getPriceTypeArray(),
                'pay_source, deposit_pay_source, settlement_pay_source' => \common\components\OrderModule::getOrderPayTypeArray(),
                'settlement_status' => \common\components\OrderModule::getSettlementTypeArray(),
                'source' => \common\components\OrderModule::getOrderSourceArray(),
                'customer_id_type' => \common\models\Pub_user_info::getIdentityTypesArray(),
                'start_time,end_time,new_end_time,confirmed_at,car_dispatched_at,car_returned_at,settlemented_at,created_at,updated_at,customer_driver_license_time' => 'datetime:Y-m-d H:i:s',
            ],
            'findAttributes' => [
                'vehicle_model_id' => Pro_vehicle_model::createFindIdNamesArrayConfig(),
                'vehicle_id' => Pro_vehicle::createFindIdNamesArrayConfig(),
                'user_id' => Pub_user_info::createFindIdNamesArrayConfig(),
                'belong_office_id,office_id_rent,office_id_return' => Pro_office::createFindIdNamesArrayConfig(),
                'edit_user_id,settlement_user_id' => \backend\models\Rbac_admin::createFindIdNamesArrayConfig(),
            ],
        ], $config));
    }

    public static function createFindIdNamesArrayConfig($config = array()) {
        return ['class'=> static::className(), 'idField'=>'id', 'textField'=>'serial'];
    }

    public function save($runValidation = true, $attributeNames = null) {
		
        // check if there is field changed.
        $oOriginOrder = null;
        $hasChanged = false;
        $hasPriceChanged = false;
        $arrPriceDeltas = [];
		
        if ($this->id) {
            $cdb = static::find(true);
            $cdb->where(['id' => $this->id]);
            $oOriginOrder = $cdb->one();
            
            if ($oOriginOrder) {
                $arrAttributesOrigin = $oOriginOrder->getAttributes();
                $arrAttributesNow = $this->getAttributes();
				
                foreach ($arrAttributesNow as $k => $v) {
                    $v0 = (isset($arrAttributesOrigin[$k]) ? $arrAttributesOrigin[$k] : '');
                    if (substr($k, 0, 6) == 'price_') {
                        $v = floatval($v);
                        $v0 = floatval($v0);
                        if ($v == $v0) {
                            $arrPriceDeltas[$k] = 0;
                        }
                        else {
                            $arrPriceDeltas[$k] = $v - $v0;
                            $hasPriceChanged = true;
                        }
                    }
                    if ($v0 != $v) {
                        $hasChanged = true;
                    }
                }
            }
        }
        else {
            $hasChanged = true;
            $arrAttributesNow = $this->getAttributes();
            foreach ($arrAttributesNow as $k => $v) {
                if (substr($k, 0, 6) == 'price_') {
                    if ($v == 0) {
                        $arrPriceDeltas[$k] = 0;
                    }
                    else {
                        $arrPriceDeltas[$k] = floatval($v);
                        $hasPriceChanged = true;
                    }
                }
            }
        }
        
        $result = parent::save($runValidation, $attributeNames);
        if ($result && $hasChanged) {
            $log = new Pro_vehicle_order_change_log();
            $log->load($this);
            $log->save();
            
            if ($hasPriceChanged) {
                $odr = new Pro_vehicle_order_price_detail();
                $odr->load($this, $arrPriceDeltas);
                $odr->type = Pro_vehicle_order_price_detail::TYPE_SHOULD_PAY;
                $odr->pay_source = static::PAY_TYPE_NONE;
                $odr->autoSerial();
                $odr->save();
            }
        }
        return $result;
    }

    public static function isMultidaysPackagePriceType($priceType) {
        static $_arr = [
            Pro_vehicle_order::PRICE_TYPE_MULTIDAYS => 1,//2=>1打包价
            Pro_vehicle_order::PRICE_TYPE_WEEK => 1,//4=>1   7天打包价
            Pro_vehicle_order::PRICE_TYPE_MONTH => 1,//5=>1   月租打包价
            Pro_vehicle_order::PRICE_TYPE_HOUR => 1,//6=>1   时租价
        ];
        
        if (isset($_arr[$priceType])) {
            return true;
        }
        return false;
    }
    
    public static function isUseOnlinePrice($orderSource, $priceType = 0) {
        static $_onlinearr = [
            Pro_vehicle_order::ORDER_SOURCE_APP => 1,
            Pro_vehicle_order::ORDER_SOURCE_TELEPHONE => 1,
        ];
        if ($priceType == Pro_vehicle_order::PRICE_TYPE_ONLINE
            //|| isset($_onlinearr[$orderSource])
            ) {
            return true;
        }
        return false;
    }
    
    public function getPriceAttributeFields() {
        $allAttributes = $this->attributes();
		
        $attributes = [];
        foreach ($allAttributes as $k) {
            if (substr($k, 0, 6) == 'price_') {
                $attributes[] = $k;
            }
        }
        return $attributes;
    }
    
    public static function getPreferentialPriceFields() {
        return [
            'price_preferential' => 1,
            'price_bonus_point_deduction' => 1,
            'price_gift' => 1,
        ];
    }
    
    public function getTotalPreferentialPrice() {
        return $this->price_preferential + $this->price_bonus_point_deduction + $this->price_gift;
    }
    
    public function getTotalDepositPrice() {
        return $this->price_deposit + $this->price_deposit_violation;
    }
    
    public function getCustomerBirthday() {
        $birthday = null;
        if ($this->customer_id_type == \common\components\Consts::ID_TYPE_IDENTITY) {
            $day = substr($this->customer_id, 10, 4);
            if (strlen($day) == 4) {
                $birthday = substr($day, 0, 2).'-'.substr($day, 2);
            }
        }
        elseif ($this->user_id) {
            $objUserInfo = Pub_user_info::findById($this->user_id);
            if ($objUserInfo) {
                $birthday = $objUserInfo->getBirthday();
            }
        }
        return $birthday;
    }
    
    public function getCheckingCustomerBirthdayInfo() {
        // find custumer birthday
        $customerBirthday = $this->getCustomerBirthday();
        $year1 = intval(date('Y', $this->start_time));
        $year2 = intval(date('Y', $this->new_end_time));
        $checkBirthDays = [];
        for($y = $year1; $y <= $year2; $y++) {
            $birth = strtotime($year1.'-'.$customerBirthday);
            if ($birth + 86399 >= $this->start_time && $birth <= $this->new_end_time) {
                $checkBirthDays[] = [$birth, $birth+86399];
            }
        }
        return $checkBirthDays;
    }


    // sjj 判断是否老用户，订单存在则老用户，不存在则新用户
    static public function CheckCustomerIsNew($userId=0)
    {
        $cond = ['user_id'=>$userId,'status'=>[10,100]];
        $result = Pro_vehicle_order::find()->where($cond)->one();
        if($result){
            return 1;
        }else{
            return -1;
        }
    }
    // sjj

}