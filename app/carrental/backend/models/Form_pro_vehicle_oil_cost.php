<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_vehicle_oil_cost".
 */
class Form_pro_vehicle_oil_cost extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $vehicle_id;
    public $type;
    public $time;
    public $oil_label;
    public $oil_volume;
    public $amount;
    public $pay_type;
    public $purpose;
    public $mileage;
    public $oil_tanker;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vehicle_id', 'time', 'oil_label', 'oil_volume', 'amount', 'pay_type'], 'required'],
            [['id', 'vehicle_id', 'type', 'oil_label', 'oil_volume', 'amount', 'pay_type', 'mileage'], 'integer'],
            [['purpose'], 'string', 'max' => 255],
            [['oil_tanker'], 'string', 'max' => 32],
            
            [['time'], 'datetime'],
            [['purpose', 'oil_tanker'], 'filter', 'filter' => 'trim'],
            [['pay_type'], 'default', 'value'=>0],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_vehicle_oil_cost();
        return $model;
    }

}
