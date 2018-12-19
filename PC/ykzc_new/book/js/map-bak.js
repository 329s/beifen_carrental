/**
 * Created by Administrator on 2017/4/21.
 */
function initialize(city) {
    city==""?city='金华':city=city;
    var mp = new BMap.Map('map2');
    mp.centerAndZoom(city, 12);
    mp.addControl(new BMap.NavigationControl({anchor: BMAP_ANCHOR_TOP_LEFT, type: BMAP_NAVIGATION_CONTROL_SMALL}));
    mp.enableScrollWheelZoom();
    var mp2 = new BMap.Map('map');
    mp2.centerAndZoom(city, 12);
    mp2.addControl(new BMap.NavigationControl({anchor: BMAP_ANCHOR_TOP_LEFT, type: BMAP_NAVIGATION_CONTROL_SMALL}));
    mp2.enableScrollWheelZoom();
    //mp.addControl(new BMap.OverviewMapControl());
    //mp.addControl(new BMap.ScaleControl());
    function addMarker(point,index){
        var marker = new BMap.Marker(point);
        marker.addEventListener('click',function(){
            //console.log(index);
            addOpt(point);
            //alert(index);
        })
        /*marker.addEventListener('onmouseover',function(){
         addOpt();
         })*/
        mp.addOverlay(marker);
        mp2.addOverlay(marker);
    }
    //标注
    var points = [{x:119.6625757,y:29.125},{x:119.6425757,y:29.105}];
    for(var i=0;i<points.length;i++){
        var point = new BMap.Point(points[i].x,points[i].y);
        addMarker(point,i);
    }
    //信息窗口BMap_pop
    function addOpt(point){
        var opts = {
            width : 250,     // 信息窗口宽度
            height: 100,     // 信息窗口高度
            title : "Hello"  // 信息窗口标题
        }
        var infoWindow = new BMap.InfoWindow("World", opts);  // 创建信息窗口对象
        mp.openInfoWindow(infoWindow, point);
        mp2.openInfoWindow(infoWindow, point);
    }

    // 百度地图API功能
    function G(id) {
        return document.getElementById(id);
    }
    var ac = new BMap.Autocomplete(    //建立一个自动完成的对象
        {"input" : "suggestId"
            ,"location" : mp
        });

    ac.addEventListener("onhighlight", function(e) {  //鼠标放在下拉列表上的事件
        var str = "";
        var _value = e.fromitem.value;
        var value = "";
        if (e.fromitem.index > -1) {
            value = _value.province +  _value.city +  _value.district +  _value.street +  _value.business;
        }
        str = "FromItem<br />index = " + e.fromitem.index + "<br />value = " + value;

        value = "";
        if (e.toitem.index > -1) {
            _value = e.toitem.value;
            value = _value.province +  _value.city +  _value.district +  _value.street +  _value.business;
        }
        str += "<br />ToItem<br />index = " + e.toitem.index + "<br />value = " + value;
        G("searchResultPanel").innerHTML = str;
    });

    var myValue;

    ac.addEventListener("onconfirm", function(e) {    //鼠标点击下拉列表后的事件
        var _value = e.item.value;
        myValue = _value.province +  _value.city +  _value.district +  _value.street +  _value.business;
        G("searchResultPanel").innerHTML ="onconfirm<br />index = " + e.item.index + "<br />myValue = " + myValue;

        setPlace();
    });

    function setPlace() {
        mp.clearOverlays();
        mp2.clearOverlays(); //清除地图上所有覆盖物
        function myFun() {
            var pp = local.getResults().getPoi(0).point;    //获取第一个智能搜索的结果
            //console.log(pp);
            mp.centerAndZoom(pp, 18);
            mp.addOverlay(new BMap.Marker(pp));    //添加标注
            mp2.centerAndZoom(pp, 18);
            mp2.addOverlay(new BMap.Marker(pp));    //添加标注
        }

        var local = new BMap.LocalSearch(mp2, { //智能搜索
            onSearchComplete: myFun,
        });
        local.search(myValue,{forceLocal:true});
    }

    return [mp,mp2]
}

function loadScript() {
    var script = document.createElement("script");
    script.src = "http://api.map.baidu.com/api?v=2.0&ak=FiyvLDHpCL6fUaiIhAHk0YtiRfFSCUc1&callback=initialize";
    document.body.appendChild(script);
}

function theLocation(city){
    var mp = new initialize();
    if(city != ""){
        mp[1].centerAndZoom(city,12);      // 用城市名设置地图中心点
        mp[0].centerAndZoom(city,12);
    }
}

window.onload = loadScript;
