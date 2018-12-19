<?php

namespace common\models;

/**
 * 
 * @property integer $id
 * @property string $sn
 * @property integer $type
 * @property integer $customer_id
 * @property integer $amount
 * @property integer $status
 * @property integer $flag
 * @property integer $edit_user_id
 * @property integer $activated_at
 * @property integer $used_at
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_gift_code extends \common\helpers\ActiveRecordModel
{
    
    const STATUS_LOCKED = -1;
    const STATUS_NORMAL = 0;
    const STATUS_USED = 100;
    const STATUS_DISABLED = -10;

    const TYPE_PREFERENTIAL_CODE = 1;
    const TYPE_PERSONAL_CODE = 2;

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
            'sn' => \Yii::t('locale', 'Serial'),
            'type' => \Yii::t('locale', 'Type'),
            'customer_id' => \Yii::t('locale', 'Bind customer ID'),
            'amount' => \Yii::t('locale', 'Gift amount'),
            'status' => \Yii::t('locale', 'Status'),
            'flag' => \Yii::t('locale', 'Flag'),
            'edit_user_id' => \Yii::t('locale', 'Edit user'),
            'activated_at' => \Yii::t('locale', 'Activited time'),
            'used_at' => \Yii::t('locale', 'Used time'),
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
            'sn' => array('width' => 100),
            'type' => array('width' => 100, 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getTypesArray())." }"),
            'customer_id' => array('width' => 100, 'formatter' => "function(value,row){ return row.customer_disp; }"),
            'amount' => array('width' => 100),
            'status' => array('width' => 80, 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getStatusArray())." }"
            ),
            'flag' => array('width' => 100),
            'edit_user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.edit_user_disp; }"),
            'activated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'used_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
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
            static::STATUS_LOCKED => \Yii::t('locale', 'Non-activited'),
            static::STATUS_NORMAL => \Yii::t('locale', 'Normal'),
            static::STATUS_USED => \Yii::t('locale', 'Used'),
            static::STATUS_DISABLED => \Yii::t('locale', 'Disabled'),
        ];
    }
    
    public static function getTypesArray() {
        return [
            static::TYPE_PREFERENTIAL_CODE => \Yii::t('locale', 'Preferential code'),
            static::TYPE_PERSONAL_CODE => \Yii::t('locale', 'Personal preferential code'),
        ];
    }
    
    public function generateSN() {
        if (empty($this->sn)) {
            $this->sn = static::autoSN();
        }
        
        return $this->sn;
    }
    
    public static function autoSN() {
        $maxId = static::getAutoIncreamentId('sn');
        if ($maxId < 10000000) {
            srand(time());
            $maxId = rand(12320000, 21329999);
        }
        return $maxId;
    }
    
}

