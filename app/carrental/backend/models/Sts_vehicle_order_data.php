<?php
namespace backend\models;

class Sts_vehicle_order_data extends \yii\base\Model
{
    public $time = 0;
    public $serial = '';
    public $plate = '';
    public $customer_name = '';
    public $handler = '';
    public $office = '';
    public $days = 0;
    public $pre_licensing_amount = 0;           // 预授权 
    public $deposit_amount = 0;                 // 押金 
    public $rent_amount = 0;                    // 租金 
    public $optional_amount = 0;                // 增值服务费 
    public $delay_cost = 0;                     // 误工费 
    public $car_damage_amount = 0;              // 车损费 
    public $oil_amount = 0;                     // 油费 
    public $violation_amount = 0;               // 违章费 
    public $poundage_amount = 0;                // 代办费 
    public $service_amount = 0;                 // 取车，送车费等 
    public $accessories_amount = 0;             // 配件费 
    public $price_different_office = 0;         // 异地还车
    public $price_designated_driving = 0;       // 代驾费用
    public $price_agency = 0;       			// 代办费用
    public $price_poundage = 0;       			// 手续费
    public $price_basic_insurance = 0;       	// 基本服务费
    public $other_amount = 0;       
    public $total_amount = 0;       
    public $pay_source = 0;
    public $operation_type = 0;       
    public $from_xtrip = 0;       
    public $source = 0;       
    public $remark = '';
    
    public function attributeLabels() {
        return [
            'time' => \Yii::t('locale', 'Time'),
            'serial' => \Yii::t('locale', '{name} No.', ['name'=>\Yii::t('locale', 'Order')]),
            'plate' => \Yii::t('locale', 'Plate number'),
            'customer_name' => \Yii::t('locale', 'Name'),
            'handler' => \Yii::t('locale', 'Handler name'),
            'office' => \Yii::t('locale', 'Office'),
            'days' => \Yii::t('locale', 'Days'),
            'pre_licensing_amount' => \Yii::t('locale', 'Pre-licensing'),
            'deposit_amount' => \Yii::t('locale', 'Deposit'),
            'rent_amount' => \Yii::t('locale', 'Rent'),
            'optional_amount' => \Yii::t('locale', 'Value-added services'),
            'delay_cost' => '误工费',
            'car_damage_amount' => \Yii::t('locale', '{type} price', ['type'=>\Yii::t('carrental', 'Car damage')]),
            'oil_amount' => \Yii::t('carrental', 'Fuel cost'),
            'violation_amount' => \Yii::t('carrental', 'Violation price'),
            'poundage_amount' => '代办费',
            'service_amount' => '服务费',
            'accessories_amount' => '配件费',
            'price_different_office' => '异地还车费',
            'price_designated_driving' => '代驾费用',
            'price_basic_insurance' => '基本服务费',
            'price_poundage' => '手续费',
            'price_agency' => '代办费用',
            'other_amount' => \Yii::t('locale', '{type} price', ['type'=>\Yii::t('locale', 'Other')]),
            'total_amount' => '合计',
            'pay_source' => '收款方式',
            'operation_type' => '业务类型',
            'from_xtrip' => '携程订单',
            'source' => '订单类型',
            'remark' => \Yii::t('locale', 'Remark'),
        ];
    }
    
    /**
     * 
     * @param type $data
     * @return \backend\models\Sts_vehicle_order_data
     */
    public static function create($data = [])
    {
        $obj = new static();
        foreach ((array)$data as $k => $v) {
            if ($obj->hasProperty($k)) {
                $obj->$k = $v;
            }
        }
        return $obj;
    }
    
}

