<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_activity_image".
 */
class Form_pro_activity_image extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $type;
    public $bind_param;
    public $name;
    public $status;
    public $image;
    public $icon;
    public $href;
    public $remark;
    public $city_id;
    public $office_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'name'], 'required'],
            [['id', 'type','city_id', 'office_id', 'bind_param', 'status'], 'integer'],
            [['remark'], 'string'],
            [['name', 'href',], 'string', 'max' => 255],
            
            [['image'], 'required', 'on'=>['insert']],
            [['name', 'href', 'remark'], 'filter', 'filter' => 'trim'],
            ['name', 'unique', 'targetClass' => 'common\models\Pro_activity_image', 'filter'=>['<>', 'id', $this->id]],
            [['image', 'icon'], 'image'],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_activity_image();
        return $model;
    }

}

