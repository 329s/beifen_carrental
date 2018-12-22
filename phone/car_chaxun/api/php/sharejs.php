<?php
require_once "jssdk.php";
//此处填写appid和appsecret
// $jssdk = new JSSDK("wxa0b2c7d9bee03fad", "9b60a5bcef5ae2d75cc33dd83edf1601");
$jssdk = new JSSDK("wxad7195f2a9f765bb", "d0ba8bd2aba86092d3ed00dfd2a6d790");
//改成分享页面的地址
$signPackage = $jssdk->GetSignPackage($_SERVER['HTTP_REFERER']);
// echo "<pre>";
// print_r($signPackage);
// echo "</pre>";die;

?>

wx.config({
    debug: false,
    appId: '<?php echo $signPackage["appId"];?>',
    timestamp: <?php echo $signPackage["timestamp"];?>,
    nonceStr: '<?php echo $signPackage["nonceStr"];?>',
    signature: '<?php echo $signPackage["signature"];?>',
    jsApiList: ['showOptionMenu','onMenuShareTimeline', 'onMenuShareAppMessage','showMenuItems', 'hideMenuItems','updateTimelineShareData']
  });
wx.ready(function () {
    wx.showOptionMenu();
    // 在这里调用 API
    //分享到朋友圈
    wx.onMenuShareTimeline({
        title: '把车放到易卡出租安心赚租金！', // 分享标题
        desc:'车辆实时定位，租车订单透明,做易卡的车主，轻松赚车租金！',
        link:'<?php echo $signPackage["url"];?>',
        imgUrl: 'https://www.yikazc.com/public/logo.png', // 分享图标,必须是http开头的完整路径

    });
    //分享到好友
    wx.onMenuShareAppMessage({
        title: '把车放到易卡出租安心赚租金！', // 分享标题
        desc: '车辆实时定位，租车订单透明,做易卡的车主，轻松赚车租金！', // 分享描述

        link:'<?php echo $signPackage["url"];?>',
        imgUrl: 'https://www.yikazc.com/public/logo.png', // 分享图标,必须是http开头的完整路径

    });

    


});
wx.error(function(res){
    console.log(res);
    // config信息验证失败会执行error函数，如签名过期导致验证失败，具体错误信息可以打开config的debug模式查看，也可以在返回的res参数中查看，对于SPA可以在这里更新签名。
});
