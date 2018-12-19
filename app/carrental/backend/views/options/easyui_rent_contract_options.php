<?php

use common\helpers\CMyHtml;

$objForm = new backend\models\Form_pro_rent_contract_options();

$htmlArray = [];
$arrScripts = [];

$baseDir = \common\helpers\Utils::getRootUrl();
$assetsDir = "{$baseDir}assets";
//$htmlArray[] = \yii\helpers\Html::cssFile("{$assetsDir}/js/kindeditor/plugins/code/prettify.css");
//$htmlArray[] = \yii\helpers\Html::cssFile("{$assetsDir}/js/kindeditor/themes/default/default.css");

$editContractOptionsUrl = \yii\helpers\Url::to(['options/rent_contract_options_edit']);

$groupContractSignatureCellCls = 'rent-contract-options-signature-cells';
$formPanelHeight = '470px';
$count = 0;
foreach ($arrContracts as $type => $objContract) {
    if ($count % 2 == 0) {
        $htmlArray[] = CMyHtml::beginTag('div', ['style' => "display:block;width:100%"]);
    }
    $formId = CMyHtml::getIDPrefix()."form-contract-{$objContract->id}-".CMyHtml::genID();
    $footerId = CMyHtml::getIDPrefix()."form-contract-footer-{$objContract->id}-".CMyHtml::genID();
    $htmlArray[] = CMyHtml::beginTag('div', ['style' => "float:left;width:50%"]);
    $htmlArray[] = CMyHtml::beginPanel($objContract->name, ['fit'=>'true', 'collapsible'=>'true', 'footer'=>"'#{$footerId}'", 
        'onResize'=>"function(width,height){ $('.{$groupContractSignatureCellCls}').width(Math.floor(width/2 - 2)); }",
        'style'=>"width:100%;height:{$formPanelHeight};padding:8px 8px 8px 8px;"]);
    
    $htmlArray[] = \yii\helpers\Html::beginForm($editContractOptionsUrl, 'post', ['id'=>$formId]);
    $htmlArray[] = \yii\helpers\Html::label($objContract->getAttributeLabel('title'), '', ['style'=>"display:block"]);
    $htmlArray[] = \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_TEXTBOX, $objForm->fieldName('title'), $objContract->title, '', ['style'=>"width:100%"]);
    $htmlArray[] = \yii\helpers\Html::label($objContract->getAttributeLabel('instruction'), '', ['style'=>"display:block"]);
    $htmlArray[] = \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_TEXTAREA, $objForm->fieldName('instruction'), $objContract->instruction, '', ['style'=>"width:100%;height:120px;resize:none"]);
    $htmlArray[] = CMyHtml::beginTag('div', ['style' => "display:table-row;width:100%"]);
    $htmlArray[] = CMyHtml::beginTag('div', ['style' => "display:table-cell;width:50%;padding-right:2px", 'class'=>$groupContractSignatureCellCls]);
    $htmlArray[] = \yii\helpers\Html::label($objContract->getAttributeLabel('signature_a'), '', ['style'=>"display:block"]);
    $htmlArray[] = \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_TEXTAREA, $objForm->fieldName('signature_a'), $objContract->signature_a, '', ['style'=>"width:100%;height:120px;resize:none"]);
    $htmlArray[] = CMyHtml::endTag('div');
    $htmlArray[] = CMyHtml::beginTag('div', ['style' => "display:table-cell;width:50%;padding-left:2px"]);
    $htmlArray[] = \yii\helpers\Html::label($objContract->getAttributeLabel('signature_b'), '', ['style'=>"display:block"]);
    $htmlArray[] = \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_TEXTAREA, $objForm->fieldName('signature_b'), $objContract->signature_b, '', ['style'=>"width:100%;height:120px;resize:none"]);
    $htmlArray[] = CMyHtml::endTag('div');
    $htmlArray[] = CMyHtml::endTag('div');
    $htmlArray[] = \yii\helpers\Html::label($objContract->getAttributeLabel('footer'), '', ['style'=>"display:block"]);
    $htmlArray[] = \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_TEXTAREA, $objForm->fieldName('footer'), $objContract->footer, '', ['style'=>"width:100%;height:60px;resize:none"]);
    $htmlArray[] = \yii\helpers\Html::hiddenInput($objForm->fieldName('id'), $objContract->id);
    $htmlArray[] = \yii\helpers\Html::hiddenInput($objForm->fieldName('type'), $objContract->type);
    $htmlArray[] = \yii\helpers\Html::hiddenInput($objForm->fieldName('name'), $objContract->name);
    $htmlArray[] = \yii\helpers\Html::hiddenInput($objForm->fieldName('flag'), $objContract->flag);
    
    $htmlArray[] = \yii\helpers\Html::endForm();
            
    $htmlArray[] = CMyHtml::endPanel();
    $htmlArray[] = CMyHtml::beginTag('div', ['id'=>$footerId, 'style' => "text-align:right;padding:5px 50px 5px 50px;width:auto;height:auto"]);
    $htmlArray[] = CMyHtml::tag('a', \Yii::t('locale', 'Submit'), ['href' => "javascript:void(0)", 'class' => 'easyui-linkbutton', 'onclick' => "easyuiFuncFormOnSubmit('#{$formId}')", 'data-options' => "iconCls:'icon-ok',width:90"]);
    $htmlArray[] = CMyHtml::endTag('div');
    
    $htmlArray[] = CMyHtml::endTag('div');
    
    $count++;
    if ($count % 2 == 0) {
        $htmlArray[] = CMyHtml::endTag('div');
    }
}

$funcId = CMyHtml::genID();
/* rent contract */
$contractRentingEditorVariable = "contract_renting_{$objContractRenting->id}_".CMyHtml::genID();
$contractRentingTextareaId = "form_contract_renting_textarea_{$objContractRenting->id}_".CMyHtml::genID();
$formId = CMyHtml::getIDPrefix()."form-contract-renting-{$objContractRenting->id}-".CMyHtml::genID();
$footerId = CMyHtml::getIDPrefix()."form-contract-renting-footer-{$objContractRenting->id}-".CMyHtml::genID();
$htmlArray[] = CMyHtml::beginTag('div', ['style' => "width:100%"]);
$htmlArray[] = CMyHtml::beginPanel($objContractRenting->name, ['fit'=>'false', 'collapsible'=>'true', 'collapsed'=>'true', 'footer'=>"'#{$footerId}'", 
    //'onOpen' => "initializeKindEditor{$funcId}",
    'style'=>"width:100%;height:auto;padding:8px 8px 8px 8px;"]);
$htmlArray[] = \yii\helpers\Html::beginForm($editContractOptionsUrl, 'post', ['id'=>$formId]);
//$htmlArray[] = \yii\helpers\Html::label($objContractRenting->getAttributeLabel('instruction'), '', ['style'=>"display:block"]);
$htmlArray[] = \yii\helpers\Html::textarea($objForm->fieldName('instruction'), $objContractRenting->instruction, ['id'=>$contractRentingTextareaId, 'class'=>'easyui-kindeditor', 'style'=>"width:100%;height:200px;visibility:hidden;"]);
//$htmlArray[] = \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_TEXTAREA, $objForm->fieldName('instruction'), $objContractRenting->instruction, '', []);
$htmlArray[] = CMyHtml::beginTag('div', ['style' => "display:table-row;width:100%"]);
$htmlArray[] = CMyHtml::beginTag('div', ['style' => "display:table-cell;width:50%;padding-right:2px"]);
$htmlArray[] = \yii\helpers\Html::hiddenInput($objForm->fieldName('id'), $objContractRenting->id);
$htmlArray[] = \yii\helpers\Html::hiddenInput($objForm->fieldName('type'), $objContractRenting->type);
$htmlArray[] = \yii\helpers\Html::hiddenInput($objForm->fieldName('name'), $objContractRenting->name);
$htmlArray[] = \yii\helpers\Html::hiddenInput($objForm->fieldName('title'), empty($objContractRenting->title) ? '0' : $objContractRenting->title);
$htmlArray[] = \yii\helpers\Html::hiddenInput($objForm->fieldName('signature_a'), empty($objContractRenting->signature_a) ? '0' : $objContractRenting->signature_a);
$htmlArray[] = \yii\helpers\Html::hiddenInput($objForm->fieldName('signature_b'), empty($objContractRenting->signature_b) ? '0' : $objContractRenting->signature_b);
$htmlArray[] = \yii\helpers\Html::hiddenInput($objForm->fieldName('footer'), empty($objContractRenting->footer) ? '0' : $objContractRenting->footer);
$htmlArray[] = \yii\helpers\Html::hiddenInput($objForm->fieldName('flag'), empty($objContractRenting->flag) ? 0 : $objContractRenting->flag);
$htmlArray[] = \yii\helpers\Html::endForm();
$htmlArray[] = CMyHtml::endPanel();
$htmlArray[] = CMyHtml::beginTag('div', ['id'=>$footerId, 'style' => "text-align:right;padding:5px 50px 5px 50px;width:auto;height:auto"]);
$htmlArray[] = CMyHtml::tag('a', \Yii::t('locale', 'Submit'), ['href' => "javascript:void(0)", 'class' => 'easyui-linkbutton', 'onclick' => "easyuiFuncFormOnSubmit('#{$formId}')", 'data-options' => "iconCls:'icon-ok',width:90"]);
$htmlArray[] = CMyHtml::endTag('div');

$htmlArray[] = CMyHtml::endTag('div');


$htmlArray[] = \yii\bootstrap\Html::jsFile("{$assetsDir}/jquery-easyui/extension/jquery.kindeditor.js");

$arrScripts[] = <<<EOD
var {$contractRentingEditorVariable} = undefined;
function initializeKindEditor{$funcId}() {
    if (!{$contractRentingEditorVariable}) {
        KindEditor.ready(function() {
            prettyPrint();
        });
        KindEditor.ready(function(K) {
            {$contractRentingEditorVariable} = K.create('#{$contractRentingTextareaId}', {
                allowFileManager : true
            });
        });
    }
}
EOD;

$htmlArray[] = CMyHtml::endPanel();

//$htmlArray[] = \yii\helpers\Html::script(implode("\n", $arrScripts));

echo implode("\n", $htmlArray);