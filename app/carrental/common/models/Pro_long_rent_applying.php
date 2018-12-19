<?php

namespace common\models;

/**
 *
 * @property integer $id
 * @property string $name
 * @property string $phone
 * @property string $company
 * @property string $mail
 * @property string $message
 * @property integer $office_id_take_car
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $status
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_long_rent_applying extends \common\helpers\ActiveRecordModel
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
            'company' => \Yii::t('locale', '{name} name', ['name'=>\Yii::t('locale', 'Enterprise')]),
            'mail' => \Yii::t('locale', 'Email'),
            'message' => \Yii::t('locale', 'Message'),
            'office_id_take_car' => \Yii::t('locale', '{name} office', ['name'=>\Yii::t('carrental', 'Take car')]),
            'start_time' => \Yii::t('locale', '{name} time', ['name'=>\Yii::t('carrental', 'Start rent car')]),
            'end_time' => \Yii::t('locale', '{name} time', ['name'=>\Yii::t('carrental', 'End rent car')]),
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
            'company' => array('width' => 100),
            'mail' => array('width' => 100),
            'message' => array('width' => 100),
            'office_id_take_car' => array('width' => 120, 'sortable' => 'true'),
            'start_time' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'end_time' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'status' => array('width' => 60,
                'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getStatusArray())." }"),
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
            static::STATUS_PROCESSED => \Yii::t('locale', 'Processed'), 
        ];
    }

}
