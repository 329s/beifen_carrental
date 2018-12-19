<?php

$urlRoot = \common\helpers\Utils::getRootUrl();

$urlCssFile = \yii\helpers\Url::to(['css/homepanel.css']);

$statusTotalCount = $arrVehicleStateData['waitingRentVehicleCount'] + $arrVehicleRentalData['rentingCount'] + 
        $arrVehicleStateData['maintenanceVehicleCount'] + $arrVehicleStateData['needUpkeepVehicleCount'];
if ($statusTotalCount == 0) {
    $statusTotalCount = 1;
}

?>
<link rel="stylesheet" href="<?= "{$urlRoot}assets/css/font-awesome.min.css" ?>">
<link rel="stylesheet" href="<?= $urlCssFile ?>">
<script src="<?= "{$urlRoot}assets/" ?>js/charts/Chart.js"></script>
<script>
    $(function () {
        Canvas1();
        Canvas2();
        Canvas3();
    });
    function Canvas1() {
        var doughnutData = [
            {
                value: <?= $arrVehicleRentalData['rentingCount']; ?>,
                color: "#F7464A",
                highlight: "#FF5A5E",
                label: "在租"
            },
            {
                value: <?= $arrVehicleStateData['waitingRentVehicleCount']; ?>,
                color: "#46BFBD",
                highlight: "#5AD3D1",
                label: "待租"
            },
            {
                value: <?= $arrVehicleStateData['maintenanceVehicleCount']; ?>,
                color: "#FDB45C",
                highlight: "#FFC870",
                label: "维修"
            },
            {
                value: <?= $arrVehicleStateData['needUpkeepVehicleCount']; ?>,
                color: "#949FB1",
                highlight: "#A8B3C5",
                label: "保养"
            }
        ];
        var ctx = document.getElementById("Canvas1").getContext("2d");
        window.myDoughnut = new Chart(ctx).Doughnut(doughnutData, { responsive: false });
    }
    function Canvas2() {
        var randomScalingFactor = function () { return Math.round(Math.random() * 100) };
        var lineChartData = {
            labels: ["星期一", "星期二", "星期三", "星期四", "星期五", "星期六", "星期日"],
            datasets: [
                {
                    fillColor: "rgba(220,220,220,0.2)",
                    strokeColor: "rgba(220,220,220,1)",
                    pointColor: "rgba(220,220,220,1)",
                    pointStrokeColor: "#fff",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "rgba(220,220,220,1)",
                    data: [randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor()]
                }
            ]
        }
        var ctx = document.getElementById("Canvas2").getContext("2d");
        window.myLine = new Chart(ctx).Line(lineChartData, {
            bezierCurve: false,
        });
    }
    function Canvas3() {
        var randomScalingFactor = function () { return Math.round(Math.random() * 100) };
        var lineChartData = {
            labels: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "8月", "10月", "11月", "12月"],
            datasets: [
                {
                    fillColor: "#578ebe",
                    data: [randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor()]
                }
            ]
        }
        var ctx = document.getElementById("Canvas3").getContext("2d");
        window.myLine = new Chart(ctx).Bar(lineChartData, {
            bezierCurve: false,

        });
    }
</script>
<div id="areascontent">
    <div class="rows" style="margin-bottom: 0.8%; overflow: hidden;">
        <div style="float: left; width: 69.2%;">
            <div style="height: 100%; border: 1px solid #e6e6e6; overflow: hidden;">
                <div class="dashboard-stats">
                    <div class="dashboard-stats-item" style="background-color: #578ebe;">
                        <div class="stat-icon">
                            <i class="fa fa-clock-o"></i>
                        </div>
                        <h2 class="m-top-none"><?= $arrVehicleRentalData['rentingCount'] ?><span>个</span></h2>
                        <h5>租赁登记</h5>
                    </div>
                </div>
                <div class="dashboard-stats">
                    <div class="dashboard-stats-item" style="background-color: #e35b5a;">
                        <h2 class="m-top-none"><?= $arrVehicleStateData['waitingRentVehicleCount']; // $arrVehicleRentalData['bookingCount'] ?><span>台</span></h2>
                        <h5>预定待租</h5>
                        <div class="stat-icon">
                            <i class="fa fa-bell"></i>
                        </div>
                    </div>
                </div>
                <div class="dashboard-stats">
                    <div class="dashboard-stats-item" style="background-color: #44b6ae;">
                        <h2 class="m-top-none"><?= $arrVehicleStateData['dispatchingVehicleCount'] ?><span>台</span></h2>
                        <h5>车辆调度</h5>
                        <div class="stat-icon">
                            <i class="fa fa-envelope-o"></i>
                        </div>
                    </div>
                </div>
                <div class="dashboard-stats">
                    <div class="dashboard-stats-item" style="background-color: #8775a7; margin-right: 0px;">
                        <h2 class="m-top-none"><?= $arrVehicleRentalData['curDayTakingCount'] ?><span>台</span></h2>
                        <h5>今日取车</h5>
                        <div class="stat-icon">
                            <i class="fa fa-gavel"></i>
                        </div>
                    </div>
                </div>
                <div class="dashboard-stats">
                    <div class="dashboard-stats-item" style="background-color: #4f5c65; margin-bottom: 0px;">
                        <h2 class="m-top-none"><?= $arrVehicleRentalData['curDayReturningCount'] ?><span>台</span></h2>
                        <h5>今日还车</h5>
                        <div class="stat-icon">
                            <i class="fa fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
                <div class="dashboard-stats">
                    <div class="dashboard-stats-item" style="background-color: #14aae4; margin-bottom: 0px;">
                        <h2 class="m-top-none"><?= $arrVehicleRentalData['overtimeRentingCount'] ?><span>件</span></h2>
                        <h5>逾期订单</h5>
                        <div class="stat-icon">
                            <i class="fa fa-file-text-o"></i>
                        </div>
                    </div>
                </div>
                <!-- <div class="dashboard-stats">
                    <div class="dashboard-stats-item" style="background-color: #949FB1; margin-bottom: 0px;">
                        <h2 class="m-top-none"><?= \backend\components\StatisticsService::getTodaySignupedUsers() ?><span>位</span></h2>
                        <h5>客户信息</h5>
                        <div class="stat-icon">
                            <i class="fa fa-coffee"></i>
                        </div>
                    </div>
                </div> -->
                <div class="dashboard-stats">
                    <div class="dashboard-stats-item" style="background-color: #949FB1; margin-bottom: 0px;">
                        <h2 class="m-top-none"><?= \backend\components\StatisticsService::getReturnDepositOrderCountByUser() ?><span>位</span></h2>
                        <h5>押金到期</h5>
                        <div class="stat-icon">
                            <i class="fa fa-coffee"></i>
                        </div>
                    </div>
                </div>
                <div class="dashboard-stats">
                    <div class="dashboard-stats-item" style="background-color: #f29503; margin-right: 0px; margin-bottom: 0px;">
                        <h2 class="m-top-none"><?= $arrVehicleRentalData['violationCount'] ?><span>个</span></h2>
                        <h5>待处理违章</h5>
                        <div class="stat-icon">
                            <i class="fa fa-rmb" style="padding-left: 10px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style="float: right; width: 30%;">
            <div style="height: 221px; border: 1px solid #e6e6e6; background-color: #fff;">
                <div class="panel panel-default">
                    <div class="panel-heading"><i class="fa fa-area-chart fa-lg" style="padding-right: 5px;"></i>门店当日订单</div>
                    <div class="panel-body">
                        <canvas id="Canvas2" style="height: 165px; width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="rows" style="margin-bottom: 0.8%; overflow: hidden;">
        <div style="float: left; width: 69.2%;">
            <div style="height: 290px; border: 1px solid #e6e6e6; background-color: #fff;">
                <div class="panel panel-default">
                    <div class="panel-heading"><i class="fa fa-bar-chart fa-lg" style="padding-right: 5px;"></i>门店每日成交额</div>
                    <div class="panel-body">
                        <canvas id="Canvas3" style="height: 230px; width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div style="float: right; width: 30%;">
            <div style="height: 290px; border: 1px solid #e6e6e6; background-color: #fff;">
                <div class="panel panel-default">
                    <div class="panel-heading"><i class="fa fa-pie-chart fa-lg" style="padding-right: 5px;"></i>车辆状态</div>
                    <div class="panel-body">
                        <canvas id="Canvas1" style="height: 180px; width: 100%; margin-top: 10px;"></canvas>
                        <div style="text-align: center; padding-top: 15px;">
                            <span><i class="fa fa-square" style="color: #F7464A; font-size: 20px; padding-right: 5px; vertical-align: middle; margin-top: -3px;"></i>在租</span>
                            <span style="margin-left: 10px;"><i class="fa fa-square" style="color: #46BFBD; font-size: 20px; padding-right: 5px; vertical-align: middle; margin-top: -3px;"></i>待租</span>
                            <span style="margin-left: 10px;"><i class="fa fa-square" style="color: #FDB45C; font-size: 20px; padding-right: 5px; vertical-align: middle; margin-top: -3px;"></i>维修</span>
                            <span style="margin-left: 10px;"><i class="fa fa-square" style="color: #949FB1; font-size: 20px; padding-right: 5px; vertical-align: middle; margin-top: -3px;"></i>保养</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="rows" style="overflow: hidden;">
        <div style="float: left; width: 33.8%; margin-right: 0.8%;">
            <div style="height: 240px; border: 1px solid #e6e6e6; background-color: #fff;">
                <div class="panel panel-default">
                    <div class="panel-heading"><i class="fa fa-send fa-lg" style="padding-right: 5px;"></i>企业文化</div>
                    <div class="panel-body">

                    </div>
                </div>
            </div>
        </div>
        <div style="float: left; width: 34.6%; margin-right: 0.8%;">
            <div style="height: 240px; border: 1px solid #e6e6e6; background-color: #fff;">
                <div class="panel panel-default">
                    <div class="panel-heading"><i class="fa fa-rss fa-lg" style="padding-right: 5px;"></i>通知公告</div>
                    <div class="panel-body">

                    </div>
                </div>
            </div>
        </div>
        <div style="float: right; width: 30%;">
            <div style="height: 240px; border: 1px solid #e6e6e6; background-color: #fff;">
                <div class="panel panel-default">
                    <div class="panel-heading"><i class="fa fa-thumbs-o-up fa-lg" style="padding-right: 5px;"></i>最新签约</div>
                    <div class="panel-body">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
#copyrightcontent {
    height: 30px;
    line-height: 29px;
    overflow: hidden;
    position: absolute;
    top: 100%;
    margin-top: -30px;
    width: 100%;
    background-color: #fff;
    border: 1px solid #e6e6e6;
    padding-left: 10px;
    padding-right: 10px;
}

.dashboard-stats {
    float: left;
    width: 25%;
}

.dashboard-stats-item {
    position: relative;
    overflow: hidden;
    color: #fff;
    cursor: pointer;
    height: 105px;
    margin-right: 10px;
    margin-bottom: 10px;
    padding-left: 15px;
    padding-top: 20px;
}

.dashboard-stats-item .m-top-none {
    margin-top: 5px;
}

.dashboard-stats-item h2 {
    font-size: 28px;
    font-family: inherit;
    line-height: 1.1;
    font-weight: 500;
    padding-left: 70px;
}

.dashboard-stats-item h2 span {
    font-size: 12px;
    padding-left: 5px;
}

.dashboard-stats-item h5 {
    font-size: 12px;
    font-family: inherit;
    margin-top: 1px;
    line-height: 1.1;
    padding-left: 70px;
}

.dashboard-stats-item .stat-icon {
    position: absolute;
    top: 18px;
    font-size: 50px;
    opacity: .3;
}

.dashboard-stats i.fa.stats-icon {
    width: 50px;
    padding: 20px;
    font-size: 50px;
    text-align: center;
    color: #fff;
    height: 50px;
    border-radius: 10px;
}

.panel-default {
    border: none;
    border-radius: 0px;
    margin-bottom: 0px;
    box-shadow: none;
    -webkit-box-shadow: none;
}

.panel-default > .panel-heading {
    color: #777;
    background-color: #fff;
    border-color: #e6e6e6;
    padding: 10px 10px;
}

.panel-default > .panel-body {
    padding: 10px;
    padding-bottom: 0px;
}

.panel-default > .panel-body ul {
    overflow: hidden;
    padding: 0;
    margin: 0px;
    margin-top: -5px;
}

.panel-default > .panel-body ul li {
    line-height: 27px;
    list-style-type: none;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.panel-default > .panel-body ul li .time {
    color: #a1a1a1;
    float: right;
    padding-right: 5px;
}
</style>

