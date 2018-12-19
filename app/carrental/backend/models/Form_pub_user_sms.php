<?php

namespace backend\models;

/**
 * This is the active form model class for table "pub_user_sms".
 */
class Form_pub_user_sms extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $type;
    public $time;
    public $customer_id;
    public $customer_name;
    public $customer_phone;
    public $content;
    public $status;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'time', 'customer_name', 'customer_phone', 'content', 'status'], 'required'],
            [['id', 'type', 'customer_id', 'status'], 'integer'],
            [['customer_name', 'customer_phone'], 'string', 'max' => 32],
            [['content'], 'string', 'max' => 255],
            
            [['time'], 'datetime'],
            [['customer_name', 'customer_phone', 'content'], 'filter', 'filter' => 'trim'],
            ['status', 'default', 'value' => \common\models\Pub_user_sms::STATUS_NORMAL],
            ['status', 'in', 'range' => [\common\models\Pub_user_sms::STATUS_NORMAL]],
            ['customer_id', 'default', 'value' => 0],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pub_user_sms();
        return $model;
    }

}
