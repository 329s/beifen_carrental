<?php

namespace common\models;

/**
 *
 * @property integer $id
 * @property integer $type
 * @property string $title
 * @property string $content
 * @property integer $send_flag
 * @property integer $send_interval
 * @property integer $status
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_config_sms extends \common\helpers\ActiveRecordModel
{
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
            'type' => \Yii::t('locale', 'Type'),
            'title' => \Yii::t('locale', 'Title'),
            'content' => \Yii::t('locale', 'Content'),
            'send_flag' => Yii::t('locale', 'Send flag'),
            'send_interval' => Yii::t('locale', 'Send interval'),
            'status' => Yii::t('locale', 'Status'),
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
        $flagEnabled = \common\components\Consts::STATUS_ENABLED;
        return array(
            'id' => array('data-options' => array('checkbox'=>'true'), 'key' => true),
            'type' => array('width' => 100),
            'title' => array('width' => 200),
            'content' => array('width' => 400),
            'send_flag' => array('width' => 100, 'formatter'=>"function(value,row){ return '0x'+parseInt(value).toString(16); }"),
            'send_interval' => array('width' => 100),
            'status' => array('width' => 80, 'sortable' => 'true',
                'formatter' => "function(value,row){ if (value == {$flagEnabled}) { return $.custom.lan.defaults.role.enabled; } else { return $.custom.lan.defaults.role.disabled; };}"),
            'edit_user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.edit_user_disp; }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 160, 
                'buttons' => array(
                ),
            ),
        );
    }
    
    /**
     * @return boolean if the config is enabled.
     */
    public function isEnabled()
    {
        return $this->status == \common\components\Consts::STATUS_ENABLED;
    }
    
    /**
     * 
     * @param integer $type
     * @param array $defaults
     * @return \common\models\Pro_config_sms
     */
    public static function instanceByType($type, $defaults) {
        $obj = static::findOne(['type' => $type]);
        if (!$obj) {
            $obj = new Pro_config_sms();
            $obj->type = $type;
            $obj->office_id = 0;
            $obj->title = (isset($defaults['title']) ? $defaults['title'] : '');
            $obj->content = (isset($defaults['content']) ? $defaults['content'] : '');
            $obj->send_flag = (isset($defaults['send_flag']) ? $defaults['send_flag'] : 0);
            $obj->send_interval = (isset($defaults['send_interval']) ? $defaults['send_interval'] : 0);
            $obj->status = \common\components\Consts::STATUS_ENABLED;
            $obj->edit_user_id = \Yii::$app->user->id;
            $obj->save();
        }
        return $obj;
    }
    
    /**
     * 
     * @param integer $type
     * @return \common\models\Pro_config_sms
     */
    public static function getByType($type) {
        return static::findOne(['type' => $type]);
    }
    
}
