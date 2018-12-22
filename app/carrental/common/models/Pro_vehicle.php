<?php

namespace common\models;

use Yii;

/**
 *
 * @property integer $id
 * @property string $plate_number
 * @property integer $model_id
 * @property integer $status
 * @property string $engine_number
 * @property string $vehicle_number
 * @property string $certificate_number
 * @property integer $color
 * @property integer $baught_price
 * @property integer $baught_tax
 * @property integer $baught_time
 * @property integer $baught_insurance
 * @property integer $decoration_fee
 * @property integer $license_plate_fee
 * @property integer $baught_kilometers
 * @property integer $cur_kilometers
 * @property integer $belong_office_id
 * @property integer $stop_office_id
 * @property integer $vehicle_property
 * @property integer $gps_id
 * @property string $remark
 * @property string $vehicle_image
 * @property string $certificate_image
 * @property integer $annual_inspection_time
 * @property integer $tci_renewal_time
 * @property integer $vci_renewal_time
 * @property integer $upkeep_mileage_interval
 * @property integer $upkeep_time_interval
 * @property integer $last_upkeep_mileage
 * @property integer $last_upkeep_time
 * @property integer $next_upkeep_mileage
 * @property integer $next_upkeep_time
 * @property integer $validation_id
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $mobile
 */
class Pro_vehicle extends \common\helpers\ActiveRecordModel
{
    const STATUS_NORMAL = 1;    // 在库待租
    const STATUS_BOOKED = 2;    // 已预订
    const STATUS_RENTED = 3;    // 已出租
    const STATUS_MAINTENANCE = 4; // 维护
    const STATUS_SAILED = 5;
    const STATUS_DELETED = 10;
    
    const PROPERTY_BAUGHT = 1;
    const PROPERTY_AFFILIATE = 2;
    const PROPERTY_TRUSTTEE = 3;
    
    private $_vehicleModel = null;
    private $_showModelDetail = false;
    
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
            [['plate_number', 'model_id', 'status', 'engine_number', 'vehicle_number', 'certificate_number'], 'required'],
            [['model_id', 'status','isoneway', 'color', 'baught_time', 'baught_kilometers', 'cur_kilometers', 'belong_office_id', 'stop_office_id', 'vehicle_property', 'gps_id', 'annual_inspection_time', 'tci_renewal_time', 'vci_renewal_time', 'upkeep_config_id', 'upkeep_mileage_interval', 'upkeep_time_interval', 'last_upkeep_mileage', 'last_upkeep_time', 'next_upkeep_mileage', 'next_upkeep_time', 'validation_id'], 'integer'],
            [['baught_price', 'baught_tax', 'baught_insurance', 'decoration_fee', 'license_plate_fee'], 'number'],
            [['plate_number', 'engine_number', 'vehicle_number', 'certificate_number'], 'string', 'max' => 32],
            [['remark', 'vehicle_image', 'certificate_image'], 'string', 'max' => 255],
            [['plate_number', 'engine_number', 'vehicle_number', 'certificate_number'], 'unique'],
            
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
            'plate_number' => Yii::t('locale', 'Plate number'),
            'model_id' => Yii::t('locale', 'Vehicle model'),
            'status' => Yii::t('locale', 'Vehicle status'),
            'isoneway' => Yii::t('locale', 'Is one rent'),
            'engine_number' => Yii::t('locale', 'Engine ID'),
            'vehicle_number' => Yii::t('locale', 'Vehicle identity number'),
            'certificate_number' => Yii::t('locale', 'Vehicle certificate number'),
            'color' => Yii::t('locale', 'Vehicle color'),
            'baught_price' => Yii::t('carrental', 'Baught price'),
            'baught_tax' => Yii::t('locale', 'Purchase tax'),
            'baught_time' => Yii::t('locale', 'Baught time'),
            'baught_insurance' => Yii::t('carrental', 'Insurance fee'),
            'decoration_fee' => Yii::t('carrental', 'Vehicle decoration fee'),
            'license_plate_fee' => Yii::t('carrental', 'License plate fee'),
            'baught_kilometers' => Yii::t('locale', 'Baught kilometers'),
            'cur_kilometers' => Yii::t('locale', 'Current kilometers'),
            'belong_office_id' => Yii::t('locale', 'Belong office'),
            'stop_office_id' => Yii::t('locale', 'Stop office'),
            'vehicle_property' => Yii::t('locale', 'Vehicle property'),
            'gps_id' => Yii::t('locale', 'GPS ID'),
            'remark' => Yii::t('locale', 'Remark'),
            'vehicle_image' => Yii::t('locale', 'Vehicle image'),
            'certificate_image' => Yii::t('locale', 'Certificate image'),
            'annual_inspection_time' => Yii::t('locale', 'Annual inspection date'),
            'tci_renewal_time' => Yii::t('locale', 'Renewal date').'('.Yii::t('carrental', 'Traffic compulsory insurance').')',
            'vci_renewal_time' => Yii::t('locale', 'Renewal date').'('.Yii::t('carrental', 'Vehicle commercial insurance').')',
            'upkeep_mileage_interval' => Yii::t('carrental', 'Maintenance by mileage interval'),
            'upkeep_time_interval' => Yii::t('carrental', 'Maintenance by time interval'),
            'last_upkeep_mileage' => Yii::t('carrental', 'Current maintenance mileage'),
            'last_upkeep_time' => Yii::t('carrental', 'Current maintenance time'),
            'next_upkeep_mileage' => Yii::t('carrental', 'Next maintenance mileage'),
            'next_upkeep_time' => Yii::t('carrental', 'Next maintenance time'),
            'validation_id' => Yii::t('locale', 'Vehicle validation info'),
            'edit_user_id' => Yii::t('locale', 'Edit user'),
            'created_at' => Yii::t('locale', 'Create time'),
            'updated_at' => Yii::t('locale', 'Update time'),
            'operation' => Yii::t('locale', 'Operation'),
            'operation_button' => Yii::t('locale', 'Operation'),

            'upkeep_status' => \Yii::t('carrental', 'Upkeep'),
            'annual_survey' => \Yii::t('carrental', 'Annual survey'),
            'renewal_status' => \Yii::t('carrental', 'Renewal'),
            
            'left_upkeep_mileage' => Yii::t('carrental', 'Left upkeep mileage'),
            'left_upkeep_time' => Yii::t('carrental', 'Left upkeep time'),
            'inquiryCount'=> Yii::t('locale', 'Inquiry count'),
            'mobile'=> Yii::t('locale', 'Mobile'),
			'locator_device'=> Yii::t('locale', 'Locator'),
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
            'plate_number' => array('width' => 100, 'sortable' => 'true'),
            'model_id' => array('width' => 120, 'sortable' => 'true'),
            'status' => array('width' => 60, 'sortable' => 'true'),
            'isoneway' => array('width' => 60, 'sortable' => 'true'),
            'mobile' => array('width' => 60, 'sortable' => 'true'),
            'locator_device' => array('width' => 60, 'sortable' => 'true'),
            'engine_number' => array('width' => 100),
            'vehicle_number' => array('width' => 150),
            'certificate_number' => array('width' => 100),
            'color' => array('width' => 60, 'sortable' => 'true'),
            'baught_price' => array('width' => 100),
            'baught_tax' => array('width' => 100),
            'baught_time' => array('width' => 80, 'sortable' => 'true'),
            'baught_insurance' => array('width' => 100),
            'decoration_fee' => array('width' => 100),
            'license_plate_fee' => array('width' => 100),
            'baught_kilometers' => array('width' => 100, 'sortable' => 'true'),
            'cur_kilometers' => array('width' => 100),
            'belong_office_id' => array('width' => 100, 'sortable' => 'true'),
            'stop_office_id' => array('width' => 100, 'sortable' => 'true'),
            'vehicle_property' => array('width' => 100),
            'gps_id' => array('width' => 100),
            'vehicle_image' => array('width' => 148),
            'certificate_image' => array('width' => 100),
            'annual_inspection_time' => array('width' => 80, 'sortable' => 'true'),
            'tci_renewal_time' => array('width' => 80, 'sortable' => 'true'),
            'vci_renewal_time' => array('width' => 80, 'sortable' => 'true'),
            'upkeep_mileage_interval' => array('width' => 100),
            'upkeep_time_interval' => array('width' => 100),
            'last_upkeep_mileage' => array('width' => 100),
            'last_upkeep_time' => array('width' => 140, 'sortable' => 'true'),
            'next_upkeep_mileage' => array('width' => 100),
            'next_upkeep_time' => array('width' => 140, 'sortable' => 'true'),
            'validation_id' => array('width' => 100),
            'upkeep_status' => array('width' => 100),
            'annual_survey' => array('width' => 100),
            'renewal_status' => array('width' => 100),
            'inquiryCount' => array('sortable' => 'true'),
            
            'edit_user_id' => array('width' => 100, 'sortable' => 'true'),
            'created_at' => array('width' => 140, 'sortable' => 'true'),
            'updated_at' => array('width' => 140, 'sortable' => 'true'),
            'operation' => array('width' => 270, 
                'buttons' => array(
                    //\Yii::$app->user->can('vehicle/edit') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['vehicle/edit', 'id'=>'']), 'name' => Yii::t('locale', 'Edit'), 'title' => Yii::t('locale', 'Edit'), 'paramField' => 'id', 'icon' => 'icon-edit', 'showText'=>true) : null,
                    //\Yii::$app->user->can('vehicle/delete') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['vehicle/delete', 'id'=>'']), 'name' => Yii::t('locale', 'Delete'), 'title' => Yii::t('locale', 'Are you sure to delete these records?'), 'paramField' => 'id', 'icon' => 'icon-delete', 'showText'=>true) : null,
                    \Yii::$app->user->can('vehicle/validation') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['vehicle/validation', 'purpose'=>'management_validation'])."&vehicle_id=", 'name' => Yii::t('carrental', 'Vehicle validation'), 'title' => Yii::t('carrental', 'Vehicle validation'), 'paramField' => 'id', 'icon' => 'icon-bug', 'showText' => true) : null,
                    \Yii::$app->user->can('vehicle/violation_info') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['vehicle/violation_info', 'vehicle_id'=>'']), 'name' => Yii::t('carrental', 'Violation input'), 'title' => Yii::t('carrental', 'Violation input'), 'paramField' => 'id', 'icon' => 'icon-bug', 'showText' => true) : null,

                    // \Yii::$app->user->can('order/violation_info') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['order/vehicle_id_office_change', 'serial'=>'']), 'name' => Yii::t('locale', 'Payment details'), 'title' => Yii::t('locale', 'Order payment details'), 'paramField' => 'serial', 'icon' => 'icon-book_tabs', 'showText'=>true) : null,
                    \Yii::$app->user->can('vehicle/violation_info') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['vehicle/vehicle_id_office_change', 'vehicle_id'=>'']), 'name' => Yii::t('carrental', 'Vehicle office change'), 'title' => Yii::t('carrental', 'Vehicle office change'), 'paramField' => 'id', 'icon' => 'icon-bug', 'showText' => true) : null,
                ),
            ),
			'operation_button'=> array(
				'width' => 100, 
				'buttons' => array(
					array('type' => 'tab', 'url' => \yii\helpers\Url::to(['inquiry/index'])."?plate_number=", 'name' => Yii::t('locale', 'Vehicle Inquiry'), 'title' => Yii::t('locale', 'Vehicle Inquiry'), 'paramField' => 'plate_number', 'icon' => '', 'showText' => true),
				),
			),
            
        );
    }
    
    public function updateNextMaintenanceCheckPoint() {
        $nextMileage = $this->last_upkeep_mileage + $this->upkeep_mileage_interval;
        $nextTime = $this->last_upkeep_time + $this->upkeep_time_interval;
        $hasUpdate = false;
        if ($nextMileage != $this->next_upkeep_mileage) {
            $this->next_upkeep_mileage = $nextMileage;
            $hasUpdate = true;
        }
        elseif ($nextTime != $this->next_upkeep_time) {
            $this->next_upkeep_time = $nextTime;
            $hasUpdate = true;
        }
        return $hasUpdate;
    }
    
    public function setVehicleModel($vehicleModel) {
        $this->_vehicleModel = $vehicleModel;
    }
    
    public function getVehicleModel() {
        if ($this->_vehicleModel == null) {
            $this->_vehicleModel = \common\models\Pro_vehicle_model::findById($id);
        }
        return $this->_vehicleModel;
    }
    
    public function setShowVehicleModelDetail($enabled = true) {
        $this->_showModelDetail = $enabled;
    }
    
    public function getColorText() {
        $arrColors = \common\components\VehicleModule::getVehicleColorsArray();
        return (isset($arrColors[$this->color]) ? $arrColors[$this->color] : '');
    }
    
    /**
     * @inheritdoc
     * @return \yii\db\ActiveQuery the newly created [[\yii\db\ActiveQuery]] instance.
     */
    public static function find($skipOfficeLimit = false)
    {
        if ($skipOfficeLimit) {
            return \Yii::createObject(\yii\db\ActiveQuery::className(), [get_called_class()]);
        }
        else {
            return \Yii::createObject(\common\components\OfficeLimitedActiveQuery::className(), [get_called_class(), ['attribute'=>['stop_office_id'], 'enableAreaLimit'=>true]]);
        }
    }
    
    /**
     * 
     * @return \common\helpers\ExtendActiveDataProvider
     */
    public static function createDataProvider($config = []) {
        return parent::createDataProvider(\yii\helpers\ArrayHelper::merge([
            'formattingAttributes' => [
                'status' => \common\components\VehicleModule::getVehicleStatusWithAllArray(),
                'color' => \common\components\VehicleModule::getVehicleColorsArray(),
                //'vehicle_image' => function($model, $attribute) {
                //    
                //},
                'vehicle_image' => 'image:link',
                'certificate_image' => 'image:link',
                'baught_time,annual_inspection_time,tci_renewal_time,vci_renewal_time,next_upkeep_time' => 'date',
                'created_at,updated_at' => 'datetime',
            ],
            'findAttributes' => [
                'model_id' => Pro_vehicle_model::createFindIdNamesArrayConfig(),
                'belong_office_id,stop_office_id' => Pro_office::createFindIdNamesArrayConfig(),
                'edit_user_id' => \backend\models\Rbac_admin::createFindIdNamesArrayConfig(),
            ],
            'prepareDatas' => function($models, $dataProvider) {
                $arrIds = [];
                foreach ($models as $model) {
                    $arrIds[] = $model['id'];
                }
                $arrVehicleStatus = \common\components\OrderModule::getVehicleStatusByVehicleIds($arrIds);
                foreach ($models as $model) {
                    $statusInfo = (isset($arrVehicleStatus[$model['id']]) ? $arrVehicleStatus[$model['id']] : null);
                    if ($statusInfo) {
                        $model['status'] = $statusInfo['status'];
                    }
                }
            }
        ], $config));
    }

    public static function createFindIdNamesArrayConfig($config = array()) {
        return ['class'=> static::className(), 'idField'=>'id', 'textField'=>'plate_number'];
    }

}