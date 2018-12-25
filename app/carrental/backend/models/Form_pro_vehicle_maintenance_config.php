<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_vehicle_maintenance_config".
 */
class Form_pro_vehicle_maintenance_config extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $name;
    public $belong_brand;
    public $status;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'belong_brand'], 'required'],
            [['id', 'belong_brand', 'status'], 'integer'],
            [['name'], 'string', 'max' => 128],
            [['name'], 'unique', 'targetClass' => 'common\models\Pro_vehicle_maintenance_config', 'filter'=>['<>', 'id', $this->id]],
            
            [['name'], 'filter', 'filter' => 'trim'],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_vehicle_maintenance_config();
        return $model;
    }

}