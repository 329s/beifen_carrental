/**
 * Created by Administrator on 2017/3/31.
 */
$(function(){
    if(!Object.values){
        Object.values = function(obj){
            var arr = [];
            for(var key in obj){
               arr.push(obj[key]);
            }
            return arr;
        }
    }
    if(!Object.key){
        Object.key = function(obj){
            var arr = [];
            for(var key in obj){
                arr.push(key);
            }
            return arr;
        }
    }
})
function dateDeal0(d){
    var y = d.getYear()+1900;
    var m = d.getMonth() + 1;
    var d = d.getDate();
    if(m<10){
        m = "0"+m;
    }
    if(d<10){
        d = "0"+d;
    }
    return y+'-'+m+'-'+d
}

function dateDeal1(d){
    d= d.split('-');
    d.shift(0);
    d= d.join("")
    return d;
}
function dateDeal2(a,b){
    a=new Date(Date.parse(a.replace(/-/g, '/')));
    b=new Date(Date.parse(b.replace(/-/g, '/')));
    var day = (a-b)/(24*60*60*1000)+1;
    return day;
}
function datePlus(a,num){
    a = new Date(a);
    a.setDate(a.getDate()+num);
    var month=a.getMonth()+1;
    var day = a.getDate();
    if(month<10){
        month = "0"+month;
    }
    if(day<10){
        day = "0"+day;
    }
    var val = a.getFullYear()+"-"+month+"-"+day;
    return val;
}
function getMouse(){

}
//根据城市获取城市ID，
function getCityCid(city){
    var cityCid;
    if(sessionStorage.getItem("city_list") != undefined){

        var cityList = JSON.parse( sessionStorage.getItem("city_list"));
        //console.log(cityList);
            cityList = cityList.city_list
        for(var i =0;i<cityList.length;i++){
            for(var key in cityList[i]){
                if( city == cityList[i].city){
                    cityCid = cityList[i].cid;
                    break;
                }
            }
        }
    }else {
        $.ajax({
            type: 'get',
            url: "http://www.yikazc.com/app/carrental/frontend/web/index.php/papi/city_list",
            dataType: "json",
            async: false,
            success: function (data) {
                var cityList = data.city_list;

                for(var i =0;i<cityList.length;i++){
                    for(var key in cityList[i]){
                        if( city === cityList[i].city){
                            cityCid = cityList[i].cid;
                            break;
                        }
                    }
                }
                sessionStorage.setItem("city_list",JSON.stringify(data));
            }
        });
    }

    return cityCid;
}
////根据城市ID获取城市名字
//function getCityName(cid){
//    var cityName;
//    if(sessionStorage.getItem("city_list") != undefined){
//        var cityList = JSON.parse( sessionStorage.getItem("city_list")).city_list;
//        console.log(cityList);
//        for (i=0;i<cityList.length;i++){
//            console.log(cid, cityList[i].cid,i);
//            if(cid == cityList[i].cid){
//                cityName = cityList[i].city;
//                return cityName
//            }
//        }
//    }else {
//        $.ajax({
//            type: 'get',
//            url: "http://gm.yikazc.com:8010/app/carrental/frontend/web/index.php/papi/city_list",
//            dataType: "json",
//            async: false,
//            success: function (data) {
//                var cityList = data.city_list;
//                console.log(cityList);
//                for (i=0;i<cityList.length;i++){
//                    if(cid == cityList[i].cid){
//                        cityName = cityList[i].city;
//                        sessionStorage.setItem("city_list",JSON.stringify(data));
//                        return cityName
//                    }
//                }
//
//            }
//        });
//    }
//
//}
//根据门店获取门店ID
function getStoreSid(city,store){
    var cityId = city;
    var store = store;
    var storeId ;
    if( isNaN(Number(cityId))){
        cityId =getCityCid(cityId);
        //console.log(cityId);
    }
    //console.log(store,'+++++++++++++++++++++++++++++++++++++++++')
    //console.log(cityId);
    $.ajax({
        type: 'get',
        url: "http://www.yikazc.com/app/carrental/frontend/web/index.php/papi/shop_list?cid="+cityId,
        dataType: "json",
        async:false,
        success: function (data) {
            //console.log(data.shop_list);
            var storeList = data.shop_list;
            for(var i =0;i<storeList.length;i++){
                for(var key in storeList[i]){
                    if( store === storeList[i].shop_name){
                        storeId = storeList[i].sid;
                        //console.log(storeId,'********************************************************');
                        break;
                    }
                    //console.log(1);
                }
                //console.log(2);
            }
        }
    });
    //console.log(storeId)
    return storeId;
}
