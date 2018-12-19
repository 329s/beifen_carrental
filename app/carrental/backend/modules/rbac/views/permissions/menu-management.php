<?php

$idPrefix = \common\helpers\BootstrapHtml::getIDPrefix();
$autoId = \common\helpers\BootstrapHtml::genID();

$allPermissions = \backend\modules\rbac\components\AdminMenuAuth::findAllColumn(false, false, false);

$urlEditPermission = \yii\helpers\Url::to(['/rbac/permissions/edit', '_'=>time()]);
$urlDeletePermission = \yii\helpers\Url::to(['/rbac/permissions/delete', '_'=>time()]);
//$itemButtonEventPrefix = "$.custom.bootstrap.showModal('#{$idPrefix}auth_editor{$autoId}', '{$urlEditPermission}&name=";

function renderTreeNode($item, $autoId) {
    $name = urlencode($item['o']['name']);
    $nodeButtons = [];
    $nodeButtons[] = yii\bootstrap\Html::button('', ['class'=>'btn btn-xs btn-default glyphicon glyphicon-plus', 'onclick'=>"funcOpenEditDialog{$autoId}('{$name}', 'create')"]);
    $nodeButtons[] = yii\bootstrap\Html::button('', ['class'=>'btn btn-xs btn-default glyphicon glyphicon-pencil', 'onclick'=>"funcOpenEditDialog{$autoId}('{$name}', 'edit')"]);
    $nodeButtons[] = yii\bootstrap\Html::button('', ['class'=>'btn btn-xs btn-default glyphicon glyphicon-minus', 'onclick'=>"funcConfirmDeleteItem{$autoId}('{$name}', 'delete')"]);
    $nodeHtml = yii\bootstrap\Html::a(yii\bootstrap\Html::tag('span',
        yii\bootstrap\Html::tag('i', '', ['class'=>'glyphicon glyphicon-'.($item['o']['category']=='menu'?'list':($item['o']['category']=='action'?'play-circle':'triangle-bottom'))]). $item['o']['description'], []
        ) . yii\bootstrap\Html::tag('code', "[{$item['o']['name']}]"). implode("\n", $nodeButtons), 'javascript:void(0)', []);
    //nodeHtml .= yii\bootstrap\Html::a('Add', 'javascript:void(0)', []);
    $htmls = [];
    if (isset($item['children'])) {
        $htmls[] = yii\bootstrap\Html::beginTag('li', ['class'=>'jstree-open']).$nodeHtml;
        $htmls[] = yii\bootstrap\Html::beginTag('ul');
        foreach ($item['children'] as $child) {
            $htmls[] = renderTreeNode($child, $autoId);
        }
        $htmls[] = yii\bootstrap\Html::endTag('ul');
        $htmls[] = yii\bootstrap\Html::endTag('li');
    }
    else {
        $htmls[] = yii\bootstrap\Html::tag('li', $nodeHtml, []);
    }
    return implode("\n", $htmls);
}

$htmlArray = [];

$htmlArray[] = yii\bootstrap\Html::beginTag('div', ['id' => "{$idPrefix}auth_tree{$autoId}"]);
$htmlArray[] = yii\bootstrap\Html::beginTag('ul');
foreach ($allPermissions as $item) {
    $htmlArray[] = renderTreeNode($item, $autoId);
}
$htmlArray[] = yii\bootstrap\Html::endTag('ul');
$htmlArray[] = yii\bootstrap\Html::endTag('div');

/*
$formModel = new \backend\modules\rbac\models\PermissionForm();
$form = common\widgets\ActiveFormExtendWidget::begin([
    'layout' => 'inline',
    'id' => "{$idPrefix}editform{$autoId}",
    'action' => \yii\helpers\Url::to(['/rbac/permissions/edit']),
    //'options' => ['style'=>'display:none'],
]);
$inputElements = [
    'name' => $form->field($formModel, 'name', ['inputOptions'=>['style'=>'width:100px']]),
    'category' => $form->field($formModel, 'category', [])->dropDownList(backend\modules\rbac\models\Permission::getCategoriesArray()),
    'parent' => $form->field($formModel, 'parent', ['inputOptions'=>['style'=>'width:100px']]),
    'href' => $form->field($formModel, 'href', []),
    'description' => $form->field($formModel, 'description', []),
    'icon' => $form->field($formModel, 'icon', ['inputOptions'=>['style'=>'width:100px']]),
    'icon_traditional' => $form->field($formModel, 'icon_traditional', ['inputOptions'=>['style'=>'width:100px']]),
    'c_order' => $form->field($formModel, 'c_order', ['inputOptions'=>['style'=>"width:40px"]]),
    'status' => $form->field($formModel, 'status', [])->dropDownList(backend\modules\rbac\models\Permission::getStatusArray()),
    'target' => $form->field($formModel, 'target', ['inputOptions'=>['style'=>'width:100px']]),//->dropDownList([''=> '默认', 'document'=> '当前页', '_blank'=>'新标签页']),
    'operation' => yii\bootstrap\Html::resetButton(Yii::t('locale', 'Reset'), ['class'=>'form-control btn btn-default']) . yii\bootstrap\Html::submitButton(Yii::t('locale', 'Submit'), ['class'=>'form-control btn btn-primary'])
];
$dataProvider = new \yii\data\ArrayDataProvider([
    'allModels' => [$inputElements],
    'modelClass' => backend\modules\rbac\models\Permission::className(),
]);
$inn = \yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => ['name:raw', 'category:raw', 'parent:raw', 'href:raw', 'description:raw', 
        'icon:raw', 'icon_traditional:raw', 'c_order:raw', 'status:raw', 'target:raw', 'operation:raw'],
    'layout' => '{items}',
]);

echo $inn;
$htmlArray[] = common\widgets\ActiveFormExtendWidget::end();
*/

$htmlArray[] = \common\helpers\BootstrapHtml::dialog(['id'=>"{$idPrefix}auth_editor{$autoId}"]);

$arrScripts = [];
$arrScripts[] = "$(function () {";
$arrScripts[] = <<<EOD
$('#{$idPrefix}auth_tree{$autoId}').jstree({
    'core':{
        'themes':{
            'variant':'large'
        }
    }
});
EOD;
$arrScripts[] = "});";
$arrScripts[] = "function funcOpenEditDialog{$autoId}(name, action) {".
    "\n  $('#{$idPrefix}auth_tree{$autoId}').data('__submited', false);".
    "\n  $.custom.bootstrap.showModal('#{$idPrefix}auth_editor{$autoId}', '{$urlEditPermission}&act='+action+'&key='+name+'&cb=fosp{$autoId}', function(){ if ($('#{$idPrefix}auth_tree{$autoId}').data('__submited')) { $.custom.bootstrap.loadElement($('#{$idPrefix}auth_tree{$autoId}').parent(), '".yii\helpers\Url::to(['/rbac/permissions/menu-management'])."'); } });".
    "\n}";
$arrScripts[] = "function funcConfirmDeleteItem{$autoId}(name) {".
    "\n  $.custom.bootstrap.confirm($.custom.utils.format('".
        \Yii::t('locale', 'Are you sure to delete the authority [{name}]?', ['name'=>'{1}'])."', decodeURI(name)), '".
        \Yii::t('locale', 'Warning').
        "', function(r) { if (r) { $.custom.bootstrap.queryUrl('{$urlDeletePermission}&key='+name); } });".
    "\n}";
$arrScripts[] = "function fosp{$autoId}() {".
    "\n  $('#{$idPrefix}auth_tree{$autoId}').data('__submited', true);".
    "\n}";

$htmlArray[] = yii\bootstrap\Html::script(implode("\n", $arrScripts), ['type'=>'text/javascript']);

echo implode("\n", $htmlArray);
