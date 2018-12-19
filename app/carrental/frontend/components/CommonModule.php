<?php

namespace frontend\components;

class CommonModule
{
    /*
    *@所有门店的经纬度
    */
    public static function getAllShopInfo(){
        $cdb = \common\models\Pro_office::find();
        $cdb->where(['status' => 0]);
        $cdb->andWhere(['parent_id' => 0]);
        $arrRows = $cdb->all();
        foreach ($arrRows as $key => $value) {
            if($value->geo_x && $value->geo_y){
                $arr[$key]['xy'] = $value->geo_x.','.$value->geo_y;
                $arr[$key]['id'] = $value->id;
                $arr[$key]['fullname'] = $value->fullname;
            }
        }
        return $arr;
    }

    /*
    *@desc 根据地址得到经纬度
    *@param $address
    */
    public static function getXandYByaddress($address)
    {
        $map = \common\components\MapApiGaode::create();
        $arrCoordinateResult = $map->getCoordinateByAddress($address);
        if(!$arrCoordinateResult[0]){
            $arrData['result'] = -1;
            $arrData['desc'] = $arrCoordinateResult[1];
            return $arrData;
        }else{
            
            return $arrCoordinateResult;
        }
    }


    public static function getUserId($seid='')
    {
        $seid_data = \Yii::$app->session->readSession($seid);
        if($seid_data){
            $session_data = self::unserialize_php($seid_data);
            $useid = $session_data['__id'];
            return $useid;
        }else{
            return false;
        }
    }
    public static function unserialize_php($session_data) {
        $return_data = array();
        $offset = 0;
        while ($offset < strlen($session_data)) {
            if (!strstr(substr($session_data, $offset), "|")) {
                throw new Exception("invalid data, remaining: " . substr($session_data, $offset));
            }
            $pos = strpos($session_data, "|", $offset);
            $num = $pos - $offset;
            $varname = substr($session_data, $offset, $num);
            $offset += $num + 1;
            $data = unserialize(substr($session_data, $offset));
            $return_data[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        return $return_data;
    }

    /*小程序统一下单接口调用*/
    public static function getUrlInfo($wxurl,$data){
        $headerArray =array("Content-type:application/json;charset='utf-8'","Accept:application/json");
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $wxurl);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl,CURLOPT_HTTPHEADER,$headerArray);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

}
