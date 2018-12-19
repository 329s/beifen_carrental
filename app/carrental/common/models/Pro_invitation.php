<?php
namespace common\models;

/**
* 
*/
class Pro_invitation extends \common\helpers\ActiveRecordModel
{
    const STATUS_No    = 0;
	const STATUS_Man   = 1;
    const STATUS_Woman = 2;

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
            [['phone'],'unique'],
            // [['updated_at','created_at'],'integer'],
        ];
    }

    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => \Yii::t('locale', 'Sign up name'),
            'phone' => \Yii::t('locale', 'Sign up phone'),
            'status' => \Yii::t('locale', 'Sign up status'),
            'sex' => \Yii::t('locale', 'Sign up sex'),
            'code' => \Yii::t('locale', 'Invitation code'),
            'school_id' => \Yii::t('locale', 'Invitation school'),
            'created_at' => \Yii::t('locale', 'Sign up created_at'),
            'operation' => \Yii::t('locale', 'Operation'),
        ];
    }
    public function attributeCustomTypes()
    {
        return array(
            'id' => array('data-options' => array('checkbox'=>'true'), 'key' => true),
            'name' => array('width' => 120),
            'phone' => array('width' => 120),
            'sex' => array('width' => 100,'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getSexArray())." }"),
            'status' => array('width' => 100,'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getStatusArray())." }"),
            'code' => array('width' => 120),
            'school_id' => array('width' => 100,'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getSchoolsArray())." }"),
            'created_at' => array('width' => 120, 'sortable' => 'true','formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => ['width' => 60,
                'buttons' => [
                    !\Yii::$app->user->can('user/invitation_update') ? ['type' => 'ajax', 'url' => \yii\helpers\Url::to(['user/invitation_update', 'id'=>'']), 'name' => \Yii::t('locale', 'Edit'), 'title' => \Yii::t('locale', 'Are you sure to edit these records?'), 'paramField' => 'id', 'icon' => 'icon-edit'] : null,
                ],
            ],


        );
    }
    
    public static function getSexArray() {
        return [
            static::STATUS_No    => \Yii::t('locale','未知'),
            static::STATUS_Man   => \Yii::t('locale', 'Man'), 
            static::STATUS_Woman => \Yii::t('locale', 'Woman'), 
        ];
    }



    public static function getStatusArray() {
        return [
            '0' => '未购车',
            '1' => '已购车',
        ];
    }

    public static function getSchoolsArray() {
        return [
            '0' => '',
            '1' => '万福驾校',
            '2' => '商场',
            '3' => '商场1',
            '4' => '商场2',
            '5' => '商场3',
            '6' => '商场4',
            '7' => '商场5',
            '8' => '商场6',
            '9' => '街电',
            '10' => '海报入口',
            '11' => '商场9',
            '12' => '商场10',
            '13' => '商场11',
        ];
    }

}
