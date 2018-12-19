var calendar = {
    config: {
        id: null,
        ok: null,
        maxDay: null,
        minDay: new Date().getFullYear()+"-"+(new Date().getMonth()+1)+"-"+new Date().getDate(),
        zIndex: 1000,
        ishotel: false,
        format: "yyyy-MM-dd"
    },
    clear: function () {
        calendar.config.id = null;
        calendar.config.ok = null;
        calendar.config.maxDay = null;
        var mon = new Date().getMonth() + 1;
        //calendar.config.minDay = null;
        calendar.config.zIndex = 1000;
        calendar.config.ishotel = false;
    },
    _setDay: function (m, y, d) {
        var currDay = d;
        var currMon = m;
        var currYear = y;

        var setMon = new Date().getMonth() + 1;
        var setYear = new Date().getFullYear();
        var setDay = new Date().getDate();

        var firstDay = new Date(y + "/" + m + "/" + 1).getDay();
        var calDay = [];
        var _d = 1;
        var lastDay = new Date(y, m, 0).getDate();
        for (var i = 0; i < 6; i++) {
            calDay.push("<tr>");
            for (var j = 0; j < 7; j++) {
                if (i == 0) {
                    if (firstDay > j) {
                        calDay.push("<td date=''>&nbsp;</td>");
                    }
                    else {
                        var f = calendar.festivals(m, _d);
                        var _dx = (y + "-" + m + "-" + _d);
                        if (m == currMon && currDay == _d && currYear == y) {
                            calDay.push("<td date='" + _dx + "' class='_selDay'>" + _d + "</td>");
                        }
                        else if (m == setMon && setDay == _d && setYear == y) {
                            calDay.push("<td date='" + _dx + "' title='" + _d + "号' class='_sday'>今天</td>");
                        }
                        else if (calendar.config.minDay != null && new Date(calendar.config.minDay.replace(/[年月日-]/g, "\/")) > new Date(_dx.replace(/[-]/g, "\/"))) {
                            calDay.push("<td date='' class='disDay'>" + _d + "</td>");
                        }
                        else if (calendar.config.maxDay != null && new Date(calendar.config.maxDay.replace(/[年月日-]/g, "\/")) < new Date(_dx.replace(/[-]/g, "\/"))) {
                            calDay.push("<td date='' class='disDay'>" + _d + "</td>");
                        }
                        else if (f !== "") {
                            calDay.push("<td date='" + _dx + "' class='festival'>" + f + "</td>");
                        }
                        else {
                            calDay.push("<td date='" + _dx + "' >" + _d + "</td>");
                        }
                        _d++;
                    }
                }
                else {
                    if (_d <= lastDay) {
                        var f = calendar.festivals(m, _d);
                        var _dx = (y + "-" + m + "-" + _d);
                        if (m == currMon && currDay == _d && currYear == y) {
                            calDay.push("<td date='" + _dx + "' class='_selDay'>" + _d + "</td>");
                        }
                        else if (m == setMon && setDay == _d && setYear == y) {
                            calDay.push("<td date='" + _dx + "' title='" + _d + "号' class='_sday'>今天</td>");
                        }
                        else if (calendar.config.minDay != null && new Date(calendar.config.minDay.replace(/[年月日-]/g, "\/")) > new Date(_dx.replace(/[-]/g, "\/"))) {
                            calDay.push("<td date='' class='disDay'>" + _d + "</td>");
                        }
                        else if (calendar.config.maxDay != null && new Date(calendar.config.maxDay.replace(/[年月日-]/g, "\/")) < new Date(_dx.replace(/[-]/g, "\/"))) {
                            calDay.push("<td date='' class='disDay'>" + _d + "</td>");
                        }
                        else if (f !== "") {
                            calDay.push("<td date='" + _dx + "' title='" + m + "-" + _d + "' class='festival'>" + f + "</td>");
                        }
                        else {
                            calDay.push("<td date='" + _dx + "'>" + _d + "</td>");
                        }
                        _d++;
                    }
                    else calDay.push("<td date=''>&nbsp;</td>");
                }
            }
            calDay.push("</tr>");
        }
        return calDay.join("");
    },
    L: function (e) {
        var l = 0; while (e) { l += e.offsetLeft; e = e.offsetParent; }
        return l
    },
    T: function (e) {
        var t = 0; while (e) { t += e.offsetTop; e = e.offsetParent; }
        return t
    },
    colse: function () {
        if (document.getElementById("_calendar")) {
            document.body.removeChild(document.getElementById("_calendar"));
            $(".calendar").remove();
        }
    },
    show: function () {
        calendar.colse();
        var config = arguments[0]; var that = calendar.config;
        calendar.clear();
        for (var i in that) { if (config[i] != undefined) { that[i] = config[i]; } };
        calendar.init(calendar.config.id);
        $('input').blur();
        $('.pop').hide();
    },
    festivals: function (m, d) {
        var lFtv = ["101:国庆", "1225:圣诞","11:元旦","51:劳动","44:清明","530:端午","104:中秋"];
        var f = "";
        for (var i = 0; i < lFtv.length; i++) {
            if (lFtv[i].split(':')[0] == (m.toString() + d.toString()))
                f = lFtv[i].split(':')[1];
        }
        return f;
    },
    init: function (s) {
        var obj = s;
        var oDay = s.value.replace(/[年月]/g, "-").replace(/[日]/g, "");
        if ($(s).attr("date") != "" && $(s).attr("date") != undefined) {
            oDay = $(s).attr("date");
        }
        var currMon = new Date().getMonth() + 1;
        var currYea = new Date().getFullYear();
        var currDay = new Date().getDate();
        var reg = /^(\d{4})-(\d{2})-(\d{2})$/;
        if (oDay != "" && reg.test(oDay)) {
            currMon = parseInt(oDay.split('-')[1]);
            currDay = parseInt(oDay.split('-')[2]);
            currYea = oDay.split('-')[0];
        }
        var modMone = new Date().getFullYear();
        modMone = modMone - 5;
        var _yers = [];
        _yers.push("<table style='display:none;' id='calYear' class='calYear'>");
        for (var m = 0; m < 4; m++) {
            _yers.push("<tr>");
            for (var n = 0; n < 3; n++) {
                _yers.push("<td>" + modMone + "</td>");
                modMone++;
            }
            _yers.push("</tr>");
        }
        _yers.push("</table>");

        var _c = [];
        var t = s.offsetHeight;
        _c.push("<div id='calendar_head' class='calendar_head'><em class='calendaremL'>«</em><input id='_calyear' type='text' value='" + currYea + "' /><input id='_calmod' type='text' value='" + currMon + "' /><em class='calendaremR'>»</em>");
        _c.push(_yers.join(""));
        _c.push("<table style='display:none;' id='calMonth' class='calMonth'><tr><td>1月</td><td>2月</td><td>3月</td><td>4月</td></tr><tr><td>5月</td><td>6月</td><td>7月</td><td>8月</td></tr><tr><td>9月</td><td>10月</td><td>11月</td><td>12月</td></tr></table>");
        _c.push("</div>");
        _c.push("<div class='calendar_boy' ><i id='_bgMon'>" + currMon + "</i>");
        _c.push("<table id='_tdCal' class='_caltable' border='0'><tr><td>日</td><td>一</td><td>二</td><td>三</td><td>四</td><td>五</td><td>六</td></tr>");
        _c.push("" + calendar._setDay(currMon, currYea, currDay) + "");
        _c.push("</table>");

        var calTop = calendar.T(s) + t;
        var calLeft = calendar.L(s);
        var bodyHith = document.body.parentNode.offsetHeight;
        var bodywidth = document.body.parentNode.offsetWidth;
        if ((calTop + 225) > bodyHith) { calTop = calendar.T(s) - 225; }
        var _boy = document.getElementsByTagName("body").item(0);
        var _div = document.createElement("div");
        _div.setAttribute('id', "_calendar");
        _div.setAttribute('class', "calendar");
        _div.setAttribute('style', "left:" + calLeft + "px;top:" + calTop + "px;z-index:" + calendar.config.zIndex + ";");
        _boy.appendChild(_div);
        document.getElementById("_calendar").innerHTML = _c.join("");

        $("#_calyear").click(function () {
            $("#calYear").show();
            $("#calMonth").hide();
            $("#calYear td").click(function () {
                $("#_calyear").val(this.innerHTML);
                calendar._yearMon($("#_calmod").val(), this.innerHTML, s);
                $("#calYear").hide();
            });
        }).blur(function () {
            calendar._yearMon($("#_calmod").val(), this.value, s);
        }).keyup(function () {
            $("#calYear").hide();
            $("#calMonth").hide();
            this.value = this.value.replace(/[^\d]/g, "");
        });
        $("#_calmod").click(function () {
            $("#calYear").hide();
            $("#calMonth").show();
            $("#calMonth td").click(function () {
                $("#_calmod").val(this.innerHTML.replace(/[月]/g, ""));
                calendar._yearMon(this.innerHTML.replace(/[月]/g, ""), $("#_calyear").val(), s);
                $("#calMonth").hide();
            });
        }).blur(function () {
            if (0 < parseInt(this.value) < 13) {
                calendar._yearMon(this.value, $("#_calyear").val(), s);
            }
        }).keyup(function () {
            $("#calYear").hide();
            $("#calMonth").hide();
            this.value = this.value.replace(/[^\d]/g, "");
        });

        $("#_tdCal tr:gt(0) td[date!='']").click(function () {
            s.value = new Date($(this).attr("date").replace(/[-]/g, "/")).format(calendar.config.format);
            $(s).attr("date", new Date($(this).attr("date").replace(/[-]/g, "/")).format("yyyy-MM-dd"));
            if (calendar.config.ok != null) {
                eval(calendar.config.ok());
            }
            calendar.colse();
        }).on("mouseover", function () {
            $(this).css({ "color": "#fff", "background-color": "#ff9900" });
        }).on("mouseout", function () { $(this).removeAttr("style") });


        $("#calendar_head em").click(function () {
            if ($("#calendar_head em").index(this) == 0) {
                calendar._nexPrv("L", $("#_calmod").val(), $("#_calyear").val(), s);
            }
            else {
                calendar._nexPrv("R", $("#_calmod").val(), $("#_calyear").val(), s);
            }
        });
        $("#_tdCal").click(function () {
            $("#calYear").hide();
            $("#calMonth").hide();
        });
        document.onclick = function (e) {
            var event = e || window.event;
            var Target = event.target || event.srcElement;
            calendar.hide(event, Target, obj);
        }
    },
    hide: function (event, Target, obj) {
        var oPare = Target.parentNode;
        var isChild = true;
        if (oPare == obj || Target == obj) {
            isChild = true;
        } else {
            loop: while (oPare != document.getElementById("_calendar")) {
                oPare = oPare.parentNode;
                if (oPare == obj || oPare == null) {
                    isChild = false;
                    break loop;
                }
            }
        }
        if (!isChild) {
            calendar.colse();
        }
    },
    _selCal: function (e) {
        $("#_tdCal tr:gt(0) td[date!='']").click(function () {
            e.value = new Date($(this).attr("date").replace(/[-]/g, "/")).format(calendar.config.format);
            $(e).attr("date", new Date($(this).attr("date").replace(/[-]/g, "/")).format("yyyy-MM-dd"));
            if (calendar.config.ok != null) {
                eval(calendar.config.ok());
            }
            calendar.colse();
        });
    },
    _yearMon: function (m, y, s) {
        $("#_tdCal tr:gt(0)").remove();
        $("#_tdCal").append(calendar._setDay(m, y, 0));
        $("#_bgMon").html(parseInt(m));
        $("#_tdCal tr:gt(0) td[date!='']").click(function () {
            s.value = new Date($(this).attr("date").replace(/[-]/g, "/")).format(calendar.config.format);
            $(s).trigger('change');
            calendar.colse();
        });
    },
    _nexPrv: function (t, m, y, s) {

        var ys = y;
        var ms = m;
        if (t == "L") {
            if (m == 1) {
                ms = 12;
                ys = parseInt(y) - 1;
            }
            else {
                ms = parseInt(m) - 1;
            }
        }
        else {
            if (m == 12) {
                ms = 1;
                ys = parseInt(y) + 1;
            }
            else {
                ms = parseInt(m) + 1;
            }
        }
        $("#_tdCal tr:gt(0)").remove();
        $("#_tdCal").append(calendar._setDay(ms, ys, 0));
        $("#_calmod").val(ms);
        $("#_calyear").val(ys);
        $("#_bgMon").html(ms);
        $("#_tdCal tr:gt(0) td[date!='']").click(function () {
            s.value = new Date($(this).attr("date").replace(/[-]/g, "/")).format(calendar.config.format);
            $(s).attr("date", new Date($(this).attr("date").replace(/[-]/g, "/")).format("yyyy-MM-dd"));
            calendar.colse();
        });
    }
};
Date.prototype.format = function (format) {
    var o =
    {
        "M+": this.getMonth() + 1,
        "d+": this.getDate(),
        "h+": this.getHours(),
        "m+": this.getMinutes(),
        "s+": this.getSeconds(),
        "q+": Math.floor((this.getMonth() + 3) / 3),
        "S": this.getMilliseconds()
    };
    if (/(y+)/.test(format)) {
        format = format.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    };
    for (var k in o) {
        if (new RegExp("(" + k + ")").test(format)) {
            format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length));
        };
    };
    return format;
};

var inputBox = {
    config:{},
    show:function(obj){
        $('.pop').hide();
        $(obj).nextAll('.pop').show();
        inputBox.init(obj);
        inputBox.change(obj);
    },
    init:function(obj){
        $(document).click(function(e){
            inputBox.hide(e, e.target,obj);
        })
        $(obj).nextAll('.area').css({"border-right-color":"#eeb81a"});
    },

    close: function(obj){
         $('.pop').fadeOut();
         $('.store').removeClass('change');
         $('.area').removeClass('change').css({"border-right-color":"#ddd"});
         $(obj).prevAll('label').removeClass('change');
         obj=null;
    },
    hide: function (event, Target, obj) {
        var oPare = Target.parentNode || obj;
        var isChild = true;
        if (oPare == obj || Target == obj) {
            isChild = true;
        } else {
            loop: while ($.inArray(oPare, $('.picker,.ipicker'))==-1) {
                oPare = oPare.parentNode;
                if (oPare == obj || oPare == null) {
                    isChild = false;
                    break loop;
                }
            }
        }
        if (!isChild) {
            inputBox.close(obj);
            obj=null;
        }
    },
    change: function(obj){
        if(obj.className.indexOf('area')!=-1){
            $('.pop').removeClass('change');
            $(obj).addClass('change');
            $('.store').removeClass('change');
            $(obj).prev('label').addClass('change');
        }
        if(obj.className.indexOf('store')!=-1){
            $('.pop').addClass('change');
            $(obj).addClass('change');
            $(obj).prevAll('.area').removeClass('change').css({"border-right-color":"#eeb81a"});
            $(obj).prevAll('label').removeClass('change');
        }
    }
}
var calUtil = {
    //当前日历显示的年份
    showYear:2017,
    //当前日历显示的月份
    showMonth:1,
    //当前日历显示的天数
    showDays:1,
    eventName:"load",
    //初始化日历
    init:function(signList){
        calUtil.setMonthAndDay();
        calUtil.draw(signList);
        calUtil.bindEnvent();
    },
    draw:function(signList){
        //绑定日历
        var str = calUtil.drawCal(calUtil.showYear,calUtil.showMonth,signList);
        $("#calendar").html(str);
        //绑定日历表头
        var calendarName=calUtil.showYear+"年"+calUtil.showMonth+"月"+calUtil.showDays+"日";
        $(".calendar_month_span").html(calendarName);
    },
    //绑定事件
    bindEnvent:function(){
        //绑定上个月事件
        $(".calendar_month_prev").click(function(){
            //ajax获取日历json数据
            var signList=[{"signDay":"10"},{"signDay":"11"},{"signDay":"12"},{"signDay":"13"}];
            calUtil.eventName="prev";
            calUtil.init(signList);
        });
        //绑定下个月事件
        $(".calendar_month_next").click(function(){
            //ajax获取日历json数据
            var signList=[{"signDay":"10"},{"signDay":"11"},{"signDay":"12"},{"signDay":"13"}];
            calUtil.eventName="next";
            calUtil.init(signList);
        });
    },
    //获取当前选择的年月
    setMonthAndDay:function(){
        switch(calUtil.eventName)
        {
            case "load":
                var current = new Date();
                calUtil.showYear=current.getFullYear();
                calUtil.showMonth=current.getMonth() + 1;
                break;
            case "prev":
                var nowMonth=$(".calendar_month_span").html().split("年")[1].split("月")[0];
                calUtil.showMonth=parseInt(nowMonth)-1;
                if(calUtil.showMonth==0)
                {
                    calUtil.showMonth=12;
                    calUtil.showYear-=1;
                }
                break;
            case "next":
                var nowMonth=$(".calendar_month_span").html().split("年")[1].split("月")[0];
                calUtil.showMonth=parseInt(nowMonth)+1;
                if(calUtil.showMonth==13)
                {
                    calUtil.showMonth=1;
                    calUtil.showYear+=1;
                }
                break;
        }
    },
    getDaysInmonth : function(iMonth, iYear){
        var dPrevDate = new Date(iYear, iMonth, 0)
        return dPrevDate.getDate();
    },
    bulidCal : function(iYear, iMonth) {
        var aMonth = new Array();
        aMonth[0] = new Array(7);
        aMonth[1] = new Array(7);
        aMonth[2] = new Array(7);
        aMonth[3] = new Array(7);
        aMonth[4] = new Array(7);
        aMonth[5] = new Array(7);
        aMonth[6] = new Array(7);
        var dCalDate = new Date(iYear, iMonth - 1, 1);
        var iDayOfFirst = dCalDate.getDay();
        var iDaysInMonth = calUtil.getDaysInmonth(iMonth, iYear);
        var iVarDate = 1;
        var d, w;
        aMonth[0][0] = "日";
        aMonth[0][1] = "一";
        aMonth[0][2] = "二";
        aMonth[0][3] = "三";
        aMonth[0][4] = "四";
        aMonth[0][5] = "五";
        aMonth[0][6] = "六";
        for (d = iDayOfFirst; d < 7; d++) {
            aMonth[1][d] = iVarDate;
            iVarDate++;
        }
        for (w = 2; w < 7; w++) {
            for (d = 0; d < 7; d++) {
                if (iVarDate <= iDaysInMonth) {
                    aMonth[w][d] = iVarDate;
                    iVarDate++;
                }
            }
        }
        return aMonth;
    },
    ifHasSigned : function(signList,day){
        var signed = false;
        $.each(signList,function(index,item){
            if(item.signDay == day) {
                signed = true;
                return false;
            }
        });
        return signed ;
    },
    drawCal : function(iYear, iMonth ,signList) {
        var myMonth = calUtil.bulidCal(iYear, iMonth);
        var htmls = new Array();
        //htmls.push("<div class='sign_main' id='sign_layer'>");
       // htmls.push("<div class='sign_succ_calendar_title'>");
        // htmls.push("<div class='calendar_month_next'>下月</div>");
        //htmls.push("<div class='calendar_month_prev'>上月</div>");
        htmls.push("<div class='calendar_month_span'></div>");
        htmls.push("</div>");
        htmls.push("<div class='sign' id='sign_cal'>");
        htmls.push("<table>");
        htmls.push("<tr>");
        htmls.push("<th>" + myMonth[0][0] + "</th>");
        htmls.push("<th>" + myMonth[0][1] + "</th>");
        htmls.push("<th>" + myMonth[0][2] + "</th>");
        htmls.push("<th>" + myMonth[0][3] + "</th>");
        htmls.push("<th>" + myMonth[0][4] + "</th>");
        htmls.push("<th>" + myMonth[0][5] + "</th>");
        htmls.push("<th>" + myMonth[0][6] + "</th>");
        htmls.push("</tr>");
        var d, w;
        for (w = 1; w < myMonth.length; w++) {
            htmls.push("<tr>");
            for (d = 0; d < 7; d++) {
                var ifHasSigned = calUtil.ifHasSigned(signList,myMonth[w][d]);
                if(myMonth[w][d]==new Date().getDate()){
                    myMonth[w][d]="今天"
                }
                if(ifHasSigned){
                    htmls.push("<td class='on'>" + (!isNaN(myMonth[w][d])||myMonth[w][d]=="今天"? myMonth[w][d] : " ") + "</td>");
                } else {
                    htmls.push("<td>" + (!isNaN(myMonth[w][d])||myMonth[w][d]=="今天" ? myMonth[w][d] : " ") + "</td>");
                }
            }
            htmls.push("</tr>");
        }
        htmls.push("</table>");
        htmls.push("</div>");
        htmls.push("</div>");
        return htmls.join('');
    }
};