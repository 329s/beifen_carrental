/**
 * Created by Administrator on 2017/4/21.
 */
  var mp = [];
  var base;
var storeList;
$.ajax({
    type: 'get',
    url: "http://www.yikazc.com/app/carrental/frontend/web/index.php/papi/allcity_shop",
    dataType: "json",
    success: function (data) {
        //console.log(data);
        var arr = [];
        for(key in data){
            if(data[key].list){
                for(i=0;i<data[key].list.length;i++){
                    arr.push(data[key].list[i]);
                }
            }

        }
        for(i=0;i<arr.length;i++){
            arr[i].pos.lng  = arr[i].pos.x;
            arr[i].pos.lat  = arr[i].pos.y;
            delete arr[i].pos.x;
            delete arr[i].pos.y;
        }
        //console.log(arr);
        storeList = arr;
        function setPlace(pp ,mp,cl) {
            for(var i = 0;i<mp.length;i++){
                if(cl==false||cl==""||cl==undefined){
                    mp[i].clearOverlays();
                    mp[i].addOverlay(new BMap.Marker(pp));    //添加标注
                }
                mp[i].centerAndZoom(new BMap.Point(pp.lng, pp.lat), 18);
            }
        }


        //根据ip获取默认城市
        //地图初始化
        function initialize(base){
            var city = $('.takeArea').val() || $('.returnArea').val() || base;
            mp[0] = new BMap.Map('map');
            mp[0].centerAndZoom(city, 12);
            mp[0].addControl(new BMap.NavigationControl({anchor: BMAP_ANCHOR_TOP_LEFT, type: BMAP_NAVIGATION_CONTROL_SMALL}));
            mp[0].enableScrollWheelZoom();

            mp[1] = new BMap.Map('map-search');
            mp[1].centerAndZoom(city, 12);
            mp[1].addControl(new BMap.NavigationControl({anchor: BMAP_ANCHOR_TOP_LEFT, type: BMAP_NAVIGATION_CONTROL_SMALL}));
            mp[1].enableScrollWheelZoom();

            //标注
            marker(mp[0]);
            marker(mp[1]);
        }
        $.when(wait()).done(function(base){
            initialize(base);
        });
    }
})



// 百度地图API功能


//


