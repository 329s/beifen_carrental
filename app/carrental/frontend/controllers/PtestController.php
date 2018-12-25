<?php
namespace frontend\controllers;

use yii\filters\VerbFilter;
use yii\filters\AccessControl;
/**
 * Description of TestController
 *
 * @author sjj
 */
class PtestController  extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
            'csrf' => [
                'class' => \common\helpers\NoCsrfBehavior::className(),
                'controller' => $this,
                'actions' => [
                    'login',
                    'logout',
                    'aaa',
                ],
            ]
        ];
    }
    /*public function beforeAction($action) {
        $preVerify = \common\components\SysmaintenanceService::verifyMaintenanceStatus($action);
        if (!$preVerify[0]) {
            echo json_encode(['result'=> \frontend\components\ApiModule::CODE_ON_MAINTENANCE, 'desc' => $preVerify[1]]);
            return false;
        }
        return true;
    }*/

    public function actionAaa(){
        
    }

    public function actionTest() {
       // $jsonp = \Yii::$app->request->post('callback');
        $postinfo = \Yii::$app->request->post('a');
        // $callback = 'jak';
        $callback = \Yii::$app->request->get('callback');
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do
        {
            if($postinfo){
                $arrData['post'] = $postinfo;
            }else{
                $arrData['post'] = 'no post';
            }
            $tmp= json_encode($arrData); //json 数据
            $aa = $callback.'(' . $tmp .')';  //返回格式，必需
        }while (0);
        header('Access-Control-Allow-Origin:*');
        // echo json_encode($arrData);
        echo $aa;
    }

    public function actionLogin(){
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => "Success."];
        // echo "1";die;
        do
        {
            // 配置项 
            $api = 'https://webapi.sms.mob.com/sms/sendmsg'; 
            $appkey = '1408a2894205a'; 
            // 发送验证码 
            $response = \common\components\SmsComponent::postRequest($api, array( 'appkey' => $appkey, 'zone' =>'86', 'phone' => '18395947721') );
             /** * 发起一个post请求到指定接口 * @param string $api 请求的接口 * @param array $params post参数 * @param int $timeout 超时时间 * @return string 请求结果 */

            
            $arrData['aa'] = $response;
        }while (0);
        echo json_encode($arrData);
    }


}
