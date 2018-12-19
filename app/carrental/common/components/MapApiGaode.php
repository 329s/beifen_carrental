<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\components;

/**
 * Description of MapApiGaode
 *
 * @author kevin
 */
class MapApiGaode extends MapApiBase implements MapApiInterface {
    //put your code here
    
    private $_drivingDistanceUrl = 'http://restapi.amap.com/v3/distance';
    
    private $_drivingRouteUrl = 'http://restapi.amap.com/v3/direction/driving';
    
    private $_geoCoordinateUrl = 'http://restapi.amap.com/v3/geocode/geo';
    private $_addressUrl  = 'https://restapi.amap.com/v3/geocode/regeo';
    /*
     * strategy 驾车选择策略
     *  0速度优先（时间）
        1费用优先（不走收费路段的最快道路）
        2距离优先
        3不走快速路
        4躲避拥堵
        5多策略（同时使用速度优先、费用优先、距离优先三个策略计算路径）。
        其中必须说明，就算使用三个策略算路，会根据路况不固定的返回一~三条路径规划信息。
        6不走高速
        7不走高速且避免收费
        8躲避收费和拥堵
        9不走高速且躲避收费和拥堵 
        10多备选，时间最短，距离最短，躲避拥堵（考虑路况） 
        11多备选，时间最短，距离最短 
        12多备选，躲避拥堵（考虑路况） 
        13多备选，不走高速 
        14多备选，费用优先 
        15多备选，躲避拥堵，不走高速（考虑路况） 
        16多备选，费用有限，不走高速 
        17多备选，躲避拥堵，费用优先（考虑路况） 
        18多备选，躲避拥堵，不走高速，费用优先（考虑路况） 
        19多备选，高速优先 
        20多备选，高速优先，躲避拥堵（考虑路况） 
     */
    
    public function init()
    {
        if (empty($this->appKey) && isset(\Yii::$app->params['map.gaode.appkey'])) {
            $this->appKey = \Yii::$app->params['map.gaode.appkey'];
        }
    }
    
    public function getDistance($srcCoordinate, $dstCoordinate) {
        /**
         * type 路径计算的方式和方法
         *  0：直线距离
            1：驾车导航距离（仅支持国内坐标）。
            必须指出，当为1时会考虑路况，故在不同时间请求返回结果可能不同
            2：公交规划距离（仅支持同城坐标）
            3：步行规划距离（仅支持5km之间的距离）
         */
        $params = [
            'key' => $this->appKey,
            'origins' => $srcCoordinate,
            'destination' => $dstCoordinate,
            'type' => 1,
            'output' => 'JSON',
        ];
        
        $distance = 0;
        $desc = 'OK';
        $result = \common\helpers\Utils::queryUrlGet($this->_drivingDistanceUrl, $params);
        if ($result[0] == 200) {
            // success
            $data = json_decode($result[1], true);
            if ($data['status'] == 1) {
                // success
                if (!isset($data['results']) || empty($data['results'])) {
                    $distance = -1;
                    $desc = \Yii::t('locale', 'No data in results');
                }
                else {
                    $distance = $data['results'][0]['distance'];
                }
            }
            else {
                $distance = -1;
                $desc = $data['info'];
                \Yii::error("query gaode url:{$this->_drivingDistanceUrl} failed with error:{$desc}", 'sdk');
            }
        }
        else {
            // failed
            $distance = -1;
            $desc = $result[1];
            \Yii::error("query gaode url:{$this->_drivingDistanceUrl} failed with code:{$result[0]} and error:{$desc}", 'sdk');
        }
        
        return [$distance, $desc];
    }
    
    public function getCoordinateByAddress($address, $city = '') {
        $params = [
            'key' => $this->appKey,
            'address' => $address,
            'batch' => false,
            'output' => 'JSON',
        ];
        if (!empty($city)) {
            $params['city'] = $city;
        }
        
        $coordinate = false;
        $desc = 'OK';
        $result = \common\helpers\Utils::queryUrlGet($this->_geoCoordinateUrl, $params);
        if ($result[0] == 200) {
            // success
            $data = json_decode($result[1], true);
            if ($data['status'] == 1) {
                // success
                if ($data['count'] == 0 || !isset($data['geocodes']) || empty($data['geocodes'])) {
                    $desc = \Yii::t('locale', 'Your address is too vague');
                }
                elseif ($data['count'] > 1) {
                    $desc = \Yii::t('locale', 'Your address is too vague');
                }
                else {
                    $coordinate = $data['geocodes'][0]['location'];
                }
            }
            else {
                $desc = $data['info'];
                \Yii::error("query gaode url:{$this->_geoCoordinateUrl} failed with error:{$desc}", 'sdk');
            }
        }
        else {
            // failed
            $desc = $result[1];
            \Yii::error("query gaode url:{$this->_geoCoordinateUrl} failed with code:{$result[0]} and error:{$desc}", 'sdk');
        }
        
        return [$coordinate, $desc];
    }

    /**
    *根据经纬度得到城市
    */
    public function getAddressByLocation($coordinate='119.653633,29.122915'){
        $params = [
            'key' => $this->appKey,
            'location' => $coordinate,
            // 'radius' => '1000',
            // 'extensions' => 'all',
            'output' => 'JSON',
        ];
        $result = \common\helpers\Utils::queryUrlGet($this->_addressUrl, $params);
        return $result;
    }
}
