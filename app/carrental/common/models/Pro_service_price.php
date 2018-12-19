<?php

namespace common\models;

use Yii;

/**
 * optional service price model
 *
 * @property integer $id
 * @property integer $office_id
 * @property integer $type
 * @property string $name
 * @property integer $requirement
 * @property integer $default_count
 * @property integer $price
 * @property integer $flag
 * @property integer $app_enablement
 * @property integer $unit_type
 * @property integer $unit_name
 * @property string $tips
 */
class Pro_service_price extends \common\helpers\ActiveRecordModel
{
    const FLAG_DISABLED = 10;
    const FLAG_ENABLED = 0;
    
    const TYPE_VIOLATION_DEPOSIT = 0x0001;  // 违章押金
    const TYPE_NON_DEDUCTIBLE_INSURANCE = 0x0002;  // 不计免赔
    const TYPE_ACCIDENT_INSURANCE = 0x0004;  // 意外保险
    const TYPE_NAVIGATION_PRICE = 0x0008;  // 导航费用
    const TYPE_CHILDREN_SEAT_PRICE = 0x0010;  // 儿童座椅
    const TYPE_FULL_OIL_PRICE = 0x0020;  // 满油服务
    const TYPE_HOME_DELIVERTY_PRICE = 0x0040;  // 送车上门
    const TYPE_HOME_TAKE_CAR_PRICE = 0x0080;  // 上门取车
    const TYPE_DIFF_OFFICE_RETURN_CAR_PRICE = 0x0100;  // 异店还车
    const TYPE_DIFF_CITY_RETURN_CAR_PRICE = 0x0200;  // 异地还车
    const TYPE_WATER_SERVICE = 0x0400;  // 矿泉水
    const TYPE_TISSUE_SERVICE = 0x0800;  // 纸巾
    
    const ID_POUNDAGE = 1;
    const ID_BASIC_INSURANCE = 2;
    const ID_DESIGNATED_DRIVING = 3;
    const ID_DESIGNATED_DRIVING_OVERTIME = 4;
    const ID_OVERTIME = 5;
    
    const UNIT_TYPE_DAILY = 1;  // 元/日
    const UNIT_TYPE_ONCE = 2;   // 元/次
    const UNIT_TYPE_COUNT = 3;  // 元/份
    const UNIT_TYPE_BOX = 4;    // 元/箱
    const UNIT_TYPE_KM = 5;     // 元/公里
    
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
            'office_id' => Yii::t('locale', 'Office'),
            'type' => \Yii::t('locale', 'Type'),
            'name' => Yii::t('locale', 'Name'),
            'requirement' => Yii::t('locale', 'Is requirement'),
            'default_count' => Yii::t('locale', 'Default count'),
            'price' => Yii::t('locale', 'Price'),
            'flag' => Yii::t('locale', 'Flag'),
            'app_enablement' => Yii::t('locale', 'App visible'),
            'unit_type' => Yii::t('locale', 'Unit type'),
            'unit_name' => Yii::t('locale', 'Unit name'),
            'tips' => Yii::t('locale', 'Tips'),
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
            'office_id' => array('width' => 70, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.office_disp; }"),
            'type' => array('width' => 100, 'sortable' => 'true'),
            'name' => array('width' => 100, 'sortable' => 'true'),
            'requirement' => array('width' => 100, 'sortable' => 'true'),
            'default_count' => array('width' => 100, 'sortable' => 'true'),
            'price' => array('width' => 100, 'sortable' => 'true'),
            'flag' => array('width' => 80, 'sortable' => 'true',
                'formatter' => "function(value,row){ if (value == {$flagEnabled}) { return $.custom.lan.defaults.role.enabled; } else { return $.custom.lan.defaults.role.disabled; };}"),
            'app_enablement' => array('width' => 80, 'sortable' => 'true',
                'formatter' => "function(value,row){ if (value > 0) { return $.custom.lan.defaults.role.enabled; } else { return $.custom.lan.defaults.role.disabled; };}"),
            'unit_type' => array('width' => 100, 'sortable' => 'true'),
            'unit_name' => array('width' => 100, 'sortable' => 'true'),
            'tips' => array('width' => 100, 'sortable' => 'true'),
            'operation' => array('width' => 90, 
                'buttons' => array(
                ),
            ),
        );
    }
    
    public function getActualCount($rentTimeData) {
        $days = $rentTimeData->days;
        if (\common\components\Consts::OPTIONAL_SERVICE_OVERTIME_AS_ONE_DAY && $rentTimeData->hours > 0) {
            $days++;
        }
        $monthByNature = false;
        $count = intval($this->default_count);
        if ($this->unit_type == Pro_service_price::UNIT_TYPE_DAILY) {
            $monthDays = intval($this->month_days);
            if (($monthDays > 0) && ($monthDays < 30) && ($days > $monthDays)) {
                if ($monthByNature) {
                    $days = 0;
                    $y = date('Y', $rentTimeData->startTime);
                    $m0 = date('m', $rentTimeData->startTime);
                    $d = date('d', $rentTimeData->startTime);
                    $next = $rentTimeData->startTime + ($monthDays * 86400);
                    $deltaDays = $monthDays;
                    do
                    {
                        $m1 = date('m', $next);
                        if ($m1 != $m0) {
                            $d = date('d', $next);
                            $days += $deltaDays - intval($d);

                            $m0 = $m1;
                            $y0 = date('Y', $next);
                        }
                        else {
                            $days += $deltaDays;

                            if ($m0 >= 12) {
                                $m0 = 1;
                                $y0 = date('Y', $next) + 1;
                            }
                            else {
                                $m0 += 1;
                                $y0 = date('Y', $next);
                            }
                        }

                        $next = strtotime("{$y0}-{$m0}-1");
                        if ($next < $rentTimeData->endTime) {
                            $next += ($monthDays * 86400);
                            if ($next > $rentTimeData->endTime) {
                                $next = $rentTimeData->endTime;
                                $deltaDays = intval(date('d', $next));
                            }
                            else {
                                $deltaDays = $monthDays;
                            }
                        }
                    }while($next < $rentTimeData->endTime);
                }
                else {
                    $d = floor($days / 30);
                    $m = $days % 30;
                    if ($m > $monthDays) {
                        $m = $monthDays;
                    }
                    $days = $d * $monthDays + $m;
                }
            }
            $count = $days;
        }
        elseif ($this->unit_type == Pro_service_price::UNIT_TYPE_KM) {
            // TODO determine kilometre
            $count = 0;
        }
        return $count;
    }
    
    public function getActualUnitPrice() {
        if ($this->unit_type == Pro_service_price::UNIT_TYPE_KM) {
            return 0;
        }
        return $this->price;
    }
    
    public static function findAllServicePrices($authOfficeId, $serviceIds = null) {
        $arrCondition = ['flag'=>static::FLAG_ENABLED];
        $arrSort = [];
        if ($authOfficeId > 0) {
            $arrCondition['office_id'] = [$authOfficeId, 0];
            $arrSort['office_id'] = SORT_DESC;
        }
        else {
            $arrCondition['office_id'] = 0;
        }
        if ($serviceIds !== null) {
            $arrCondition['id'] = $serviceIds;
        }
        
        $objQuery = static::find();
        $objQuery->where($arrCondition);
        if (!empty($arrSort)) {
            $objQuery->orderBy($arrSort);
        }
        
        $arrRows = $objQuery->all();
        $arrData = [];
        foreach ($arrRows as $row) {
            if (isset($arrData[$row->id])) {
                continue;
            }
            $arrData[$row->id] = $row;
        }
        return $arrData;
    }
    
    public static function findByIdAndOffice($id, $officeId) {
        $objQuery = static::find();
        $objQuery->where(['id'=>$id, 'office_id'=>$officeId]);
        return $objQuery->one();
    }
    
}
