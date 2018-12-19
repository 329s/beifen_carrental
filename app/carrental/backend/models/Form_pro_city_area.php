<?php

namespace backend\models;

/**
 * city area form
 */
class Form_pro_city_area extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $name;
    public $city_id;
    public $status;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'city_id'], 'required'],
            [['id', 'city_id', 'status'], 'integer'],
            [['name'], 'string', 'max' => 45],
            [['name'], 'unique', 'targetClass' => 'common\models\Pro_city_area', 'filter'=>['<>', 'id', $this->id]],
            [['name'], 'filter', 'filter' => 'trim'],
            ['status', 'in', 'range' => [\common\models\Pro_city_area::STATUS_NORMAL, \common\models\Pro_city_area::STATUS_DISABLED]],
        ];
    }

    public function getActiveRecordModel() {
        $model = new \common\models\Pro_city_area();
        return $model;
    }

}


