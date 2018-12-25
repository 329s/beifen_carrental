<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_vehicle_fee_plan".
 */
class Form_pro_vehicle_fee_plan extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $name;
    public $source;
    public $vehicle_model_id;
    public $office_id;
    public $status;
    public $price_default;
    public $price_3days;
    public $price_week;
    public $price_15days;
    public $price_month;
    public $special_festivals_price_month;
    public $price_sunday;
    public $price_monday;
    public $price_tuesday;
    public $price_wednesday;
    public $price_thirsday;
    public $price_friday;
    public $price_saturday;
    public $festival_prices;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['source', 'vehicle_model_id', 'office_id', 'status'], 'required'],
            [['id', 'source', 'vehicle_model_id', 'office_id', 'status', 'price_default', 'price_3days', 'price_week', 'price_15days', 'price_month','special_festivals_price_month', 'price_sunday', 'price_monday', 'price_tuesday', 'price_wednesday', 'price_thirsday', 'price_friday', 'price_saturday'], 'integer'],
            [['name'], 'string', 'max' => 64],
            //[['festival_prices'], 'string', 'max' => 1024],
            [['vehicle_model_id'], 'unique', 'targetClass' => 'common\models\Pro_vehicle_fee_plan', 'filter'=>['<>', 'id', $this->id], 'targetAttribute' => ['source', 'vehicle_model_id', 'office_id'], 'message' => \Yii::t('carrental', 'This vehicle model has already configured fee plan, please do not duplicate configure!')],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_vehicle_fee_plan();
        return $model;
    }

    protected function afterLoad($formData) {
        // festival fields
        $this->festival_prices = [];
        $objFeePlan = new \common\models\Pro_vehicle_fee_plan();
        $objFeePlan->setFestivalNames(\common\components\OptionsModule::getFestivalsArray());
        foreach ($objFeePlan->festivalFieldsArray as $field => $festivalId) {
            $price = (isset($formData[$field]) ? intval($formData[$field]) : 0);
            if ($price) {
                $this->festival_prices[$festivalId] = $price;
            }
        }
    }
    
}
