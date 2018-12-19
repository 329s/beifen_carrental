/**
 * Created by Administrator on 2017/5/19.
 */
$(function(){
    var bt=0, at= 0,count=0;
    var ofsetTop  = $('.panel-list').offset().top;
    $(window).scroll(function(){
        at = $(this).scrollTop();
        var h = $('.panel-about').height()- $('.panel-list').height();
        if(at<bt&&at>220&&count!=1){
            $('.panel-top .panel-body').css({'position':'fixed','width':'100%','top':0,'background':'rgba(255,255,255,.7)','margin-top':-70})
            $('.panel-top .panel-body').animate({'margin-top':0})
            count=1;
        } else if(at>bt||at<220){
            $('.panel-top  .panel-body').css({'position':'relative','width':'100%','top':0,'background':'rgba(255,255,255,1)'})
            count=0
        }
        setTimeout(function(){bt = at;},0);
        if(at>=h){
            $('.panel-list').css({'position':'relative','top':h});
            //console.log(1)
        }else if(at<=0){
            $('.panel-list').css({'position':'relative','top': 0});
            //console.log(2)
        }else {
            $('.panel-list').css({'position':'fixed','top':220});
            //console.log(3)
        }
        //console.log($('.panel-list').offset().top,h,ofsetTop,at)

        //else  {
        //   console.log($('.panel-list').offset().top,h,ofsetTop,at)
        //   $('.panel-list').css({'position':'fixed','top':'ofsetTop'});
        //}

        //for(var i=1;i<=12;i++){
        //    var dis = $('#rule'+i).offset().top - $(window).scrollTop();
        //    var disbottom = $(window).height() - dis;
        //    if (at>bt || i<2) {
        //        if (dis < 50 && dis > -50) {
        //            $('.panel-list li').removeClass('active');
        //            $('.panel-list li').eq(i).addClass('active');
        //        }
        //    } else {
        //        if (disbottom < 50 && disbottom > -50) {
        //            $('.panel-list li').removeClass('active');
        //            $('.panel-list li').eq(i-1).addClass('active');
        //        }
        //    }
        //}
    });

    $('.qs').click(function(){
        $(this).next('.as').slideToggle();
    })

    //$.when(wait()).done(function(base){
    //    var geo;
    //    base = base;
    //    initialize(geo,base,[{id:'map',zoom:12}]);
    //});
})


$('.panel-list').find('li:gt(0)').click(function () {
    $(this).siblings().removeClass('active');               //删除类名
    $(this).addClass('active');                             //添加类名
    //$($(this).find('a').attr('href'))[0].scrollIntoView();//实现锚链接
    var hr = $(this).find('a').attr("href");
    var anh = $(hr).offset().top;
    $("html,body").stop().animate({scrollTop:anh},500);
})
function
scrollRest(){
   var hr =  location.hash.replace(/\//g,'');
    var anh = $(hr).offset().top;
    $("html,body").stop().animate({scrollTop:anh},500);
}
scrollRest()