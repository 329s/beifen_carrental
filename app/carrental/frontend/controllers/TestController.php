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
    
}
