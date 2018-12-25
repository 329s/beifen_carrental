<?php
namespace common\models;

/**
* 
*/
class Pro_mnp_pay_return extends \common\helpers\ActiveRecordModel
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
