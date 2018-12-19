<?php
namespace frontend\controllers;
/**
*分期
*/
class InstalmentController extends \yii\web\Controller
{

	public function init(){
        $this->enableCsrfValidation = false;
    }

    public function behaviors()
    {
        return [
        ];
    }
    public function beforeAction($action) {
    	return true;
    }

    public function actionGoods_info()
    {
    	$arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
    	do {
    		$goods_id = \Yii::$app->request->get('goods_id');
    		if (empty($goods_id)) {
    			$arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc']   = \Yii::t('locale', 'Invalid parameter!');
                break;
    		}

    		$cdb = \common\models\Pro_goods::find();
        	$cdb->where(['id'=>$goods_id,'status'=>1]);
        	$arrRows = $cdb->asArray()->one();

        	if(!$arrRows){
        		$arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc']   = \Yii::t('locale', 'Goods not grounding');
                break;
        	}

        	$cdb_color = \common\models\Pro_goods_color::find();
        	$cdb_color->select(['id','goods_id','color_name','color']);
        	$cdb_color->where(['goods_id'=>$goods_id]);
        	$arrRowsColor = $cdb_color->asArray()->all();

        	$GoodsMemoryArray = \common\models\Pro_goods::getGoodsMemoryArray();

        	$arrData['goods'] = $arrRows;
        	$arrData['arrRowsColor'] = $arrRowsColor;
        	$arrData['GoodsMemoryArray'] = $GoodsMemoryArray;
    	} while (0);

    	echo json_encode($arrData);
    }

    public function actionAjax_goods($value='')
    {
    	$arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
    	do {
    		$goods_id    = \Yii::$app->request->get('goods_id');
    		$memory      = \Yii::$app->request->get('memory');
    		$goods_color = \Yii::$app->request->get('goods_color');

    		if(empty($goods_id)){
    			$arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
    			$arrData['desc']   = \Yii::t('locale','Invalid parameter');
    			break;
    		}
    		$cdb = \common\models\Pro_goods_product::find();
    		$cdb->where(['goods_id'=>$goods_id]);

    		if(!empty($memory)){
    			$cdb->andwhere(['memory'=>$memory]);
    		}

    		if(!empty($goods_color)){
    			$cdb->andwhere(['goods_color'=>$goods_color]);
    		}
    		$arrRows = $cdb->asArray()->all();
    		// $sql=$cdb->createCommand()->getRawSql();
    		// $arrData['sql'] = $sql;
    		$colorArr  = array_unique(array_column($arrRows, 'goods_color'));
    		$memoryArr = array_unique(array_column($arrRows, 'memory'));
    		$arrData['colorArr'] = $colorArr;
    		$arrData['memoryArr'] = $memoryArr;
    		$arrData['goods']    = $arrRows;
    		if(!$arrRows){
    			$arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
    			$arrData['desc']   = \Yii::t('locale', 'Goods not find');
    			break;
    		}



    	} while (0);
    	echo json_encode($arrData);
    }
}