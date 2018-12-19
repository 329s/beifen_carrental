<?php

namespace common\components;

class CheckModule
{
	/*计算两个时间戳之差的日时分秒*/
    public static function timediff($start_time,$end_time)
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
    public static function is_discount_period($start_time,$end_time)
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


  /**
    *@example 二维数组排序
    *@param $arr 二维数组
    *@param $keys 二维数组中的某个key值
  */
  public static function array_sort($arr, $keys, $type = 'desc') {
        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v) {
            $keysvalue[$k] = $v[$keys];
        }
        if ($type == 'asc') {
            asort($keysvalue);
        } else {
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
  }


  public static function check_office_order_time($sid,$start_time){
    $time      = time();
    $cdb = \common\models\Pro_office::find(true);
    $cdb->where(['id'=>$sid]);
    $cdb->andWhere(['parent_id'=>30]);//30是萧山机场门店
    $hasRentedBefore = $cdb->one() ? true : false;

    if($hasRentedBefore){
      $diff_time = $start_time - $time;
      if($diff_time > 7200){
        return true;
      }else{
        return false;
      }
    }else{
      return true;
    }
  }





}