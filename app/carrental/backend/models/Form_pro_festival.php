<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_festival".
 */
class Form_pro_festival extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $name;
    public $start_time;
    public $end_time;
    public $alldays_required;
    public $status;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'start_time', 'end_time'], 'required'],
            [['id', 'alldays_required', 'status'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['name'], 'unique', 'targetClass' => 'common\models\Pro_festival', 'filter'=>['<>', 'id', $this->id]],
            
            ['name', 'filter', 'filter' => 'trim'],
            
            [['start_time'], 'date'],
            [['end_time'], \common\helpers\validators\DateValidator::className(), 'defaultTimepart'=> '23:59:59'],
            ['status', 'in', 'range' => [\common\models\Pro_festival::STATUS_NORMAL, \common\models\Pro_festival::STATUS_CLOSED]],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_festival();
        return $model;
    }

}
