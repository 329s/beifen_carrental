<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace backend\modules\sysmaintenance\controllers;

/**
 * Description of AuthManagerController
 *
 * @author kevin
 */
class AuthManagerController extends \backend\components\AuthorityController
{
    
    public function beforeAction($action) {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            return false;
        }
        return parent::beforeAction($action);
    }
    
    
}
