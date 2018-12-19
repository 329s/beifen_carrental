<?php
namespace common\models;

/**
* 
*/
class Pro_instalment extends \common\helpers\ActiveRecordModel
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
            [['name','phone','product','numbers','type'],'required'],
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
            'product' => \Yii::t('locale', 'Instalment product'),
            'numbers' => \Yii::t('locale', 'Instalment numbers'),
            'type' => \Yii::t('locale', 'Instalment type'),
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
            'product' => array('width' => 120),
            'status' => array('width' => 100,'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getStatusArray())." }"),
            'numbers' => array('width' => 120),
            'type' => array('width' => 100,'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getTypeArray())." }"),
            'remark' => array('width' => 100),
            'created_at' => array('width' => 120, 'sortable' => 'true','formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => ['width' => 60,
                'buttons' => [
                    !\Yii::$app->user->can('user/instalment_update') ? ['type' => 'ajax', 'url' => \yii\helpers\Url::to(['user/instalment_update', 'id'=>'']), 'name' => \Yii::t('locale', 'Delete'), 'title' => \Yii::t('locale', 'Are you sure to delete these records?'), 'paramField' => 'id', 'icon' => 'icon-edit'] : null,
                ],
            ],


        );
    }

    
    public static function getStatusArray() {
        return [
            '0' => '未处理',
            '1' => '已处理',
        ];
    }
    //1:分期，2心愿
    public static function getTypeArray() {
        return [
            1 => '分期',
            2 => '心愿',
        ];
    }
}
