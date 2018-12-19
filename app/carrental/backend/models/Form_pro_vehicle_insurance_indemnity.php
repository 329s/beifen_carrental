<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_vehicle_insurance_indemnity".
 */
class Form_pro_vehicle_insurance_indemnity extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $vehicle_id;
    public $serial;
    public $insurance_no;
    public $time;
    public $address;
    public $driver;
    public $report_time;
    public $filing_time;
    public $closing_time;
    public $case_status;
    public $indemnity_amount;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vehicle_id', 'serial', 'insurance_no', 'time', 'address', 'driver', 'report_time', 'filing_time'], 'required'],
            [['id', 'vehicle_id', 'time', 'report_time', 'filing_time', 'closing_time', 'case_status'], 'integer'],
            [['indemnity_amount'], 'number'],
            [['serial', 'insurance_no', 'driver'], 'string', 'max' => 64],
            [['address'], 'string', 'max' => 255],
            [['serial'], 'unique', 'targetClass' => 'common\models\Pro_vehicle_insurance_indemnity', 'filter'=>['<>', 'id', $this->id]],
            
            [['serial', 'insurance_no', 'driver', 'address'], 'filter', 'filter' => 'trim'],
            [['time', 'report_time', 'filing_time', 'closing_time'], 'datetime'],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_vehicle_insurance_indemnity();
        return $model;
    }

}
