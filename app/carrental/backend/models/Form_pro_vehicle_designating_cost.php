<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_vehicle_designating_cost".
 */
class Form_pro_vehicle_designating_cost extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $vehicle_id;
    public $type;
    public $time;
    public $driver;
    public $driver_fee;
    public $road_fee;
    public $parking_fee;
    public $fuel_fee;
    public $remark;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vehicle_id', 'type', 'time', 'driver', 'driver_fee', 'road_fee', 'parking_fee', 'fuel_fee'], 'required'],
            [['id', 'vehicle_id', 'type'], 'integer'],
            [['driver_fee', 'road_fee', 'parking_fee', 'fuel_fee'], 'number'],
            [['driver'], 'string', 'max' => 32],
            [['remark'], 'string', 'max' => 255],
            
            [['time'], 'datetime'],
            [['driver', 'remark'], 'filter', 'filter' => 'trim'],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_vehicle_designating_cost();
        return $model;
    }

}
