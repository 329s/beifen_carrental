<?php 
namespace frontend\controllers;
/**
* @author sjj
* @since 2017-9-16
* @desc 活动
*/
class ActivityController extends \yii\web\Controller{
    private $actionKey = \frontend\components\ApiModule::KEY;//ae027603f7aac1a3ae3e83edaf0abf33

	public function init(){
        //去掉Yii2.0 csrf验证
        $this->enableCsrfValidation = false;
    }
    public function behaviors()
    {
        return [
        ];
    }
	public function beforeAction111($action) {
        $preVerify = \common\components\SysmaintenanceService::verifyMaintenanceStatus($action);
        if (!$preVerify[0]) {
            echo json_encode(['result'=> \frontend\components\ApiModule::CODE_ON_MAINTENANCE, 'desc' => $preVerify[1]]);
            return false;
        }
        //return true;
        $arrParams = [];
        $sign = '';
        $params = \Yii::$app->request->get();
        foreach ($params as $k => $v) {
            if ($k == 'sign') {
                $sign = $v;
            } else {
                $arrParams[$k] = $v;
            }
        }

        $arrVerifys = [];
        ksort($arrParams);
        foreach ($arrParams as $k => $v) {
            $k = strval($k);
            $v = strval($v);
            $arrVerifys[] = "{$k}={$v}";
        }
        $arrVerifys[] = $this->actionKey;

        $mySign = md5(implode("|", $arrVerifys));
        if ($mySign == $sign) {
            return true;
        }

        \Yii::error("verify api failed str:".implode("|", $arrVerifys)." my_sign:{$mySign} sign:{$sign}", 'api');
        echo json_encode(['result'=> \frontend\components\ApiModule::CODE_INVALID_PACKAGE, 'desc' => \Yii::t('locale', 'Invalid access!')]);
        return false;

    }

    /**
    *@desc 文字活动
    *@return result 0正常 1错误或者空 desc 
    *@url http://www.ykzc_beifen.com/app/carrental/frontend/web/index.php/activity/allactivity
    */
    public function actionAllactivity($value='')
    {
    	$cdb = \common\models\Pro_activity_info::find();
        $cdb->where(['status'=>0]);//0正常，-10禁用
        $arrRows = $cdb->asArray()->all();
        $arrData['result'] = 0;
        $arrData['desc'] = '';
        if (empty($arrRows)) {
        	$arrData['result'] = 1;
        	$arrData['desc'] = '没有活动';

        }else{
            $cdb2 = \common\models\Pro_office::find();
            $cdb3 = \common\models\Pro_city::find();
            foreach ($arrRows as $key => $value) {
                // 城市
                if($value['city_id'] == '0'){
                    $arrRows[$key]['city'] = "所有城市";
                }else{
                    $office = $cdb3->where(['id'=>$value['city_id']])->one();
                    $arrRows[$key]['city'] = $office['name'];
                }
                // 门店
                if($value['office_id'] == '0'){
                    $arrRows[$key]['office'] = "所有门店";
                }else{
                    $office = $cdb2->where(['id'=>$value['office_id']])->one();
                    $arrRows[$key]['office'] = $office['fullname'];
                }
                // 状态
                if($value['status'] == '0'){
                    $arrRows[$key]['status_name'] = "正常";
                }else{
                    $arrRows[$key]['status_name'] = "禁用";
                }

            }
        	$arrData['desc'] = $arrRows;
        }
        echo json_encode($arrData);
    }

    /**
    *@desc 首页banner图
    *@author sjj
    *@return result 0正常 1错误或者空 desc 
    *@url http://www.ykzc_beifen.com/app/carrental/frontend/web/index.php/activity/activitybanner
    */
    public function actionActivitybanner(){
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];//0,成功
        do{
            $arrImageRows = \common\models\Pro_activity_image::findAll(['type'=>\common\models\Pro_activity_image::TYPE_APP_HOME_IMAGES, 'status'=>\common\models\Pro_activity_image::STATUS_ENABLED]);
            foreach ($arrImageRows as $row) {
                $arrImageList[] = [
                    'image' => \common\helpers\Utils::toFileAbsoluteUrl($row->image),
                    'link' => $row->href,
                    'title' => $row->name,
                    'content' => $row->remark,
                    // 'icon' => $row->icon,
                    'icon' => \common\helpers\Utils::toFileAbsoluteUrl($row->icon),
                ];
            }
            $arrData['image_list'] = $arrImageList;
        }while (0);
        echo json_encode($arrData);
    }
    /**
    *@desc 首页PC banner图
    *@author sjj
    *@return result 0正常 1错误或者空 desc 
    *@url http://www.ykzc_beifen.com/app/carrental/frontend/web/index.php/activity/activitybanner
    */
    public function actionActivity_pc_banner(){
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];//0,成功
        do{
            $arrImageRows = \common\models\Pro_activity_image::findAll(['type'=>\common\models\Pro_activity_image::TYPE_WEB_HOME_IMAGES, 'status'=>\common\models\Pro_activity_image::STATUS_ENABLED]);
            

            $cdb2 = \common\models\Pro_office::find();
            $cdb3 = \common\models\Pro_city::find();
            foreach ($arrImageRows as $row) {
                // echo "<pre>";
                // var_dump($row->city_id);
                // echo "</pre>";die;
                // 城市
                if($row->city_id == '0' || $row->city_id == null){
                    $city_name = "所有城市";
                }else{
                    $office = $cdb3->where(['id'=>$row->city_id])->one();
                    $city_name = $office['name'];
                }
                // 门店
                if($row->office_id == '0' || $row->city_id == null){
                    $office_name = "所有门店";
                }else{
                    $office = $cdb2->where(['id'=>$row->office_id])->one();
                    $office_name = $office['fullname'];
                }
                // 状态
                if($row->status == '0'){
                    $status_name = "正常";
                }else{
                    $status_name = "禁用";
                }
                $arrImageList[] = [
                    'image' => \common\helpers\Utils::toFileAbsoluteUrl($row->image),
                    'link' => $row->href,
                    'title' => $row->name,
                    'content' => $row->remark,
                    'city_name' => $city_name,
                    'office_name' => $office_name,
                    // 'icon' => $row->icon,
                    'icon' => \common\helpers\Utils::toFileAbsoluteUrl($row->icon),
                    'status' => $row->status,
                    'status_name' => $status_name,
                    'allStore' => true,
                ];
            }
            $arrData['image_list'] = $arrImageList;
        }while (0);
        echo json_encode($arrData);
    }

    // 判断app金融版块是否上线显示
    public function actionIs_online(){
        $arrData = [
            'result' => \frontend\components\ApiModule::CODE_SUCCESS,
            'desc' => 'Success',
            'is_online' => '1',//0不上线，1上线
        ];
        echo json_encode($arrData);
    }



    /*PC端意向投资报名*/
    public function actionSign_up() {
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Sign up success')];
        do
        {
            $name   = \Yii::$app->request->post('name');
            $phone  = \Yii::$app->request->post('phone');
            $sex    = \Yii::$app->request->post('sex');
            $way    = \Yii::$app->request->post('way');
            $source = \Yii::$app->request->post('source');
            $city   = \Yii::$app->request->post('city');
            $remark = \Yii::$app->request->post('remark');

            // $name   = '1';
            // $phone  = '18395947725';
            // $sex =1;
            // $way=1;
            // $source=1;
            // $city ='金华';
            // $remark='这是备注信息';

            if (empty($name) || empty($phone) || empty($way) || empty($source)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'Invalid parameter!');
                break;
            }
            if(!preg_match("/^1[34578]{1}\d{9}$/",$phone)){
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'Invalid parameter!');
                break;
            }
            $now     = time();
            \Yii::$app->cache->flush();

            $obj = \common\models\Pro_sign_up::find();
            $objsign = $obj->where(['phone'=>$phone,'name'=>$name])->one();
            if($objsign){
                $arrData['result'] = '1';
                $arrData['desc'] = '你已成功提交信息，请勿重复提交';
            }else{
                $objItem = new \common\models\Pro_sign_up();
                $objItem->name       = $name;
                $objItem->phone      = $phone;
                $objItem->status      = 0;
                $objItem->sex        = $sex;
                $objItem->way        = $way;
                $objItem->source     = $source;
                $objItem->city       = $city;
                $objItem->remark     = $remark;
                $objItem->updated_at = $now;
                $objItem->created_at = $now;

                if(!$objItem->save()){
                    $arrData['result'] = '1';
                    $arrData['desc']   = '提交失败';
                }

            }
                // print_r($objsign);

        }while (0);

        echo json_encode($arrData);
    }

    /*分期商品登记接口*/
    public function actionInstalment(){
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Sign up success')];
        do{
            $name   = \Yii::$app->request->post('name');
            $phone  = \Yii::$app->request->post('phone');
            $product= \Yii::$app->request->post('product');
            $numbers= \Yii::$app->request->post('numbers');
            $price  = \Yii::$app->request->post('price');
            $status = \Yii::$app->request->post('status');
            $type   = \Yii::$app->request->post('type');
            $remark = \Yii::$app->request->post('remark');

            

            if (empty($phone) || empty($product) || empty($price) ||  empty($type)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'Invalid parameter!');
                break;
            }
            if(!preg_match("/^1[34578]{1}\d{9}$/",$phone)){
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'Invalid parameter!');
                break;
            }
            $now     = time();
            // \Yii::$app->cache->flush();

            $obj = \common\models\Pro_instalment::find();
            $objsign = $obj->where(['phone'=>$phone,'name'=>$name,'status'=>0])->one();
            if($objsign){
                $arrData['result'] = '1';
                $arrData['desc'] = '你已成功提交信息，请勿重复提交';
            }else{
                $objItem = new \common\models\Pro_instalment();
                $objItem->name       = $name;
                $objItem->phone      = $phone;
                $objItem->status     = 0;
                $objItem->product    = $product;
                $objItem->numbers    = $numbers;
                $objItem->price      = $price;
                $objItem->type       = $type;
                $objItem->remark     = $remark;
                $objItem->updated_at = $now;
                $objItem->created_at = $now;

                if(!$objItem->save()){
                    $arrData['result'] = '1';
                    $arrData['desc']   = '提交失败';
                }
            }
        }while (0);
        echo json_encode($arrData);
    }

     public function actionTee(){
        return \Yii::$app->renderv();
     }

}