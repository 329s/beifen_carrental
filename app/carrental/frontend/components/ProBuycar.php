<?php
namespace frontend\components;

class ProBuycar
{
	/*
    *@params    "id":           id
    *@params    "name":         姓名
    *@params    "mobile":       手机号码
    *@params    "sex":          性别
    *@params    "car_models":   意向车型
    *@params    "buy_city":     购买城市
    *@params    "add_time",     提交时间
    *@params    "status":       状态
    */
	public static function processOrder($params){
       
		$arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
		do{
            $objFormData = new \common\models\Pro_buy_car();
            $objFormData->name =         empty($params['name']) ? '' : htmlspecialchars(trim($params['name']));
            $objFormData->mobile =       empty($params['mobile']) ? '' : htmlspecialchars(trim($params['mobile']));
            $objFormData->sex =          empty($params['sex']) ? 0 : intval($params['sex']);
            $objFormData->car_models =   empty($params['car_models']) ? '' : htmlspecialchars(trim($params['car_models']));
            $objFormData->buy_city =     empty($params['buy_city']) ? '' : htmlspecialchars(trim($params['buy_city']));
            $objFormData->add_time =     time();
            $objFormData->status =       0;
            if(empty($objFormData->name) || empty($objFormData->mobile) || empty($objFormData->car_models) || empty($objFormData->buy_city)){
                $arrData['result'] = \frontend\components\ApiModule::CODE_ERROR;
                $arrData['desc'] = \Yii::t('locale', 'Please fill in the complete information and submit it again!');
                break;
            }
            $objIsName = \common\models\Pro_buy_car::findOne(['name' => $objFormData->name,'mobile' => $objFormData->mobile,'car_models' => $objFormData->car_models]);
            if($objIsName){
                $arrData['result'] = \frontend\components\ApiModule::CODE_ERROR;
                $arrData['desc'] = \Yii::t('locale', 'Buy car name Already existed!');
                break;
            }
            if (!$objFormData->save()) {
               $arrData['result'] = \frontend\components\ApiModule::CODE_ERROR;
               $arrData['desc'] = \Yii::t('locale', 'Create buy car failed!');
               break;
           }
           
		}while (0);
		return $arrData;
	}

}
