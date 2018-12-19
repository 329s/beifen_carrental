<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_vehicle_brand".
 */
class Form_pro_vehicle_brand extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $name;
    public $belong_brand;
    public $flag;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'belong_brand'], 'required'],
            [['id', 'belong_brand', 'flag'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['name'], 'unique', 'targetClass' => 'common\models\Pro_vehicle_brand', 'filter'=>['<>', 'id', $this->id]],
            [['flag'], 'default', 'value' => 0],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_vehicle_brand();
        return $model;
    }

}
