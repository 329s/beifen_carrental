<?php

namespace backend\models;

/**
 * region form
 */
class Form_pro_vehicle extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $plate_number;
    public $model_id;
    public $status;
    public $isoneway;
    public $mobile;
    public $locator_device;
    public $engine_number;
    public $vehicle_number;
    public $certificate_number;
    public $color;
    public $baught_price;
    public $baught_tax;
    public $baught_time;
    public $baught_insurance;
    public $decoration_fee;
    public $license_plate_fee;
    public $baught_kilometers;
    public $cur_kilometers;
    public $belong_office_id;
    public $stop_office_id;
    public $vehicle_property;
    public $gps_id;
    public $remark;
    public $vehicle_image;
    public $certificate_image;
    public $annual_inspection_time;
    public $tci_renewal_time;
    public $vci_renewal_time;
    public $upkeep_config_id;
    public $upkeep_mileage_interval;
    public $upkeep_time_interval;
    public $next_upkeep_mileage;
    public $next_upkeep_time;
    public $validation_id;
    
    public $finishUpkeepOpened = false;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['plate_number', 'model_id', 'status','isoneway', 'engine_number', 'vehicle_number', 
                'certificate_number', 'color', 'baught_price', 'baught_tax', 'baught_time',
                'baught_kilometers', 'cur_kilometers', 'belong_office_id', 'stop_office_id',
                'vehicle_property', 'annual_inspection_time', 'tci_renewal_time'], 'required'],
            [['id', 'model_id', 'status', 'color', 'baught_kilometers', 'cur_kilometers', 
                'belong_office_id', 'stop_office_id', 'vehicle_property', 'gps_id', 
                'upkeep_config_id', 'upkeep_mileage_interval', 'upkeep_time_interval', 
                'next_upkeep_mileage', 'validation_id'], 'integer'],
            [['baught_price', 'baught_tax', 'baught_insurance', 'decoration_fee', 'license_plate_fee'], 'number'],
            [['plate_number', 'engine_number', 'vehicle_number', 'certificate_number'], 'string', 'max' => 32],
            [['remark'], 'string', 'max' => 255],
            [['plate_number'], 'unique', 'targetClass' => 'common\models\Pro_vehicle', 'filter'=>['<>', 'id', $this->id]],
            [['engine_number'], 'unique', 'targetClass' => 'common\models\Pro_vehicle', 'filter'=>['<>', 'id', $this->id]],
            [['vehicle_number'], 'unique', 'targetClass' => 'common\models\Pro_vehicle', 'filter'=>['<>', 'id', $this->id]],
            [['certificate_number'], 'unique', 'targetClass' => 'common\models\Pro_vehicle', 'filter'=>['<>', 'id', $this->id]],
            
            [['baught_time', 'annual_inspection_time', 'tci_renewal_time', 'vci_renewal_time', 'next_upkeep_time'], 'date'],
            [['vehicle_image', 'certificate_image'], 'image', 'maxSize'=>256000],
            ['status', 'in', 'range' => [\common\models\Pro_vehicle::STATUS_NORMAL, \common\models\Pro_vehicle::STATUS_MAINTENANCE, \common\models\Pro_vehicle::STATUS_SAILED, \common\models\Pro_vehicle::STATUS_DELETED]],
            ['vehicle_property', 'in', 'range' => [\common\models\Pro_vehicle::PROPERTY_BAUGHT, \common\models\Pro_vehicle::PROPERTY_AFFILIATE,\common\models\Pro_vehicle::PROPERTY_TRUSTTEE, \common\models\Pro_vehicle::PROPERTY_CHEFENQI]],
            
            [['upkeep_time_interval'], \common\helpers\validators\FloatValidator::className(), 'factor'=>86400*30],
            
            ['finishUpkeepOpened', 'boolean'],
        ];
    }

    public function getActiveRecordModel() {
        $model = new \common\models\Pro_vehicle();
        return $model;
    }
    
    public function afterSaveToModel($model) {
        parent::afterSaveToModel($model);
        if ($this->finishUpkeepOpened) {
            $model->last_upkeep_mileage = $this->cur_kilometers;
            $model->last_upkeep_time = time();
            $model->next_upkeep_mileage = $model->last_upkeep_mileage + $this->upkeep_mileage_interval;
            $model->next_upkeep_time = $model->last_upkeep_time + $this->upkeep_time_interval;
        }
    }
    
    
}
