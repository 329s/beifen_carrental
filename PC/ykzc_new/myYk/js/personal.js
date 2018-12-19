/**
 * Created by Administrator on 2017/5/15.
 */
$(function(){
    $(".btn-recharge").click(function(){
         $(".box-medium").slideDown();

    })
    $(".btn-confim").click(function(){
        var a = $('.image-container.large img').eq(1).attr('src');
        $('.image-container.large img').eq(0).attr('src',a);
        var w =  $('.image-container.large img').eq(1).width();
        var h =  $('.image-container.large img').eq(1).height();
        var t =  $('.image-container.large img').eq(1).css('top');
        var l =  $('.image-container.large img').eq(1).css('left');
        $('.image-container.large img').eq(0).css({'width': w,'height': h,'left': l,'top': t});
        $('.modal.fade.in .close').click();
    })
    $('.phone-change').click(function(){
        $('.box-phone-change').slideToggle();
    })
    $('.email-change').click(function(){
        $('.box-email-change').slideToggle();
    })
    //验证
    $('.panel-pchange input').blur(function(e){
        validate.check(e);
    });
    $(".btn-pchange").click(function(e){
        var t = validate.complete(e);
        if(t){
            $(".panel-register form").submit();
        }
    });
    $("[data-toggle='popover']").popover();
})