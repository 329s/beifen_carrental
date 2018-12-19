<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_vehicle_insurance".
 */
class Form_pro_vehicle_insurance extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $vehicle_id;
    public $type;
    public $time;
    public $insurance_company;
    public $insurance_type;
    public $insurance_no;
    public $price;
    public $insurance_amount;
    public $remark;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vehicle_id', 'type', 'time', 'insurance_company', 'insurance_type', 'insurance_no', 'price', 'insurance_amount'], 'required'],
            [['id', 'vehicle_id', 'type', 'insurance_type', 'insurance_amount'], 'integer'],
            [['price'], 'number'],
            [['insurance_company', 'remark'], 'string', 'max' => 255],
            [['insurance_no'], 'string', 'max' => 64],
            [['insurance_no'], 'unique', 'targetClass' => 'common\models\Pro_vehicle_insurance', 'filter'=>['<>', 'id', $this->id]],
            
            [['time'], 'datetime'],
            [['insurance_company', 'insurance_no', 'remark'], 'filter', 'filter' => 'trim'],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_vehicle_insurance();
        return $model;
    }

}
