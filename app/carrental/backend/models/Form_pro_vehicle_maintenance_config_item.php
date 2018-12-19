<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_vehicle_maintenance_config_item".
 */
class Form_pro_vehicle_maintenance_config_item extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $belong_id;
    public $type;
    public $value;
    public $status;
    public $reference_price;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['belong_id', 'type'], 'required'],
            [['id', 'belong_id', 'type', 'value', 'status', 'reference_price'], 'integer'],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_vehicle_maintenance_config_item();
        return $model;
    }

}