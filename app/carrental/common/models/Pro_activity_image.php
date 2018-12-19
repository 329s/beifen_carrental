<?php

namespace common\models;

use Yii;

/**
 *
 * @property integer $id
 * @property integer $type
 * @property integer $bind_param
 * @property string $name
 * @property integer $status
 * @property string $image
 * @property string $icon
 * @property string $href
 * @property string $remark
 * @property integer $end_time
 * @property integer $city_id
 * @property integer $office_id
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_activity_image extends \common\helpers\ActiveRecordModel
{
    const STATUS_DISABLED = 10;
    const STATUS_ENABLED = 0;
    
    const TYPE_APP_HOME_IMAGES = 1;
    const TYPE_WEB_HOME_IMAGES = 2;

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
            'type' => \Yii::t('locale', 'Type'),
            'bind_param' => \Yii::t('locale', 'Parameter'),
            'name' => \Yii::t('locale', 'Name'),
            'status' => \Yii::t('locale', 'Status'),
            'image' => \Yii::t('locale', 'Image'),
            'icon' => \Yii::t('locale', 'Icon'),
            'href' => \Yii::t('carrental', 'Activity link'),
            'remark' => \Yii::t('locale', 'Remark'),
            'edit_user_id' => \Yii::t('locale', 'Edit user'),
            'created_at' => \Yii::t('locale', 'Create time'),
            'updated_at' => \Yii::t('locale', 'Update time'),
            'operation' => \Yii::t('locale', 'Operation'),
            'city_id' => \Yii::t('locale', 'City'),
            'office_id' => \Yii::t('locale', 'Office'),
        );
    }
    
    /**
     * Returns the attribute custom types.
     * Attribute custom types is used for display model data in datagrid specified display options.
     * @return array attribute custom types (name=>array('width'=>null, 'data-options'=>null, 'editor'=>null,'formatter'=>null,'sortable'=>true))
     */
    public function attributeCustomTypes()
    {
        $flagEnabled = self::STATUS_ENABLED;
        return array(
            'id' => array('data-options' => array('checkbox'=>'true'), 'key' => true),
            'type' => array('width' => 180, 'sortable' => 'true',
                'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getTypesArray())." }"),
            'bind_param' => array('width' => 100),
            'name' => array('width' => 140),
            'status' => array('width' => 80, 'sortable' => 'true',
                'formatter' => "function(value,row){ if (value == {$flagEnabled}) { return $.custom.lan.defaults.role.enabled; } else { return $.custom.lan.defaults.role.disabled; };}"),
            'image' => array('width' => 148,
                'formatter' => "function(value,row){ return '<img src=\''+value+'\' width=\'140\' alt=\''+row.name+'\' title=\''+row.name+'\'/>'; }"),
            'icon' => array('width' => 80,
                'formatter' => "function(value,row){ if (value == '') { return ''; } return '<img src=\''+value+'\' width=\'80\' alt=\''+row.name+'\' title=\''+row.name+'\'/>'; }"),
            'href' => array('width' => 180),
            'city_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){return row.city_disp;}"),
            'office_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){return row.office_disp;}"),
            'remark' => array('width' => 180,
                'editor' => 'textarea'),
            'edit_user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.edit_user_disp; }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 160, 
                'buttons' => array(
                    \Yii::$app->user->can('activities/image_activities_edit') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['activities/image_activities_edit', 'id'=>'']), 'name' => \Yii::t('locale', 'Edit'), 'title' => \Yii::t('locale', 'Edit'), 'paramField' => 'id', 'icon' => 'icon-edit') : null,
                    \Yii::$app->user->can('activities/image_activities_delete') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['activities/image_activities_delete', 'id'=>'']), 'name' => \Yii::t('locale', 'Delete'), 'needReload'=>true, 'title' => \Yii::t('locale', 'Are you sure to delete these records?'), 'paramField' => 'id', 'icon' => 'icon-delete') : null,
                ),
            ),
        );
    }
    
    public static function getTypesArray() {
        return [
            \common\models\Pro_activity_image::TYPE_APP_HOME_IMAGES => \Yii::t('locale', '{type} image', ['type'=>  \Yii::t('locale', 'App homepage activity')]),
            \common\models\Pro_activity_image::TYPE_WEB_HOME_IMAGES => \Yii::t('locale', '{type} image', ['type'=>  \Yii::t('locale', 'Web homepage activity')]),
        ];
    }
    
    public function getImageUrl() {
        return \common\helpers\Utils::toFileAbsoluteUrl($this->image);
    }
    
}
