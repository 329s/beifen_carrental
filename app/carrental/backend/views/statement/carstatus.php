<?php

use common\helpers\CMyHtml;

$urlRoot = \common\helpers\Utils::getRootUrl();

$urlGetRentalData = \yii\helpers\Url::to(['statement/carstatus_rental_data']);
$urlGetStateData = \yii\helpers\Url::to(['statement/carstatus_state_data']);

$titleRentalStatement = Yii::t('carrental', 'Vehicle rental statement');
$titleStateStatement = Yii::t('carrental', 'Vehicle state statement');

$funcId = CMyHtml::genID();

$htmlArray = [];

$htmlArray[] = CMyHtml::beginPanel($titleRentalStatement, ['style'=>'width:100%;height:50%']);

$htmlArray[] = CMyHtml::beginPanel('', ['id'=>"chart_view_vehicle_rental_statement{$funcId}", 'fit'=>'true']);
$htmlArray[] = CMyHtml::endPanel();

$htmlArray[] = CMyHtml::endPanel();

$htmlArray[] = CMyHtml::beginPanel($titleStateStatement, ['style'=>'width:100%;height:50%']);

$htmlArray[] = CMyHtml::beginPanel('', ['id'=>"chart_view_vehicle_state_statement{$funcId}", 'fit'=>'true']);
$htmlArray[] = CMyHtml::endPanel();

$htmlArray[] = CMyHtml::endPanel();

//$urlCredits = Yii::$app->getHomeUrl();
$urlCredits = '#';
$creditsName = Yii::$app->params['app.copyright.name'];

$strRentalLabels = json_encode($arrRentalLabels);
$strRentalData = json_encode($arrRentalData);
$strStateLabels = json_encode($arrStateLabels);
$strStateData = json_encode($arrStateData);

$arrScripts = [];
$arrScripts[] = <<<EOD
var chart_vehicle_rental_statement{$funcId};
var chart_vehicle_state_statement{$funcId};

setTimeout(function() {
    chart_vehicle_rental_statement{$funcId} = new Highcharts.Chart({
        chart: {
            type: 'column',
            renderTo: 'chart_view_vehicle_rental_statement{$funcId}',
        },
        title: {
            text: '{$titleRentalStatement}'
        },
        credits: {
            href: '{$urlCredits}',
            text: '{$creditsName}',
        },
        xAxis: {
            categories: {$strRentalLabels}
        },
        yAxis: {
            min: 0,
            title: {
                text: '(辆)',
                align: 'high',
                rotation: 0,
            }
        },
        legend: {
            layout: 'horizontal',
            floating: true,
            backgroundColor: '#FFFFFF',
            align: 'right',
            verticalAlign: 'top',
            y: 60,
            x: -60
        },
        tooltip: {
            formatter: function () {
                return '<b>' + this.series.name + '</b><br/>' +
                    this.x + ': ' + this.y;
            }
        },
        series: [{
            data: {$strRentalData}
        }]
    });
    chart_vehicle_state_statement{$funcId} = new Highcharts.Chart({
        chart: {
            type: 'column',
            renderTo: 'chart_view_vehicle_state_statement{$funcId}',
        },
        title: {
            text: '{$titleStateStatement}'
        },
        credits: {
            href: '{$urlCredits}',
            text: '{$creditsName}',
        },
        xAxis: {
            categories: {$strStateLabels}
        },
        yAxis: {
            min: 0,
            title: {
                text: '(辆)',
                align: 'high',
                rotation: 0,
            }
        },
        legend: {
            layout: 'vertical',
            floating: true,
            backgroundColor: '#FFFFFF',
            align: 'right',
            verticalAlign: 'top',
            y: 60,
            x: -60
        },
        tooltip: {
            formatter: function () {
                return '<b>' + this.series.name + '</b><br/>' +
                    this.x + ': ' + this.y;
            }
        },
        series: [{
            data: {$strStateData}
        }]
    });
}, 100);

EOD;

$htmlArray[] = \yii\helpers\Html::script(implode("\n", $arrScripts));

echo implode("\n", $htmlArray);
