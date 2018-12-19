<?php

namespace common\models;

use Yii;

/**
 * City model
 *
 * @property integer $id
 * @property string $name
 * @property integer $type
 * @property integer $belong_id
 * @property integer $flag
 * @property integer $status
 * @property string $city_code
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_city extends \common\helpers\ActiveRecordModel
{
    const TYPE_PROVINCE = 10;
    const TYPE_CITY = 3;
    const TYPE_SUB = 2;
    
    const STATUS_NORMAL = 0;
    const STATUS_DISABLED = -1;
    
    const FLAG_NORMAL = 0;
    const FLAG_HOT = 0x01;
    
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
            ['type', 'default', 'value' => self::TYPE_SUB],
            ['type', 'in', 'range' => [self::TYPE_PROVINCE, self::TYPE_CITY, self::TYPE_SUB]],
            
            ['status', 'default', 'value' => self::STATUS_NORMAL],
            ['status', 'in', 'range' => [self::STATUS_NORMAL, self::STATUS_DISABLED]],
            
            ['flag', 'default', 'value' => self::FLAG_NORMAL],
            ['flag', 'in', 'range' => [self::FLAG_NORMAL, self::FLAG_HOT]],
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
            'name' => \Yii::t('locale', 'Name'),
            'type' => \Yii::t('locale', 'Region type'),
            'belong_id' => \Yii::t('locale', 'Belongs to region'),
            'flag' => \Yii::t('locale', 'Flag'),
            'status' => \Yii::t('locale', 'Status'),
            'city_code' => \Yii::t('carrental', 'City ID of gaode'),
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
        $disableFlag = self::STATUS_DISABLED;
        return array(
            'id' => array('data-options' => array('checkbox'=>'true'), 'key' => true),
            'name' => array('width' => 180, 'sortable' => 'true'),
            'type' => array('width' => 100),
            'belong_id' => array('width' => 100, 'sortable' => 'true'),
            'flag' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.flag_disp; }"),
            'status' => array('width' => 100, 'sortable' => 'true', 'formatter' => <<<EOD
function(value,row){ 
    if (value == {$disableFlag}) {
        return '<font color=\'red\' style=\'vertical-align:center;\'>X</font>';
    } else {
        return '<font color=\'green\' style=\'vertical-align:center;\'>√</font>';
    }
}
EOD
                ),
            'city_code' => array('width' => 100),
            'edit_user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.edit_user_disp; }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 160, 
                'buttons' => array(
                    \Yii::$app->user->can('city/add') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['city/add', 'type'=>self::TYPE_SUB]).'&belong_id=', 'condition'=>array("{field} >= ".self::TYPE_CITY, array('{field}'=>'type')), 'name' => Yii::t('locale', 'Add borough'), 'title' => Yii::t('locale', 'Add borough'), 'paramField' => 'id', 'icon' => 'icon-add') : null,
                    \Yii::$app->user->can('city/add') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['city/add', 'type'=>self::TYPE_SUB]).'&belong_id=', 'condition'=>array("{field} == ".self::TYPE_SUB, array('{field}'=>'type')), 'name' => Yii::t('locale', 'Add borough'), 'title' => Yii::t('locale', 'Add borough'), 'paramField' => 'belong_id', 'icon' => 'icon-add') : null,
                    \Yii::$app->user->can('city/edit') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['city/edit', 'id'=>'']), 'name' => Yii::t('locale', 'Edit'), 'title' => Yii::t('locale', 'Edit'), 'paramField' => 'id', 'icon' => 'icon-edit') : null,
                    \Yii::$app->user->can('city/delete') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['city/delete', 'id'=>'']), 'name' => Yii::t('locale', 'Delete'), 'needReload'=>true, 'title' => Yii::t('locale', 'Are you sure to delete these records?'), 'paramField' => 'id', 'icon' => 'icon-delete') : null,
                    \Yii::$app->user->can('city/alterstatus') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['city/alterstatus','status'=>self::STATUS_DISABLED]).'&id=', 'needReload'=>true, 'condition'=>array("{field} >= ".self::STATUS_NORMAL, array('{field}'=>'status')), 'name' => Yii::t('locale', 'Disable'), 'title' => Yii::t('locale', 'Disable'), 'paramField' => 'id', 'icon' => 'icon-lock') : null,
                    \Yii::$app->user->can('city/alterstatus') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['city/alterstatus','status'=>self::STATUS_NORMAL]).'&id=', 'needReload'=>true, 'condition'=>array("{field} <= ".self::STATUS_DISABLED, array('{field}'=>'status')), 'name' => Yii::t('locale', 'Enable'), 'title' => Yii::t('locale', 'Enable'), 'paramField' => 'id', 'icon' => 'icon-accept') : null,
                ),
            ),
            'children_city' => array('detailed' => true, ),
        );
    }
    
    public function getAttributeValues() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'status' => $this->status,
            'flag' => $this->flag,
            'belong_id' => $this->belong_id,
            'city_code' => $this->city_code,

            'flag_disp' => \common\components\CityModule::getCityFlagDisplayText($this->flag),
        ];
    }

}
