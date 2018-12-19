<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_member_card".
 */
class Form_pro_member_card_useredit extends \common\helpers\ActiveFormModel
{
    public $id;
    public $card_no;
    public $card_name;
    public $type;
    public $card_code;
    public $card_password;
    public $amount;
    public $recharged_amount;
    public $activated_at;
    public $status;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //[['card_no', 'type', 'card_code', 'status'], 'required'],
            [['id', 'type', 'status'], 'integer'],
            [['amount', 'recharged_amount'], 'number'],
            [['card_no', 'card_name', 'card_code'], 'string', 'max' => 32],
            [['card_password'], 'string', 'max' => 128],
            //[['card_no'], 'unique', 'targetClass' => 'common\models\Pro_member_card', 'filter'=>['<>', 'id', $this->id]],
            //[['card_code'], 'unique', 'targetClass' => 'common\models\Pro_member_card', 'filter'=>['<>', 'id', $this->id]],
            
            [['card_no', 'card_name', 'card_code'], 'filter', 'filter' => 'trim'],
            [['activated_at'], 'date'],
            ['type', 'in', 'range' => [\common\models\Pub_user_info::VIP_LEVEL_NORMAL, \common\models\Pub_user_info::VIP_LEVEL_SILVER, \common\models\Pub_user_info::VIP_LEVEL_GOLDEN, \common\models\Pub_user_info::VIP_LEVEL_DIAMOND]],
            ['status', 'in', 'range' => [\common\models\Pro_member_card::STATUS_LOCKED, \common\models\Pro_member_card::STATUS_ACTIVITED, \common\models\Pro_member_card::STATUS_DISABLED]],
            ['type', 'default', 'value' => \common\models\Pub_user_info::VIP_LEVEL_NORMAL],
            ['status', 'default', 'value' => \common\models\Pro_member_card::STATUS_LOCKED],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_member_card();
        return $model;
    }

}


