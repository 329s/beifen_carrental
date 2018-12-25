<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_vehicle_order_relet".
 */
class Form_pro_vehicle_order_relet extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $serial;             // 订单号
    public $order_id;           // 主订单ID
    public $origion_end_time;   // 原还车时间
    public $new_end_time;       // 新还车时间
    public $pay_source;         // 租金支付方式
    public $total_amount;       // 续租金额
    public $paid_amount;        // 已缴金额
    public $status;
    public $remark;             // 订单备注

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['origion_end_time', 'new_end_time'], 'required'],
            [['id', 'order_id', 'pay_source', 'status'], 'integer'],
            [['total_amount', 'paid_amount'], 'number'],
            [['serial'], 'string', 'max' => 64],
            [['remark'], 'string', 'max' => 255],
            [['serial'], 'unique', 'targetClass' => 'common\models\Pro_vehicle_order_relet', 'filter'=>['<>', 'id', $this->id]],
            [['serial', 'remark'], 'filter', 'filter' => 'trim'],
            [['origion_end_time', 'new_end_time'], 'datetime', 'format'=>'php:Y-m-d H:i'],
            [['pay_source'], 'in', 'range' => array_keys(\common\components\OrderModule::getOrderPayTypeArray())],
            [['pay_source'], 'default', 'value'=> \common\models\Pro_vehicle_order::PAY_TYPE_NONE],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_vehicle_order_relet();
        return $model;
    }

}