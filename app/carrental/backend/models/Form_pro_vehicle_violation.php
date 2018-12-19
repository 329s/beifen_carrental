<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_vehicle_violation".
 */
class Form_pro_vehicle_violation extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $vehicle_id;
    public $order_id;
    public $violated_at;
    public $notified_at;
    public $score;
    public $penalty;
    public $status;
    public $description;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vehicle_id', 'order_id', 'violated_at', 'notified_at', 'score', 'status', 'description'], 'required'],
            [['id', 'vehicle_id', 'order_id', 'score', 'penalty', 'status'], 'integer'],
            [['description'], 'string', 'max' => 255],
            
            [['description'], 'filter', 'filter' => 'trim'],
            [['violated_at', 'notified_at'], 'datetime'],
            ['status', 'default', 'value' => \common\models\Pro_vehicle_violation::STATUS_UNPROCESSED],
            ['status', 'in', 'range' => [\common\models\Pro_vehicle_violation::STATUS_UNPROCESSED, \common\models\Pro_vehicle_violation::STATUS_PROCESSED]],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_vehicle_violation();
        return $model;
    }

}
