<?php
namespace common\models;

/**
* 
*/
class Pro_sign_up extends \common\helpers\ActiveRecordModel
{
	const STATUS_Man = 1;
    const STATUS_Woman = 0;

    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
        ];
    }

    public function rules()
    {
        return [
            [['name','phone','sex','way','source'],'required'],
            // [['phone'],'unique'],
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
            'way' => \Yii::t('locale', 'Sign up way'),
            'source' => \Yii::t('locale', 'Sign up source'),
            'city' => \Yii::t('locale', 'Sign up city'),
            'created_at' => \Yii::t('locale', 'Sign up created_at'),
            'remark' => \Yii::t('locale', 'Sign up remark'),
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
            'way' => array('width' => 100,'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getWayArray())." }"),
            'source' => array('width' => 100,'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getSourceArray())." }"),
            // 'way' => array('width' => 100),
            // 'source' => array('width' => 100),
            'city' => array('width' => 100),
            'remark' => array('width' => 100),
            'created_at' => array('width' => 120, 'sortable' => 'true','formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => ['width' => 60,
                'buttons' => [
                    !\Yii::$app->user->can('user/sign_up_update') ? ['type' => 'ajax', 'url' => \yii\helpers\Url::to(['user/sign_up_update', 'id'=>'']), 'name' => \Yii::t('locale', 'Delete'), 'title' => \Yii::t('locale', 'Are you sure to delete these records?'), 'paramField' => 'id', 'icon' => 'icon-edit'] : null,
                ],
            ],


        );
    }
    public static function getSexArray() {
        return [
            static::STATUS_Man => \Yii::t('locale', 'Man'), 
            static::STATUS_Woman => \Yii::t('locale', 'Woman'), 
        ];
    }

    //1：10万，2：20万，3：30万，4：50万
    public static function getWayArray() {
        return [
            1 => \Yii::t('locale', 'Ten million'),
            2 => \Yii::t('locale', 'Twenty million'),
            3 => \Yii::t('locale', 'Thirty million'),
            4 => \Yii::t('locale', 'Fifty million'),
        ];
    }
    //1：朋友推荐，2：微信上，3：单页，4：官网，5：其他
    public static function getSourceArray() {
        return [
            1 => '朋友推荐',
            2 => '微信',
            3 => '单页',
            4 => '官网',
            5 => '其他',
        ];
    }

    public static function getStatusArray() {
        return [
            '0' => '未处理',
            '1' => '已处理',
        ];
    }

}
