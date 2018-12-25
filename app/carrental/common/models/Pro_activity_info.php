<?php

namespace common\models;

/**
 *
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property string $icon
 * @property string $href
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $city_id
 * @property integer $office_id
 * @property integer $status
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_activity_info extends \common\helpers\ActiveRecordModel
{
    const STATUS_NORMAL = 0;
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
            'title' => \Yii::t('locale', 'Title'),
            'content' => \Yii::t('locale', 'Content'),
            'icon' => \Yii::t('locale', 'Icon'),
            'href' => \Yii::t('carrental', 'Activity link'),
            'start_time' => \Yii::t('locale', 'Start time'),
            'end_time' => \Yii::t('locale', 'End time'),
            'city_id' => \Yii::t('locale', 'City'),
            'office_id' => \Yii::t('locale', 'Office'),
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
            'title' => array('width' => 200),
            'content' => array('width' => 300),
            'icon' => array('width' => 80,
                'formatter' => "function(value,row){ if (value == '') { return ''; } return '<img src=\''+value+'\' width=\'80\' alt=\''+row.title+'\' title=\''+row.title+'\'/>'; }"),
            'href' => array('width' => 180),
            'start_time' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'end_time' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'city_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){return row.city_disp;}"),
            'office_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){return row.office_disp;}"),
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
            static::STATUS_DISABLED => \Yii::t('locale', 'Disabled'), 
        ];
    }

}

