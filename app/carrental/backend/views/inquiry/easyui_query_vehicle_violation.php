<?php

use common\helpers\CMyHtml;

$queryCount = empty($queryCount) ? 0 : $queryCount;

$inputs = [
    ['type' => CMyHtml::INPUT_TEXTAREA, 'name' => 'lsnum',
        'label' => Yii::t('locale', 'Lsnum Number'),
        'htmlOptions' => [ 'required' => false, 'style'=>'width:400px;height:100px;'],
        'columnindex' => 0,
    ],
	// ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => 'pageNum',
        // 'label' => Yii::t('locale', 'Sub Query'),
        // 'data' => $querydata,
        // 'htmlOptions' => ['required' => true, 'editable'=>false,'style'=>'width:150px;'],
    // ],
	
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => 'lookupMethod',
        'label' => Yii::t('locale', 'Lookup Method'),
        'data' => \common\models\Pro_violation_inquiry::getQueryArray($queryCount),
        'htmlOptions' => ['required' => true, 'editable'=>false,'style'=>'width:150px;'],
    ],
	
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'queryTotalCount',
        'label' => Yii::t('locale', 'Query Total Count'),
        'value' => $queryCount,
        'htmlOptions' => ['readonly'=>true],
    ],
	
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'queryCount',
        'label' => Yii::t('locale', 'Query Count'),
        'value' => \common\models\Pro_violation_inquiry::QUERY_NUM,
        'htmlOptions' => ['readonly'=>true],
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => '',
        'label' => Yii::t('locale', 'Waiting time'),
        'value' => \common\models\Pro_violation_inquiry::OVERTIME,
        'htmlOptions' => ['readonly'=>true,'id'=>'waiting_time', 'tailhtml'=>'秒'],
    ],
];
// if(!empty($queryCount) && $queryCount > 0){
	$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];
// }else{
	// $buttons = [];
// }
$hiddenFields = ['action' => 'query'];

$onSubmitCallback = <<<EOD
	function(){ 
		var parentForm = $('#queryFrom');
		var waiting_time = parseInt($('#waiting_time',parentForm).val());
		var start_time = 1;
		var waiting_time_div = document.createElement('div');
		waiting_time_div.innerHTML = "查询时间<span class='nub_mes' style='color:red;font-size:22px;'>1</span>秒";
		
		waiting_time_div.style.textAlign = 'center';
		waiting_time_div.style.marginTop = '20px';
		waiting_time_div.style.fontSize = '22px';
		
		document.querySelector('#queryFrom').appendChild(waiting_time_div);
		var timer = setInterval(function(){
			start_time++;
			document.querySelector(".nub_mes").innerHTML = start_time;
			if(document.querySelector(".panel.window.easyui-fluid").style.display == 'none'){
				clearInterval(timer);
			}
			if(start_time >= waiting_time){
				clearInterval(timer);
			}
		},1000);
	}
EOD;

echo CMyHtml::form('', \yii\helpers\Url::to(['inquiry/query_vehicle_violation']), 'post', ['id'=>'queryFrom','onSubmitCallback'=>$onSubmitCallback], $inputs, $buttons, $hiddenFields);