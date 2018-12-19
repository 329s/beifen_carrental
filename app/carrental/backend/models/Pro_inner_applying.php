<?php
namespace backend\models;

/**
 * 
 * @property integer $id
 * @property integer $type
 * @property integer $office_id
 * @property integer $approval_office_id
 * @property string $plate_number
 * @property integer $status
 * @property string $content
 * @property string $applyer
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $vehicle_outbound_mileage
 * @property integer $vehicle_inbound_mileage
 * @property string $approval_content
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_inner_applying extends \common\helpers\ActiveRecordModel
{
    
    const STATUS_APPLYING = 1;     		// 待审批
    const STATUS_CANCELED = 2;                  // 已取消
    const STATUS_APPROVED = 3;    		// 已批复
    const STATUS_REJECTED = 4;                  // 已拒绝

    const TYPE_VEHICLE_BELONG_OFFICE = 1;	// 车辆划拨
    const TYPE_VEHICLE_STOP_OFFICE = 2;	// 车辆调用
    const TYPE_VEHICLE_INNER_USE = 3;	// 内部用车
    const TYPE_RENT_BENEFIT = 4;		// 租金优惠

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
            \yii\behaviors\BlameableBehavior::className(),
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
            'type' => \Yii::t('carrental', 'Applying type'),
            'office_id' => \Yii::t('carrental', 'Applying office'),
            'approval_office_id' => \Yii::t('carrental', 'Approval office'),
            'plate_number' => \Yii::t('locale', 'Plate number'),
            'status' => \Yii::t('carrental', 'Approval status'),
            'content' => \Yii::t('carrental', 'Applying description'),
            'applyer' => \Yii::t('carrental', 'Applyer'),
            'start_time' => \Yii::t('locale', 'Start time'),
            'end_time' => \Yii::t('locale', 'End time'),
            'vehicle_outbound_mileage' => \Yii::t('locale', 'Outbound mileage'),
            'vehicle_inbound_mileage' => \Yii::t('locale', 'Inbound mileage'),
            'approval_content' => \Yii::t('carrental', 'Approval content'),
            'created_by' => \Yii::t('locale', 'Created by'),
            'updated_by' => \Yii::t('locale', 'Updated by'),
            'created_at' => \Yii::t('carrental', 'Applying time'),
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
            'id' => array('data-options' => array('checkbox'=>'true'), 'key' => true, 'sortable' => 'true'),
            'type' => array('width' => 130, 'sortable' => 'true'),
            'office_id' => array('width' => 130, 'sortable' => 'true'),
            'plate_number' => array('width' => 180),
            'status' => array('width' => 80),
            'content' => array('width' => 180),
            'applyer' => array('width' => 120),
            'start_time' => array('width' => 140, 'sortable' => 'true'),
            'end_time' => array('width' => 140, 'sortable' => 'true'),
            'vehicle_outbound_mileage' => array('width' => 100),
            'vehicle_inbound_mileage' => array('width' => 100),
            'approval_content' => array('width' => 160),
            'created_by' => array('width' => 100, 'sortable' => 'true'),
            'updated_by' => array('width' => 100, 'sortable' => 'true'),
            'created_at' => array('width' => 140, 'sortable' => 'true'),
            'updated_at' => array('width' => 140, 'sortable' => 'true'),
            'operation' => array('width' => 60, 
                'buttons' => array(
                ),
            ),
        );
    }
    
    public static function getTypeArray() {
        return [
            static::TYPE_VEHICLE_STOP_OFFICE => \Yii::t('carrental', 'Vehicle stop office applying'),
            static::TYPE_VEHICLE_BELONG_OFFICE => \Yii::t('carrental', 'Vehicle belong office applying'),
            static::TYPE_VEHICLE_INNER_USE => \Yii::t('carrental', 'Inner using vehicle applying'),
            //static::TYPE_RENT_BENEFIT => \Yii::t('carrental', 'Rent benefit applying'),
        ];
    }
    
    public static function getStatusArray() {
        return [
            static::STATUS_APPLYING => \Yii::t('carrental', 'Waiting approval'),
            static::STATUS_CANCELED => \Yii::t('locale', 'Canceled'),
            static::STATUS_APPROVED => \Yii::t('carrental', 'Approved'),
            static::STATUS_REJECTED => \Yii::t('locale', 'Rejected'),
        ];
    }
    
    /**
     * @inheritdoc
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function find($skipOfficeLimit = false)
    {
        if ($skipOfficeLimit) {
            return \Yii::createObject(\yii\db\ActiveQuery::className(), [get_called_class()]);
        }
        else {
            return \Yii::createObject(\common\components\OfficeLimitedVehicleActiveQuery::className(), [get_called_class(), ['attribute'=>['office_id', 'approval_office_id']]]);
        }
    }

    /**
     * 
     * @return \common\helpers\ExtendActiveDataProvider
     */
    public static function createDataProvider($config = []) {
        return parent::createDataProvider(\yii\helpers\ArrayHelper::merge([
            'formattingAttributes' => [
                'status' => static::getStatusArray(),
                'type' => static::getTypeArray(),
                'start_time,end_time,created_at,updated_at' => 'datetime:Y-m-d H:i:s',
            ],
            'findAttributes' => [
                'office_id,approval_office_id' => \common\models\Pro_office::createFindIdNamesArrayConfig(),
                'created_by,updated_by' => \backend\models\Rbac_admin::createFindIdNamesArrayConfig(),
            ],
        ], $config));
    }

    public static function createFindIdNamesArrayConfig($config = array()) {
        return ['class'=> static::className(), 'idField'=>'id', 'textField'=>'content'];
    }

}

