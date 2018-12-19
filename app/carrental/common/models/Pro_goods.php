<?php
namespace common\models;

/**
* 
*/
class Pro_goods extends \common\helpers\ActiveRecordModel
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

    public static function getGoodsMemoryArray() {
        return [
            0 => '64', 
            1 => '128', 
            2 => '256', 
        ];
    }

}
