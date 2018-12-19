/**
 * Created by Administrator on 2017/4/28.
 */
$(function(){
    $(".f-checkbox").click(function(){
        if( $("#autoLogin").is(':checked')){
            $("#autoLogin").removeAttr('checked')
            $(".f-checkbox>div").fadeOut("fast");
        } else {
            $("#autoLogin").prop('checked',true);
            $(".f-checkbox>div").fadeIn("fast");
        }
    });
    $('.panel-register input').blur(function(e){
        validate.check(e);
    });
    $(".btn-register").click(function(e){
        var t = validate.complete(e);
        if(t){
            $(".panel-register form").submit();
        }
    });
    $("[data-toggle='popover']").popover();



        //$("#signupForm").validate({
        //    rules: {
        //        firstname: "required",
        //        email: {
        //            required: true,
        //            email: true
        //        },
        //        password: {
        //            required: true,
        //            minlength: 5
        //        },
        //        confirm_password: {
        //            required: true,
        //            minlength: 5,
        //            equalTo: "#password"
        //        }
        //    },
        //    messages: {
        //        firstname: "请输入姓名",
        //        email: {
        //            required: "请输入Email地址",
        //            email: "请输入正确的email地址"
        //        },
        //        password: {
        //            required: "请输入密码",
        //            //minlength: jQuery.format("密码不能小于{0}个字 符")
        //        },
        //        confirm_password: {
        //            required: "请输入确认密码",
        //            minlength: "确认密码不能小于5个字符",
        //            equalTo: "两次输入密码不一致不一致"
        //        }
        //    }
        //});

    //$('#autoLogin').is(':checked')

})

app.controller('regCtr', function ($scope) {
    $scope.isRegClick = true;
    $scope.regCodeText ='获取验证码';
    $scope.regToSubmit= function (phone,code,psw,pswad) {
        //console.log(phone,code,psw,pswad)
        if(!phone){
            alert('请输入手机号码！');
            return false
        }
        if(!code){
            alert('请输入验证码！');
            return false
        }
        if(!psw){
            alert('请输入密码！');
            return false
        }
        if (!pswad){
            alert('请确认密码！');
            return false
        }
        if(psw.length<8){
            alert('密码最小位数为8位！');
            return false
        }
        if(psw!=pswad){
            alert('两次输入密码不一致，请确认后再试!');
            return false
        }
        //13362930560
        $.ajax({
            url:"http://www.yikazc.com/app/carrental/frontend/web/index.php/puser/signup",
            type:"post",
            data :{
                phone:phone,
                code:code,
                password:hex_md5(psw)
            },
            dataType:"json",
            contentType:"application/x-www-form-urlencoded",
            success:function( data  ){
                //console.log(data);
                if(data.result == 0){
                    alert('账号已成功注册!');
                }else {
                    alert(data.desc)
                }
            },
        })


    }


    $scope.regGetCode = function(phone){
        $scope.isRegClick = false;
        var second = 59;
        $scope.regCodeText = second + '秒后重发';
        var timer = setInterval(function(){

            if(second <=0){
                $scope.regCodeText = '重发验证码';
                second = 59;
                clearInterval(timer);
                $scope.isRegClick = true;
            }else{

                second--;
                $scope.regCodeText = second + '秒后重发';
            }

            $scope.$apply(function(){
                $scope.date = new Date();
            });
        },1000);
        $.ajax({
            url:"http://www.yikazc.com/app/carrental/frontend/web/index.php/puser/get_verify_code",
            type:"post",
            data :{
                phone:phone
            },
            dataType:"json",
            contentType:"application/x-www-form-urlencoded",
            success:function( data  ){
                //console.log(data)
                if(data.result){
                    alert(data.desc);
                }
            },
        });

    }




})

