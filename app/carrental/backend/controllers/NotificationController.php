<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace backend\controllers;

/**
 * Description of NotificationController
 *
 * @author kevin
 */
class NotificationController extends \backend\components\AuthorityController
{
    
    public function actionCheck()
    {
        $status = \backend\components\NoticeService::currentlyStatus();
        // $date=date('Y-m-d H:i:s',time());
        // $b=json_encode($status);
        // file_put_contents('status.txt',"$date'-----currentlyStatus>'$b'\n",FILE_APPEND);
        return $this->asJson(json_encode($status));
    }
    
}
