<?php 
namespace backend\controllers;
use Yii;

/**
*@author sjj
*@since 2017-8-3
*@example ????
*/
class VersionsController extends \backend\components\AuthorityController
{

	function actionList()
	{
		return $this->renderPartial('list');
	}
}
