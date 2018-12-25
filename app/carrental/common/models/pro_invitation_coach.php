<?php
namespace common\models;

/**
* 
*/
class Pro_invitation_coach extends \common\helpers\ActiveRecordModel
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
            [['name','phone'],'required'],
            [['phone','code'],'unique'],
            // [['updated_at','created_at'],'integer'],
        ];
    }
}