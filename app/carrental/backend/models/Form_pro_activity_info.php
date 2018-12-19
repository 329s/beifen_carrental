<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_activity_info".
 */
class Form_pro_activity_info extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $title;
    public $content;
    public $icon;
    public $href;
    public $start_time;
    public $end_time;
    public $city_id;
    public $office_id;
    public $status;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content'], 'required'],
            [['id', 'city_id', 'office_id', 'status'], 'integer'],
            [['title', 'href'], 'string', 'max' => 255],
            [['content'], 'string'],
            
            [['title', 'content', 'href'], 'filter', 'filter' => 'trim'],
            ['title', 'unique', 'targetClass' => 'common\models\Pro_activity_info', 'filter'=>['<>', 'id', $this->id]],
            [['start_time', 'end_time'], 'datetime'],
            [['icon'], 'image'],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_activity_image();
        return $model;
    }
    
}

