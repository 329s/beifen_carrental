<?php

namespace common\models;

use Yii;

/**
 * City area model
 *
 * @property integer $id
 * @property string $name
 * @property integer $city_id
 * @property integer $status
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_city_area extends \common\helpers\ActiveRecordModel
{
    const STATUS_NORMAL = 0;
    const STATUS_DISABLED = -1;
    
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
            ['status', 'default', 'value' => self::STATUS_NORMAL],
            ['status', 'in', 'range' => [self::STATUS_NORMAL, self::STATUS_DISABLED]],
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
            'city_id' => \Yii::t('locale', 'Belong city'),
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
        $disableFlag = self::STATUS_DISABLED;
        return array(
            'id' => array('data-options' => array('checkbox'=>'true'), 'key' => true),
            'name' => array('width' => 180, 'sortable' => 'true'),
            'city_id' => array('width' => 100, 'sortable' => 'true', 'formatter'=>"function(value,row){ return row.city_disp; }"),
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
            'edit_user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.edit_user_disp; }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 160, 
                'buttons' => array(
                    \Yii::$app->user->can('city/add_area') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['city/add_area', 'city_id'=>'']), 'name' => Yii::t('locale', 'Add borough'), 'title' => Yii::t('locale', 'Add borough'), 'paramField' => 'city_id', 'icon' => 'icon-add') : null,
                    \Yii::$app->user->can('city/edit_area') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['city/edit_area', 'id'=>'']), 'name' => Yii::t('locale', 'Edit'), 'title' => Yii::t('locale', 'Edit'), 'paramField' => 'id', 'icon' => 'icon-edit') : null,
                    \Yii::$app->user->can('city/delete_area') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['city/delete_area', 'id'=>'']), 'name' => Yii::t('locale', 'Delete'), 'needReload'=>true, 'title' => Yii::t('locale', 'Are you sure to delete these records?'), 'paramField' => 'id', 'icon' => 'icon-delete') : null,
                    \Yii::$app->user->can('city/alterstatus_area') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['city/alterstatus_area','status'=>self::STATUS_DISABLED]).'&id=', 'needReload'=>true, 'condition'=>array("{field} >= ".self::STATUS_NORMAL, array('{field}'=>'status')), 'name' => Yii::t('locale', 'Disable'), 'title' => Yii::t('locale', 'Disable'), 'paramField' => 'id', 'icon' => 'icon-lock') : null,
                    \Yii::$app->user->can('city/alterstatus_area') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['city/alterstatus_area','status'=>self::STATUS_NORMAL]).'&id=', 'needReload'=>true, 'condition'=>array("{field} <= ".self::STATUS_DISABLED, array('{field}'=>'status')), 'name' => Yii::t('locale', 'Enable'), 'title' => Yii::t('locale', 'Enable'), 'paramField' => 'id', 'icon' => 'icon-accept') : null,
                ),
            ),
        );
    }
    
}

