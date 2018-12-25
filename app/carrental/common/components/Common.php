<?php
namespace common\components;

class Common
{
	public static function getString() {
        $uniquecode = true;
        while ($uniquecode) {
	        $code                      = mt_rand(10000,99999);
	        $obj                       = \common\models\Pro_invitation_coach::find();
	        $objcoach                  = $obj->where(['code'=>$code])->asArray()->one();
	        if(empty($objcoach)){
	            $uniquecode = false;
	        }
        }
        return $code;
    }
}