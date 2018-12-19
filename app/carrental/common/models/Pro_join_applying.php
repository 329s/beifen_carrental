<?php

namespace common\models;

/**
 *
 * @property integer $id
 * @property string $name
 * @property string $phone
 * @property string $address
 * @property string $mail
 * @property string $message
 * @property integer $user_id
 * @property integer $status
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_join_applying extends \common\helpers\ActiveRecordModel
{
    const STATUS_NORMAL = 0;
    const STATUS_PROCESSED = 1;
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
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
            'name' => \Yii::t('locale', 'User Name'),
            'phone' => \Yii::t('locale', 'Telephone'),
            'address' => \Yii::t('locale', 'Address'),
            'mail' => \Yii::t('locale', 'Email'),
            'message' => \Yii::t('locale', 'Message'),
            'user_id' => \Yii::t('locale', 'User Name'),
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
            'name' => array('width' => 120, 'sortable' => 'true'),
            'phone' => array('width' => 120),
            'address' => array('width' => 100),
            'mail' => array('width' => 100),
            'message' => array('width' => 100),
            'user_id' => array('width' => 120, 'sortable' => 'true'),
            'status' => array('width' => 60,
                'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getStatusArray())." }"),
            'edit_user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.edit_user_disp; }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => ['width' => 60, 
                'buttons' => [
                    ['type' => 'dialog', 'url' => \yii\helpers\Url::to(['user/investapply_look', 'id'=>'']), 'name' => \Yii::t('locale', 'Edit'), 'title' => \Yii::t('locale', 'Edit'), 'paramField' => 'id', 'icon' => 'icon-edit'],
                ],
            ],
        );
    }
    
    public static function getStatusArray() {
        return [
            static::STATUS_NORMAL => \Yii::t('locale', 'Normal'), 
            static::STATUS_PROCESSED => \Yii::t('locale', 'Processed'), 
        ];
    }

}
