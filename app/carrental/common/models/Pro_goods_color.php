<?php
namespace common\models;

/**
* 
*/
class Pro_goods_color extends \common\helpers\ActiveRecordModel
{
	public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
        ];
    }

    public function rules()
    {
        return [
        ];
    }

}
