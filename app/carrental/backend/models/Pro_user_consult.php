<?php
namespace backend\models;

/**
 * 
 * @property integer $id
 * @property integer $office_id
 * @property integer $time
 * @property string $customer_name
 * @property string $customer_phone
 * @property string $content
 * @property integer $price
 * @property string $inputer_name
 * @property integer $status
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_user_consult extends \common\helpers\ActiveRecordModel
{
    
    const STATUS_FIRST_CONSULT = 1;     // 首次咨询
    const STATUS_SECOND_CONSULT = 2;    // 二次咨询
    const STATUS_INTENTION = 3;         // 确认意向
    const STATUS_PROCESSED = 4;         // 已经处理

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
            'office_id' => \Yii::t('locale', 'Belong office'),
            'time' => \Yii::t('carrental', 'Consult time'),
            'customer_name' => \Yii::t('locale', 'Customer name'),
            'customer_phone' => \Yii::t('locale', 'Contact number'),
            'content' => \Yii::t('carrental', 'Consult content and vehicle models'),
            'price' => \Yii::t('carrental', 'Consult price'),
            'inputer_name' => \Yii::t('locale', 'Inputer name'),
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
            'office_id' => array('width' => 130, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.office_disp; }",
                'editor' => array(
                    'type'=>'combotree',
                    'options' => array('valueField'=>'id', 'textField'=>'text', 'editable'=>'false', 'data'=>\common\helpers\CEasyUI::convertComboTreeDataToString(\common\components\OfficeModule::getOfficeComboTreeData())),
                ),
            ),
            'time' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}",
                'editor' => array('type'=>'datetimebox', 'options'=>array('readonly'=>'true')),),
            'customer_name' => array('width' => 120,
                'editor' => 'textbox'),
            'customer_phone' => array('width' => 120,
                'editor' => 'textbox'),
            'content' => array('width' => 260,
                'editor' => 'textarea'),
            'price' => array('width' => 100,
                'editor' => 'numberbox'),
            'inputer_name' => array('width' => 120,
                'editor' => 'textbox'),
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
            static::STATUS_FIRST_CONSULT => \Yii::t('carrental', 'First consult'),
            static::STATUS_SECOND_CONSULT => \Yii::t('carrental', 'Second consult'),
            static::STATUS_INTENTION => \Yii::t('carrental', 'Confirm intention'),
            static::STATUS_PROCESSED => \Yii::t('locale', 'Processed'),
        ];
    }
    
}


