<?php

namespace common\models;

/**
 *
 * @property integer $id
 * @property string $type
 * @property integer $office_id
 * @property integer $int_value
 * @property float $float_value
 * @property string $str_value
 * @property integer $flag
 * @property integer $status
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_config_rent extends \common\helpers\ActiveRecordModel
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
            'office_id' => \Yii::t('locale', 'Office'),
            'int_value' => \Yii::t('locale', '{name} value', ['name'=>\Yii::t('locale', 'Integer')]),
            'float_value' => \Yii::t('locale', '{name} value', ['name'=>\Yii::t('locale', 'Decimal')]),
            'str_value' => \Yii::t('locale', '{name} value', ['name'=>\Yii::t('locale', 'Text')]),
            'flag' => Yii::t('locale', 'Flag'),
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
            'office_id' => array('width' => 100),
            'int_value' => array('width' => 100),
            'float_value' => array('width' => 100),
            'str_value' => array('width' => 100),
            'flag' => array('width' => 100, 'formatter'=>"function(value,row){ return '0x'+parseInt(value).toString(16); }"),
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
     * 
     * @param integer $key
     * @param array $defaults
     * @return \common\models\Pro_config_rent
     */
    public static function instanceByType($key, $defaults) {
        $obj = static::findOne(['type' => $key]);
        if (!$obj) {
            $obj = new Pro_config_rent();
            $obj->type = $key;
            $obj->office_id = 0;
            $obj->int_value = (isset($defaults['int_value']) ? $defaults['int_value'] : 0);
            $obj->float_value = (isset($defaults['float_value']) ? $defaults['float_value'] : 0);
            $obj->str_value = (isset($defaults['str_value']) ? $defaults['str_value'] : '');
            $obj->flag = (isset($defaults['flag']) ? $defaults['flag'] : 0);
            $obj->status = \common\components\Consts::STATUS_ENABLED;
            $obj->edit_user_id = \Yii::$app->user->id;
            $obj->save();
        }
        return $obj;
    }
    
    /**
     * 
     * @param integer $key
     * @return \common\models\Pro_config_rent
     */
    public static function getByType($key) {
        return static::findOne(['type' => $key]);
    }
}
