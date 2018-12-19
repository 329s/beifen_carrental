<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_gift_code".
 */
class Form_pro_gift_code extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $sn;
    public $type;
    public $customer_id;
    public $amount;
    public $status;
    public $flag;
    public $activated_at;
    public $used_at;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sn'], 'required'],
            [['id', 'type', 'customer_id', 'status', 'flag', 'activated_at', 'used_at'], 'integer'],
            [['amount'], 'number'],
            [['sn'], 'string', 'max' => 64],
            [['sn'], 'unique', 'targetClass' => 'common\models\Pro_gift_code', 'filter'=>['<>', 'id', $this->id]],
            
            [['sn'], 'filter', 'filter' => 'trim'],
            [['activated_at', 'used_at'], 'datetime'],
            ['type', 'in', 'range' => [\common\models\Pro_gift_code::TYPE_PREFERENTIAL_CODE, \common\models\Pro_gift_code::TYPE_PERSONAL_CODE]],
            ['status', 'in', 'range' => [\common\models\Pro_gift_code::STATUS_LOCKED, \common\models\Pro_gift_code::STATUS_NORMAL, \common\models\Pro_gift_code::STATUS_USED, \common\models\Pro_gift_code::STATUS_DISABLED]],
            
        ];
    }

    public function getActiveRecordModel() {
        $model = new Pro_gift_code();
        return $model;
    }

}

