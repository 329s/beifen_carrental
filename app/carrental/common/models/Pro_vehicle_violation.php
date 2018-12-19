<?php

namespace common\models;

use Yii;

/**
 *
 * @property integer $id
 * @property integer $vehicle_id
 * @property integer $order_id
 * @property integer $violated_at
 * @property integer $notified_at
 * @property integer $score
 * @property integer $penalty
 * @property integer $status
 * @property integer $description
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_vehicle_violation extends \common\helpers\ActiveRecordModel
{
    const STATUS_UNPROCESSED = 0;
    const STATUS_PROCESSED = 1;
    
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
            'vehicle_id' => Yii::t('locale', 'Vehicle'),
            'order_id' => Yii::t('locale', '{name} No.', ['name'=>Yii::t('locale', 'Order')]),
            'violated_at' => \Yii::t('locale', '{name} time', ['name'=>  Yii::t('carrental', 'Violation')]),
            'notified_at' => \Yii::t('locale', '{name} time', ['name'=>  Yii::t('carrental', 'Notified')]),
            'score' => Yii::t('carrental', 'Violation score'),
            'penalty' => Yii::t('carrental', 'Violation penalty'),
            'status' => Yii::t('locale', 'Status'),
            'description' => Yii::t('carrental', 'Violation description'),
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
        return array(
            'id' => array('data-options' => array('checkbox'=>'true'), 'key' => true),
            'vehicle_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.plate_number; }"),
            'order_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.order_serial; }"),
            'violated_at' => array('width' => 160, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}",
                'editor' => 'datetimebox'),
            'notified_at' => array('width' => 160, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}",
                'editor' => 'datetimebox'),
            'score' => array('width' => 100, 'sortable' => 'true',
                'editor' => 'numberbox'),
            'penalty' => array('width' => 100, 'sortable' => 'true',
                'editor' => 'numberbox'),
            'status' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.status_disp; }",
                'editor' => array(
                    'type'=>'combobox',
                    'options' => array('valueField'=>'id', 'textField'=>'text', 'data'=>\common\helpers\CEasyUI::convertComboboxDataToString(\common\components\VehicleModule::getVehicleViolationStatusArray())),
                )),
            'description' => array('width' => 240, 'sortable' => 'true',
                'editor' => 'textarea'),
            'edit_user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.edit_user_disp; }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 60, 
                'buttons' => array(
                    \Yii::$app->user->can('vehicle/violation_edit') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['vehicle/violation_edit', 'id'=>'']), 'name' => Yii::t('locale', 'Edit'), 'title' => Yii::t('locale', 'Edit'), 'paramField' => 'id', 'icon' => 'icon-edit') : null,
                    \Yii::$app->user->can('vehicle/violation_delete') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['vehicle/violation_delete', 'id'=>'']), 'name' => Yii::t('locale', 'Delete'), 'title' => Yii::t('locale', 'Are you sure to delete these records?'), 'paramField' => 'id', 'icon' => 'icon-delete') : null,
                ),
            ),
        );
    }
}    
