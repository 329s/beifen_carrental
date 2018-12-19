<?php

namespace common\models;

use Yii;

/**
 * Vehicle order relet
 *
 * @property integer $id
 * @property string $serial 订单号
 * @property integer $order_id 主订单ID
 * @property integer $origion_end_time 原还车时间
 * @property integer $new_end_time 新还车时间
 * @property integer $pay_source 租金支付方式
 * @property integer $total_amount 续租金额
 * @property integer $paid_amount 已缴金额
 * @property integer $status
 * @property string $remark                 // 订单备注
 * @property integer $edit_user_id           // 订单登记者管理员ID
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_vehicle_order_relet extends \common\helpers\ActiveRecordModel
{
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
            'serial' => Yii::t('locale', '{name} No.', ['name'=>Yii::t('locale', 'Order')]),
            'order_id' => Yii::t('locale', 'Main order'),
            'origion_end_time' => Yii::t('carrental', 'Origion return car time'),
            'new_end_time' => Yii::t('carrental', 'New return car time'),
            'pay_source' => Yii::t('locale', '{name} payment method', ['name' => Yii::t('locale', 'Rent')]),
            'total_amount' => Yii::t('carrental', 'Relet price'),
            'paid_amount' => Yii::t('locale', '{type} price', ['type'=>\Yii::t('locale', 'Paid')]),
            'status' => Yii::t('locale', 'Status'),
            'remark' => Yii::t('locale', 'Remark'),
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
            'serial' => array('width' => 100, 'sortable' => 'true'),
            'order_id' => array('width' => 100, 'sortable' => 'true',
                'formatter' => "function(value,row){return row.main_order_serial;}"),
            'origion_end_time' => array('width' => 160, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}",
                'editor' => array('type'=>'datetimebox', 'options'=>array('readonly'=>'true', 'showSeconds'=>'false')),
                ),
            'new_end_time' => array('width' => 170, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}",
                'editor' => array('type'=>'datetimebox', 'options'=>array('editable'=>'false', 'showSeconds'=>'false')),
                ),
            'pay_source' => array('width' => 120, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(\common\components\OrderModule::getOrderPayTypeArray())." }",
                'editor' => array(
                    'type'=>'combobox',
                    'options' => array('valueField'=>'id', 'textField'=>'text', 'editable'=>'false', 'data'=>\common\helpers\CEasyUI::convertComboboxDataToString(\common\components\OrderModule::getOrderPayTypeArray())),
                )),
            'total_amount' => array('width' => 100, 'sortable' => 'true',
                'editor' => array('type'=>'numberbox', 'options'=>array('readonly'=>'true'))),
            'paid_amount' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return value; }",
                //'editor' => array('type'=>'numberbox', 'options'=>array('readonly'=>'true'))
                ),
            'status' => array('width' => 60, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.status_disp; }",
                'editor' => array(
                    'type'=>'combobox',
                    'options' => array('valueField'=>'id', 'textField'=>'text', 'data'=>\common\helpers\CEasyUI::convertComboboxDataToString(\common\components\OrderModule::getOrderStatusArray())),
                ),
            ),
            'remark' => array('width' => 120,
                'editor' => 'textarea'),
            'edit_user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.edit_user_disp; }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 90, 
                'buttons' => array(
                    array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['order/order_relet_view', 'id'=>'']), 'name' => Yii::t('locale', 'View'), 'title' => \Yii::t('locale', 'View'), 'paramField' => 'id', 'icon' => 'icon-view'),
                    \Yii::$app->user->can('order/order_relet_delete') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['order/order_relet_delete', 'id'=>'']), 'name' => Yii::t('locale', 'Delete'), 'title' => Yii::t('locale', 'Delete'), 'paramField' => 'id', 'icon' => 'icon-delete') : null,
                ),
            ),
        );
    }
    
    public function setSerialNo() {
        if (empty($this->serial)) {
            $objMainOrder = Pro_vehicle_order::findById($this->order_id);
            $id = 0;
            if ($objMainOrder) {
                $id = $objMainOrder->getSerialNoHighPart();
            }
            $id += static::getAutoIncreamentId();
            $this->serial = \common\components\Consts::RELET_TRADE_NO_PREFIX.$id;
        }
        return $this->serial;
    }
    
    public function getEditUserName() {
        if ($this->edit_user_id) {
            $objAdmin = \backend\models\Rbac_admin::findById($this->edit_user_id);
            if ($objAdmin) {
                return $objAdmin->username;
            }
        }
        return '';
    }
    
    public function getMainOrderSerial() {
        $objOrder = Pro_vehicle_order::findById($this->order_id);
        if ($objOrder) {
            return $objOrder->serial;
        }
        return $this->order_id;
    }
    
    public function getMainOrder() {
        return Pro_vehicle_order::findById($this->order_id);
    }
    
    public static function createFindIdNamesArrayConfig($config = array()) {
        return ['class'=> static::className(), 'idField'=>'id', 'textField'=>'serial'];
    }

}
