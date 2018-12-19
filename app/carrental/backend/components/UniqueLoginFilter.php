<?php

namespace backend\components;

class UniqueLoginFilter extends \yii\base\ActionFilter
{
    
    public function beforeAction($action) {
        if (!parent::beforeAction($action)) {
            return false;
        }
        
        $actionID = $action->id;
        $isAllowed = true;
        
        if ($isAllowed) {
            if (!\Yii::$app->user->getIsGuest() && $actionID != 'logout')
            {
                $id = \Yii::$app->user->id;
                $session = \Yii::$app->session;
                $username = \Yii::$app->user->identity->username;
                $tokenSES = $session->get(md5(sprintf("%s&%s",$id,$username))); //取出session中的用户登录token  
                $sessionTBL = \backend\models\Rbac_admin_session::findOne(['id' => $id]);
                $tokenTBL = ($sessionTBL ? $sessionTBL->session_token : null);
                
                if($tokenTBL == null || $tokenSES != $tokenTBL)  //如果用户登录在 session中token不同于数据表中token  
                {
                    \Yii::$app->user->logout(); //执行登出操作  
                    \Yii::$app->getResponse()->redirect(\Yii::$app->getHomeUrl());
                    \Yii::$app->run();
                    $isAllowed = false;
                }
            }
        }
        
        return $isAllowed;
    }
    
}

