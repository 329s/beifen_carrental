<?php

namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;

/**
 * Vehicle brand
 *
 * @property integer $id
 * @property string $name 品牌
 * @property integer $belong_brand
 * @property integer $flag
 */
class Pro_vehicle_brand extends \common\helpers\ActiveRecordModel
{
    const FLAG_DISABLED = 10;
    const FLAG_ENABLED = 0;
    
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
            'name' => Yii::t('locale', 'Name'),
            'belong_brand' => Yii::t('locale', 'Belongs to {name}', ['name' => \Yii::t('locale', 'brand')]),
            'flag' => Yii::t('locale', 'Flag'),
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
        $flagEnabled = self::FLAG_ENABLED;
        return array(
            'id' => array('data-options' => array('checkbox'=>'true'), 'key' => true),
            'name' => array('width' => 100, 'sortable' => 'true'),
            'belong_brand' => array('width' => 100, 'sortable' => 'true',
                'formatter' => "function(value,row){return row.name_x;}"),
            'flag' => array('width' => 80, 'sortable' => 'true',
                'formatter' => "function(value,row){ if (value == {$flagEnabled}) { return $.custom.lan.defaults.role.enabled; } else { return $.custom.lan.defaults.role.disabled; };}"),
            'operation' => array('width' => 90, 
                'buttons' => array(
                    \Yii::$app->user->can('vehicle/addbrand') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['vehicle/addbrand', 'belong_id'=>'']), 'name' => Yii::t('locale', '{operation} vehicle brand', ['operation' => \Yii::t('locale', 'Add')]), 'title' => Yii::t('locale', '{operation} vehicle brand', ['operation' => \Yii::t('locale', 'Add')]), 'paramField' => 'id', 'icon' => 'icon-add') : null,
                    \Yii::$app->user->can('vehicle/editbrand') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['vehicle/editbrand', 'id'=>'']), 'name' => Yii::t('locale', '{operation} vehicle brand', ['operation' => \Yii::t('locale', 'Edit')]), 'title' => Yii::t('locale', '{operation} vehicle brand', ['operation' => \Yii::t('locale', 'Edit')]), 'paramField' => 'id', 'icon' => 'icon-edit') : null,
                    \Yii::$app->user->can('vehicle/deletebrand') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['vehicle/deletebrand', 'id'=>'']), 'name' => Yii::t('locale', '{operation} vehicle brand', ['operation' => \Yii::t('locale', 'Delete')]), 'title' => Yii::t('locale', 'Are you sure to delete these records?'), 'paramField' => 'id', 'icon' => 'icon-delete') : null,
                ),
            ),
        );
    }
    
}
