<?php

namespace common\models;

/**
 * 
 * @property integer $id
 * @property integer $type
 * @property integer $time
 * @property integer $customer_id
 * @property string $customer_name
 * @property string $customer_phone
 * @property string $content
 * @property integer $status
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pub_user_sms extends \common\helpers\ActiveRecordModel
{
    
    const STATUS_NORMAL = 0;

    const TYPE_SENT = 1;
    const TYPE_RECEIVED = 2;

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
            'type' => \Yii::t('locale', 'Type'),
            'time' => \Yii::t('locale', 'Time'),
            'customer_id' => \Yii::t('locale', 'Customer name'),
            'customer_name' => \Yii::t('locale', 'Customer name'),
            'customer_phone' => \Yii::t('locale', 'Contact number'),
            'content' => \Yii::t('carrental', 'SMS content'),
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
            'type' => array('width' => 60),
            'time' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}",
                'editor' => array('type'=>'datetimebox', 'options'=>array('readonly'=>'true')),),
            'customer_id' => array('width' => 130, 'sortable' => 'true'),
            'customer_name' => array('width' => 120,
                'editor' => array(
                    'type'=>'combobox',
                    'options' => array('valueField'=>'id', 'textField'=>'text', 'method'=>'get', 'hasDownArrow'=>'false'),
                )
            ),
            'customer_phone' => array('width' => 120,
                'editor' => 'textbox'),
            'content' => array('width' => 260,
                'editor' => 'textarea'),
            'status' => array('width' => 80, 'formatter' => "function(value,row) {return row.status_disp;}",
                'editor' => array(
                    'type'=>'combobox',
                    'options' => array('valueField'=>'id', 'textField'=>'text', 'editable'=>'false', 'data'=>\common\helpers\CEasyUI::convertComboboxDataToString(static::getStatusArray())),
                ),
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
            static::STATUS_NORMAL => \Yii::t('locale', 'Normal'),
        ];
    }
    
}
