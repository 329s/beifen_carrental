<?php

namespace common\models;

/**
 * 
 * @property integer $id
 * @property string $card_no
 * @property string $card_name
 * @property integer $type
 * @property string $card_code
 * @property string $card_password
 * @property integer $amount
 * @property integer $recharged_amount
 * @property integer $activated_at
 * @property integer $status
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_member_card extends \common\helpers\ActiveRecordModel
{
    
    const STATUS_LOCKED = 0;
    const STATUS_ACTIVITED = 100;
    const STATUS_DISABLED = -10;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
            \common\helpers\behaviors\EditorBehavior::className(),
        ];
    }

    /**
     * Returns the attribute labels.
     * Attribute labels are mainly used in error messages of validation.
     * By default an attribute label is generated using {@link generateAttributeLabel}.
     * This method allows you to explicitly specify attribute labels.
     *
     * Note, in order to inherit labels defined in the parent class, a child class needs to
     * merge the parent labels with child labels using functions like array_merge().
     *
     * @return array attribute labels (name=>label)
     * @see generateAttributeLabel
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'card_no' => \Yii::t('locale', 'Member card no'),
            'card_name' => \Yii::t('locale', '{name} name', ['name'=>\Yii::t('locale', 'Member')]),
            'type' => \Yii::t('locale', 'Member type'),
            'card_code' => \Yii::t('locale', 'Card code'),
            'card_password' => \Yii::t('locale', 'Pay password'),
            'amount' => \Yii::t('locale', 'Current balance'),
            'recharged_amount' => \Yii::t('locale', 'Recharge amount'),
            'activated_at' => \Yii::t('locale', 'Activited time'),
            'status' => \Yii::t('locale', 'Status'),
            'edit_user_id' => \Yii::t('locale', 'Edit user'),
            'created_at' => \Yii::t('locale', 'Create time'),
            'updated_at' => \Yii::t('locale', 'Update time'),
            'operation' => \Yii::t('locale', 'Operation'),
        );
    }
    
    /**
     * Returns the attribute custom types.
     * Attribute custom types is used for display model data in datagrid specified display options.
     * @return array attribute custom types (name=>array('width'=>null, 'data-options'=>null, 'editor'=>null,'formatter'=>null,'sortable'=>true))
     */
    public function attributeCustomTypes()
    {
        return array(
            'id' => array('data-options' => array('checkbox'=>'true'), 'key' => true),
            'card_no' => array('width' => 100),
            'card_name' => array('width' => 100),
            'type' => array('width' => 100, 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(\common\models\Pub_user_info::getVipLevelsArray())." }"),
            'card_code' => array('width' => 100),
            'card_password' => array('width' => 100),
            'amount' => array('width' => 100),
            'recharged_amount' => array('width' => 100),
            'activated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'status' => array('width' => 80, 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getStatusArray())." }"
            ),
            'edit_user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.edit_user_disp; }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 60, 
                'buttons' => array(
                ),
            ),
        );
    }
    
    public static function getStatusArray() {
        return [
            static::STATUS_LOCKED => \Yii::t('locale', 'Non-activited'),
            static::STATUS_ACTIVITED => \Yii::t('locale', 'Activited'),
            static::STATUS_DISABLED => \Yii::t('locale', 'Disabled'),
        ];
    }
    
    /**
     * 
     * @return \common\helpers\ExtendActiveDataProvider
     */
    public static function createDataProvider($config = []) {
        return parent::createDataProvider(array_merge([
            'formattingAttributes' => [
                'type' => \common\models\Pub_user_info::getVipLevelsArray(),
                'status' => static::getStatusArray(),
                'created_at,updated_at' => 'datetime',
            ],
            'findAttributes' => [
                'edit_user_id' => \backend\models\Rbac_admin::createFindIdNamesArrayConfig(),
            ],
        ], $config));
    }

    public static function createFindIdNamesArrayConfig($config = array()) {
        return ['class'=> static::className(), 'idField'=>'id', 'textField'=>'card_no', 'extend'=>[0=>'']];
    }
    
}

