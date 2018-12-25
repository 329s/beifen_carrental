<?php

/* @var $this yii\web\View */

$this->title = \Yii::$app->params['app.company.name'];

$autoId = \common\helpers\CMyHtml::genID();
$urlRoot = \common\helpers\Utils::getRootUrl();

?>
<!DOCTYPE html>
<html>

	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=750, user-scalable=no, target-densitydpi=device-dpi">
		<title>我的易卡</title>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/mui/3.7.1/js/mui.min.js"></script>
		<link href="https://cdnjs.cloudflare.com/ajax/libs/mui/3.7.1/css/mui.css" rel="stylesheet" />
		<script type="text/javascript" charset="utf-8">
			mui.init();
		</script>
	</head>

	<body>
		<header class="mui-bar mui-bar-nav index_nav">
			<a href="index.html" style="color: #ccc;" class="mui-icon mui-icon-back"></a>
			<h1 class="mui-title">我的易卡</h1>
			<style>
				/*顶部标题*/
				
				.index_nav {
					background-color: #fff;
					height: 90px;
					line-height: 90px;
					box-shadow: none;
					border-bottom: 2px solid #e4e4e4
				}
				
				.index_nav h1 {
					color: #242424;
					font-weight: 400;
					font-size: 32px;
					letter-spacing: 5px;
					line-height: 90px;
					height: 90px;
				}
				
				.index_nav a {
					font-size: 40px !important;
					line-height: 70px;
					color: #FFD732 !important;
				}
			</style>
		</header>

		<form action="">
			<div class="shadow search mui-row">
				<div class="mui-col-sm-2 mui-col-xs-2">车牌号:</div>
				<div class="shadow inputBox mui-col-sm-7 mui-col-xs-7">
					<!--<input class="car_nobs" autocomplete="on" type="text" />-->
					<select class="car_nobs" name="">
					</select>

				</div>
				<div class="mui-col-sm-1 mui-col-xs-1"></div>
				<input class="mui-col-sm-2 mui-col-xs-2 btn_cha" type="button" value="查询" onclick="query()" />

			</div>
		</form>

		<div id="capture" class="infos_box" style="display: none;">

			<div class="shadow mui-row car_nr">
				<div class="mui-col-xs-6 mui-col-sm-6 leftimg">
					<div class="shadow" style="margin: 0;">
						<i class="flag"></i>
						<img class="car_img" src="" alt="" />
					</div>
				</div>
				<div class="mui-col-xs-6 mui-col-sm-6 righttext">
					<div class="title"><b class="car_name" style="display: block;"></b> <span class="car_type"></span></div>
					<div class="scontent car_info"></div>
				</div>
			</div>
			<div class="shadow bot_info">
				<p>本月已租次数：<span><b class="count"></b>次</span></p>
				<p>本月已租天数：<span><b class="all_rent_days"></b>天</span></p>
				<p>本月车辆收入：<span><b class="all_total_amount"></b>元</span></p>
			</div>
			<div class="shadow mui-row guishu">
				<div class="mui-col-xs-2 mui-col-sm-2"></div>
				<div class="mui-col-xs-3 mui-col-sm-3">所属门店：</div>
				<div class="mui-col-xs-7 mui-col-sm-7 car_stop"></div>
			</div>
			<div class="shadow rent_box">
				<h3>当前在租订单信息</h3>
				<div class="mui-row">
					<div class="mui-col-xs-2 mui-col-sm-2"></div>
					<div class="mui-col-xs-3 mui-col-sm-3">
						<div class="title start_date"></div>
						<div class="scontent start_time"></div>
					</div>
					<div class="mui-col-xs-7 mui-col-sm-7">
						<div class="title"><span class="rent_shop"></span>租出</div>
						<div class="scontent address"></div>

					</div>

				</div>
				<hr />
				<div class="mui-row">
					<div class="mui-col-xs-2 mui-col-sm-2"></div>
					<div class="mui-col-xs-3 mui-col-sm-3">
						<div class="title new_end_date"></div>
						<div class="scontent new_end_time"></div>
					</div>
					<div class="mui-col-xs-7 mui-col-sm-7">
						<div class="title">租期：<span class="rent_days"></span>天</div>
						<div class="scontent">租金：<span class="rent_per_day"></span>元/天 x <span class="rent_days"></span>天</div>

					</div>

				</div>
			</div>

			<!--<div class="tip">实际租金以车辆实际出租天数为准。</div>-->
			<input id="btn_fx" type="button" value="点击分享" /></div>
		<iframe style="display: none;" id="address_car_dw" src="http://pageapi.gpsoo.net/third?method=jump&appkey=68f7e5358c1773e5cfe37ade7b82e4d5&account=金华易卡租车&page=tracking&target=868120172758432&s=1" width="100%" height="600"></iframe>
		<iframe style="" id="address_car_dws" src="http://pageapi.gpsoo.net/third?method=jump&appkey=68f7e5358c1773e5cfe37ade7b82e4d5&account=金华易卡租车&page=playback&target=868120172758432&s=1" width="100%" height="600"></iframe>
		<iframe src="http://pageapi.gpsoo.net/third?method=jump&page=monitor&locale=zh-cn&account=%E9%87%91%E5%8D%8E%E6%98%93%E5%8D%A1%E7%A7%9F%E8%BD%A6&target=%E9%87%91%E5%8D%8E%E6%98%93%E5%8D%A1%E7%A7%9F%E8%BD%A6&appkey=68f7e5358c1773e5cfe37ade7b82e4d5&t=1545636395721&s=1" width="800" height="450"></iframe>

		<style>
			body,
			html {
				width: 100%;
				height: 100%;
				background-color: #fff;
			}
			
			body {
				padding: 10px;
				padding-top: 90px;
			}
			
			p {
				color: #212121;
			}
			
			ol,
			li,
			ul {
				list-style: none;
				padding: 0;
				margin: 0;
			}
			
			.shadow {
				background: rgba(255, 255, 255, 1);
				box-shadow: 0px 1px 9px 1px rgba(211, 211, 211, 0.75);
				border-radius: 8px;
			}
			
			img {
				max-width: 100%;
			}
			
			.shadow {
				margin: 20px 0;
			}
			
			img {
				max-width: 100%;
			}
			/*搜索*/
			
			.search {
				height: 100px;
				line-height: 100px;
				padding: 0 40px;
				font-size: 24px;
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin: 10px 0;
			}
			
			.search .inputBox {
				height: 50px;
				position: relative;
				line-height: 50px;
			}
			
			.search .inputBox input {
				font-size: 24px;
				outline: none;
				border: none;
				height: 50px;
				margin: 0;
				padding: 0;
				vertical-align: middle;
				position: absolute;
				top: 0;
				padding: 0 20px;
			}
			
			.car_nobs {
				margin: 0;
				padding: 0;
				line-height: unset;
				height: 50px;
				padding-left: 20px;
				font-size: 30px
			}
			
			.search .btn_cha {
				font-size: 24px;
				height: 50px;
				background-color: #FFB200;
				color: #fff;
				border-radius: 10px;
			}
			
			.car_nr {
				height: 230px;
			}
			
			.car_nr .leftimg {
				padding: 20px 60px;
			}
			
			.car_nr .leftimg>div {
				position: relative;
				padding: 20px 10px;
			}
			
			.car_nr .leftimg .flag {
				color: #fff;
				font-size: 30px;
				background-color: #FFB200;
				/*width: 70px;*/
				padding:  10px;
				/*height: 30px;*/
				/*line-height: 30px;*/
				text-align: center;
				display: inline-block;
				position: absolute;
				left: 0;
				bottom: 20px;
				font-style: normal;
			}
			
			.car_nr .righttext {
				display: flex;
				padding: 30px 0;
				justify-content: space-around;
				flex-direction: column;
				height: 100%;
			}
			
			.car_nr .righttext .title {
				font-size: 35px;
				color: #454444;
				text-align: center;
			}
			
			.car_nr .righttext .title span {
				background-color: #FFB200;
				display: inline-block;
				color: #fff;
				font-size: 16px;
				/*width: 60px;*/
				height: 30px;
				padding: 0 10px;
				line-height: 30px;
				vertical-align: middle;
				border-radius: 8px;
				position: relative;
				top: 25px;
			}
			
			.car_nr .righttext .scontent {
				font-size: 24px;
				color: #9D9C9C;
				text-align: center;
				line-height: 1.2;
			}
			
			.guishu {
				font-size: 28px;
				color: #313131;
				height: 120px;
				line-height: 120px;
			}
			
			.rent_box {
				padding: 30px;
			}
			
			.rent_box .title {
				font-size: 27px;
				color: #2A2A2A;
			}
			
			.rent_box .scontent {
				font-size: 27px;
				color: #545454;
				line-height: 1.3;
			}
			
			.rent_box>div {
				height: 150px;
				padding: 20px 0;
			}
			
			.rent_box>div>div {
				height: 100%;
				display: flex;
				justify-content: space-around;
				flex-direction: column;
			}
			
			.rent_box>div>div:first-child {
				position: relative;
			}
			
			.rent_box>div>div:first-child::after {
				display: block;
				content: '';
				position: absolute;
				width: 30px;
				height: 30px;
				border-radius: 514659173px;
				background-color: #000;
				left: 50%;
				top: 50%;
				margin-left: -15px;
				margin-top: -15px;
			}
			
			.rent_box>div:last-child>div:first-child::after {
				background-color: #F2B742;
			}
			
			.rent_box>div>div:last-child {
				border-left: 1px solid #ccc;
				padding-left: 40px;
			}
			
			.bot_info {
				padding: 0 30px;
			}
			
			.bot_info p {
				display: flex;
				justify-content: space-between;
				font-size: 28px;
				padding: 30px 10px;
				border-bottom: 1px solid #E5E5E5;
				margin: 0;
			}
			
			.bot_info p:last-child {
				border: none;
			}
			
			.bot_info p span {
				color: #A5A5A5;
				font-size: 28px;
			}
			
			.bot_info p span b {
				color: #FFB200;
				font-size: 50px;
				margin-right: 5px;
				font-weight: 400;
			}
			
			.tip {
				background: url(img/12122.png) no-repeat 77px center #f7f8fa;
				background-size: 30px 30px;
				color: #525355;
				font-size: 18px;
				text-align: center;
				height: 50px;
				line-height: 50px;
			}
			
			#btn_fx {
				color: #fff;
				font-size: 25px;
				background-color: #F6B443;
				height: 60px;
				line-height: 60px;
				width: 100%;
				padding: 0;
				margin: 0;
				margin-top: 20px;
				margin-bottom: 80px;
				border-radius: 10px;
			}
		</style>
		<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.js"></script>
		<script src="http://www.yikazc.com/phone/car_chaxun/js/min.js"></script>
		<script>
			if(location.href.indexOf('https')!='-1') {
				location.href = location.href.replace(/https/, 'http')

			} else {
				function getCarList() {
				if(sessionStorage.getItem('carList')) {
					console.log(sessionStorage.getItem('carList'));
					console.log(JSON.parse(sessionStorage.getItem('carList')));
					var carArr =JSON.parse(sessionStorage.getItem('carList')) ;

					var car_html = '';
					for(let i = 0; i < carArr.length; i++) {
//						car_html += `<option id="`+carArr[i].id+`" value="` + carArr[i].carNub + `">` + carArr[i].carNub + `</option>`
						car_html += `<option  value="` + carArr[i].id + `">` + carArr[i].carNub + `</option>`
					}
					$('.car_nobs').html(car_html)
				} else {
					//alert('请登陆后再试！');
					//window.location.href = 'index'
				}
			}
			getCarList()

			function query() {
				$('#address_car_dw').hide()
				// var car_nub = $('.car_nobs option:selected').text();
				var car_nub = '浙G987654';
				if(car_nub) {
					$.ajax({
						type: "post",
						url: "http://www.yikazc.com/app/carrental/frontend/web/index.php/wxvehicle/get_vehicle_info",
						data: {
							'plate_number': car_nub
						},
						dataType: 'json',
						success: function(data) {
														console.log(data)

							if(data.result) {
								alert(data.desc)
							} else {
								$('.infos_box').show();
								var infos = data.adorn_vehicle;
								$('.car_img').attr('src', infos.image);
								$('.car_name').text(infos.vehicle_model);
								$('.car_type').text(infos.vehicle_property);
								$('.car_info').text(infos.text);
								$('.car_stop').text(infos.stop_office_id);
								$('.rent_shop').text(infos.office_id_rent);
								$('.address').text(infos.address);
								$('.start_date').text(infos.start_date);
								$('.start_time').text(infos.start_time);
								$('.new_end_date').text(infos.new_end_date);
								$('.new_end_time').text(infos.new_end_time);
								$('.all_rent_days').text(infos.all_rent_days);
								$('.rent_per_day').text(infos.rent_per_day);
								$(".rent_days").text(infos.rent_days);
								$('.count').text(infos.count)
								$('.all_total_amount').text(infos.all_total_amount);
								$('.flag').text(infos.status);
								if(infos.isrent) {
									$('.rent_box').show()
								} else {
									$('.rent_box').hide()
								}
								if(infos.url_is_null === '0') {
									$('#address_car_dw').show()
								} else {
									$('#address_car_dw').show()
								}

							}
						}
					});
				} else {
					alert('请输入车牌号！')
				}
			}
			$('#btn_fx').click(function() {
				//				html2canvas(document.querySelector("#capture")).then(canvas => {
				//					document.body.appendChild(canvas)
				//				});
//				var fx_data = {};
//				fx_data.imgSrc = $('.car_img').attr('src');
//				fx_data.flag = $('.flag').text();
//				fx_data.car_name = $('.car_name').text();
//				fx_data.car_type = $('.car_type').text();
//				fx_data.car_info = $('.car_info').text();
//				fx_data.car_type = $('.car_type').text();
//				fx_data.car_stop = $('.car_stop').text();
//				fx_data.start_date = $('.start_date').text();
//				fx_data.start_time = $('.start_time').text();
//				fx_data.address = $('.address').text();
//				fx_data.rent_shop = $('.rent_shop').text();
//				fx_data.new_end_date = $('.new_end_date').text();
//				fx_data.new_end_time = $('.new_end_time').text();
//				fx_data.rent_days = $($('.rent_days')[0]).text();
//				fx_data.count = $('.count').text();
//				fx_data.all_rent_days = $('.all_rent_days').text();
//				fx_data.all_total_amount = $('.all_total_amount').text();
//				fx_data.rent_per_day = $('.rent_per_day').text();
//				console.log(fx_data);
//				var url_code = 'https://www.yikazc.com/phone/car_chaxun/fx_html.html?'
				//				var url_code = 'fx_html.html?'
//				var key_arr = []
//				for(key in fx_data) {
//					key_arr.push(key);
//					url_code += key + '=' + fx_data[key] + '&'
//				}
//				key_arr.sort()
//				console.log(key_arr)
//				for(let i=0;i<key_arr.length;i++){
//					url_code += key_arr[i] + '=' + fx_data[key_arr[i]] + '&'
//				}
				
//				console.log(url_code);
//				location.href = url_code.substr(0, url_code.length - 1)+'&from=singlemessage&isappinstalled=0'
				location.href =  'http://www.yikazc.com/phone/car_chaxun/fx_html.html?car_id='+$('.car_nobs option:selected').val()
//				location.href =  'fx_html.html?car_id='+$('.car_nobs option:selected').val()
			})
		
			}
			</script>
	</body>

</html>