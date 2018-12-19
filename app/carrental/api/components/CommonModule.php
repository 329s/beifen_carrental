<?php

namespace frontend\components;

class CommonModule
{
    /*
    *@所有门店的经纬度
    */
    public function getAllShopInfo(){
        $cdb = \common\models\Pro_office::find();
        $cdb->where(['status' => 0]);
        $cdb->andWhere(['parent_id' => 0]);
        $arrRows = $cdb->all();
        foreach ($arrRows as $key => $value) {
            if($value->geo_x && $value->geo_y){
                $arr[$key]['xy'] = $value->geo_x.','.$value->geo_y;
                $arr[$key]['id'] = $value->id;
            }
        }
        return $arr;
    }

    /*
    *@desc 根据地址得到经纬度
    *@param $address
    */
    public function getXandYByaddress($address)
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

    

}
