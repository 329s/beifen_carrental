<?php

namespace common\models;

use Yii;

/**
 *
 * @property integer $id
 * @property integer $type
 * @property integer $name
 * @property string $title
 * @property string $instruction
 * @property string $signature_a
 * @property string $signature_b
 * @property string $footer
 * @property integer $flag
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_rent_contract_options extends \common\helpers\ActiveRecordModel
{
    const STATUS_DISABLED = 10;
    const STATUS_ENABLED = 0;
    
    const TYPE_BOOKING = 1;         // 预定单 
    const TYPE_DISPATCHING = 2;     // 出车单 
    const TYPE_SETTLEMENT = 3;      // 结算单提示 
    const TYPE_VIOLATION = 4;       // 违章单

    const TYPE_RENTIG = 100;        // 租车合同 

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
            'type' => Yii::t('locale', 'Type'),
            'name' => Yii::t('locale', 'Name'),
            'title' => Yii::t('locale', '{name} setting', ['name'=>Yii::t('locale', 'Title')]),
            'instruction' => Yii::t('locale', '{name} setting', ['name'=>Yii::t('locale', 'Instruction')]),
            'signature_a' => Yii::t('locale', '{name} setting', ['name'=>Yii::t('carrental', 'Signature A')]),
            'signature_b' => Yii::t('locale', '{name} setting', ['name'=>Yii::t('carrental', 'Signature B')]),
            'footer' => Yii::t('locale', '{name} setting', ['name'=>Yii::t('locale', 'Footer')]),
            'flag' => Yii::t('locale', 'Flag'),
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
            'type' => array('width' => 180, 'sortable' => 'true'),
            'name' => array('width' => 100),
            'title' => array('width' => 200),
            'instruction' => array('width' => 200),
            'signature_a' => array('width' => 200),
            'signature_b' => array('width' => 200),
            'footer' => array('width' => 200),
            'flag' => array('width' => 80, 'sortable' => 'true',
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
    
}


