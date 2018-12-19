<?php 
namespace common\models;

/**
* 
*/
class Pro_vehicle_office_change extends \common\helpers\ActiveRecordModel
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
            // [['name','phone','sex','way','source'],'required'],
            // [['phone'],'unique'],
            // [['updated_at','created_at'],'integer'],
        ];
    }

     public function attributeLabels() {
        return [
            'id' => 'ID',
            'belong_office_id' => \Yii::t('locale', 'Old belong office'),
            'new_belong_office_id' => \Yii::t('locale', 'New belong office'),
            'vehicle_id' => \Yii::t('locale', 'Vehicle name'),
            'new_belong_office_id' => \Yii::t('locale', 'New belong office'),
            
            'updated_at' => \Yii::t('locale', 'Vehicle office change updated_at'),
            'created_at' => \Yii::t('locale', 'Sign up created_at'),
            
        ];
    }
}