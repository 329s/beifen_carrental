<?php

namespace backend\modules\rbac\controllers;

/**
 * Description of RoleController
 *
 * @author kevin
 */
class RoleController extends \backend\components\AuthorityController
{
    
    public function actionIndex()
    {
        return $this->renderPartial('index');
    }
    
}
