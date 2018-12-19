<?php

namespace common\models;

use Yii;

/**
 * Vehicle model
 *
 * @property integer $id
 * @property integer $brand 品牌ID
 * @property integer $model_series 子品牌ID
 * @property string $vehicle_model 车辆型号
 * @property integer $vehicle_type 车型类型
 * @property integer $vehicle_flag 车型标签
 * @property integer $carriage 车厢数量
 * @property integer $seat 座位数量
 * @property integer $gearbox 变速箱类型
 * @property integer $emission 排量
 * @property integer $oil_capacity 邮箱容积
 * @property integer $oil_label 燃油标号
 * @property integer $air_intake_mode 进气形式
 * @property integer $gps GPS配置
 * @property integer $driving_mode 驱动模式
 * @property integer $display_order
 * @property integer $edit_user_id
 * @property integer $limit_flag 限制标记
 * @property integer $poundage 手续费
 * @property integer $basic_insurance 基本保险
 * @property integer $rent_deposit 租车押金
 * @property integer $designated_driving_price 代驾费用
 * @property integer $overtime_price_personal 自驾超时费用标准(元/小时)
 * @property integer $overtime_price_designated 代驾超时费用标准(元/小时)
 * @property integer $overmileage_price_personal 自驾超里程费用标准(元/小时)
 * @property integer $overmileage_price_designated 代驾超里程费用标准(元/小时)
 * @property string $mileage_price 每公里行车费用（用以计算异店还车费用）
 * @property integer $vehicle_configuration_id 车型配置ID
 * @property string $image_0
 * @property string $image_a
 * @property string $image_b
 * @property string $image_c
 * @property string $image_d
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $description
 */
class Pro_vehicle_model extends \common\helpers\ActiveRecordModel
{
    const TYPE_CAR = 0x0001;    // 轿车1
    const TYPE_SUV = 0x0002;    // SUV2
    const TYPE_MPV = 0x0004;    // MPV4
    const TYPE_BUISINESS = 0x0008;  // 商务车8
    const TYPE_PASSENGER = 0x0010;  // 乘用车 16
    const TYPE_ECONOMIC = 0x0020;  // 经济车 32
    const TYPE_COMFORTABLE = 0x0040;  // 舒适车 64
    
    const FLAG_HOT = 0x00000001;
    const FLAG_NEW = 0x00000002;
    const FLAG_SALE = 0x00000004;   // 特价
    const FLAG_COMFORTABLE = 0x00000008;    // 舒适
    const FLAG_ECONOMIC = 0x00000010;   // 经济16
    const FLAG_FASHON = 0x00000020;     // 时尚32
    const FLAG_BUSINESS = 0x00000040;   // 商务64
    const FLAG_SUV = 0x00000080;   // SUV128
    const FLAG_MPV = 0x00000100;   // MPV256
    
    const GEARBOX_MANUAL = 0x00000000;  // 手动
    const GEARBOX_AUTO = 0x10000000;    // 自动
    
    const LIMIT_FLAG_NEW_DRIVER = 0x01000000;       // 新手限制
    const LIMIT_FLAG_DRIVING_LICENSE = 0x02000000;  // 驾照限制
    
    const AIRINTAKE_MODE_NORMAL = 0x01; // 自然吸气
    const AIRINTAKE_MODE_TURBO = 0x02;  // 涡轮增压
    
    const ENGINE_FRONT = 0x10000;    // 前置
    const ENGINE_MIDDLE = 0x20000;   // 中置
    const ENGINE_REAR = 0x40000;     // 后置
    const DRIVER_WHEEL_FRONT = 0x0001;  // 前驱
    const DRIVER_WHEEL_REAR = 0x0002;   // 后驱
    const DRIVER_WHEEL_FULL = 0x0010;   // 四驱
    
    private $_mileagePriceInfo = null;
    
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
     * PHP setter magic method.
     * This method is overridden so that AR attributes can be accessed like properties.
     * @param string $name property name
     * @param mixed $value property value
     */
    public function __set($name, $value)
    {
        if ($name == 'mileage_price') {
            $value = $this->setMileagePriceInfo($value);
        }
        parent::__set($name, $value);
    }
    
    /**
     * @inheritdoc
     */
    public static function populateRecord($record, $row)
    {
        parent::populateRecord($record, $row);
        
        $record->setMileagePriceInfo($record->mileage_price);
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
            'brand' => Yii::t('locale', 'Brand'),
            'model_series' => Yii::t('locale', 'Model series'),
            'vehicle_model' => Yii::t('locale', 'Vehicle model'),
            'vehicle_type' => Yii::t('locale', 'Vehicle type'),
            'vehicle_flag' => Yii::t('locale', 'Vehicle flag'),
            'carriage' => Yii::t('locale', 'Vehicle carriage'),
            'seat' => Yii::t('locale', 'Vehicle seat'),
            'gearbox' => Yii::t('locale', 'Gearbox type'),
            'emission' => Yii::t('locale', 'Emission'),
            'oil_capacity' => Yii::t('locale', 'Oil capacity'),
            'oil_label' => Yii::t('locale', 'Oil label'),
            'air_intake_mode' => Yii::t('locale', 'Air intake mode'),
            'gps' => 'GPS',
           'radar' => '倒车雷达',
            'chair' => '座椅',
            'driving_mode' => Yii::t('locale', 'Driving mode'),
            'display_order' => Yii::t('locale', 'Display order'),
            'edit_user_id' => Yii::t('locale', 'Edit user'),
            'limit_flag' => Yii::t('locale', 'Limit flag'),
            'poundage' => \Yii::t('locale', 'Poundage'),
            'basic_insurance' => \Yii::t('locale', 'Basic insurance'),
            'rent_deposit' => \Yii::t('locale', 'Vehicle deposit'),
            'designated_driving_price' => \Yii::t('locale', '{type} price', ['type'=>\Yii::t('locale', 'Designated driving')]),
            'overtime_price_personal' => \Yii::t('locale', '{type} overtime price', ['type'=>\Yii::t('locale', 'Personal driving')]),
            'overtime_price_designated' => \Yii::t('locale', '{type} overtime price', ['type'=>\Yii::t('locale', 'Designated driving')]),
            'overmileage_price_personal' => \Yii::t('locale', '{type} overmileage price', ['type'=>\Yii::t('locale', 'Personal driving')]),
            'overmileage_price_designated' => \Yii::t('locale', '{type} overmileage price', ['type'=>\Yii::t('locale', 'Designated driving')]),
            'mileage_price' => \Yii::t('carrental', 'Price per mileage'),
            'vehicle_configuration_id' => \Yii::t('locale', 'Vehicle configuration info'),
            'image_0' => Yii::t('locale', 'Image'),
            'image_a' => Yii::t('locale', 'Front view'),
            'image_b' => Yii::t('locale', 'Left view'),
            'image_c' => Yii::t('locale', 'Right view'),
            'image_d' => Yii::t('locale', 'Rear view'),
            'created_at' => Yii::t('locale', 'Create time'),
            'updated_at' => Yii::t('locale', 'Update time'),
            'description' => Yii::t('locale', 'Extra vehicle description'),
            'operation' => Yii::t('locale', 'Operation'),
            'vehicle_model_info' => Yii::t('locale', 'Vehicle model info'),
            'vehicle_price_info' => Yii::t('locale', 'Vehicle price info'),
            'edit_info' => \Yii::t('locale', 'Edit info'),
            'extra_info' => \Yii::t('locale', 'Extra info'),
            
            'price_online' => Yii::t('locale', '{type} price', ['type' => Yii::t('locale', 'Online')]),
            'price_office' => Yii::t('locale', '{type} price', ['type' => Yii::t('locale', 'Office')]),
            'price_3days' => Yii::t('locale', '{type} price', ['type' => Yii::t('locale', '{number}-day', ['number'=>3])]),
            'price_week' => Yii::t('locale', '{type} price', ['type' => Yii::t('locale', 'Week')]),
            'price_15days' => Yii::t('locale', '{type} price', ['type' => Yii::t('locale', '{number}-day', ['number'=>15])]),
            'price_month' => Yii::t('locale', '{type} price', ['type' => Yii::t('locale', 'Month')]),
            
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
            'brand' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.brand_disp; }"),
            'model_series' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.model_series_disp; }"),
            'vehicle_model' => array('width' => 80, 'sortable' => 'true'),
            'vehicle_type' => array('width' => 80, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.vehicle_type_disp; }"),
            'vehicle_flag' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.vehicle_flag_disp; }"),
            'carriage' => array('width' => 60, 'sortable' => 'true'),
            'seat' => array('width' => 60, 'sortable' => 'true'),
            'gearbox' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.gearbox_disp; }"),
            'emission' => array('width' => 80, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.emission_disp; }"),
            'oil_capacity' => array('width' => 70, 'sortable' => 'true'),
            'oil_label' => array('width' => 60),
            'air_intake_mode' => array('width' => 60, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.air_intake_mode_disp; }"),
            'gps' => array('width' => 30),
            'radar' => array('width' => 30),
            'chair' => array('width' => 30),
            'driving_mode' => array('width' => 60,
                'formatter' => "function(value,row){ return row.driving_mode_disp; }"),
            'display_order' => array('width' => 10),
            'poundage' => array('width' => 80, 'sortable' => 'true'),
            'basic_insurance' => array('width' => 80, 'sortable' => 'true'),
            'rent_deposit' => array('width' => 80, 'sortable' => 'true'),
            'designated_driving_price' => array('width' => 80, 'sortable' => 'true'),
            'overtime_price_personal' => array('width' => 80, 'sortable' => 'true'),
            'overtime_price_designated' => array('width' => 80, 'sortable' => 'true'),
            'overmileage_price_personal' => array('width' => 80, 'sortable' => 'true'),
            'overmileage_price_designated' => array('width' => 80, 'sortable' => 'true'),
            'mileage_price' => array('width' => 80, 'sortable' => 'true'),
            'vehicle_configuration_id' => array('width' => 80),
            'edit_user_id' => array('width' => 80, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.edit_user_disp; }"),
            'limit_flag' => array('width' => 80, 
                'formatter' => "function(value,row){\n    var style1 = 'color:green', style2 = 'color:green', prompt1 = $.custom.lan.defaults.vehicle.newDriverAllowed, prompt2 = $.custom.lan.defaults.vehicle.driverLisenceNotLimited;\n".
                    "    if (value & ".self::LIMIT_FLAG_NEW_DRIVER.") { style1 = 'color:red'; prompt1 = $.custom.lan.defaults.vehicle.newDriverLimited;\n    }\n".
                    "    if (value & ".self::LIMIT_FLAG_DRIVING_LICENSE.") { style2 = 'color:red'; prompt2 = $.custom.lan.defaults.vehicle.driverLisenceLimited;\n    }\n".
                    "    return '<span style=\''+style1+'\' title=\''+prompt1+'\'>'+$.custom.lan.defaults.vehicle.newDriver+'</span>|' + '<span style=\''+style2+'\' title=\''+prompt2+'\'>'+$.custom.lan.defaults.vehicle.driverLisence+'</span>'; }"),
            'image_0' => array('width' => 148, /*'rowspan' => 3,*/
                'formatter' => "function(value,row){ return '<img src=\''+value+'\' width=\'140\' alt=\''+row.vehicle_model+'\' title=\''+row.vehicle_model+'\'/>'; }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 60, 
                'buttons' => array(
                ),
            ),
            'vehicle_model_info' => array('width' => 280,
                'formatter' => <<<EOD
function(value,row) {
    var t1 = '<div style=\'width:280px;height:28px;display:block\'><font style=\'font-weight:bold;text-shadow: 2px 2px 2px #B8B8B8\'>'+row.vehicle_model+'</font><font style=\'font-weight:2;color:#ff7f00;padding-left:14px\'>';
    t1 += row.vehicle_type_disp;
    if (row.gearbox_disp != '') {
        t1 += '/' + row.gearbox_disp;
    }
    t1 += '</font></div>';
    var lineDatas = new Array();
    lineDatas.push([{label:'{$this->getAttributeLabel('seat')}', value:row.seat}, {label:'{$this->getAttributeLabel('emission')}', value:row.emission_disp}]);
    lineDatas.push([{label:'{$this->getAttributeLabel('oil_label')}', value:row.oil_label + '#'}, {label:'{$this->getAttributeLabel('oil_capacity')}', value:row.oil_capacity + 'L'}]);
    lineDatas.push([{label:'{$this->getAttributeLabel('driving_mode')}', value:row.driving_mode_disp}, {label:'{$this->getAttributeLabel('air_intake_mode')}', value:row.air_intake_mode_disp}]);
    return t1 + easyuiFuncFormatTableDisplayHtml(lineDatas);
}
EOD
                ),
            'vehicle_price_info' => array('width' => 280,
                'formatter' => <<<EOD
function(value,row) {
    var lineDatas = new Array();
    lineDatas.push([{label:'{$this->getAttributeLabel('price_online')}', value:row.price_online}, {label:'{$this->getAttributeLabel('price_office')}', value:row.price_office}]);
    lineDatas.push([{label:'{$this->getAttributeLabel('price_3days')}', value:row.price_3days}, {label:'{$this->getAttributeLabel('price_15days')}', value:row.price_15days}]);
    lineDatas.push([{label:'{$this->getAttributeLabel('price_week')}', value:row.price_week}, {label:'{$this->getAttributeLabel('price_month')}', value:row.price_month}]);
    return easyuiFuncFormatTableDisplayHtml(lineDatas);
}
EOD
                ),
            'edit_info' => array('width' => 210,
                'formatter' => <<<EOD
function(value,row) {
    var lineDatas = new Array();
    lineDatas.push([{label:'{$this->getAttributeLabel('created_at')}', value:$.custom.utils.humanTime(row.created_at)}]);
    lineDatas.push([{label:'{$this->getAttributeLabel('updated_at')}', value:$.custom.utils.humanTime(row.updated_at)}]);
    lineDatas.push([{label:'{$this->getAttributeLabel('edit_user_id')}', value:row.edit_user_disp}]);
    return easyuiFuncFormatTableDisplayHtml(lineDatas);
}
EOD
            ),
            'extra_info' => array('width' => 260,
                'formatter' => <<<EOD
function(value,row) {
    var lineDatas = new Array();
    lineDatas.push([{label:'{$this->getAttributeLabel('poundage')}', value:row.poundage}]);
    lineDatas.push([{label:'{$this->getAttributeLabel('basic_insurance')}', value:row.basic_insurance}]);
    lineDatas.push([{label:'{$this->getAttributeLabel('rent_deposit')}', value:row.rent_deposit}]);
    lineDatas.push([{label:'{$this->getAttributeLabel('designated_driving_price')}', value:row.designated_driving_price}]);
    return easyuiFuncFormatTableDisplayHtml(lineDatas);
}
EOD
            ),
        );
    }
    
    public static function getTypesArray() {
        return [
            static::TYPE_CAR => \Yii::t('carrental', 'Car'),
            static::TYPE_SUV => '越野车',//'SUV',
            static::TYPE_MPV => '7至15座商务车',//'MPV',
            static::TYPE_BUISINESS => \Yii::t('carrental', 'Buisiness car'),
            static::TYPE_PASSENGER => \Yii::t('carrental', 'Passenger car'),
            //static::TYPE_ECONOMIC => '经济车',
            //static::TYPE_COMFORTABLE => '舒适车',
        ];
    }
    
    public static function getVehicleFlagsArray() {
        return [
            static::FLAG_HOT => \Yii::t('locale', 'Hot'),
            static::FLAG_NEW => \Yii::t('locale', 'New car'),
            static::FLAG_SALE => \Yii::t('locale', 'On sale'),
            static::FLAG_ECONOMIC => \Yii::t('locale', 'Economic'),
            static::FLAG_COMFORTABLE => \Yii::t('locale', 'Comfortable'),
            static::FLAG_FASHON => '手动挡',//\Yii::t('locale', 'Fashon'),
            static::FLAG_BUSINESS => \Yii::t('locale', 'Buisiness'),
            static::FLAG_SUV => '越野车',//'SUV',
            static::FLAG_MPV => '7至15座商务车',//'MPV',
        ];
    }
    /*sjj 和车型图片*/
    public static function getVehicleFlagsImgArray()
    {
        return [
            static::FLAG_HOT => 'img/chexing/jingji.png',
            static::FLAG_NEW => 'img/chexing/jingji.png',
            static::FLAG_SALE => 'img/chexing/jingji.png',
            static::FLAG_ECONOMIC => 'img/chexing/jingji.png',
            static::FLAG_COMFORTABLE => 'img/chexing/shushi.png',
            static::FLAG_FASHON => 'img/chexing/jingji.png',
            static::FLAG_BUSINESS => 'img/chexing/shangwu.png',
            static::FLAG_SUV => 'img/chexing/suv.png',
            // static::FLAG_MPV => 'img/chexing/mpv.png',
            static::FLAG_MPV => 'img/chexing/mpv.png',
        ];
    }
    
    public static function getLimitFlagsArray() {
        return [
            static::LIMIT_FLAG_NEW_DRIVER => \Yii::t('locale', 'New driver limited'),
            static::LIMIT_FLAG_DRIVING_LICENSE => \Yii::t('locale', 'Driving license limited'),
        ];
    }
    
    public static function getAirIntakeModesArray() {
        return [
            static::AIRINTAKE_MODE_NORMAL => \Yii::t('locale', 'Naturally aspirated'),
            static::AIRINTAKE_MODE_TURBO => \Yii::t('locale', 'Turbo-charged'),
        ];
    }
    
    public static function getDrivingModesArray() {
        return [
            static::ENGINE_FRONT  | static::DRIVER_WHEEL_FRONT => \Yii::t('locale', 'Engine front, front-wheel drive'),
            static::ENGINE_FRONT  | static::DRIVER_WHEEL_REAR => \Yii::t('locale', 'Engine front, rear-wheel drive'),
            static::ENGINE_FRONT  | static::DRIVER_WHEEL_FULL => \Yii::t('locale', 'Engine front, full-wheel drive'),
            static::ENGINE_MIDDLE | static::DRIVER_WHEEL_FRONT => \Yii::t('locale', 'Engine middle, front-wheel drive'),
            static::ENGINE_MIDDLE | static::DRIVER_WHEEL_REAR => \Yii::t('locale', 'Engine middle, rear-wheel drive'),
            static::ENGINE_MIDDLE | static::DRIVER_WHEEL_FULL => \Yii::t('locale', 'Engine middle, full-wheel drive'),
            static::ENGINE_REAR   | static::DRIVER_WHEEL_FRONT => \Yii::t('locale', 'Engine rear, front-wheel drive'),
            static::ENGINE_REAR   | static::DRIVER_WHEEL_REAR => \Yii::t('locale', 'Engine rear, rear-wheel drive'),
            static::ENGINE_REAR   | static::DRIVER_WHEEL_FULL => \Yii::t('locale', 'Engine rear, full-wheel drive'),
        ];
    }
    
    public function vehicleFlagArrayData() {
        $arrData = [];
        foreach (static::getVehicleFlagsArray() as $k => $v) {
            if (($this->vehicle_flag & $k) > 0) {
                $arrData[] = $k;
            }
        }
        return $arrData;
    }
    
    public function vehicleFlagDisplayString() {
        $arrData = [];
        foreach (static::getVehicleFlagsArray() as $k => $v) {
            if (($this->vehicle_flag & $k) > 0) {
                $arrData[] = $v;
            }
        }
        return implode(",", $arrData);
    }
    
    public function vehicleLimitFlagArrayData() {
        $arrData = [];
        foreach (static::getLimitFlagsArray() as $k => $v) {
            if (($this->limit_flag & $k) > 0) {
                $arrData[] = $k;
            }
        }
        return $arrData;
    }
    
    public function vehicleEmissionDisplayValue() {
        return round((floatval($this->emission) / 1000), 1);
    }
    
    public function vehicleEmissionHumanText() {
        return $this->vehicleEmissionDisplayValue().(($this->air_intake_mode & static::AIRINTAKE_MODE_TURBO) ? 'T' : 'L');
    }
    
    public function getCarriageDisplayText() {
        $arr = \common\components\VehicleModule::getVehicleCarriagesArray();
        // return (isset($arr[$this->carriage]) ? $arr[$this->carriage] : '');//因为有0不显示的情况，所以不能用isset
        if($this->carriage == 0){
            return '';
        }else{
            return (isset($arr[$this->carriage]) ? $arr[$this->carriage] : '');
        }
    }
    
    public function getGearboxNormalTypeText() {
        if ($this->gearbox & static::GEARBOX_AUTO) {
            return \Yii::t('locale', 'Auto');
        }
        return \Yii::t('locale', 'Manual');
    }
    
    public function getHumanDisplayText($options=[]) {
        if (!$options) {
            $options = [];
        }
        $txt = '';
        if (isset($options['brand'])&&$options['brand']) {
            $txt .= $this->brand;
        }
        if (isset($options['series'])&&$options['series']) {
            $txt .= $this->model_series;
        }
        $txt .= $this->vehicle_model;
        if (isset($options['detail']) && $options['detail']) {
            $txt0 = $this->getCarriageDisplayText();
            if (!empty($txt0)) {
                $txt .= '/'.$txt0;
            }
            $txt .= '/'.$this->vehicleEmissionHumanText().'/'.$this->getGearboxNormalTypeText();
        }
        return $txt;
    }
    
    public function getPropertyHumanDisplayText() {
        $txt = $this->vehicleEmissionHumanText().'|'.$this->getGearboxNormalTypeText();
        $txt0 = $this->getCarriageDisplayText();
        if (!empty($txt0)) {
            $txt .= '|'.$txt0;
        }
        $arrSeats = \common\components\VehicleModule::getVehicleSeatsArray();
        if (isset($arrSeats[$this->seat])) {
            $txt .= '|'.$arrSeats[$this->seat];
        }
        return $txt;
    }
    
    public static function createFindIdNamesArrayConfig($config = array()) {
        return ['class'=> static::className(), 'idField'=>'id', 'textField'=>'vehicle_model'];
    }
    
    public function setMileagePriceInfo($value) {
        if (is_array($value)) {
            $mileagePriceInfoArray = [];
            foreach ($value as $k => $v) {
                $mileagePriceInfoArray[intval($k)] = doubleval($v);
            }
            ksort($mileagePriceInfoArray);
            $arr = [];
            foreach ($mileagePriceInfoArray as $k => $v) {
                $arr[] = "{$k}:{$v}";
            }
            $value = implode(";", $arr);
            $this->_mileagePriceInfo = $mileagePriceInfoArray;
        }
        else if (is_string($value)) {
            $arr = explode(";", $value);
            $mileagePriceInfoArray = [];
            foreach ($arr as $slice) {
                $arr2 = explode(":", trim($slice));
                if (count($arr2) > 1) {
                    $mileagePriceInfoArray[intval($arr2[0])] = doubleval($arr2[1]);
                }
            }
            ksort($mileagePriceInfoArray);
            $arr = [];
            foreach ($mileagePriceInfoArray as $k => $v) {
                $arr[] = "{$k}:{$v}";
            }
            $value = implode(";", $arr);
            $this->_mileagePriceInfo = $mileagePriceInfoArray;
        }
        return $value;
    }

    public function getMileagePriceInfo() {
        if ($this->_mileagePriceInfo === null) {
            $this->setMileagePriceInfo($this->mileage_price);
        }
        return $this->_mileagePriceInfo;
    }
    
    public static function getVehicleModelName($id) {
        if (empty($id)) {
            return '';
        }
        $model = static::findById($id);
        return $model ? $model->vehicle_model : '';
    }
    
    public static function getYesOrNoArray() {
        return [
            '0' => '无',
            '1' => '有',
        ];
    }
    public static function getChair() {
        return [
            '1' => '真皮',
            '2' => '织布',
            '3' => '其他',
        ];
    }
}
