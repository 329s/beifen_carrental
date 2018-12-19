<?php 

use common\helpers\CMyHtml;
$parent_id = trim(Yii::$app->request->getParam('parent_id'));
if($parent_id){
	$nav = ['id', 'name','category',  'parent','href','description','status','operation'];
	$url = ['rbac2/node_list?parent_id='.$parent_id];
}else{
	$nav = ['id', 'name','category', 'description','status','operation'];
	$url = ['rbac2/node_list'];
}
$urlsArray = [
    'url' => \yii\helpers\Url::to($url),
];
$toolbarArray = [
	Yii::$app->user->can('rbac2/node_add') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', ['dialog'=>\yii\helpers\Url::to(['rbac2/node_add'])]) : null,
];

echo CMyHtml::datagrid('节点管理', // $title
    new \backend\models\Rbac_permission(),    // $model
    $nav,            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
