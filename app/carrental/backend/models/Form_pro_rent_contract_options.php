<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_rent_contract_options".
 */
class Form_pro_rent_contract_options extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $type;
    public $name;
    public $title;
    public $instruction;
    public $signature_a;
    public $signature_b;
    public $footer;
    public $flag;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'name'], 'required'],
            [['id', 'type', 'flag'], 'integer'],
            [['title', 'instruction', 'signature_a', 'signature_b', 'footer'], 'string'],
            [['name'], 'string', 'min' => 2, 'max' => 64],
            [['type'], 'unique', 'targetClass' => 'common\models\Pro_rent_contract_options', 'filter'=>['<>', 'id', $this->id]],
            [['name'], 'unique', 'targetClass' => 'common\models\Pro_rent_contract_options', 'filter'=>['<>', 'id', $this->id]],
            
            [['title', 'instruction', 'signature_a', 'signature_b', 'footer'], 'filter', 'filter' => 'trim'],
            ['flag', 'default', 'value' => \common\models\Pro_rent_contract_options::STATUS_ENABLED],
            ['flag', 'in', 'range' => [\common\models\Pro_rent_contract_options::STATUS_ENABLED, \common\models\Pro_rent_contract_options::STATUS_DISABLED]],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_rent_contract_options();
        return $model;
    }

}
