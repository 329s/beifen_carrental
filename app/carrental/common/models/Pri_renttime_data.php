<?php

namespace common\models;

class Pri_renttime_data extends \yii\base\Model {
    public $startTime;
    public $endTime;
    public $days;
    public $hours;
    public $minute;
    public $calcStartTime;
    public $calcEndTime;
    
    public static function create($startTime, $endTime,$pay_type = 0) {
		
        $obj = new Pri_renttime_data();
        $obj->startTime = $startTime;
        $obj->endTime = $endTime;
        $obj->calcStartTime = $startTime;
        $obj->calcEndTime = $endTime;
        if($pay_type == 6){
			$rentDuration = $endTime - $startTime;
			$extraDuration = ($rentDuration % 3600);
			$rentDays = floor($rentDuration / 3600);
			$rentHours = 0;
			if ($extraDuration >= 1800) {
				$rentDays++;
			}
		  
			
			$obj->days = $rentDays;
			$obj->hours = $rentHours;
			
		}else{
			$rentDuration = $endTime - $startTime;
			$extraDuration = ($rentDuration % 86400);
			$rentDays = floor($rentDuration / 86400);
			$rentHours = 0;
			if ($extraDuration >= 3600 * \common\components\Consts::ONEDAY_MIN_HOURS) {
				$rentDays++;
			}
			else {
				$rentHours = floor($extraDuration / 3600);
				$rentHours += ($extraDuration % 3600) >= 1800 ? 1 : 0;
			}
			
			$obj->days = $rentDays;
			$obj->hours = $rentHours;
			
			// 如果当天时间超过下午6点，则租车第一天取第二天价格。
			$hour = date('G', $startTime);
			if ($hour >= \common\components\Consts::HOUR_AS_NEXT_DAY) {
				$xtime = strtotime(date('Y-m-d', $startTime + (24-\common\components\Consts::HOUR_AS_NEXT_DAY)*3600).' 00:00:00');
				$delta = $xtime - $startTime;
				$obj->calcStartTime += $delta;
				$obj->calcEndTime += $delta;
			}
		}
        
        
        return $obj;
    }

    /*单程租车时间*/
    public static function createTime($startTime,$endTime){
        $obj = new Pri_renttime_data();
        $obj->startTime     = $startTime;
        $obj->endTime       = $endTime;
        $obj->calcStartTime = $startTime;
        $obj->calcEndTime   = $endTime;
        
        $rentDuration       = $endTime - $startTime;
        $extraDuration      = ($rentDuration % 86400);//小时级别
        $extraDurationMinute= ($extraDuration %  3600);//分钟级别
        $extraDurationSecond= ($extraDurationMinute %  60);//秒级别

        $rentDays           = floor($rentDuration / 86400);
        $rentHours          = 0;

        $rentHours          = floor($rentDuration / 3600);
        if($extraDurationMinute >= 600){
            $rentHours++;
            $rentMinute         = floor($extraDurationMinute / 60);
        }else{
            $rentMinute         = floor($extraDurationMinute / 60);
        }


        $obj->days          = $rentDays;
        $obj->hours         = $rentHours;
        $obj->minute        = $rentMinute;
        return $obj;
    }
}

