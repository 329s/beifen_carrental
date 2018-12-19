<?php

namespace backend\components;

/**
 * Description of DefaultController
 *
 * @author kevin
 */
class AuthorityController  extends \common\helpers\AuthorityController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    '*' => ['get', 'post'],
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
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $rel1 = \common\components\SysmaintenanceService::verifyMaintenanceStatus($action);
            if (!$rel1[0]) {
                echo \yii\helpers\Html::tag('div', $rel1[1], ['class'=>'alert alert-danger']);
                return false;
            }
            $objUser = \backend\models\Rbac_admin::findIdentity(\Yii::$app->user->id);
            if (!$objUser) {
                \Yii::$app->getResponse()->redirect(\Yii::$app->getHomeUrl());
                \Yii::$app->run();
                return false;
            }
            if ($objUser->authority_at && $objUser->authority_at < time()) {
                \yii\bootstrap\Alert::widget(['body'=> \Yii::t('locale', 'Sorry, your account had already been expired.')]);
                \Yii::$app->user->logout();
                \Yii::$app->run();
                return false;
            }
            
            $authName = $action->getUniqueId();
            if (\Yii::$app->authManager->getPermission($authName)) {
                if (!\Yii::$app->user->can($authName, ['get'=>\Yii::$app->request->get(), 'post'=> \Yii::$app->request->post()])) {
                    echo \yii\helpers\Html::tag('div', \Yii::t('locale', 'Sorry, no operating privileges for current user!'), ['class'=>'alert alert-danger']);
                    return false;
                }
            }
            
            \backend\models\Rbac_admin_log::doLog();
            return true;
        }
        return false;
    }
    
}

