<?php
return [
    'adminEmail' => 'kevinyjn@foxmail.com',
    'supportEmail' => 'kevinyjn@foxmail.com',
    'user.passwordResetTokenExpire' => 3600,
    
    'app.host' => 'http://www.yikazc.com',
    // 'app.host' => 'http://gm.yikazc.com:8010',
    'app.relative' => 'app/carrental',
    
    'mob.sms.appkey' => '1408a2894205a',    // mob 短信验证码 appKey
    'mob.sms.enabled' => true,
    'mob.sms.sendmsgurl' => 'https://webapi.sms.mob.com/sms/sendmsg',//PC端发送手机验证码接口
    'mob.sms.verify' => 'https://webapi.sms.mob.com/sms/verify',//PC端发送手机验证码验证接口
    'mob.sms.sendurl' => 'https://webapi.sms.mob.com/custom/msg',
    'mob.identity.appkey' => '1408869dde09b',    // mob 身份证验证 appKey
    'mob.identify.enabled' => true,
    
    'component.sms.class' => '\common\components\SmsSdkMob',
    
    // 'map.gaode.appkey' => '431089dcc92115d5269f158002c08c95',
    'map.gaode.appkey' => '4d522eb9bfec98c20bf64aaa0a68f4d8',
    'map.gaode.jsappkey' => '59b1fc0e528d9dcf4f5af226c9b78f19',
    
    'payment.weixin.appid' => 'wxc0e537007f4c47f7',
    'payment.weixin.appkey' => '31e2f47c6c9f5a03a3ee104517b673b1',  // app secret
    'payment.weixin.mch_id' => '1395342202',
    'payment.weixin.pay_unifiedorder' => 'https://api.mch.weixin.qq.com/pay/unifiedorder',
    
    'payment.alipay.appid' => '2015120200900773',
    'payment.alipay.alipaypubkey' => "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkr
IvYISRcc73s3vF1ZT7XN8RNPwJxo8pWaJMmvyTn9N4HQ632qJBVHf8sxHi/fEsra
prwCtzvzQETrNRwVxLO5jVmRGi60j8Ue1efIlzPXV9je9mkjzOmdssymZkh2QhUr
CmZYI/FCEa3/cNMW0QIDAQAB
-----END PUBLIC KEY-----
",
    'payment.alipay.apppubkey' => "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCi4EJWvQjMYJBCkblP331zGtgk
L6zopxiajS6ng+DDIxRPfdw54A3vLasHahu+7ZXaIgBrpEJGl2i1Z7vp6PxBzgEB
IJygmCGnyEv93HBZxO3GzbR/8Nb/sxlIjn9LeBzb9bdEL+dHThNr60lhg+5l9aAT
BfZI1U4+73X1skL6gwIDAQAB
-----END PUBLIC KEY-----
",
    'payment.alipay.appprikey' => "-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQCi4EJWvQjMYJBCkblP331zGtgkL6zopxiajS6ng+DDIxRPfdw5
4A3vLasHahu+7ZXaIgBrpEJGl2i1Z7vp6PxBzgEBIJygmCGnyEv93HBZxO3GzbR/
8Nb/sxlIjn9LeBzb9bdEL+dHThNr60lhg+5l9aATBfZI1U4+73X1skL6gwIDAQAB
AoGAPkDK+k4sQ7mQyfDazH2WfF1BSatzQkVDsSbPMzXDvbH1vGBVwUUy7j0dRqs2
yaYThZlDYeEZd9O9MMDiYQgtCfmbsTOFgELrS2B7mmtkoVSac6Jy08HiRLpoDp/t
Tnre22WdjjfPgvcql7r8X5rUuVtsAhd03+p+BSXVrIfW33kCQQDTZjwF8oUQNKfm
eJoyBKsTJCGxJPz8HxATHLhtGb78PxBI1zME0OlnU1cOx4yRwlL+EuP61M+e2U1f
2ypyzhw3AkEAxT1CWQJjzWKePinSEnL+FLBEL7fjd08aguHePek9N0l10p47Umqk
mJ+ILXVeHccEYbE+qkGKOagENsELJemmFQJBALrtJZokrmB8DxMOBVrBIfyU8G3R
RRoy5WXg+XsPTv+BTAb8sxJJIHnLCan57WRdrkEXtxtu3f+aKn7eLxTBB9UCQQCA
v0L+zPOOjnASzcOlbVBy+rgXmeYt7AG3K26hZQ0jeJ0jNUwtuRTl10TRv4oGz/EJ
P+RSyGNMzWKqxwna3pAdAkA+0s0fNOHuPSk1iRKDdirKg6xy8WxV1F4ghCepzr8I
Eukw1b5PibWWgwDPW4z83h5wGctZa0LycexSg6AB8CBY
-----END RSA PRIVATE KEY-----
",
    'payment.alipay.pid' => '2088411974941635',
    'payment.alipay.pay_unifiedorder' => 'https://api.mch.weixin.qq.com/pay/unifiedorder',
    
    'lan_locale' => 'zh_CN',
    'app.company.name' => '易卡租车',
    'app.company.fullname' => '金华市易卡汽车租赁有限公司',
    'app.management.name' => '易卡租车管理系统',
    'app.copyright.name' => '租车系统开发者',
    'clientGameapiDomain' => 'localhost:8015',

    // sjj
    'alipay' =>array(
        //应用ID,您的APPID。
        'app_id' => "2017121100556083",

        //商户私钥
        'merchant_private_key' => "MIIEpQIBAAKCAQEA16kixL/s3PD2PfCs762SRKb6qH/Npj8baN/nhEE23kuJCzeO7G2PdxRBaFmj84Ee8cOCP3Tm3K5kRyP4h+D8DQ0QcAquOvtp0jIfpCtWGrD/iQG0D2winmNS9Ho+HzLULwBtqgUQz6oVHwLFIoVma+vUfutZ1Knw2jexP017djMcoNuB3zDzmYTsldNxrLVQApFbxtdzz9tLUAdb4GvERO0NgszPpU8vlB8/daN1WiHNmBF5O2w3i6Ntge2o4cRAhcdiZc9oTvzyg5rMC4B/ey/l+6Y3VPcQmX8dKrze6/lrHCoMqE9SpHCTftpGY9xghGKZHj8zoi3diTYI8vUZoQIDAQABAoIBAQC39VdqGwjSAl0ZwtXRcO6GzySbXMEwIT6hO/UBlJtYDXit78tjk6U//zA38dbvXGHn+kx9Epvm/H8fS+ixB1IZU96EpCewukqu+QCSikDdbcPbu287hJMXJkRZtHiPJk1mdmgVElMfwMZZeIRuCPqv7i+Jv/oVdIy1p7Hy/IXm8Oo24vzqbDaa4bzQL1WquM4R3SuR+7Qi8UnDVRT5+wc3SOuEdlnxaPqLacjtxa6cUPJKYX0yhqRgWKqYjnO4A0kH1Bb5qcmCBiOhpYHl8A5RpZCVNZ+10pCWv67Ldp/PPVzmWtucKltFzKuVB6yY48ENOtRAxJRIbPfh6rZ7xkcBAoGBAP7sA35qlu/9v7j5rFLZaSaCSu3FBWvzVUlET0yOeV1cNWgN2r1mDfMEfpyuyZKVscZhQfxc2pJg/4XPPNHpiWwKPrj+NldNP1jViXFInEYwtFj5D1qvjAAJNZReLHo2DCnifM3q8xmkule6ZbNr9idTDlIKFajXJCuy17SnhLJRAoGBANiSneJNuOWtZjfIOVHIxHQml4Fb2XLfoFc1uxJXmjUJ01WDXwWFqt1DQ9/vp6427/kFayRlZlfbnAUJddVZ22/o/TnGVQ2QYVK8y1xwnBkCAEOkAGz59gvhz/auH4P3JS6PpWx26VjuFzom+qjT4F8KiuXm6OipLB7zSM8cZE5RAoGBAL4acUyP3nmgSJ6ACqNKIKEoHwqLl8x2DU7zExPrV9RolCdB1bLSbWqiGm1r50RaP9XJ57Rz5kes8EDwV9HOj72zMi0w3oNiRvBPZgzF8kxhu8xyB6JAMW5Bb+RyCkclERMXJK1HHf0snA4aIgeYZlvTE2XYwWhE8GNqHnRJTKrRAoGAYTzLGJ2O48iL+YWzfq3mzsO5CIKlyjbvtdhRCvY8LY4gzeczFbajNb8KzZO7tLPD4Qn2xhuk7NOUZIqP1mAG72MrtvH+pB1sJQrRP8rruyqz1arf6g380+7qQbaCPe0MS7CBNIbbVGtqEFkJ+B1RQzrnjDu13Sh/v9G5ogv8WVECgYEAnRuz3vDc0vwsh1hhUtGKiow1I6QiYAtSo5CXkJqaEONjFaiNsc7OkvWgxkQFIuaseaNQIO3pnUUEKd3Nwzl1vRCJIWmHYI/I7pdl5MPN9nalBGO/k9rf/oUqZXC0+rWns6lZr6SrHGJqw6s/kV4EsFKsnN4VCmbAGwBYRW5QOc4=",
        
        //异步通知地址
        'notify_url' => "http://www.yikazc.com/app/carrental/frontend/web/index.php/ppayment/pc_alipay_gateway",
        //'notify_url' => "http://gm.yikazc.com:8010/app/carrental/admin/ppayment/pc_alipay_gateway",
        
        //同步跳转
        // 'return_url' => "http://www.yikazc.com/PC/ykzc_/myYk/myYK.html",
        'return_url' => "http://www.yikazc.com/PC/ykzc_new/orderProcess/paySuccess.html",

        //编码格式
        'charset' => "UTF-8",

        //签名方式
        'sign_type'=>"RSA2",

        //支付宝网关
        'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

        //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
        'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAryCb5rtKedyRLINRrMpbcoxqNe4ke0e6qjkh7DY8ORGNzkMCCF8/ANKQ6m+mvp81KPp/j3eataDqM2YBLM8wXKgGlDeAdHnYMaxZ/+1xFHTZNjicyo25ynK7mJ/sG6fZKWIQBD29PwkTIu8j37v9xAhagArxJL7jC1CU7DQIkwoMhrp4pofIDsQP7MIZ6n/IMelpPhQtzSr9hJz26ok4POsJmMPTC+zjuxKLb/iXMK+CwQZXj5U0RNLJUd7KqszGD1zMVWPhTkaslfkk690zSS0NsGMkfFKPkGpwAlJ7Mc59WGuSy+3oAsYSEho2zoWzaw6KZuZZexqvCIj7c+im6wIDAQAB",
    ),


    // 小程序支付
    'wxappconfig' => array(
        'appid'      => 'wx6257364ee334cd75',//小程序ID
        'mch_id'     => '1395342202',//商户号
        'notify_url' => 'https://www.yikazc.com/app/carrental/frontend/web/index.php/wxapppayment/notify_url',
    ),
    'AppSecret'=>'e91cbfc01e5006f1eece32c0c514030d',//小程序设置秘钥
    // 汽车在线appkey和账号account
    'pageApi' =>array(
        'appkey'  => '68f7e5358c1773e5cfe37ade7b82e4d5',
        'account' => '金华易卡租车',
    ),
];