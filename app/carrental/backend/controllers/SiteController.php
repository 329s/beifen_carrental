<?php
namespace backend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;

/**
 * Site controller
 */
class SiteController extends Controller
{
    private $arrUAuth = [];
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'captcha'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'homepanel'],
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
            'checker' => [
                'class' => 'backend\components\UniqueLoginFilter',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Redirects the browser to the home page.
     *
     * You can use this method in an action by returning the [[Response]] directly:
     *
     * ```php
     * // stop executing this action and redirect to home page
     * return $this->goHome();
     * ```
     *
     * @return Response the current response object
     */
    public function goHome()
    {
        return Yii::$app->getResponse()->redirect(Yii::$app->getHomeUrl());
    }

    public function actionIndex()
    {
        $userId = \Yii::$app->user->getId();
        if (empty($this->arrUAuth)) {
            $this->arrUAuth = \backend\modules\rbac\components\AdminMenuAuth::findUAColumn($userId);
        }
        
        $arrUser = \backend\models\Rbac_admin::findOne(['id'=>$userId]);
        if ($arrUser) {
            $adminName = $arrUser->username;
        }
        else {
            $adminName = $userId;
        }
        
        $params = [];
        $params['admin_name'] = $adminName;
        $params['arrAuth'] = $this->arrUAuth;
        
        $prefix = \backend\components\AdminHtmlService::getViewPrefix();
        if ($prefix) {
            $this->layout = "@app/views/layouts/{$prefix}_main.php";
            return $this->render("{$prefix}_index", $params);
        }
        
        return $this->render('index', $params);
    }

    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new \backend\models\Form_rbac_admin_login();
        if ($model->load(\Yii::$app->request->post())) {
            if ($model->login()) {
                return $this->goHome();
            }
            else {
                $errors = $model->getErrors();
                $errTexts = [];
                foreach ($errors as $field => $fieldErrors) {
                    $errTexts[] = implode(' ', $fieldErrors);
                }
                return $this->render('error', [
                    'name' => \Yii::t('locale', 'Login failed.'),
                    'message' => implode('<br />', $errTexts),
                ]);
                //\yii\bootstrap\Alert::widget(['body'=> implode('<br />', $errTexts)]);
            }
        } else {
            $prefix = \backend\components\AdminHtmlService::getViewPrefix();
            if ($prefix) {
                $this->layout = "@app/views/layouts/{$prefix}_main.php";
            }
            
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
    
    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new \backend\models\Form_rbac_admin();
        if ($model->load(\Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    public function actionChangetheme() {
        $themeName = \Yii::$app->request->getParam('theme');
        $cookie = new \yii\web\Cookie(['name'=>'easyui_theme',
            'value'=>$themeName,
            'expire'=>time() + 86400 * 15
            ]);
        $cookies = \Yii::$app->response->cookies;
        $cookies->remove('easyui_theme');
        $cookies->add($cookie);
        return $themeName;
    }
    
    public function actionHomepanel() {
        $arrData = [
            'arrVehicleRentalData' => \backend\components\StatisticsService::getCarStatusRentalData(),
            'arrVehicleStateData' => \backend\components\StatisticsService::getCarStatusStateData(),
        ];
       /*echo "<pre>";
       print_r($arrData);
       echo "</pre>";die;*/
        
        $prefix = \backend\components\AdminHtmlService::getViewPrefix();
        if ($prefix) {
            return $this->renderPartial("{$prefix}_homepanel", $arrData);
        }
        return $this->renderPartial('homepanel', $arrData);
    }
    
}
