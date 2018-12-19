/**
 * Created by Administrator on 2017/4/6.
 */
var priceDate = {
    config:{
        year: 2018,
        starDay: '0101',
        endDay: '0103',
        ysq:'5000',
        fwf:'50',
        sxf:'20'
    },
    getDay : function(obj){
        var priceList =   $(obj).data('price');
        var dataList = Object.keys(priceList);
        var day = dataList[0].split("");
        day = day[0]+day[1]+"/"+day[2]+day[3]+"/"+2017;
        var firstDay = new Date(Date.parse(day)).getDay();
        if($('.priceDay')){
            for(var i=0;i<firstDay-1;i++){
                $('.priceDay table tr').eq(2).append("<td >&nbsp</td>");
            }
            var count = firstDay;
            var row = 2;
            for(var j=0;j<dataList.length;j++){
                var date = date = dataList[j].split("");
                date[2]==0?date = date[3]:date=date[2]+date[3];
                if(dataList[j]>=priceDate.config.starDay&&(dataList[j]<=priceDate.config.endDay)){
                    $('.priceDay table tr').eq(row).append("<td class='lease'>" + date + "<br>¥" + priceList[dataList[j]] + "</td>");
                } else {
                    $('.priceDay table tr').eq(row).append("<td >" + date + "<br>¥" + priceList[dataList[j]] + "</td>");
                }
                count++;
                if(count>7){
                    $('.priceDay table tr').eq(row).after('<tr></tr>');
                    row++;
                    count=1;
                }
            }
        }

        return firstDay;
    },
    show: function(obj){
        $(obj).append("<div class='priceDay'><i class='cor'></i><table><tr><th colspan='7'>"+ priceDate.config.starDay.substring(0,2)+"月"+priceDate.config.starDay.substring(2,4)+"日  至 "+this.config.endDay.substring(0,2)+"月"+this.config.endDay.substring(2,4)+"日</th></tr>" +
            "" +"<tr><td>一</td><td>二</td><td>三</td><td>四</td><td>五</td><td>六</td><td>日</td></tr><tr></tr>"+
            "<tr><th style='border-radius: 0 0 4px 4px' colspan='7'>手续费：￥"+ priceDate.config.sxf+" ， 基础服务费：¥"+priceDate.config.fwf+"/天 ， 预授权：￥"+priceDate.config.ysq+"</th></tr></table></div>");
        priceDate.getDay(obj);

        //$(".priceDay").addClass("active")
        $(".priceDay").animate({marginTop:'8px'},100);
        $(".priceDay").animate({opacity:'.95'},200);
    },
    close: function(obj){
        //console.log($(obj))
        $(obj).next(".priceDate").remove();
    }
}