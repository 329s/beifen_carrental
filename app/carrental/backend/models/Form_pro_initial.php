<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_initial".
 */
class Form_pro_initial extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $name;
    public $value;
    public $status;
    public $description;
    public $tips;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'value'], 'required'],
            [['id', 'status'], 'integer'],
            [['name'], 'string', 'max' => 32],
            [['value'], 'string', 'max' => 1024],
            [['description', 'tips'], 'string', 'max' => 255],
            [['name'], 'unique', 'targetClass' => 'common\models\Pro_initial', 'filter'=>['<>', 'id', $this->id]],
            
            [['name', 'value', 'description', 'tips'], 'filter', 'filter' => 'trim'],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_initial();
        return $model;
    }

}
