/**
 * Created by Administrator on 2017/12/12.
 */
$('.panel-list').find('li:gt(0)').click(function () {
    $(this).siblings().removeClass('active');               //删除类名
    $(this).addClass('active');                             //添加类名
})
app.controller('getJobCtr', function ($scope) {

})