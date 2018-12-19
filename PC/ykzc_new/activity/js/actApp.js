/**
 * Created by Administrator on 2017/8/21.
 */
app.controller('actCtrl',function($scope,$http,urlService,$timeout,$interval){

    $scope.actLists=[];
    $scope.actList = [];
    //app/carrental/frontend/web/index.php/activity/allactivity
    function getAct(){
        //$http.get('http://gm.yikazc.com:8010/app/carrental/frontend/web/index.php/activity/activity_pc_banner').success(function (data, status, config, headers) {
        //    console.log(data.image_list)
        //    $scope.activeForIndex = data.image_list;
        //}).error(function (data) {
        //    console.log(data,646567686961626360);
        //});

        $http.get($scope.https+'/app/carrental/frontend/web/index.php/activity/activity_pc_banner', {
        //$http.get('http://gm.yikazc.com:8010/app/carrental/frontend/web/index.php/activity/allactivity', {

        }).success(function (data, status, config, headers) {
            if(data.result === 0){
                //data = data.image_list;
                $scope.actList = [];
                //console.log(data)
                $scope.actLists = data.image_list;
                //console.log(  $scope.actLists.length);
                for( i = 0 ; i < $scope.actLists.length ; i++){
                    var stmep = {};
                    stmep.name =$scope.actLists[i].title;
                    stmep.content =$scope.actLists[i].content;
                    stmep.allStore =$scope.actLists[i].city_name === '所有城市'? true : false;
                    stmep.actImg = $scope.actLists[i].image;
                    stmep.actUrl =$scope.actLists[i].link ;
                    stmep.state = $scope.actLists[i].status_name ==='正常'? 1:0;
                    $scope.actList.push(stmep);

                    $scope.lgAct = $scope.actList.slice(0,6);
                    $scope.lrAct = $scope.actList.slice(6,12);
                    if($scope.lrAct.length==0){
                        $scope.lrAct = $scope.lgAct;
                    }
                }

                //console.log($scope.actList);
            }

        }).error(function (data) {
            //console.log(data);
        });
    }
    getAct();


    //$scope.actList = [{
    //    name:'易卡租车活动as1',
    //    content: '易卡租车端午租车特惠来袭，端午出游租五免一，即日起至5月30日，租期在5天及以上的端午节日租订单，即可享受租5天免1天的优惠活动。',
    //    allStore: true,
    //    actImg: 'img/sp-act1.jpg',
    //    actUrl:'act1.html',
    //    state: 2,
    //    //活动状态
    //    //2活动即将结束
    //    //1进行中
    //    //0已结束
    //}, {
    //    name: '易卡租车活动',
    //    content: '易卡租车端午租车特惠来袭，端午出游租五免一，即日起至5月30日，租期在5天及以上的端午节日租订单，即可享受租5天免1天的优惠活动。易卡租车端午租车特惠来袭，端午出游租五免一，即日起至5月30日，租期在5天及以上的端午节日租订单，即可享受租5天免1天的优惠活动。',
    //    allStore: false,
    //    actImg: 'img/sp-act2.jpg',
    //    actUrl: 'act1.html',
    //    state: 1,
    //}]

    //console.log($scope.actList,99999);

    $scope.lgAct = $scope.actList.slice(0,6);
    $scope.lrAct = $scope.actList.slice(6,12);
    //console.log($scope.lgAct);
    //console.log($scope.lrAct);
})
