/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// 对Date的扩展，将 Date 转化为指定格式的String   
// 月(M)、日(d)、小时(h)、分(m)、秒(s)、季度(q) 可以用 1-2 个占位符，   
// 年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字)   
// 例子：   
// (new Date()).Format("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423   
// (new Date()).Format("yyyy-M-d h:m:s.S")      ==> 2006-7-2 8:9:4.18   
Date.prototype.Format = function(fmt)   
{ //author: meizz   
  var o = {   
    "M+" : this.getMonth()+1,                 //月份   
    "d+" : this.getDate(),                    //日   
    "h+" : this.getHours(),                   //小时   
    "m+" : this.getMinutes(),                 //分   
    "s+" : this.getSeconds(),                 //秒   
    "q+" : Math.floor((this.getMonth()+3)/3), //季度   
    "S"  : this.getMilliseconds()             //毫秒   
  };   
  if(/(y+)/.test(fmt))   
    fmt=fmt.replace(RegExp.$1, (this.getFullYear()+"").substr(4 - RegExp.$1.length));   
  for(var k in o)   
    if(new RegExp("("+ k +")").test(fmt))   
  fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));   
  return fmt;   
};

String.prototype.Format = function(){    
    var args = arguments;    
    return this.replace(/\{(\d+)\}/g,                    
        function(m,i){    
            return args[i];    
        });    
};

(function($){
    if ($.custom == undefined) {
        $.custom = {};
    }
    
    $.custom.utils = {
        lan: {
            defaults: {
                days : 'days',
                hours : 'hours',
                minutes : 'minutes',
                seconds : 'seconds',
                
                titleError:'Error',
                titleWarning:'Warning',
                titlePrompt:'Prompt',
                titleInfo:'Information',
                msgCannotParseJson:'Can not parse "{0}" by json format!',
                msgGotResponseFailedError:'Got response failed! error:',
                msgAreYouSureToDeleteSelectedItems:'Are you sure to delete the selected items?',
                msgPleaseConfirmAgainToDeleteItems:'Please confirm again to delete the selected items.',
                msgPleaseFinishEditingCellFirst:'Please finish the editing cell first!',
                msgAreYouSureToSaveChangedItems:'Are you sure to save the changed items?',
                msgAreYouSureToDoThisOperation:'Are you sure to do this operation?',
                msgNumRowsAreChanged:'{0} rows are changed.',
                msgUpdateUrlNotConfiguredSoSkip:'\'updateUrl\' not configured, so the changed item would not be saved.',
                msgSaveUrlNotConfiguredSoSkip:'\'saveUrl\' not configured, so the added item would not be saved.',
                msgDeleteUrlNotConfiguredSoSkip:'\'deleteUrl\' not configured, so the selected item would not be deleted.',
                msgSavingItemByIndex:'Saving No.{0} item.',
                msgYouShouldSelectARow:'You should select a row!',
                msgRequestGotEmptyData:'Your request got empty responsed data!',
            }
        },
        
        formatTime: function(fmt, tim) {
            if (tim == undefined || tim == null || tim == 0 || tim == '') {
                return '';
            }
            return new Date(parseInt(tim) * 1000).Format(fmt);
        },
        humanTime: function(tim) {
            if (tim == undefined || tim == null || tim == 0 || tim == '') {
                return '';
            }
            return new Date(parseInt(tim) * 1000).Format('yyyy-MM-dd hh:mm:ss');
        },
        toTimestamp: function(dateStr) {
            if (dateStr != '') {
                var tim = new Date(dateStr.replace(/-/g, '/'));
                return Math.ceil(tim.getTime() / 1000);
            }
            return 0;
        },
        secondsToHuman: function(secs) {
            var txt = '';
            if (secs < 0) {
                txt = '-';
                secs = Math.abs(secs);
            }
            
            var day = Math.floor(secs / 86400);
            var hour = Math.floor((secs % 86400) / 3600);
            var min = Math.floor((secs % 3600) / 60);
            var sec = secs % 60;
            if (day > 2) {
                return txt + day + $.custom.utils.lan.defaults.days;
            }
            else if (day) {
                txt += day + $.custom.utils.lan.defaults.days;
                if (hour == 0 && (min || sec)) {
                    hour = 1;
                }
                if (hour) {
                    txt += hour + $.custom.utils.lan.defaults.hours;
                }
                return txt;
            }
            
            if (hour > 2) {
                txt += hour + $.custom.utils.lan.defaults.hours;
                if (min == 0 && sec) {
                    min = 1;
                }
                if (min) {
                    txt += min + $.custom.utils.lan.defaults.minutes;
                }
                return txt;
            }
            
            if (hour) {
                txt += hour + $.custom.utils.lan.defaults.hours;
                txt += min + $.custom.utils.lan.defaults.minutes;
            }
            else if (min) {
                txt += min + $.custom.utils.lan.defaults.minutes;
            }
            if (sec) {
                txt += sec + $.custom.utils.lan.defaults.seconds;
            }
            if (txt == '') {
                return '0' + $.custom.utils.lan.defaults.seconds;
            }
            return txt;
        },
        
        format:function(s) {
            if (!s) {return '';}
            var ss = s.split(/\{\d+?\}/);
            for (var i = 0; i < ss.length; i++) {
                if (!arguments[i + 1]) {
                    break;
                }
                ss[i] += arguments[i + 1];
            }
            return ss.join('');
        },

        checkboxgroup: {
            setChildCheckBoxChecked: function (parentObj, checked) {
                if (parentObj.nodeName == 'DIV' && parentObj.className.indexOf('checkbox-group-wrapper') >= 0) {
                    for (var i in parentObj.childNodes) {
                        var childNode = parentObj.childNodes[i];
                        if (childNode.nodeName == 'LABEL' && childNode.childNodes.length > 0) {
                            for (var j in childNode.childNodes) {
                                var childNode2 = childNode.childNodes[j];
                                if (childNode2.nodeName == 'INPUT' && childNode2.type == 'checkbox') {
                                    if (childNode2.checked != checked) {
                                        childNode2.checked = checked;

                                        if (checked) {
                                            // set parent checkbox checked
                                            $.custom.utils.checkboxgroup.setChildCheckBoxChecked(parentObj.parentNode, checked);
                                            break;
                                        }
                                        else {
                                            // set child checkbox unchecked
                                            var next = childNode2.parentNode.nextSibling;
                                            while (next) {
                                                if (next.nodeName == 'DIV' && next.className.indexOf('checkbox-group-wrapper') >= 0) {
                                                    $.custom.utils.checkboxgroup.setChildCheckBoxChecked(next, checked);
                                                    break;
                                                }
                                                next = next.nextSibling;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        else if (!checked && childNode.nodeName == 'DIV' && childNode.className.indexOf('checkbox-group-wrapper') >= 0 ) {
                            $.custom.utils.checkboxgroup.setChildCheckBoxChecked(childNode, checked);
                        }
                    }
                }
            },
            
            setParentCheckBoxChecked: function (node, checked) {
                var parentObj = node.parentNode.parentNode;
                if (parentObj.nodeName == 'DIV' && parentObj.className.indexOf('checkbox-group-wrapper') >= 0) {
                    var prevObj = parentObj.previousElementSibling;
                    while(prevObj && (prevObj.nodeName != 'LABEL' || prevObj.childNodes.length == 0))
                    {
                        prevObj = prevObj.previousElementSibling;
                    }
                    if (prevObj && prevObj.nodeName == 'LABEL' && prevObj.childNodes.length > 0) {
                        for (var j in prevObj.childNodes) {
                            var childNode2 = prevObj.childNodes[j];
                            if (childNode2.nodeName == 'INPUT' && childNode2.type == 'checkbox') {
                                if (childNode2.checked != checked) {
                                    childNode2.checked = checked;

                                    if (checked) {
                                        // set parent checkbox checked
                                        $.custom.utils.checkboxgroup.setParentCheckBoxChecked(childNode2, checked);
                                        break;
                                    }
                                    else {
                                        // set child checkbox unchecked
                                        var next = childNode2.parentNode.nextSibling;
                                        while (next) {
                                            if (next.nodeName == 'DIV' && next.className.indexOf('checkbox-group-wrapper') >= 0) {
                                                $.custom.utils.checkboxgroup.setChildCheckBoxChecked(next, checked);
                                                break;
                                            }
                                            next = next.nextSibling;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            },
            
            onCheck: function (obj) {
                if (obj.checked == true) {
                    var parentObj = obj.parentNode.parentNode;
                    if (parentObj.nodeName == 'DIV' && parentObj.className.indexOf('checkbox-group-wrapper') >= 0) {
                        $.custom.utils.checkboxgroup.setParentCheckBoxChecked(obj, true);
                    }
                }
                else {
                    // set child checkbox unchecked
                    var next = obj.parentNode.nextSibling;
                    while (next) {
                        if (next.nodeName == 'DIV' && next.className.indexOf('checkbox-group-wrapper') >= 0) {
                            $.custom.utils.checkboxgroup.setChildCheckBoxChecked(next, false);
                            break;
                        }
                        next = next.nextSibling;
                    }
                }
            },
        },

        panel: {
            setTitle: function(panelId, title) {
                if ($.custom.uiframework == 'easyui') {
                    return $.custom.easyui.panel.setTitle(panelId, title);
                }
                else if ($.custom.uiframework == 'dwz') {
                    return $.custom.dwz.panel.setTitle(panelId, title);
                }
            },
            reload: function(panelId, url, params) {
                if ($.custom.uiframework == 'easyui') {
                    return $.custom.easyui.panel.reload(panelId, url, params);
                }
                else if ($.custom.uiframework == 'dwz') {
                    return $.custom.dwz.panel.reload(panelId, url, params);
                }
            },
            clear: function(panelId) {
                if ($.custom.uiframework == 'easyui') {
                    return $.custom.easyui.panel.clear(panelId);
                }
                else if ($.custom.uiframework == 'dwz') {
                    return $.custom.dwz.panel.clear(panelId);
                }
            }
        },
        
        combotree: {
            getSelectedValue: function(comboTreeId) {
                if ($.custom.uiframework == 'easyui') {
                    return $.custom.easyui.combotree.getSelectedValue(comboTreeId);
                }
                else if ($.custom.uiframework == 'dwz') {
                    return $.custom.dwz.combotree.getSelectedValue(comboTreeId);
                }
                return '';
            }
        },
        
        datalist: {
            getSelectedValue: function(id) {
                if ($.custom.uiframework == 'easyui') {
                    return $.custom.easyui.datalist.getSelectedValue(id);
                }
                else if ($.custom.uiframework == 'dwz') {
                    return $.custom.dwz.datalist.getSelectedValue(id);
                }
                return '';
            },
            getSelectedNames: function(id) {
                if ($.custom.uiframework == 'easyui') {
                    return $.custom.easyui.datalist.getSelectedNames(id);
                }
                else if ($.custom.uiframework == 'dwz') {
                    return $.custom.dwz.datalist.getSelectedNames(id);
                }
                return '';
            }
        },
        
        chart: {
            drawLineGraph: function (graphID, opts) {
                var options = {
                    axis: opts.axis || "0 0 1 1", // Where to put the labels (trbl)
                    axisxstep: opts.axisxstep || 1, // How many x interval labels to render (axisystep does the same for the y axis)
                    axisxlables: opts.axisxlables || undefined,
                    axisystep: opts.axisystep || 1,
                    axisylabels: opts.axisylabels || undefined,
                    shade: opts.shade || true, // true, false
                    smooth: opts.smooth || false, //曲线
                    symbol: opts.symbol || "circle",
                    width: opts.linewidth || 0.8,  // line width
                    colors: opts.colors || undefined,
                };

                // Make the raphael object
                var r = Raphael(graphID); 

                var _xoffset = opts.xoffset || 20;
                var lines = r.linechart(
                    _xoffset, // X start in pixels
                    opts.yoffset || 20, // Y start in pixels
                    opts.width || r.width - _xoffset - 20, // Width of chart in pixels
                    opts.height || r.height, // Height of chart in pixels
                    opts.datax || [1, 2], // Array of x coordinates equal in length to ycoords
                    opts.datay || [[0, 0]], // Array of y coordinates equal in length to xcoords
                    options // opts object
                ).hoverColumn(function () {
                    this.tags = r.set();

                    // get displaying index and display count
                    var displaying_y_indexes = new Array();
                    var displaying_y_count = 0;
                    for (var _i = 0; _i < this.symbols.length; _i++) {
                        if (this.symbols[_i].node.style.display != 'none') {
                            displaying_y_indexes[_i] = true;
                            displaying_y_count++;
                        }
                    }

                    var box_x = this.x, box_y = 30,
                        box_w = 160, box_h = 20 * displaying_y_count + 20;
                    if (box_x + box_w > r.width) box_x -= box_w;
                    var box = r.rect(box_x,box_y,box_w,box_h).attr({stroke: "#030303", "stroke-width": 0.8, fill:'#030303', 'fill-opacity':0.8, r:6});
                    this.tags.push(box);

                    var _y_i = 0;
                    var txt_x = '@'+this.axis;
                    if (opts.xvalue_formatter) {
                        txt_x = opts.xvalue_formatter(this.axis);
                    }
                    var t_x = r.text(box_x+10, box_y+10 + _y_i * 18, '' + txt_x).attr({fill: '#fff', 'text-anchor':'start', 'font-weight':'normal'})
                    this.tags.push(t_x);
                    _y_i++;

                    var end_fix = '';
                    if (opts.value_endfix && opts.value_endfix != '') {
                        end_fix = '' + opts.value_endfix;
                    }

                    for (var i = 0; i < this.y.length; i++) {
                        if (displaying_y_indexes[i]) {
                            //this.tags.push(r.blob(this.x, this.y[i], "$"+this.values[i]).insertBefore(this).attr([{ fill: "#ffa500", stroke: "#000"}, { fill: this.symbols[i].attr("fill") }]));
                            //var t = r.blob(this.x, this.y[i], "$"+this.values[i]).insertBefore(this).attr([{ fill: "#ffffff", stroke: "#000"}, { fill: this.symbols[i].attr("fill") }])
                            var t = r.text(box_x+10, box_y+10 + _y_i*18,"◆ "+this.values[i]+end_fix).attr({fill: this.symbols[i].attr("fill"), 'text-anchor':'start', 'font-weight':'normal'})
                            this.tags.push(t);

                            _y_i++;
                        }
                    }


                }, function () {
                    this.tags && this.tags.remove();
                });
                
                //lines.node.style.width = '100%';
                lines.symbols.attr({ r: 1 });   // point radius

                return lines;
            },
        },
        
        imgfilebox: {
            previewImage: function(fileObj,imgPreviewId,divPreviewId, textFieldId){
                var allowExtention=".jpg,.bmp,.gif,.png";//允许上传文件的后缀名document.getElementById("hfAllowPicSuffix").value;
                var extention=fileObj.value.substring(fileObj.value.lastIndexOf(".")+1).toLowerCase();
                var browserVersion= window.navigator.userAgent.toUpperCase();
                var filePath = fileObj.value;
                if (filePath == '') {
                    if (textFieldId) {
                        document.getElementById(textFieldId).setAttribute('value', filePath);
                    }
                    return;
                }
                if(allowExtention.indexOf(extention)>-1){
                    if(fileObj.files){//HTML5实现预览，兼容chrome、火狐7+等 
                        if(window.FileReader){
                            var reader = new FileReader();
                            reader.onload = function(e){
                                document.getElementById(imgPreviewId).setAttribute("src",e.target.result);
                            }
                            reader.readAsDataURL(fileObj.files[0]);
                        }else if(browserVersion.indexOf("SAFARI")>-1){
                            alert("不支持Safari6.0以下浏览器的图片预览!");
                        }
                    }else if (browserVersion.indexOf("MSIE")>-1){
                        if(browserVersion.indexOf("MSIE 6")>-1){//ie6  
                            document.getElementById(imgPreviewId).setAttribute("src",fileObj.value);
                        }else{//ie[7-9]
                            fileObj.select();
                            if(browserVersion.indexOf("MSIE 9")>-1)
                                fileObj.blur();//不加上document.selection.createRange().text在ie9会拒绝访问  
                            var newPreview =document.getElementById(divPreviewId+"New");
                            if(newPreview==null){
                                newPreview =document.createElement("div");
                                newPreview.setAttribute("id",divPreviewId+"New");
                                newPreview.style.width = document.getElementById(imgPreviewId).width+"px";
                                newPreview.style.height = document.getElementById(imgPreviewId).height+"px";
                                newPreview.style.border="solid 1px #d2e2e2";
                            }
                            newPreview.style.filter="progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod='scale',src='" + document.selection.createRange().text + "')";
                            var tempDivPreview=document.getElementById(divPreviewId);
                            tempDivPreview.parentNode.insertBefore(newPreview,tempDivPreview);
                            tempDivPreview.style.display="none";
                        }
                    }else if(browserVersion.indexOf("FIREFOX")>-1){//firefox  
                        var firefoxVersion= parseFloat(browserVersion.toLowerCase().match(/firefox\/([\d.]+)/)[1]);
                        if(firefoxVersion<7){//firefox7以下版本  
                            document.getElementById(imgPreviewId).setAttribute("src",fileObj.files[0].getAsDataURL());
                        }else{//firefox7.0+ 
                            document.getElementById(imgPreviewId).setAttribute("src",window.URL.createObjectURL(fileObj.files[0]));
                        }
                    }else{
                        document.getElementById(imgPreviewId).setAttribute("src",fileObj.value);
                    }
                }else{
                    alert("仅支持"+allowExtention+"为后缀名的文件!");
                    fileObj.value="";//清空选中文件  
                    if(browserVersion.indexOf("MSIE")>-1){
                        fileObj.select();
                        document.selection.clear();
                    }
                    fileObj.outerHTML=fileObj.outerHTML;
                    filePath = '';
                }
                
                if (textFieldId && filePath !== undefined) {
                    document.getElementById(textFieldId).setAttribute('value', filePath);
                }
            },
            
        },
        
        getFormData: function(formId) {
            if (formId.indexOf('#') !== 0) {
                formId = '#' + formId;
            }
            
            var arr = $(formId).serializeArray();
            var o = {};
            $.each(arr, function () {
                if (o[this.name] !== undefined) {
                    if (!o[this.name].push) {
                        o[this.name] = [o[this.name]];
                    }
                    o[this.name].push(this.value || '');
                } else {
                    o[this.name] = this.value || '';
                }
            });
            
            return o;
        },
        
        preProcessLoadError:function(e) {
            var statusCode = e ? e.status : '';
            if (statusCode == 302 || statusCode == 0) {
                $.custom.bootstrap.alert(
                    $.custom.lan.defaults.sys.sessionTimeoutOrSigninByOtherPleaseResignin,
                    $.custom.lan.defaults.sys.prompt,
                    function() {
                        window.location.reload();
                    }
                );
                return true;
            }
            return false;
        },
        
    };
    
    $.custom.element = {
        load : function(selector, urlOrOptions) {
            var options = {
                url: '#',
                data: null,
                type: 'GET',
                //contentType: 'application/x-www-form-urlencoded', // 'text/html',
                success: function (result) {
                    if (selector) {
                        $(selector).html(result);
                    }
                    else {
                        $(document).html(result);
                    }
                },
                error: function(e) {
                    if ($.custom.utils.preProcessLoadError(e)) {
                        return;
                    }
                    var statusCode = e.status;
                    var statusText = e.statusText;
                    var responseText = e.responseText;
                    var msg = $.custom.utils.format($.custom.utils.lan.defaults.msgGotResponseFailedError, ' code:' + statusCode + ' error:' + statusText) + responseText;
                    if (selector) {
                        $(selector).html(msg);
                    }
                    else {
                        $(document).html(msg);
                    }
                }
            };
            
            if (typeof urlOrOptions == 'string') {
                options.url = urlOrOptions;
            }
            else if (typeof urlOrOptions == 'object') {
                options = $.extend(options, urlOrOptions);
            }
            
            $.ajax(options);
        }
    };

})(jQuery);
