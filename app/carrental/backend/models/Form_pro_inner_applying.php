<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_inner_applying".
 */
class Form_pro_inner_applying extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $type;
    public $office_id;
    public $plate_number;
    public $status;
    public $content;
    public $applyer;
    public $start_time;
    public $end_time;
    public $vehicle_outbound_mileage;
    public $vehicle_inbound_mileage;
    public $approval_content;
    public $created_by;
    public $updated_by;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'office_id', 'status', 'content', 'applyer'], 'required'],
            [['id', 'type', 'office_id', 'status', 'vehicle_outbound_mileage', 'vehicle_inbound_mileage'], 'integer'],
            [['plate_number', 'applyer'], 'string', 'max' => 64],
            [['content', 'approval_content'], 'string', 'max' => 255],
            [['start_time', 'end_time'], 'datetime'],
            
            ['type', 'in', 'range' => array_keys(Pro_inner_applying::getTypeArray())],
            ['status', 'in', 'range' => array_keys(Pro_inner_applying::getStatusArray())],
        ];
    }

    public function getActiveRecordModel() {
        $model = new \backend\models\Pro_inner_applying();
        return $model;
    }

}
