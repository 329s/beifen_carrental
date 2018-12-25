<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_vehicle_cost".
 */
class Form_pro_vehicle_cost extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $vehicle_id;
    public $type;
    public $bind_id;
    public $name;
    public $cost_time;
    public $cost_price;
    public $remark;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vehicle_id', 'name', 'cost_time'], 'required'],
            [['id', 'vehicle_id', 'type', 'bind_id'], 'integer'],
            [['cost_price'], 'number'],
            [['name'], 'string', 'max' => 64],
            [['remark'], 'string', 'max' => 512],
            
            [['cost_time'], 'datetime'],
            [['name', 'remark'], 'filter', 'filter' => 'trim'],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_vehicle_cost();
        return $model;
    }

}