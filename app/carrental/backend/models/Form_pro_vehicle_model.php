<?php
namespace backend\models;

/**
 * region form
 */
class Form_pro_vehicle_model extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $brand;
    public $model_series;
    public $vehicle_model;
    public $vehicle_type;
    public $vehicle_flag;
    public $carriage;
    public $seat;
    public $gearbox;
    public $emission;
    public $oil_capacity;
    public $oil_label;
    public $air_intake_mode;
    public $gps;
    public $driving_mode;
    public $display_order;
    public $limit_flag;
    public $poundage;
    public $basic_insurance;
    public $rent_deposit;
    public $designated_driving_price;
    public $overtime_price_personal;
    public $overtime_price_designated;
    public $overmileage_price_personal;
    public $overmileage_price_designated;
    public $mileage_price;
    public $vehicle_configuration_id;
    public $image_0;
    public $image_a;
    public $image_b;
    public $image_c;
    public $image_d;
    public $description;
    public $radar;
    public $chair;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['brand', 'model_series', 'vehicle_model', 'vehicle_type', 'vehicle_flag', 'carriage', 'seat', 'gearbox', 'emission', 'oil_capacity', 'limit_flag'], 'required'],
            [['id', 'brand', 'model_series', 'vehicle_type', 'carriage', 'seat', 'gearbox', 'oil_capacity', 'oil_label', 'air_intake_mode', 'gps', 'driving_mode', 'display_order', 'vehicle_configuration_id'], 'integer'],
            [['poundage', 'basic_insurance', 'rent_deposit', 'designated_driving_price', 'overtime_price_personal', 'overtime_price_designated', 'overmileage_price_personal', 'overmileage_price_designated'], 'number'],
            [['description'], 'string'],
            [['vehicle_model'], 'string', 'min' => 2, 'max' => 64],
            [['image_0', 'image_a', 'image_b', 'image_c', 'image_d'], 'image', 'maxSize'=>256000],
            [['vehicle_model'], 'unique', 'targetClass' => 'common\models\Pro_vehicle_model', 'filter'=>['<>', 'id', $this->id]],
            
            [['emission'], \common\helpers\validators\FloatValidator::className(), 'factor'=>1000],
            [['mileage_price'], \common\helpers\validators\PairsValidator::className(), 'rule' => ['double']],
            [['vehicle_flag'], \common\helpers\validators\BitFlagValidator::className(), 'list' => \common\models\Pro_vehicle_model::getVehicleFlagsArray()],
            [['limit_flag'], \common\helpers\validators\BitFlagValidator::className(), 'list' => \common\models\Pro_vehicle_model::getLimitFlagsArray()],
            
            ['vehicle_type', 'default', 'value' => \common\models\Pro_vehicle_model::TYPE_CAR],
            ['vehicle_flag', 'default', 'value' => \common\models\Pro_vehicle_model::FLAG_NEW],
            ['display_order', 'default', 'value' => 0],
        ];
    }

    public function getActiveRecordModel() {
    $model = new \common\models\Pro_vehicle_model();
        return $model;
    }

}
