<?php

namespace backend\controllers;

class SplendidideaController extends \backend\components\AuthorityController
{
    public function actionIndex()
    {
        $filterModel = new \backend\models\searchers\Searcher_pro_splendid_idea();
        $dataProvider = $filterModel->search(\Yii::$app->request->getQueryParams());
        
        return $this->renderPartial('index', [
            'dataProvider'=>$dataProvider, 
            'filterModel'=>$filterModel,
        ]);
    }

    public function actionMainpage()
    {
        $end = time();
        $q = \common\models\Pro_splendid_idea::find();
        $q->asArray();
        $q->select(['publisher', 'count(publisher) as n']);
        $q->groupBy('publisher');
        $q->having(['>', 'count(publisher)', 0]);
        $q->orderBy('count(publisher) DESC');
        $q->limit(5);
        $arrAdoptWeekly = $q->where(['and', ['>=', 'approved_at', $end-86400*7], ['<', 'approved_at', $end]])->all();
        $arrAdoptMonthly = $q->where(['and', ['>=', 'approved_at', $end-86400*30], ['<', 'approved_at', $end]])->all();
        
        $q->select(['publisher', 'sum(award_amount) as n']);
        $q->having(['>', 'sum(award_amount)', 0]);
        $q->orderBy('sum(award_amount) DESC');
        $arrAwardWeekly = $q->where(['and', ['>=', 'approved_at', $end-86400*7], ['<', 'approved_at', $end]])->all();
        $arrAwardMonthly = $q->where(['and', ['>=', 'approved_at', $end-86400*30], ['<', 'approved_at', $end]])->all();
        
        $arrParams = [
            'arrAdoptWeekly' => $arrAdoptWeekly,
            'arrAdoptMonthly' => $arrAdoptMonthly,
            'arrAwardWeekly' => $arrAwardWeekly,
            'arrAwardMonthly' => $arrAwardMonthly,
        ];
        return $this->renderPartial('mainpage', $arrParams);
    }

    public function actionPublish()
    {
        $action = \Yii::$app->request->post('action');
        if ($action == 'save') {
            $formModel = new \backend\models\Form_pro_splendid_idea();
            if (!$formModel->load(\Yii::$app->request->post())) {
                $errText = $formModel->getErrorAsHtml();
                \common\widgets\JsonResultWidget::widget(['code'=>300, 'message'=>(empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText)]);
            }
            
            $objModel = new \common\models\Pro_splendid_idea();
            if ($formModel->save($objModel)) {
                if ($objModel->save()) {
                    \common\widgets\JsonResultWidget::widget(['code'=>200, 
                        'message'=>\Yii::t('locale', 'Congratulations, successful operation!'), 
                        //'callbackType'=>'forward',
                        //'forwardUrl'=>\yii\helpers\Url::to(['splendididea/index']),
                    ]);
                }
                else {
                    \common\widgets\JsonResultWidget::widget(['code'=>300, 'message'=>\Yii::t('locale', 'Sorry, the operation failed!')]);
                }
            }
            else {
                $errText = $formModel->getErrorAsHtml();
                \common\widgets\JsonResultWidget::widget(['code'=>300, 'message'=>(empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText)]);
            }
        }
        return $this->renderPartial('publish');
    }

    public function actionApproval()
    {
        $intId = intval(\Yii::$app->request->get('id'));
        if ($intId) {
            $objItem = \common\models\Pro_splendid_idea::findById($intId);
        }
        else {
            $objItem = null;
        }
        $backUrl = \Yii::$app->request->post('backUrl');
        $action = \Yii::$app->request->post('action');
        if ($action == 'save') {
            $formModel = new \backend\models\Form_pro_splendid_idea();
            if (!$formModel->load(\Yii::$app->request->post())) {
                $errText = $formModel->getErrorAsHtml();
                \common\widgets\JsonResultWidget::widget(['code'=>300, 'message'=>(empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText)]);
            }
            
            $objModel = \common\models\Pro_splendid_idea::findById($formModel->id);
            if (!$objModel) {
                \common\widgets\JsonResultWidget::widget(['code'=>300, 'message'=>\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('carrental', 'Idea')])]);
            }
            if ($formModel->save($objModel)) {
                $objModel->approved_by = \Yii::$app->user->id;
                $objModel->approved_at = time();
                if ($objModel->save()) {
                    \common\widgets\JsonResultWidget::widget(['code'=>200, 
                        'message'=>\Yii::t('locale', 'Congratulations, successful operation!'), 
                        //'callbackType'=>'forward',
                        //'forwardUrl'=>\yii\helpers\Url::to(['splendididea/index']),
                    ]);
                }
                else {
                    \common\widgets\JsonResultWidget::widget(['code'=>300, 'message'=>\Yii::t('locale', 'Sorry, the operation failed!')]);
                }
            }
            else {
                $errText = $formModel->getErrorAsHtml();
                \common\widgets\JsonResultWidget::widget(['code'=>300, 'message'=>(empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText)]);
            }
        }
        elseif (!$objItem) {
            \common\widgets\JsonResultWidget::widget(['code'=>300, 'message'=>\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('carrental', 'Idea')])]);
        }
        return $this->renderPartial('approval', ['objItem'=>$objItem, 'backUrl'=>$backUrl]);
    }

    public function actionClose()
    {
        return $this->renderPartial('close');
    }
    
    public function actionView()
    {
        $intId = intval(\Yii::$app->request->get('id'));
        if ($intId) {
            $objItem = \common\models\Pro_splendid_idea::findById($intId);
        }
        else {
            $objItem = null;
        }
        $backUrl = \Yii::$app->request->post('backUrl');
        
        if ($objItem) {
            $objItem->visits++;
            $objItem->save();
        }
        else {
            \common\widgets\JsonResultWidget::widget(['code'=>300, 'message'=>\Yii::t('locale', 'Data does not exist!')]);
        }
        
        $dataProvider = \common\models\Pro_splendid_idea_comments::createDataProvider([]);
        $dataProvider->query->where(['main_id'=>$intId]);
        $dataProvider->query->orderBy(['created_at'=>SORT_DESC]);
        
        $objItem->comment_count = $dataProvider->getTotalCount();
        $objItem->focus_count = \common\models\Pro_splendid_idea_focus::find()->where(['focus_id'=>$intId])->count();
        
        return $this->renderPartial('view', ['objItem'=>$objItem, 'dataProvider'=>$dataProvider, 'backUrl'=>$backUrl]);
    }
    
    public function actionComment()
    {
        $action = \Yii::$app->request->post('action');
        if ($action == 'save') {
            $formModel = new \backend\models\Form_pro_splendid_idea_comments();
            if (!$formModel->load(\Yii::$app->request->post())) {
                $errText = $formModel->getErrorAsHtml();
                \common\widgets\JsonResultWidget::widget(['code'=>300, 'message'=>(empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText)]);
            }
            
            $objModel = new \common\models\Pro_splendid_idea_comments();
            if ($formModel->save($objModel)) {
                if ($objModel->save()) {
                    \common\widgets\JsonResultWidget::widget(['code'=>200, 
                        'message'=>\Yii::t('locale', 'Congratulations, successful operation!'), 
                        'callbackType'=>'',
                    ]);
                }
                else {
                    \common\widgets\JsonResultWidget::widget(['code'=>300, 'message'=>\Yii::t('locale', 'Sorry, the operation failed!')]);
                }
            }
            else {
                $errText = $formModel->getErrorAsHtml();
                \common\widgets\JsonResultWidget::widget(['code'=>300, 'message'=>(empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText)]);
            }
        }
    }
    
}
