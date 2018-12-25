<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'About';
$this->params['breadcrumbs'][] = $this->title;
?>
<!DOCTYPE html>
<html lang="en" ng-app="app" ng-controller="appCtrl">
<head>
    <meta charset="UTF-8">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <link href="<?= $ho?>/PC/ykzc_new/common/css/bootstrap.min.css" rel="stylesheet" type="text/css" >
    <link href="<?= $ho?>/PC/ykzc_new/public/css/main.css" rel="stylesheet" type="text/css" >
    <link href="<?= $ho?>/PC/ykzc_new/common/css/plugin.css" rel="stylesheet" type="text/css" >
    <link href="<?= $ho?>/PC/ykzc_new/query/css/storeQuery.css" rel="stylesheet" type="text/css" >
    <meta name="description" content="易卡租车，始终致力于为个人、家庭、休闲旅客及企业提供低成本、高价值的汽车租赁服务体验。">
    <meta name="keywords" content="<?= $objOrder->name?>租车,<?= $objOrder->name?>租车公司,<?= $objOrder->name?>租车网,<?= $objOrder->name?>租车费用,<?= $objOrder->name?>租车门店">
    <title><?= $objOrder->name?>租车-<?= $objOrder->name?>租车公司-<?= $objOrder->name?>租车门店查询【易卡租车网】</title>
    <script>
        var _hmt = _hmt || [];
        (function() {
            var hm = document.createElement("script");
            hm.src = "https://hm.baidu.com/hm.js?9e5610c8a5bf7be493e91a0efd541fcb";
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(hm, s);
        })();
    </script>

    <link rel="stylesheet" href="http://api.map.baidu.com/library/SearchInfoWindow/1.5/src/SearchInfoWindow_min.css" />
</head>
<body ng-controller="queryStoreCtrl">

<div class="wrapper">
    <!--引入头部-->
    <!-- <div ng-include="'<?= $ho?>/PC/ykzc_new/public/html/header.html'"></div> -->
    <nav class="navbar navbar-inverse navbar-index" role="navigation">
	    <div class="container-fluid">
	        <div class="navbar-header">
	            <a class="navbar-brand logo" style="font-size: 0px" href="<?= $ho?>">
	                <img src="<?= $ho?>/PC/ykzc_new/public/img/logo.png"/>
	                易卡租车/yikazc.com
	            </a>
	        </div>
	        <div>
	            <ul class="nav navbar-nav da-thumbs" id="da-thumbs">
	                <li class="active index"><a style="background-color: #f1d34b;" href="<?= $ho?>">首页<div><span></span></div></a></li>
	                <li class="dropdown">
	                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">租车服务<b class="caret"></b></a>
	                    <ul class="dropdown-menu">
	                        <li><a  href="<?= $ho?>/PC/ykzc_new/book/shortOrder.html">短租服务</a></li>
	                        <li><a  href="<?= $ho?>/PC/ykzc_new/book/monthOrder.html">长租服务</a></li>
	                        <li><a  href="<?= $ho?>/PC/ykzc_new/book/packageOrder.html">3天打包价</a></li>
	                        <li><a  href="<?= $ho?>/PC/ykzc_new/book/packageWeek.html">7天打包价</a></li>
	                    </ul>
	                </li>

	                <!--<li><a href="<?= $ho?>/PC/ykzc_new/index/enterprise.html">企业用车</a></li>-->
	                <li><a href="<?= $ho?>/PC/ykzc_new/book/shortOrder.html">车型查询</a></li>
	                <li><a href="<?= $ho?>/app/carrental/frontend/web/index.php/porder/store?city_code=0579">门店查询</a></li>
	                <!--<li><a href="#">单程往返</a></li>-->
	                <!--<li><a href="#">顺风专车</a></li>-->
	                <li><a href="<?= $ho?>/PC/ykzc_new/index/invest.html">投资加盟</a></li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/activity/activity.html">易卡活动</a></li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/help-new.html">帮助中心</a></li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/callMe.html">关于易卡</a></li>
	            </ul>
	        </div>
	        <div>
	            <ul class="nav navbar-nav right" >
	                <li ng-show="!login"><a href="<?= $ho?>/PC/ykzc_new/login/login.html" >登录/注册</a></li>
	                <li class="dropdown myyk" ng-show="login">
	                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">我的易卡<span class="badge badge-hot badge-sign">签</span><b class="caret"></b></a>
	                    <ul class="dropdown-menu">
	                        <li><a href="<?= $ho?>/PC/ykzc_new/myYk/myYK.html">个人中心</a></li>
	                        <!--<li><a href="#" data-toggle="modal" data-target="#signModal">签到</a></li>-->
	                        <li><a href="#" ng-click="userExit()">退出</a></li>
	                    </ul>
	                </li>
	                <li><a href="tel:400-786-0101"><i class="glyphicon glyphicon-earphone"></i>400-876-0101</a></li>
	            </ul>
	        </div>
	    </div>
	</nav>
	<style>
	    .navbar-index{
	        background: #16314b;
	    }
	</style>
    <div class="container">
        <!--面包屑-->
        <div class="website">
            当前位置：<a href="<?= $ho?>/PC/ykzc_new/index/index.html">首页</a> - <a href="<?= $ho?>/PC/ykzc_new/query/storeQuery.html">门店查询</a> - <span>门店</span>
        </div>

        <!--开始渲染-->
        <div class="store-box">
            <div class="panel panel-city col-xs-10">
                <div class="line-city">
                    <b class="store"><span class="citynames" style="margin: 0"><?= $objOrder->name?></span>门店</b>
                </div>
                <div class="line-city">
                    <a href="<?= $ho?>/PC/ykzc_new/query/storeQuery.html" class="store">【更换城市】</a>
                    <span>热门城市：</span>
                    <!--<a href="#hz" onclick="location.reload()"> 杭州</a>-->
                    <a href="<?= $ho?>/app/carrental/frontend/web/index.php/porder/store?city_code={{x.cid}}" ng-repeat="x in hotCityList" ng-bind="x.city" onclick="location.reload()"> 杭州</a>
                </div>
            </div>


            <div class="panel panel-store">
                <div class="panel-header">
                    <p class="city" ><span class="citynames"><?= $objOrder->name?></span></p>
                    <p>共<b class="num">{{querySre.length}}</b>家门店</p>

                </div>
                <div class="panel-body scroll"  >
                    <div class="box" ng-repeat="x in querySre">
                        <div class="box-title">

                           <label class=> {{x.shortName}}</label>
                        </div>

                        <div class="box-body" >
                            <p>地址：{{x.address}}</p>
                            <p>门店电话：{{x.phone}}</p>
                            <p>营业时间：{{x.workTime}}</p>
                        </div>
                        <a href="<?= $ho?>/PC/ykzc_new/book/shortOrder.html?takeCity=<?= $objOrder->name?>&takeStore={{x.shop_name}}&isTake=false&returnCity=<?= $objOrder->name?>&returnStore={{x.shop_name}}&isReture=false">前往租车>>></a>
                    </div>

                </div>
            </div>
        </div>
        <div class="panel panel-map col-xs-10">
             <div id="map"></div>
        </div>
    </div>

    <!--引入底部-->
    <!-- <div ng-include="'<?= $ho?>/PC/ykzc_new/public/html/footer.html'"></div> -->
    <div class="sidebar">

    <div class="box box-side"><a href="<?= $ho?>/PC/ykzc_new/myYk/myOrder.html">订单</a></div>
    <div class="box box-side phone">
        <a href="" class="app app-link">手机版</a>
        <div class="box box-app">
            <div class="left">
                <img src="<?= $ho?>/PC/ykzc_new/public/img/downEWM.jpg">
            </div>
            <div class="download right">
                <p class="text-center">扫描二维码下载<br>易卡租车手机版</p>
                <a href="https://itunes.apple.com/cn/app/%E6%98%93%E5%8D%A1%E7%A7%9F%E8%BD%A6-%E6%96%B0%E7%94%A8%E6%88%B7%E9%A6%96%E7%A7%9F%E7%A7%9F%E4%B8%80%E9%80%81%E4%B8%80/id1183650719?mt=8" class="btn btn-block btn-warning">iPhone版下载</a>
                <a href="http://android.myapp.com/myapp/detail.htm?apkName=com.jinhua.yika&ADTAG=mobile" class="btn btn-block btn-warning">Android版下载</a>
            </div>
        </div>
    </div>
    <div class="box box-side"><a href="javascript:   $('html,body').stop().animate({scrollTop:0},500);">返回顶部</a></div>
    <script>
        $('.box-side.phone').hover(function(){
            $('.box-app').fadeIn();
        },function(){
            $('.box-app').fadeOut();
        })

    </script>
	</div>
	<footer>
	    <div class="container footers">
	        <div class="city-link">
	            <p class="text-center">城市快捷入口</p>
	            <hr>
	            <ul class="list-left" ng-hide="!cityDoorKuaiSu">
	                <li ng-repeat="x in cityDoorKuaiSu"><a href="<?= $ho?>/app/carrental/frontend/web/index.php/porder/store?city_code={{x.cid}}" onclick="location.reload()" >{{x.city}}租车</a></li>
	            </ul>
	            <ul class="list-left" ng-hide="cityDoorKuaiSu">
	                <li><a href="<?= $ho?>/app/carrental/frontend/web/index.php/porder/store?city_code=0571" onclick="location.reload()" >杭州租车</a></li>
	                <li><a href="<?= $ho?>/app/carrental/frontend/web/index.php/porder/store?city_code=027" onclick="location.reload()" >武汉租车</a></li>
	                <li><a href="<?= $ho?>/app/carrental/frontend/web/index.php/porder/store?city_code=0574" onclick="location.reload()" >宁波租车</a></li>
	                <li><a href="<?= $ho?>/app/carrental/frontend/web/index.php/porder/store?city_code=0577" onclick="location.reload()" >温州租车</a></li>
	                <li><a href="<?= $ho?>/app/carrental/frontend/web/index.php/porder/store?city_code=0579" onclick="location.reload()" >金华租车</a></li>
	                <li><a href="<?= $ho?>/app/carrental/frontend/web/index.php/porder/store?city_code=0579-1" onclick="location.reload()" >义乌租车</a></li>
	                <li><a href="<?= $ho?>/app/carrental/frontend/web/index.php/porder/store?city_code=0579-12" onclick="location.reload()" >兰溪租车</a></li>
	                <li><a href="<?= $ho?>/app/carrental/frontend/web/index.php/porder/store?city_code=0579-10" onclick="location.reload()" >永康租车</a></li>
	                <li><a href="<?= $ho?>/app/carrental/frontend/web/index.php/porder/store?city_code=0579-11" onclick="location.reload()" >东阳租车</a></li>
	                <li><a href="<?= $ho?>/app/carrental/frontend/web/index.php/porder/store?city_code=0579-2" onclick="location.reload()" >武义租车</a></li>
	            </ul>
	        </div>
	        <hr>
	        <div class="area-help">
	            <ul class="sp-l">
	                <li class="title">租车预订说明</li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/help-ruler.html#/rule1">服务时间</a></li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/help-ruler.html#/rule3">服务预定</a></li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/help-ruler.html#/rule5">租车资格</a></li>
	            </ul>
	            <ul class="sp-l sp-l-f">
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/help-ruler.html#/rule2">待租车况</a></li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/help-ruler.html#/rule4">短租产品</a></li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/help-ruler.html#/rule6">取还车说明</a></li>
	            </ul>
	            <ul>
	                <li class="title">会员服务</li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/login/forget.html">忘记密码</a></li>
	                <li><a href="#">会员充值</a></li>
	                <li><a href="#">积分说明</a></li>
	            </ul>
	            <ul>
	                <li class="title">紧急事务处理</li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/help-ruler.html#/rule7">保险责任</a></li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/help-ruler.html#/rule8">事故处理</a></li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/help-ruler.html#/rule9">理赔说明</a></li>
	            </ul>
	            <ul>
	                <li class="title">租车费用及结算</a></li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/help-ruler.html#/rule10">价格说明</a></li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/help-ruler.html#/rule12">结算流程</a></li>
	            </ul>
	            <ul>
	                <li class="title">帮助中心</li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/help-qa.html">常见问题</a></li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/help-new.html">新手上路</a></li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/help-ruler.html">服务规则</a></li>
	            </ul>
	            <!--<ul>-->
	                <!--<li class="title">-->
	                    <!--<img  src="<?= $ho?>/PC/ykzc_new/public/img/wchat.png" />-->
	                    <!--<img  src="<?= $ho?>/PC/ykzc_new/public/img/wchat.png" />-->
	                <!--</li>-->
	            <!--</ul>-->
	        </div>
	        <div class="wchat right">
	            <div style="float: left;margin-right: 20px">
	                <img  src="<?= $ho?>/PC/ykzc_new/public/img/wchat.png" />
	                <p style="text-align: center">扫描关注公众号</p>
	            </div>

	            <div style="float: left">
	                <img src="<?= $ho?>/PC/ykzc_new/public/img/downEWM.png" />
	                <p style="text-align: center">扫描下载APP</p>
	            </div>

	        </div>
	        <hr>
	        <div class="webmap">
	            <ul class="list-inline">
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/callMe.html">关于易卡</a></li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/getJob.html">招贤纳士</a></li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/down.html">移动客户端</a></li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/help-ruler.html">帮助中心</a></li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/webMap.html">网站导航</a></li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/help/callMe.html">联系我们</a></li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/news/news.html">新闻中心</a></li>
	                <li><a href="<?= $ho?>/PC/ykzc_new/index/invest.html">加盟合作</a></li>
	                <!--<li><a href="#">友情链接</a></li>-->
	                <li>客服热线：400-876-0101</li>
	                <li><a href="http://wpa.qq.com/msgrd?v=3&uin=1356650459&site=qq&menu=yes">客服QQ</a></li>
	            </ul>
	            <p class="copy"> Copyright © 2017-2018 All Rights Reserved. 版权所有：金华市易卡汽车租赁有限公司
	                <img src="<?= $ho?>/PC/ykzc_new/public/img/icp.png" style="    margin-top: -3px;  margin-right: 3px;" alt="">
	                <a href="http://www.beian.gov.cn/portal/registerSystemInfo">浙公网安备 33071802100171号</a>
	                <a href="http://www.miitbeian.gov.cn/publish/query/indexFirst.action">浙ICP备13016103号-1</a>
	            </p>
	            <p class="link">友情链接：<a href="#" target="_blank">浙江车纷期网络科技有限公司</a>
	                <a href="http://www.ctrip.com/" target="_blank">携程网</a></p>
	        </div>
	    </div>
	</footer>
<script>

    console.log("%c 警告！", "text-shadow: 5px 5px 1px grey;font-size:100px;color:red;font-weight:700;margin-left:400px");
    console.log("%c 您现在打开的窗口是黑客专用交流区，请不要在上面粘贴复制任何代码，否则您可能将遭到黑客的攻击！","font-size:2em;font-weight:700");
    //    console.log('%c易卡租车', 'background-image:-webkit-gradient( linear, left top, right top, color-stop(0, #f22), color-stop(0.15, #f2f), color-stop(0.3, #22f), color-stop(0.45, #2ff), color-stop(0.6, #2f2),color-stop(0.75, #2f2), color-stop(0.9, #ff2), color-stop(1, #f22) );color:transparent;-webkit-background-clip: text;font-size:3em;');
</script>
<!--SEO-->
<script>
    (function(){
        var bp = document.createElement('script');
        var curProtocol = window.location.protocol.split(':')[0];
        if (curProtocol === 'https'){
            bp.src = 'https://zz.bdstatic.com/linksubmit/push.js';
        }
        else{
            bp.src = 'http://push.zhanzhang.baidu.com/push.js';
        }
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(bp, s);
    })();
</script>
<script src="<?= $ho?>/PC/ykzc_new/common/js/md5.js"></script>



    <!-- 底部 -->
</div>
<div class="modal fade" id="signModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog sign">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    签到送积分
                    <div class="badge badge-exp">积分可用于兑换租车抵用</div>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="inline-box col-xs-7">
                        <div style="width:100%;height:300px;" id="calendar"></div>
                    </div>
                    <div class="inline-box col-xs-5 box-sign">
                        <div class="btn-group-sign sign" ng-init="sign=false">
                            <button class="btn btn-warning" ng-hide="sign">签到</button>
                            <button class="btn btn-warning" disabled ng-show="sign">已签到</button>
                        </div>
                        <div class="box-content">
                            <p>每日登陆签到即可获得积分发放</p>
                            <a href="javascript:;" class="right rule popover-show"
                               data-container="body"
                               data-html="true"
                               data-toggle="popover"
                               data-content="<ul><li>1. 签到1天可获得1点积分</li>
                               <li>2. 连续签到2天当天可获得2点积分</li>
                               <li>3. 连续签到3天当天可获得3点积分</li>
                               <li>4. 连续签到4天当天可获得4点积分</li>
                               <li>5. 连续签到5天当天可获得5点积分</li>
                               <li>6. 连续签到6天当天可获得6点积分</li>
                               <li>7. 连续签到7天当天可获得7点积分</li>
                               <li>8. 连续签到7天以上当天均获得7点积分</li>
                               <li>9. 连续签到中断过后，将从第一天开始重新计算</li>">详见签到发放规则>></a>
                            <a href="#" class="btn btn-lg btn-default btn-reward">点击领取奖励</a>
                        </div>
                        <div class="box-footer">
                            <div class="col-xs-4">
                                <p>获得积分(点)</p>
                                <div class="count">300</div>
                            </div>
                            <div class="col-xs-4">
                                <p>连续签到(天)</p>
                                <div class="count">3</div>
                            </div>
                            <div class="col-xs-4">
                                <p>累计签到(天)</p>
                                <div class="count">3</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal -->
</div>

<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=FiyvLDHpCL6fUaiIhAHk0YtiRfFSCUc1"></script>
<script type="text/javascript" src="http://api.map.baidu.com/library/SearchInfoWindow/1.5/src/SearchInfoWindow_min.js"></script>
<script src='<?= $ho?>/PC/ykzc_new/common/js/jquery.min.js'></script>
<script src='<?= $ho?>/PC/ykzc_new/common/js/tool.js'></script>
<script src='<?= $ho?>/PC/ykzc_new/common/js/bootstrap.min.js'></script>
<script src='<?= $ho?>/PC/ykzc_new/common/js/angular.min.js'></script>
<script src='<?= $ho?>/PC/ykzc_new/common/js/jquery.onoff.min.js'></script>
<script src='<?= $ho?>/PC/ykzc_new/common/js/mousewheel.js'></script>
<!--<script src='<?= $ho?>//common/js/easyscroll.js'></script>-->
<script src='<?= $ho?>/PC/ykzc_new/common/js/hDate.js'></script>
<script src='<?= $ho?>/PC/ykzc_new/public/js/app.js'></script>
<script src='<?= $ho?>/PC/ykzc_new/public/js/store.js'></script>
<script src='<?= $ho?>/PC/ykzc_new/public/js/main.js'></script>
<script src='<?= $ho?>/PC/ykzc_new/public/js/map.js'></script>
<script src='<?= $ho?>/PC/ykzc_new/query/js/query.js'></script>

<style>
    .scroll_absolute{
        width: 240px !important;
    }
</style>
</body>
</html>