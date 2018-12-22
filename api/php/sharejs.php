<?php
require_once "jssdk.php";
//此处填写appid和appsecret
$jssdk = new JSSDK("wxa0b2c7d9bee03fad", "9b60a5bcef5ae2d75cc33dd83edf1601");
// $jssdk = new JSSDK("wxad7195f2a9f765bb", "");
//改成分享页面的地址
$signPackage = $jssdk->GetSignPackage($_SERVER['HTTP_REFERER']);
echo "<pre>";
print_r($signPackage);
echo "</pre>";die;
?>

wx.config({
    debug: false,
    appId: '<?php echo $signPackage["appId"];?>',
    timestamp: <?php echo $signPackage["timestamp"];?>,
    nonceStr: '<?php echo $signPackage["nonceStr"];?>',
    signature: '<?php echo $signPackage["signature"];?>',
    jsApiList: ['showOptionMenu','onMenuShareTimeline', 'onMenuShareAppMessage','showMenuItems', 'hideMenuItems']
  });
wx.ready(function () {
    wx.showOptionMenu();
    // 在这里调用 API
    ////分享到朋友圈
    //wx.onMenuShareTimeline({
    //    title: '分享标题', // 分享标题
    //    link: '', // 分享链接
     //   imgUrl: '', // 分享图标,必须是http开头的完整路径

    //});
    //分享到好友
    wx.onMenuShareAppMessage({
        title: '分享标题', // 分享标题
        desc: '分享给朋友时的描述文字', // 分享描述
        link: 'http://www.yikazc.com/public/logo.png', // 分享链接
        imgUrl: 'http://www.yikazc.com/public/logo.png', // 分享图标,必须是http开头的完整路径
        type: 'link', // 分享类型,music、video或link，不填默认为link
        dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
    });

    <!-- //分享到好友
    wx.updateAppMessageShareData({
        title: '易卡',
        desc: '易卡挂靠车辆信息',
        link:'',
        imgUrl: '',
    });
    //分享到朋友圈
     wx.updateTimelineShareData({
            title: '易卡', // 分享标题
            desc:'易卡挂靠车辆信息',
            link: '', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
            imgUrl: '', // 分享图标

    }); -->
});
wx.error(function(res){
    console.log(res);
    // config信息验证失败会执行error函数，如签名过期导致验证失败，具体错误信息可以打开config的debug模式查看，也可以在返回的res参数中查看，对于SPA可以在这里更新签名。
});