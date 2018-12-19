<?php
namespace frontend\controllers;

/**
 * Description of TestController
 *
 * @author kevin
 */
class TestController  extends \yii\web\Controller
{
    
    public function beforeAction($action) {
        $preVerify = \common\components\SysmaintenanceService::verifyMaintenanceStatus($action);
        if (!$preVerify[0]) {
            echo json_encode(['result'=> \frontend\components\ApiModule::CODE_ON_MAINTENANCE, 'desc' => $preVerify[1]]);
            return false;
        }
        return true;
    }
    
    public function actionIndex() {
        return $this->renderPartial('index');
    }
    
    public function actionUser_editinfo() {
        return $this->renderPartial('user_editinfo');
    }
    
    public function actionTest_order() {
        return $this->renderPartial('test_order');
    }
    

    public function actionArea($value='')
    {
        $preVerify = \frontend\components\CommonModule::test();
        $time = time();
        foreach ($preVerify as $key => $value) {
            foreach ($value as $k => $v) {
                $cdb = new \common\models\Pro_area();
                $cdb->code = $k;
                $cdb->address = $v;
                $cdb->parentId = $key;
                $cdb->level = '1';
                $cdb->created =$time;
                $cdb->save();
            }
        }
        
    }
}
