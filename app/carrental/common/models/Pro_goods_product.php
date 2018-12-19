<?php
namespace common\models;

/**
* 
*/
class Pro_goods_product extends \common\helpers\ActiveRecordModel
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
