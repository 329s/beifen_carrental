<?php

namespace common\models;

use Yii;

/**
 * Vehicle fee plan model
 *
 * @property integer $id
 * @property string $name
 * @property integer $source
 * @property integer $vehicle_model_id
 * @property integer $office_id
 * @property integer $status
 * @property integer $price_default
 * @property integer $price_3days
 * @property integer $price_week
 * @property integer $price_15days
 * @property integer $price_month
 * @property integer $special_festivals_price_month
 * @property integer $price_sunday
 * @property integer $price_monday
 * @property integer $price_tuesday
 * @property integer $price_wednesday
 * @property integer $price_thirsday
 * @property integer $price_friday
 * @property integer $price_saturday
 * @property string $festival_prices
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */ 
class Pro_vehicle_fee_plan extends \common\helpers\ActiveRecordModel
{
    const FESTIVAL_FIELD_PREFIX = 'festival_id_';
    
    const STATUS_NORMAL = 0;
    const STATUS_DISABLED = -1;
    
    const DEFAULT_SOURCE = 0;
    
    public $festivalPricesArray = [];
    public $festivalNamesArray = [];
    public $festivalFieldsArray = [];
    
    private static $_daysFieldsArray = [
        3 => 'price_3days',
        7 => 'price_week',
        15 => 'price_15days',
        30 => 'price_month',
    ];
    
    private static $_weekdayFieldsArray = [
        0 => 'price_sunday',
        1 => 'price_monday',
        2 => 'price_tuesday',
        3 => 'price_wednesday',
        4 => 'price_thirsday',
        5 => 'price_friday',
        6 => 'price_saturday',
    ];
	
	//单程往返超时30分钟公里数
    // flag 8:舒适型 16：经济型 64 商务型
    private static $_oneWayOverTimeMinutePrice =[
        8  => '50',
        16 => '50',
        64 => '50'];

    // 单程往返超时30分钟以上一小时以内公里数
    private static $_oneWayOverTimeHoursPrice =[
        8  => '100',
        16 => '100',
        64 => '100',];

    // 单程往返每公里收取油耗费用
    private static $_oneWayOilByKm =[
        8  => '1',
        16 => '0.8',
        64 => '1.2',];
	
	// 单程往返押金费用
    public static $_oneWayDeposit =[
        8  => '800',
        16 => '500',
        64 => '1000',];

    public static function GetoneWayDeposit() {
        return [
            8  => '800',
            16 => '500',
            64 => '1000',
        ];
    }

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
     * PHP setter magic method.
     * This method is overridden so that AR attributes can be accessed like properties.
     * @param string $name property name
     * @param mixed $value property value
     */
    public function __set($name, $value)
    {
        if ($name == 'festival_prices') {
            $value = $this->setFestivalPricesValue($value);
        }
        parent::__set($name, $value);
    }
    
    /**
     * @inheritdoc
     */
    public static function populateRecord($record, $row)
    {
        parent::populateRecord($record, $row);
        
        $record->setFestivalPricesValue($record->festival_prices);
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
            'source' => \Yii::t('carrental', 'Apply order source'),
            'vehicle_model_id' => Yii::t('locale', 'Vehicle model'),
            'office_id' => Yii::t('locale', 'Office'),
            'status' => \Yii::t('locale', 'Status'),
            'price_default' => Yii::t('locale', '{type} price', ['type' => Yii::t('locale', 'Per day')]),
            'price_3days' => Yii::t('locale', '{type} price', ['type' => Yii::t('locale', '{number}-day', ['number'=>3])]),
            'price_week' => Yii::t('locale', '{type} price', ['type' => Yii::t('locale', 'Week')]),
            'price_15days' => Yii::t('locale', 'Is one way rent'),
            'price_month' => Yii::t('locale', '{type} price', ['type' => Yii::t('locale', 'Month')]),
            'special_festivals_price_month' => Yii::t('locale', '{type} price', ['type' => Yii::t('locale', 'Spring festival Month')]),
            'price_sunday' => Yii::t('locale', '{type} price', ['type' => Yii::t('locale', 'Sunday')]),
            'price_monday' => Yii::t('locale', '{type} price', ['type' => Yii::t('locale', 'Monday')]),
            'price_tuesday' => Yii::t('locale', '{type} price', ['type' => Yii::t('locale', 'Tuesday')]),
            'price_wednesday' => Yii::t('locale', '{type} price', ['type' => Yii::t('locale', 'Wednesday')]),
            'price_thirsday' => Yii::t('locale', '{type} price', ['type' => Yii::t('locale', 'Thirsday')]),
            'price_friday' => Yii::t('locale', '{type} price', ['type' => Yii::t('locale', 'Friday')]),
            'price_saturday' => Yii::t('locale', '{type} price', ['type' => Yii::t('locale', 'Saturday')]),
            'festival_prices' => Yii::t('locale', '{type} price', ['type' => Yii::t('locale', 'Festival')]),
            'festival_display' => Yii::t('locale', '{type} price', ['type' => Yii::t('locale', 'Festival')]),
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
        $arr = array(
            'id' => array('data-options' => array('checkbox'=>'true'), 'key' => true),
            'name' => array('width' => 80, 'sortable' => 'true'),
            'source' => array('width' => 80, 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getSourceTypesArray())." }"),
            'vehicle_model_id' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.vehicle_model_disp; }"),
            'office_id' => array('width' => 160, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.office_disp; }"),
            'status' => array('width' => 60, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getStatusArray())." }"),
            'price_default' => array('width' => 70, 'sortable' => 'true'),
            'price_3days' => array('width' => 70),
            'price_week' => array('width' => 70, 'sortable' => 'true'),
            'price_15days' => array('width' => 70, 'sortable' => 'true'),
            'price_month' => array('width' => 70),
            'special_festivals_price_month' => array('width' => 70),
            'price_sunday' => array('width' => 70),
            'price_monday' => array('width' => 70),
            'price_tuesday' => array('width' => 70),
            'price_wednesday' => array('width' => 70),
            'price_thirsday' => array('width' => 70),
            'price_friday' => array('width' => 70),
            'price_saturday' => array('width' => 70),
            'edit_user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.edit_user_disp; }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 280, 
                'buttons' => array(
                    \Yii::$app->user->can('vehicle/editfeeplan') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['vehicle/editfeeplan', 'id'=>'']), 'name' => Yii::t('locale', 'Edit'), 'title' => Yii::t('locale', 'Edit'), 'paramField' => 'id', 'icon' => 'icon-edit', 'showText'=>true) : null,
                    \Yii::$app->user->can('vehicle/addfeeplan') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['vehicle/addfeeplan', 'add_office_by_fee_id'=>'']), 'name' => Yii::t('carrental', 'Add specified office fee plan'), 'title' => Yii::t('carrental', 'Add specified office fee plan'), 'paramField' => 'id', 'icon' => 'icon-add', 'showText'=>true) : null,
                    \Yii::$app->user->can('vehicle/deletefeeplan') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['vehicle/deletefeeplan', 'id'=>'']), 'name' => Yii::t('locale', 'Delete'), 'title' => Yii::t('locale', 'Are you sure to delete these records?'), 'paramField' => 'id', 'icon' => 'icon-delete', 'showText'=>true) : null,
                ),
            ),
        );
        
        foreach ($this->festivalFieldsArray as $field => $id) {
            $arr[$field] = array('width' => 70);
        }
        
        return $arr;
    }
    
    /**
     * Returns the text label for the specified attribute.
     * @param string $attribute the attribute name
     * @return string the attribute label
     * @see generateAttributeLabel()
     * @see attributeLabels()
     */
    public function getAttributeLabel($attribute)
    {
        if (isset($this->festivalFieldsArray[$attribute])) {
            return \Yii::t('locale', '{type} price', ['type' => $this->festivalNamesArray[$this->festivalFieldsArray[$attribute]]]);
        }
        return parent::getAttributeLabel($attribute);
    }

    public static function getStatusArray() {
        return [
            static::STATUS_NORMAL => \Yii::t('locale', 'Normal'), 
            static::STATUS_DISABLED => \Yii::t('locale', 'Disabled'), 
        ];
    }

    public static function getSourceTypesArray() {
        return [
            static::DEFAULT_SOURCE => \Yii::t('locale', 'Default'),
            \common\models\Pro_vehicle_order::ORDER_SOURCE_APP => \Yii::t('locale', '{name} order', ['name' => \Yii::t('locale', 'Cellphone')]),
            \common\models\Pro_vehicle_order::ORDER_SOURCE_WEBSITE => \Yii::t('locale', '{name} order', ['name' => \Yii::t('locale', 'Website')]),
            \common\models\Pro_vehicle_order::ORDER_SOURCE_OFFICE => \Yii::t('locale', '{name} order', ['name' => \Yii::t('locale', 'Office')]),
            \common\models\Pro_vehicle_order::ORDER_SOURCE_TELEPHONE => \Yii::t('locale', '{name} order', ['name' => \Yii::t('locale', 'Telephone')]),
            \common\models\Pro_vehicle_order::ORDER_SOURCE_PROXY => \Yii::t('locale', '{name} order', ['name' => \Yii::t('locale', 'Proxy')]),
            \common\models\Pro_vehicle_order::ORDER_SOURCE_OTHER => \Yii::t('locale', '{name} order', ['name' => \Yii::t('locale', 'Other')]),
        ];
    }
    
    public function setFestivalPricesValue($value) {
        if (is_array($value)) {
            $festivalPricesArray = [];
            foreach ($value as $k => $v) {
                $festivalPricesArray[intval($k)] = doubleval($v);
            }
            ksort($festivalPricesArray);
            $arr = [];
            foreach ($festivalPricesArray as $k => $v) {
                $arr[] = "{$k}:{$v}";
            }
            $value = implode(";", $arr);
            $this->festivalPricesArray = $festivalPricesArray;
        }
        else if (is_string($value)) {
            $arr = explode(";", $value);
            $festivalPricesArray = [];
            foreach ($arr as $slice) {
                $arr2 = explode(":", trim($slice));
                if (count($arr2) > 1) {
                    $festivalPricesArray[intval($arr2[0])] = doubleval($arr2[1]);
                }
            }
            ksort($festivalPricesArray);
            $arr = [];
            foreach ($festivalPricesArray as $k => $v) {
                $arr[] = "{$k}:{$v}";
            }
            $value = implode(";", $arr);
            $this->festivalPricesArray = $festivalPricesArray;
        }
        return $value;
    }
    
    /**
     * 
     * @param integer $start
     * @param integer $end
     * @param integer $priceType
     * @param string $birthday mm-dd
     * @return array example ['price'=>0, 'details'=>[], 'hasFestivalPrice'=>false, 'festival'=>null];
     */
    public function getPriceForDuration($start, $end, $priceType = 0, $birthday = null, $userisnew=1) {
		$daysData = \common\models\Pri_renttime_data::create($start, $end,$priceType);
		
        // echo "<pre>";
        // print_r($daysData);
        // echo "</pre>";die;
		
        $arrResult = ['price'=>0, 'details'=>[], 'hasFestivalPrice'=>false, 'festival'=>null];
        $arrDetails = &$arrResult['details'];
        $hasFestival = $this->hasFestivalPrice($daysData);
        // sjj
        $getRentFestivalsArray = $this->getRentFestivalsArray($daysData);//在租期内特殊节日数组

        if ($hasFestival && $daysData->days < \common\components\Consts::AUTO_MONTH_PRICE_DAYS) {
            $arrResult['hasFestivalPrice'] = true;
            $arrResult['festival'] = $hasFestival['festival'];
        }
        else {
            // if (Pro_vehicle_order::isMultidaysPackagePriceType($priceType) || $daysData->days >= \common\components\Consts::AUTO_MONTH_PRICE_DAYS) {
				
            if (Pro_vehicle_order::isMultidaysPackagePriceType($priceType)) {
                if($priceType==2){//三天打包价判断
                    // 判断是否三天打包价
                    /*得到开始时间和结束时间之差的日时分秒*/
                    $timediff = \common\components\CheckModule::timediff($start,$end);
                    if($timediff['day'] == 3 && $daysData->days == 3){
                        $res = \common\components\CheckModule::is_discount_period($start,$end);
                        if($res==1){
                            //return self::errorResult('周五和周末'.\Yii::t('locale', 'Not in'), 300);
                        }else{//如果三天打包价时间在周日16点之周四16点之间，则继续使用打包价
                            $price = $this->getDaysPrice($daysData->days);
                            if (floatval($price)) {
                                for($i = 0; $i < $daysData->days; $i++) {
                                    $arrDetails[] = $price;
                                }
                                $arrResult['price'] = $price * $daysData->days;
                                return $arrResult;
                            }
                        }
                    }else{//如果续租的话也可以用打包价
                        $price = $this->getDaysPrice($daysData->days);
                        if (floatval($price)) {
                            for($i = 0; $i < $daysData->days; $i++) {
                                $arrDetails[] = $price;
                            }
                            $arrResult['price'] = $price * $daysData->days;
                            return $arrResult;
                        }
                    }
                }else if($priceType == 6){//分时租赁
					// print_r($priceType);exit;
					$price = $this->getDaysPrice(15);
					
					if (floatval($price)) {
						for($i = 0; $i < $daysData->days; $i++) {
							$arrDetails[] = $price;
						}
						$arrResult['price'] = $price * $daysData->days;
						return $arrResult;
					}
					
				}else{
					
                    // 春节期间月租价变动
                    if($hasFestival['festival']['id'] == 2){
                        $is_spring = 1;
                    }else{
                        $is_spring = 0;
                    }
                    foreach ($getRentFestivalsArray as $key => $value) {
                        if($value['id'] == 2){
                            $is_spring = 1;
                        }
                    }
                    // 春节期间月租价格变动

                    $price = $this->getDaysPrice($daysData->days,$is_spring);
                    // $price = 2;
                    if (floatval($price)) {
                        for($i = 0; $i < $daysData->days; $i++) {
                            $arrDetails[] = $price;
                        }
                        $arrResult['price'] = $price * $daysData->days;
                        return $arrResult;
                    }
                }
                
            }
        }
        
        $tim = $daysData->calcStartTime;
		
        $leftDays = $daysData->days;
        $price = 0;
        $checkBirthDays = null;
        $birthTimeInfo = null;
        if ($birthday) {
            $year1 = intval(date('Y', $daysData->calcStartTime));
            $year2 = intval(date('Y', $daysData->calcEndTime));
            $checkBirthDays = [];
            $__i = 0;
            for($y = $year1; $y <= $year2; $y++) {
                $birthtim = strtotime($year1.'-'.$birthday);
                if ($birthtim + 86399 >= $daysData->calcStartTime && $birthtim <= $daysData->calcEndTime) {
                    $birthinfo = [$birthtim, $birthtim+86399, ++$__i];
                    $checkBirthDays[] = $birthinfo;
                    if ($birthTimeInfo === null) {
                        $birthTimeInfo = $birthinfo;
                    }
                }
            }
        }
        
        // $arrResult['info4'] = $tim.'---'.$leftDays;
        while ($tim < $daysData->calcEndTime && $leftDays) {
            if ($birthTimeInfo && $tim > $birthTimeInfo[1]) {
                if (isset($checkBirthDays[$birthTimeInfo[2]])) {
                    $birthTimeInfo = $checkBirthDays[$birthTimeInfo[2]];
                }
                else {
                    $birthTimeInfo = null;
                }
            }
            if ($birthTimeInfo && ($tim >= $birthTimeInfo[0] && $tim <= $birthTimeInfo[1]) && $userisnew != 1) {
                
                $_p = $this->getDayPriceByTimeApp($tim,$priceType);
                /*if($_p < 160){
                    $_p = \common\components\OptionsModule::getBirthdayPrice($this->office_id);
                }*/
            }
            else {
                // $arrResult['info5'] = $daysData->calcEndTime;
                // $_p = $this->getDayPriceByTime($tim);
                $_p = $this->getDayPriceByTimeApp($tim,$priceType);
                // $arrResult['info6'] = $_p;
            }
            
            $arrDetails[] = $_p;
            
            $price += $_p;
            $tim += 86400;
            $leftDays--;
        }
        $arrResult['price'] = $price;
		
        return $arrResult;
    }
    
    /*单程租车价格计算,不含油费*/
    /**
     * @param integer $start
     * @param integer $end
     * @param integer $flag =64 经济型
     * @return array example ['price'=>0, 'details'=>[], 'hasFestivalPrice'=>false, 'festival'=>null];
     */
    public function getPriceForHours($start, $end, $address_km,$flag = 16){
        $daysData    = \common\models\Pri_renttime_data::createTime($start, $end);
        $arrResult   = ['price'=>0, 'details'=>[], 'hasFestivalPrice'=>false, 'festival'=>null];
        $arrDetails  = &$arrResult['details'];
        $hasFestival = $this->hasFestivalPrice($daysData);
        if ($hasFestival) {
            $arrResult['hasFestivalPrice'] = true;
            $arrResult['festival'] = $hasFestival['festival'];
        }else{

        }

        $tim            = $daysData->calcStartTime;
        $leftHours      = $daysData->hours;
        $price          = 0;
        $checkBirthDays = null;
        $birthTimeInfo  = null;

        /*if($daysData->minute >= 10){
            $leftHours--;
        }*/
        while ($tim < $daysData->calcEndTime && $leftHours) {
            $_p = $this->getHoursPriceByTimeApp($tim,$flag);
            $arrDetails[] = $_p;
            $price += $_p;
            $tim   += 3600;
            $leftHours--;
        }
        // 单程租车每公里油耗
        $oneWayOilByKm    = self::$_oneWayOilByKm[$flag];

        $price_address_km = $address_km * $oneWayOilByKm;
        // $price +=$price_address_km;
        /*$oneWayOverTimeMinutePrice = isset(self::$_oneWayOverTimeMinutePrice[$flag])?self::$_oneWayOverTimeMinutePrice[$flag]:0;
        $oneWayOverTimeHoursPrice  = isset(self::$_oneWayOverTimeHoursPrice[$flag])?self::$_oneWayOverTimeHoursPrice[$flag]:0;
        if($daysData->minute < \common\components\Consts::ONEHOURS_MIN_MINUTE){
            $arrDetails[] = 0;
            $price += 0;
        }elseif ($daysData->minute > \common\components\Consts::ONEHOURS_MAX_MINUTE) {
            $arrDetails[] = $oneWayOverTimeHoursPrice;
            $price += $oneWayOverTimeHoursPrice;
        }else{
            $arrDetails[] = $oneWayOverTimeHoursPrice;
            $price += $oneWayOverTimeHoursPrice;
        }*/

        $arrResult['daysData']         = $daysData;

        $arrResult['price']            = $price;
        $arrResult['price_address_km'] = $price_address_km;
        return $arrResult;
    }


    public function getDayPriceByTime($now) {
        if ($now == 0) {
            $now = time();
        }
        $arrPriceInfo = $this->getFestivalPriceInfo($now);
        if ($arrPriceInfo['price']) {
            return $arrPriceInfo['price'];
        }
        
        $price = $this->getWeekdayPrice($now);
        if ($price) {
            return $price;
        }
        
        return $this->price_default;
    }
    //sjj
    public function getDayPriceByTimeApp($now,$priceType) {
        if ($now == 0) {
            $now = time();
        }
		if($priceType == 6){
			$price = $this->getDaysPrice(15);
			 return $price;
		}
        $arrPriceInfo = $this->getFestivalPriceInfo($now);
        if ($arrPriceInfo['price']) {
            return $arrPriceInfo['price'];
        }
        
        $price = $this->getWeekdayPrice($now);
        if ($price) {
            return $price;
        }
        //if($priceType==1){
          //  return $this->price_office;
       // }
        
        return $this->price_default;
    }

    /*单程租车每小时价格*/
    public function getHoursPriceByTimeApp($now,$flag){
        if ($now == 0) {
            $now = time();
        }

        /*$arrPriceInfo = $this->getFestivalPriceInfo($now);
        if ($arrPriceInfo['price']) {
            return $arrPriceInfo['price'];
        }*/
        /*$price = $this->getWeekdayPrice($now);
        if ($price) {
            return $price;
        }*/
        return $this->price_15days;
    }
    //sjj
    
    public function hasFestivalPrice($daysData) {
        $tim = $daysData->calcStartTime;
        $leftDays = $daysData->days;
        while ($tim < $daysData->calcEndTime && $leftDays) {
            $_p = $this->getFestivalPriceInfo($tim);
            if ($_p['price']) {
                return $_p;
            }
            $tim += 86400;
            $leftDays--;
        }
        return false;
    }
    
    public function getDaysPrice($days,$is_spring = 0) {
        if (isset(self::$_daysFieldsArray[$days])) {
            $field = self::$_daysFieldsArray[$days];
            $price = $this->$field;
            // sjj 添加春节期间的月租价格判断
            if($is_spring){
                return floatval($this->special_festivals_price_month);
            }
            return floatval($price);
        }
        elseif ($days >= \common\components\Consts::AUTO_MONTH_PRICE_DAYS) {
            // sjj 添加春节期间的月租价格判断
            if($is_spring){
                return floatval($this->special_festivals_price_month);
            }
            return floatval($this->price_month);
        }
        return 0;
    }
	
	public function getHourPrice($days,$is_spring = 0) {
		
        if (isset(self::$_daysFieldsArray[$days])) {
            $field = self::$_daysFieldsArray[$days];
            $price = $this->$field;
            // sjj 添加春节期间的月租价格判断
            if($is_spring){
                return floatval($this->special_festivals_price_month);
            }
            return floatval($price);
        }
        elseif ($days >= \common\components\Consts::AUTO_MONTH_PRICE_DAYS) {
            // sjj 添加春节期间的月租价格判断
            if($is_spring){
                return floatval($this->special_festivals_price_month);
            }
            return floatval($this->price_month);
        }
        return 0;
    }
    
    public function getWeekdayPrice($now) {
        $w = date('w', $now);
        if (isset(self::$_weekdayFieldsArray[$w])) {
            $field = self::$_weekdayFieldsArray[$w];
            return $this->$field;
        }
        return 0;
    }
	
	public static function getOneWayTimePrice($flag) {
        $oneWayArr = array();
        if (isset(self::$_oneWayOverTimeMinutePrice[$flag])) {
            $oneWayArr['oneWayOverTimeMinutePrice'] = self::$_oneWayOverTimeMinutePrice[$flag];
        }
		if (isset(self::$_oneWayOverTimeHoursPrice[$flag])) {
            $oneWayArr['oneWayOverTimeHoursPrice'] = self::$_oneWayOverTimeHoursPrice[$flag];
        }
		if (isset(self::$_oneWayOilByKm[$flag])) {
            $oneWayArr['oneWayOilByKm'] = self::$_oneWayOilByKm[$flag];
        }
		if (isset(self::$_oneWayDeposit[$flag])) {
            $oneWayArr['oneWayDeposit'] = self::$_oneWayDeposit[$flag];
        }
        return $oneWayArr;
    }
    
    public function getFestivalPriceInfo($now) {
        $arrPriceInfo = ['price'=>0, 'festival'=>null];
		
        $arrFestivals = \common\components\OptionsModule::getFestivalsArray();
		
        foreach ($this->festivalPricesArray as $id => $value) {
            if (isset($arrFestivals[$id])) {
                $festival = $arrFestivals[$id];
                
                if ($festival->isTimeMatch($now)) {
                    $arrPriceInfo['price'] = $value;
                    $arrPriceInfo['festival'] = $festival;
                    break;
                }
            }
        }
        return $arrPriceInfo;
    }
    /*查一段时间内的节假日*/
    public function getRentFestivalsArray($daysData){
        $tim = $daysData->calcStartTime;
        $leftDays = $daysData->days;
        $arrF = array();
        $arrFestivals = \common\components\OptionsModule::getFestivalsArray();
        foreach ($this->festivalPricesArray as $key => $value) {
            if(isset($arrFestivals[$key])){
                $festival = $arrFestivals[$key];
                if($festival->isContainsTime($daysData->startTime,$daysData->endTime)){
                    $arrF[] = $festival;
                }
            }
        }
        return $arrF;
    }
    
    public function setFestivalNames($arrFestivals) {
        $this->festivalNamesArray = [];
        $this->festivalFieldsArray = [];
        foreach ($arrFestivals as $id => $festival) {
            $this->festivalNamesArray[$festival->id] = $festival->name;
            $this->festivalFieldsArray[self::FESTIVAL_FIELD_PREFIX.$festival->id] = $festival->id;
        }
    }
    
    /**
     * 
     * @param integer $orderSource
     * @param integer $vehicleModelId
     * @param integer $authOfficeId
     * @return \common\models\Pro_vehicle_fee_plan
     */
    public static function findByOrderSourceAndVehicleModel($orderSource = 0, $vehicleModelId = 0, $authOfficeId = 0) {
        $arrCondition = ['vehicle_model_id' => $vehicleModelId];
        $arrSort = [];
        if ($orderSource) {
            $arrCondition['source'] = [$orderSource, 0];
            $arrSort['source'] = SORT_DESC;
        }
        else {
            $arrCondition['source'] = 0;
        }
        if ($authOfficeId > 0) {
            $arrCondition['office_id'] = [$authOfficeId, 0];
            $arrSort['office_id'] = SORT_DESC;
        }
        else {
            $arrCondition['office_id'] = 0;
        }
        
        $objQuery = static::find();
        $objQuery->where($arrCondition);
        if (!empty($arrSort)) {
            $objQuery->orderBy($arrSort);
        }
        
        $objFee = $objQuery->one();
        return $objFee;
    }
    
    public static function getDefaultEditFeePlanSources() {
        return [
            0,
            Pro_vehicle_order::ORDER_SOURCE_APP,
        ];
    }
    
}
