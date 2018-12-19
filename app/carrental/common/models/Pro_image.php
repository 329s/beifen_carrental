<?php

namespace common\models;

use Yii;

/**
 *
 * @property integer $id
 * @property integer $bind_type
 * @property integer $bind_id
 * @property integer $status
 * @property string $path
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_image extends \common\helpers\ActiveRecordModel
{
    const STATUS_DISABLED = 10;
    const STATUS_ENABLED = 0;
    
    const BIND_TYPE_OFFICE_PHOTO = 1310;            // 门店照片
    const BIND_TYPE_VEHICLE_VALIDATION = 1510;      // 车辆验车图片 
    const BIND_TYPE_SPLENDID_IDEA_ATTACHMENT = 1773;    // 金点子附件 

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
            'bind_type' => Yii::t('locale', 'Type'),
            'bind_id' => Yii::t('locale', 'Bind id'),
            'status' => Yii::t('locale', 'Status'),
            'path' => Yii::t('locale', 'Path'),
            'edit_user_id' => Yii::t('locale', 'Edit user'),
            'created_at' => Yii::t('locale', 'Create time'),
            'updated_at' => Yii::t('locale', 'Update time'),
            'operation' => Yii::t('locale', 'Operation'),
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
            'bind_type' => array('width' => 180, 'sortable' => 'true'),
            'bind_id' => array('width' => 100),
            'status' => array('width' => 80, 'sortable' => 'true',
                'formatter' => "function(value,row){ if (value == {$flagEnabled}) { return $.custom.lan.defaults.role.enabled; } else { return $.custom.lan.defaults.role.disabled; };}"),
            'path' => array('width' => 100, 'sortable' => 'true'),
            'edit_user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.edit_user_disp; }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 160, 
                'buttons' => array(
                ),
            ),
            'children_city' => array('detailed' => true, ),
        );
    }
    
    public function getImageUrl() {
        return \common\helpers\Utils::toFileAbsoluteUrl($this->path);
    }
    
}
