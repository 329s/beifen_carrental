<?php

namespace frontend\components;

class ApiModule
{
    const KEY = 'ae027603f7aac1a3ae3e83edaf0abf33';
    
    const CODE_SUCCESS = 0;
    const CODE_ERROR = 1;
    const CODE_API_NOT_COMPLETED = 3;
    const CODE_ON_MAINTENANCE = 4;

    const CODE_NOT_LOGIN = 1004;
    const CODE_NO_USER_IDINEITY_INFO = 1005;
    const CODE_USER_IDENTITY_INFO_ALREADY_EXISTS = 1006;
    const CODE_PHONE_CODE_INVALID = 1007;
    const CODE_USER_SIGNUP_FAILED = 1008;
    const CODE_USER_NOT_EXISTS = 1009;
    const CODE_USER_SET_PASSWORD_FAILED = 1010;
    const CODE_USER_IDENTITY_ALREADY_BINDED = 1011;

    const CODE_RECORD_NOT_EXISTS = 1101;
    const CODE_NO_CONFIGURE_EXISTS = 1102;
    
    const CODE_OFFICE_NOT_EXISTS = 1103;
    const CODE_CAR_NOT_EXISTS = 1104;
    const CODE_PRICE_NOT_CONFIGURED = 1105;

    // about rent
    const CODE_RENT_DAYS_TOO_SHORT = 1151;
    const CODE_CAR_ALREADY_BEEN_RENT = 1152;
    const CODE_ORDER_CANNOT_CANCEL = 1153;
    const CODE_ORDER_ALREADY_PURCHASED = 1154;
    const CODE_ORDER_DESC_SHOULD_NOT_EMPTY = 1155;
    const CODE_NO_CAR_VALID_FOR_RENT = 1156;
    const CODE_NO_SHOP_FOR_CITY = 1157;
    const CODE_USER_ALREADY_RENTED_CAR = 1158;
    const CODE_NO_CAR_PRICES_FOUND = 1159;

    const CODE_INVALID_PARAMETER = 3001;
    
    const CODE_INVALID_PACKAGE = 4001;
    
    /*计算两个时间戳之差的日时分秒*/
    public function timediff($start_time,$end_time)
    {
        //计算天数
        $timediff = $end_time-$start_time;
        $days = intval($timediff/86400);
        //计算小时数
        $remain = $timediff%86400;
        $hours = intval($remain/3600);
        //计算分钟数
        $remain = $remain%3600;
        $mins = intval($remain/60);
        //计算秒数
        $secs = $remain%60;
        $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
        return $res;
    }
    /**
    *@since 2017-10-18
    *@author sjj
    *@desc 3天打包价判断时间是否在周日16:00-周四16:00期间
    */
    public function is_discount_period($start_time,$end_time)
    {
        $sw = date('w',$start_time);
        $ew = date('w',$end_time);

        switch ($sw)
        {
        case 0:
          if(date('H',$start_time) >= 16){//开始时间在打包期间
            $x = '0';
          }else{
            $x = '1';
          }
          break;
        case 1:
          $x = '0';
          break;
        case 2:
          $x = '0';
          break;
        case 3:
          $x = '0';
          break;
        case 4:
          if(date('H',$start_time) < 16){//开始时间在打包期间
            $x = '0';
          }else{
            $x = '1';
          }
          break;
        case 5:
          $x = '1';
          break;
        case 6:
          $x = '1';
          break;
        }

        // 结束时间判断
        switch ($ew)
        {
        case 0:
          if(date('H',$end_time) >= 16){//结束时间在打包期间
            $y = '0';
          }else{
            $y = '1';
          }
          break;
        case 1:
          $y = '0';
          break;
        case 2:
          $y = '0';
          break;
        case 3:
          $y = '0';
          break;
        case 4:
          if(date('H',$end_time) < 16){//结束时间在打包期间
            $y = '0';
          }else{
            $y = '1';
          }
          break;
        case 5:
          $y = '1';
          break;
        case 6:
          $y = '1';
          break;
        }

        if($x == '0' && $y == '0'){
            $res = '0';
        }else{
            $res = '1';
        }
        return $res;
    }



    /*计算送车上门几公里费用
    * 5公里内免费取还车
    * 5-10公里30
    * 10-20公里100
    * 大于20不送
    */
    public function getPriceByDistance($distance){
          if($distance<=5){
            $price = 0;
          }elseif ($distance>40) {
            $price = 10000;
          }elseif ($distance > 5 && $distance <= 10) {
            $price = 30;
          }elseif ($distance >10 && $distance <= 15) {
            $price = 50;
          }elseif ($distance >15 && $distance <= 40) {
            $price = 100;
          }
          return $price;
    }



}
