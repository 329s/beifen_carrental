<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace backend\components;

/**
 * Description of BaseService
 *
 * @author kevin
 */
class BaseService
{
    
    public static function errorResult($message, $code = Consts::CODE_ERROR)
    {
        return [$code, $message];
    }
    
}
