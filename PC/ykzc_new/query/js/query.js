app.controller('queryStoreCtrl', function ($scope,$http) {
    //$scope.querySre;
    //
    //lodeStoreQuery();
    //http://gm.yikazc.com:8010/app/carrental/frontend/web/index.php/papi/city_list
    $scope.hotCityList;
    $scope.cityListsForQuery;
    $scope.thisCity;
    //门店查询
    function GetQueryString(name)
    {
         var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
         var r = window.location.search.substr(1).match(reg);
         if(r!=null)return  unescape(r[2]); return null;
    }

    function lodeStoreQuery(CityName){
        //console.log(CityName,'/*/*/*/*/')
        /*if(CityName){
            CityName = CityName.join('');

        }else {
            CityName = "0579";
        }*/
        CityName = GetQueryString('city_code') || '0579';
        //console.log(CityName,'-99-99-99-')
        $.ajax({
            type: 'get',
            //http://gm.yikazc.com:8010/app/carrental/frontend/web/index.php/papi/shop_list?cid="+cityId,
            url: "http://www.yikazc.com/app/carrental/frontend/web/index.php/papi/get_shop_bycity?cid="+CityName,
            //url: "http://gm.yikazc.com:8010/app/carrental/frontend/web/index.php/papi/shop_list?cid="+CityName,
            dataType: "json",
            success: function (data) {
                //console.log(data)
                if(data.result == 0 ){
                    $scope.querySre = data.shop_list;
                    for(i = 0;i<$scope.querySre.length;i++){
                        $scope.querySre[i].href =  "../book/shortOrder.html?takeCity="+$scope.querySre[i].city+"&takeStore="+ $scope.querySre[i].shop_name+"&isTake=false&returnCity="+$scope.querySre[i].city+"&returnStore="+ $scope.querySre[i].shop_name+"&isReture=false";
                    }
                }else {
                    alert(data.desc);
                    window.location.href="#/0579";
                    location.reload();
                }
            }
        })
    }
    var CityName =  window.location.hash.match(/[A-Za-z_0-9-]/g);
    lodeStoreQuery(CityName);




    $(function(){
        var controllerScope = $('html[ng-controller="appCtrl"]').scope(); //this way get controller'scope



        //门店查询 页面跳转
      function storeMapJiaZai(){
          //if( window.location.hash.match(/[A-Za-z_0-9-]/g)){
          var cityIdUrl;
          // if( window.location.hash.match(/[A-Za-z_0-9-]/g)){
          //     cityIdUrl =  window.location.hash.match(/[A-Za-z_0-9-]/g).join('');
          // }else {
          //     cityIdUrl = '0579';
          // }
          

          cityIdUrl = GetQueryString('city_code')||'0579';
          $.ajax({
              type: 'get',
              url: "http://www.yikazc.com/app/carrental/frontend/web/index.php/papi/city_list",
              dataType: "json",
              success: function (data) {
                  //console.log(data)
                  $scope.hotCityList = data.hot_city;  //热门城市赋值
                  $scope.cityListsForQuery = data.city_list;//城市列表赋值
                  $scope.$apply();

                  //所有城市列表
                  var cityListsForQuery={};
                  for(i=0;i<data.city_list.length;i++){
                      cityListsForQuery[data.city_list[i].cid] =data.city_list[i].city;
                  }
                  //console.log(cityListsForQuery,cityIdUrl);
                  var storeCity = cityListsForQuery[cityIdUrl]
                  $('.citynames').text(storeCity);
                  $scope.thisCity=storeCity;
                  //console.log($scope.thisCity);
                  // $("head").append('<meta name="keywords" content="'+storeCity+'租车，'+storeCity+'租车公司，'+storeCity+'租车网，'+storeCity+'租车费用，'+storeCity+'租车门店" />');
                  // $("head").find("title").text(storeCity+'租车-'+storeCity+'租车公司-'+storeCity+'租车门店查询【易卡租车网】');



                  $.when(wait()).done(function(base){
                      var geo;
                      base = storeCity ||base;
                      //base = base;
                      //base 地址    zoom 缩放级别
                      //console.log(geo,base);
                      initialize(geo,base,[{id:'map',zoom:15}]);

                  });
                  //console.log($('.box-body').data())

                  $('.panel-city-1 li>a').click(function(){
                      var city = $(this).html();
                      theLocation(city,[0],11);
                  })
              }
          })



          //}
      }
        storeMapJiaZai();


    })



})


