<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_user_consult".
 */
class Form_pro_user_consult extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $office_id;
    public $time;
    public $customer_name;
    public $customer_phone;
    public $content;
    public $price;
    public $inputer_name;
    public $status;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['office_id', 'time', 'customer_name', 'customer_phone', 'content', 'inputer_name', 'status'], 'required'],
            [['id', 'office_id', 'status'], 'integer'],
            [['price'], 'number'],
            [['customer_name'], 'string', 'max' => 64],
            [['customer_phone', 'inputer_name'], 'string', 'max' => 32],
            [['content'], 'string', 'max' => 255],
            
            [['customer_name', 'customer_phone', 'content', 'inputer_name', 'status'], 'filter', 'filter' => 'trim'],
            [['time'], 'datetime'],
            ['status', 'default', 'value' => \backend\models\Pro_user_consult::STATUS_FIRST_CONSULT],
            ['status', 'in', 'range' => [\backend\models\Pro_user_consult::STATUS_FIRST_CONSULT, \backend\models\Pro_user_consult::STATUS_SECOND_CONSULT, \backend\models\Pro_user_consult::STATUS_INTENTION, \backend\models\Pro_user_consult::STATUS_PROCESSED]],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new Pro_user_consult();
        return $model;
    }

}
