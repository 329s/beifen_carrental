<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_city".
 */
class Form_pro_city extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $name;
    public $type;
    public $belong_id;
    public $flag;
    public $status;
    public $city_code;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['id', 'type', 'belong_id', 'flag', 'status'], 'integer'],
            [['name'], 'string', 'max' => 45],
            [['city_code'], 'string', 'max' => 8],
            
            [['name', 'city_code'], 'filter', 'filter' => 'trim'],
            ['type', 'in', 'range' => [\common\models\Pro_city::TYPE_PROVINCE, \common\models\Pro_city::TYPE_CITY, \common\models\Pro_city::TYPE_SUB]],
            ['flag', 'in', 'range' => [\common\models\Pro_city::FLAG_NORMAL, \common\models\Pro_city::FLAG_HOT]],
            ['status', 'in', 'range' => [\common\models\Pro_city::STATUS_NORMAL, \common\models\Pro_city::STATUS_DISABLED]],
        ];
    }

    public function getActiveRecordModel() {
        $model = new \common\models\Pro_city();
        return $model;
    }
    
    public function load($data, $formName = null) {
        if (parent::load($data, $formName)) {
            if (!empty($this->city_code)) {
                $_x = 0;
                $_prefix = null;
                $query = \common\models\Pro_city::find();
                do
                {
                    $exists = $query->where(['and', ['city_code'=>$this->city_code], ['<>', 'id', $this->id]])->exists();
                    if ($exists) {
                        $_x++;
                        if (!$_prefix) {
                            $_arr = explode('-', $this->city_code);
                            if (!empty($_arr)) {
                                $_prefix = $_arr[0];
                            }
                            else {
                                $_prefix = $this->city_code;
                            }
                        }
                        $this->city_code = "{$_prefix}-{$_x}";
                    }
                    else {
                        break;
                    }
                }while($exists);
            }
            return true;
        }
        return false;
    }
    
    public function beforeSaveToModel($model) {
        parent::beforeSaveToModel($model);
        
    }
    
}
