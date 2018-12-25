<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\components;

/**
 *
 * @author kevin
 */
interface MapApiInterface {
    
    /**
     * 
     * @param string $srcCoordinate gps position
     * @param string $dstCoordinate gps position
     * @return array [distance by meter, error message] if the distance less than zero, that means error occures.
     */
    public function getDistance($srcCoordinate, $dstCoordinate);
    
    /**
     * get geography coordinate by address
     * @param string $address formatted address
     */
    public function getCoordinateByAddress($address);
    
}
