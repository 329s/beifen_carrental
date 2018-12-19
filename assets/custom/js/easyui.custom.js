/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
(function($){
    if ($.custom == undefined) {
        $.custom = {};
    }
    if ($.custom.utils == undefined) {
        $.custom.utils = {};
    }
    
    $.custom.easyui = {
        defaults: {
        },
        
        config: {
            openTabInIframe:false,
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

        isObjectEmpty:function(obj) {
            var isEmpty = true;
            if (obj) {
                for (var k in obj) {
                    isEmpty = false;
                    break;
                }
            }
            return isEmpty;
        },
        
        refreshTheme:function(themeName) {
            $.ajax({
                type:'get',
                url:'../site/changetheme?theme='+encodeURI(themeName),
                success: function (data) {
                    //easyuiFuncOnProcessSuccessEvents(data);
                    window.location.reload();
                },
                error: function (e) {
                    easyuiFuncOnProcessErrorEvents(e);
                }
            });
        },
        
        preProcessLoadError:function(e) {
            var statusCode = e ? e.status : '';
            if (statusCode == 302 || statusCode == 0) {
                $.messager.alert({
                    title:$.custom.lan.defaults.sys.prompt,
                    msg:$.custom.lan.defaults.sys.sessionTimeoutOrSigninByOtherPleaseResignin,
                    fn:function() {
                        window.location.reload();
                    }
                });
                return true;
            }
            return false;
        },
        
        navtab: {
            showTab:function(tabObj, title, url, tabPanelId, isOpenInIframe) {
                if (tabObj === undefined || tabObj.length === 0) {
                    return ;
                }
                
                if (tabObj.length > 1) {
                    tabObj = $(tabObj[0]);
                }
                
                var content = undefined;
                var href = undefined;
                if (isOpenInIframe === true) {
                    if (url.indexOf('?') === -1) {
                        url += '?_blank=true';
                    }
                    else {
                        url += '&_blank=true';
                    }
                    content = '<iframe scrolling="yes" frameborder="0" style="width:100%;height:100%" src="'+url+'"></iframe>';
                }
                else {
                    href = url;
                }

                if (tabObj.tabs('exists', title)) {
                    var preTab = tabObj.tabs('getSelected');
                    tabObj.tabs('select', title);
                    var curTab = tabObj.tabs('getSelected');
                    var opts = curTab.panel('options');
                    var isRefresh = true;
                    if (isOpenInIframe) {
                        if (content == opts.content) {
                            isRefresh = false;
                        }
                    }
                    else {
                        if (href == opts.href) {
                            isRefresh = false;
                        }
                    }
                    
                    if (opts.id != tabPanelId) {
                        tabObj.tabs('update', {
                            tab:curTab,
                            options: {
                                id:tabPanelId
                            }
                        });
                    }
                    
                    if (isRefresh || preTab === curTab) {
                        if (isOpenInIframe) {
                            tabObj.tabs('update', {
                                tab:curTab,
                                type:'all',
                                options: {
                                    content:content,
                                    href:href,
                                    id:tabPanelId
                                }
                            });
                        }
                        else {
                            curTab.panel('refresh', url);
                        }
                    }
                } else {
                    tabObj.tabs('add',{
                        id:tabPanelId,
                        title:title,
                        content:content,
                        href:href,
                        closable:true,
                        tools:[
                            {iconCls:'icon-mini-refresh', handler:function(){ easyuiFuncNavTabRefreshCurTab(true); }}
                        ],
                        onLoadError:function(e) {
                            if ($.custom.easyui.preProcessLoadError(e)) {
                                return;
                            }
                            var statusCode = e ? e.status : '';
                            var statusText = e ? e.statusText : '';
                            var responseText = e ? e.responseText : '';
                            var content = '';
                            if (responseText.length > 0) {
                                content = responseText;
                            }
                            else {
                                content = $.custom.easyui.format($.custom.utils.lan.defaults.msgGotResponseFailedError, ' code:' + statusCode + ' error:' + statusText) + responseText;
                            }
                            
                            var tabsObj = $('.easyui-tabs', this.parentNode.parentNode.parentNode.parentNode);
                            if (tabsObj.length > 0) {
                                var curTab = tabObj.tabs('getSelected');
                                tabsObj.tabs('update', {
                                    tab:curTab,
                                    type:'all',
                                    options: {
                                        content:content,
                                        href:undefined,
                                    }
                                });
                            }
                            
                        }
                    });
                    tabObj.tabs('select', title);
                }

            },
            show:function(tabId, title, url, tabPanelId, isOpenInIframe) {
                if (tabId.indexOf('#') !== 0) {
                    tabId = '#' + tabId;
                }
                $.custom.easyui.navtab.showTab($(tabId), title, url, tabPanelId, isOpenInIframe);
            },
            onSelect:function(title, index) {
                var selTab = $(this).tabs('getTab', index);
                if (selTab && selTab.length > 0) {
                    var opts = selTab.panel('options');
                    if (opts.hadopened_) {
                        setTimeout(function(){ easyuiFuncNavTabRefreshCurTabGridData(); }, 100);
                    }
                    else {
                        opts.hadopened_ = true;
                    }
                }
            }
        },
        
        datagrid: {
            getById:function(dgId) {
                var clsText = $(dgId).attr('class');
                if (clsText.indexOf('easyui-combogrid') > -1) {
                    return $(dgId).combogrid('grid');
                }
                return $(dgId);
            },
            
            getSelectedUrlParams:function(dgId, isSelectAll) {
                var dbObj = $.custom.easyui.datagrid.getById(dgId);
                var opts = dbObj.datagrid('options');
                var params = {};
                var selectedArray = dbObj.datagrid('getSelections');
                
                if (isSelectAll === undefined) {
                    isSelectAll = false;
                }
                
                if (selectedArray.length > 0) {
                    var firstSelected = selectedArray[0];
                    var keyFieldArray = new Array();
                    if (opts.idField !== undefined && firstSelected[opts.idField] !== undefined) {
                        keyFieldArray.push(opts.idField);
                    }
                    else {
                        for (var k in firstSelected) {
                            if (k.substr(-2).toLowerCase() == 'id' || k.substr(0, 2).toLowerCase() == 'id') {
                                keyFieldArray.push(k);
                            }
                        }
                    }
                    
                    for (var i in keyFieldArray) {
                        var k = keyFieldArray[i];
                        if (isSelectAll && selectedArray.length > 1) {
                            var arr = new Array();
                            for (var j in selectedArray) {
                                arr.push(selectedArray[j][k]);
                            }
                            params[k] = arr;
                        }
                        else {
                            params[k] = firstSelected[k];
                        }
                    }
                }
                
                return params;
            },
            getSelectedFormatUrlParam:function(dgId, url) {
                var params = $.custom.easyui.datagrid.getSelectedUrlParams(dgId, true);

                var sep = '?';
                if (url.indexOf('?') >= 0) {
                    sep = '&';
                }

                for (var k in params) {
                    url += sep + encodeURI(k) + '=' + encodeURI(params[k]);
                    sep = '&';
                }
                return url;
            },
            endEditing:function(dgId) {
                var oTarget = $.custom.easyui.datagrid.getById(dgId);
                var opts = oTarget.datagrid('options');
                var editIndex = opts.curEditingIndex;
                if (editIndex == undefined){return true}
                if (oTarget.datagrid('validateRow', editIndex)){
                    /*
                    var eds = oTarget.datagrid('getEditors', editIndex);
                    var ed;
                    for (ed in eds) {
                        if (ed.type == 'combobox') {
                            
                        }
                        else if (ed.type == 'checkbox') {
                            
                        }
                        else {
                            
                        }
                    }
                     */
                    oTarget.datagrid('unselectRow', editIndex)
                            .datagrid('endEdit', editIndex);
                    
                    editIndex = undefined;
                    delete opts.curEditingIndex;
                    return true;
                } else {
                    return false;
                }
            },
            setCustomEvents:function(dgId) {
                var oTarget = $.custom.easyui.datagrid.getById(dgId);
                var opts = oTarget.datagrid('options');
                if (opts.customData) {
                    if (opts.customData.editorEvents) {
                        for (var k in opts.customData.editorEvents) {
                            var ed = oTarget.datagrid('getEditor', {index:opts.curEditingIndex,field:k});
                            if (ed) {
                                var ev = opts.customData.editorEvents[k];
                                if (typeof(ev) == 'object') {
                                    switch (ed.type) {
                                    case 'text':
                                    case 'textbox':
                                        {
                                            $(ed.target).textbox($.extend({}, ev));
                                        }break;
                                    case 'numberbox':
                                        {
                                            $(ed.target).numberbox($.extend({}, ev));
                                        }break;
                                    case 'datebox':
                                        {
                                            $(ed.target).datebox($.extend({}, ev));
                                        }break;
                                    case 'datetimebox':
                                        {
                                            $(ed.target).datetimebox($.extend({}, ev));
                                        }break;
                                    case 'combobox':
                                        {
                                            $(ed.target).combobox($.extend({}, ev));
                                        }break;
                                    case 'combotree':
                                        {
                                            $(ed.target).combotree($.extend({}, ev));
                                        }break;
                                    case 'combogrid':
                                        {
                                            $(ed.target).combogrid($.extend({}, ev));
                                        }break;
                                    case 'validatebox':
                                        {
                                            $(ed.target).validatebox($.extend({}, ev));
                                        }break;
                                    case 'textarea':
                                        {
                                            $(ed.target).textarea($.extend({}, ev));
                                        }break;
                                    case 'checkbox':
                                        {
                                            $(ed.target).checkbox($.extend({}, ev));
                                        }break;
                                    }
                                }
                            }
                        }
                    }
                }
            },
            
            onLoadError:function(e) {
                easyuiFuncOnProcessErrorEvents(e);
            },
            
        },
        
        dialog: {
            onLoadError:function(e) {
                if ($.custom.easyui.preProcessLoadError(e)) {
                    return;
                }
                var statusCode = e ? e.status : '';
                var statusText = e ? e.statusText : '';
                var responseText = e ? e.responseText : '';
                var content = '';
                if (responseText.length > 0) {
                    content = responseText;
                }
                else {
                    content = $.custom.easyui.format($.custom.utils.lan.defaults.msgGotResponseFailedError, ' code:' + statusCode + ' error:' + statusText) + responseText;
                }

                var dlgObj = $('.easyui-dialog', this.parentNode);
                if (dlgObj.length > 0) {
                    var opts = dlgObj.dialog('options');
                    opts.content = content;
                    opts.href = undefined;
                    dlgObj.dialog(opts);
                }
            }
        },
        
        window: {
            onLoadError:function(e) {
                if ($.custom.easyui.preProcessLoadError(e)) {
                    return;
                }
                var statusCode = e ? e.status : '';
                var statusText = e ? e.statusText : '';
                var responseText = e ? e.responseText : '';
                var content = '';
                if (responseText.length > 0) {
                    content = responseText;
                }
                else {
                    content = $.custom.easyui.format($.custom.utils.lan.defaults.msgGotResponseFailedError, ' code:' + statusCode + ' error:' + statusText) + responseText;
                }

                var wndObj = $('.easyui-window', this.parentNode);
                if (wndObj.length > 0) {
                    var opts = wndObj.window('options');
                    opts.content = content;
                    opts.href = undefined;
                    //wndObj.window(opts);
                }
            }
        },
        
        panel: {
            onLoadError:function(e) {
                if ($.custom.easyui.preProcessLoadError(e)) {
                    return;
                }
                easyuiFuncOnProcessErrorEvents(e);
                var statusCode = e ? e.status : '';
                var statusText = e ? e.statusText : '';
                var responseText = e ? e.responseText : '';
                var content = '';
                if (responseText.length > 0) {
                    content = responseText;
                }
                else {
                    content = $.custom.easyui.format($.custom.utils.lan.defaults.msgGotResponseFailedError, ' code:' + statusCode + ' error:' + statusText) + responseText;
                }

                var wndObj = $('.easyui-panel', this.parentNode);
                if (wndObj.length > 0) {
                    wndObj.panel('clear');
                    var opts = wndObj.panel('options');
                    opts.content = content;
                    opts.href = undefined;
                    //wndObj.panel('open').panel('refresh');
                }
            },

            setTitle: function(panelId, title) {
                if (panelId.indexOf('#') !== 0) {
                    panelId = '#' + panelId;
                }
                $(panelId).panel('setTitle', title);
            },
            reload: function(panelId, url, params) {
                if (panelId.indexOf('#') !== 0) {
                    panelId = '#' + panelId;
                }
                if (!url) {
                    return;
                }
                if (params) {
                    var opts = $(panelId).panel('options').queryParams;
                    if ($.custom.easyui.isObjectEmpty(opts)) {
                        $(panelId).panel('options').queryParams = params;
                    }
                    else {
                        for (var k in params) {
                            opts[k] = params[k];
                        }
                    }
                }
                $(panelId).panel('refresh', url);
            },
            clear:function(panelId) {
                if (panelId.indexOf('#') !== 0) {
                    panelId = '#' + panelId;
                }
                $(panelId).panel('clear');
            }
        },
        
        combotree: {
            getSelectedValue: function(id) {
                if (id.indexOf('#') !== 0) {
                    id = '#' + id;
                }
                var valArr = $(id).combotree('getValues');
                var a = new Array();
                for (var _i in valArr) {
                    a.push(valArr[_i]);
                }
                if (a.length == 0) {
                    return null;
                }
                else if (a.length == 1) {
                    return a[0];
                }
                return a;
            },
        },
        
        datalist: {
            getSelectedValue: function(id) {
                if (id.indexOf('#') !== 0) {
                    id = '#' + id;
                }
                var valArr = $(id).datalist('getSelections');
                var a = new Array();
                for (var _i in valArr) {
                    a.push(valArr[_i].value);
                }
                if (a.length == 0) {
                    return null;
                }
                else if (a.length == 1) {
                    return a[0];
                }
                return a;
            },
            getSelectedNames: function(id) {
                if (id.indexOf('#') !== 0) {
                    id = '#' + id;
                }
                var valArr = $(id).datalist('getSelections');
                var a = new Array();
                for (var _i in valArr) {
                    a.push(valArr[_i].text);
                }
                if (a.length == 0) {
                    return null;
                }
                else if (a.length == 1) {
                    return a[0];
                }
                return a;
            },
        },
        
        alert: {
            show: function(msg, alertTitle, alertType, msgIcon, timeout) {
                var alertParam = {showType:'slide', showSpeed:400, timeout:3000, resizable:true,
                    style:{right:'',top:document.body.scrollTop+document.documentElement.scrollTop,bottom:''}
                };
                if (!msg) {
                    msg = '';
                }
                if (msg.length > 256) {
                    alertParam.width = 'auto';
                    alertParam.height = 'auto';
                    alertParam.id = 'easyui-alert-messager';
                    alertParam.onOpen = function() {
                        var width = $(this).width();
                        var height = $(this).height();
                        var winWidth = $(document.body).width();
                        var winHeight = $(document.body).height();
                        if (width > winWidth - 88) {
                            var offset = $(this).parent().offset();
                            $(this).parent().width(winWidth - 64);
                            $(this).width(winWidth - 88);
                            offset.left = 32;
                            $(this).parent().offset(offset);
                        }
                        if (height > winHeight - 78) {
                            $(this).parent().height(winHeight - 12);
                            $(this).height(winHeight - 78);
                        }
                    };
                }
                if (!msgIcon) {
                    msgIcon = 'info';
                }
                msg = '<div class="messager-icon messager-' + msgIcon + '"></div><div>' + msg + '</div><div style=\"clear:both;\"/>';

                alertParam.title = alertTitle;
                alertParam.msg = msg;
                alertParam.icon = alertType;
                if (timeout) {
                    alertParam.timeout = timeout;
                }

                $.messager.show(alertParam);
            }
        },
    }
})(jQuery);

/**
 * 包含easyui的扩展和常用的方法
 *
 * @author
 *
 * @version 20120806
 */
 
var wjc = $.extend({}, wjc);/* 定义全局对象，类似于命名空间或包的作用 */
 
/**
 *
 * @requires jQuery,EasyUI
 *
 * panel关闭时回收内存，主要用于layout使用iframe嵌入网页时的内存泄漏问题
 */
$.fn.panel.defaults.onBeforeDestroy = function() {
    var frame = $('iframe', this);
    try {
        if (frame.length > 0) {
            for ( var i = 0; i < frame.length; i++) {
                frame[i].contentWindow.document.write('');
                frame[i].contentWindow.close();
            }
            frame.remove();
            if ($.browser.msie) {
                CollectGarbage();
            }
        }
    } catch (e) {
    }
};
 
/**
 * 使panel和datagrid在加载时提示
 *
 *
 * @requires jQuery,EasyUI
 *
 */
//$.fn.panel.defaults.loadingMessage = '加载中....';
//$.fn.datagrid.defaults.loadMsg = '加载中....';
 
/**
 * @author wfire
 *
 * @requires jQuery,EasyUI
 *
 * 通用错误提示
 *
 * 用于datagrid/treegrid/tree/combogrid/combobox/form加载数据出错时的操作
 */
//var easyuiErrorFunction = function(XMLHttpRequest) {
//    $.messager.progress('close');
//  $.messager.alert('错误', XMLHttpRequest.responseText);
//  $.messager.confirm('错  误',XMLHttpRequest.responseText,function(r){  
//      if (r){
//          parent.location.replace('login.jsp');
//      }  
//  });
//};
//$.fn.datagrid.defaults.onLoadError = easyuiErrorFunction;
//$.fn.treegrid.defaults.onLoadError = easyuiErrorFunction;
//$.fn.tree.defaults.onLoadError = easyuiErrorFunction;
//$.fn.combogrid.defaults.onLoadError = easyuiErrorFunction;
//$.fn.combobox.defaults.onLoadError = easyuiErrorFunction;
//$.fn.form.defaults.onLoadError = easyuiErrorFunction;
 
/**
 *
 * @requires jQuery,EasyUI
 *
 * 为datagrid、treegrid增加表头菜单，用于显示或隐藏列，注意：冻结列不在此菜单中
 */
var createGridHeaderContextMenu = function(e, field) {
    e.preventDefault();
    var grid = $(this);/* grid本身 */
    var headerContextMenu = this.headerContextMenu;/* grid上的列头菜单对象 */
    if (!headerContextMenu) {
        var tmenu = $('<div style="width:100px;"></div>').appendTo('body');
        var fields = grid.datagrid('getColumnFields');
        for ( var i = 0; i < fields.length; i++) {
            var fildOption = grid.datagrid('getColumnOption', fields[i]);
            if (!fildOption.hidden) {
                $('<div iconCls="icon-ok" field="' + fields[i] + '"/>').html(fildOption.title).appendTo(tmenu);
            } else {
                $('<div iconCls="icon-empty" field="' + fields[i] + '"/>').html(fildOption.title).appendTo(tmenu);
            }
        }
        headerContextMenu = this.headerContextMenu = tmenu.menu({
            onClick : function(item) {
                var field = $(item.target).attr('field');
                if (item.iconCls == 'icon-ok') {
                    grid.datagrid('hideColumn', field);
                    $(this).menu('setIcon', {
                        target : item.target,
                        iconCls : 'icon-empty'
                    });
                } else {
                    grid.datagrid('showColumn', field);
                    $(this).menu('setIcon', {
                        target : item.target,
                        iconCls : 'icon-ok'
                    });
                }
            }
        });
    }
    headerContextMenu.menu('show', {
        left : e.pageX,
        top : e.pageY
    });
};
//$.fn.datagrid.defaults.onHeaderContextMenu = createGridHeaderContextMenu;
//$.fn.treegrid.defaults.onHeaderContextMenu = createGridHeaderContextMenu;

/**
 *
 * @requires jQuery,EasyUI
 *
 * 扩展validatebox，添加验证两次密码功能
 */
$.extend($.fn.validatebox.defaults.rules, {
    eqPwd : {
        validator : function(value, param) {
            return value == $(param[0]).val();
        },
        message : '密码不一致！'
    },
    idcard : {// 验证身份证
        validator : function(value) {
            return /^\d{15}(\d{2}[A-Za-z0-9])?$/i.test(value);
        },
        message : '身份证号码格式不正确'
    },
    minLength: {
        validator: function(value, param){
            return value.length >= param[0];
        },
        message: '请输入至少（2）个字符.'
    },
    length:{validator:function(value,param){
        var len=$.trim(value).length;
            return len>=param[0]&&len<=param[1];
        },
        message:"输入内容长度必须介于{0}和{1}之间."
    },
    phone : {// 验证电话号码
        validator : function(value) {
            return /^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/i.test(value);
        },
        message : '格式不正确,请使用下面格式:010-88888888'
    },
    mobile : {// 验证手机号码
        validator : function(value) {
            return /^(13|15|18)\d{9}$/i.test(value);
        },
        message : '手机号码格式不正确'
    },
    intOrFloat : {// 验证整数或小数
        validator : function(value) {
            return /^\d+(\.\d+)?$/i.test(value);
        },
        message : '请输入数字，并确保格式正确'
    },
    currency : {// 验证货币
        validator : function(value) {
            return /^\d+(\.\d+)?$/i.test(value);
        },
        message : '货币格式不正确'
    },
    qq : {// 验证QQ,从10000开始
        validator : function(value) {
            return /^[1-9]\d{4,9}$/i.test(value);
        },
        message : 'QQ号码格式不正确'
    },
    integer : {// 验证整数
        validator : function(value) {
            return /^[+]?[1-9]+\d*$/i.test(value);
        },
        message : '请输入整数'
    },
    age : {// 验证年龄
        validator : function(value) {
            return /^(?:[1-9][0-9]?|1[01][0-9]|120)$/i.test(value);
        },
        message : '年龄必须是0到120之间的整数'
    },
    chinese : {// 验证中文
        validator : function(value) {
            return /^[\Α-\￥]+$/i.test(value);
        },
        message : '请输入中文'
    },
    english : {// 验证英语
        validator : function(value) {
            return /^[A-Za-z]+$/i.test(value);
        },
        message : '请输入英文'
    },
    unnormal : {// 验证是否包含空格和非法字符
        validator : function(value) {
            return /.+/i.test(value);
        },
        message : '输入值不能为空和包含其他非法字符'
    },
    username : {// 验证用户名
        validator : function(value) {
            return /^[a-zA-Z][a-zA-Z0-9_]{5,15}$/i.test(value);
        },
        message : '用户名不合法（字母开头，允许6-16字节，允许字母数字下划线）'
    },
    faxno : {// 验证传真
        validator : function(value) {
            return /^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/i.test(value);
        },
        message : '传真号码不正确'
    },
    zip : {// 验证邮政编码
        validator : function(value) {
            return /^[0-9]\d{5}$/i.test(value);
        },
        message : '邮政编码格式不正确'
    },
    ip : {// 验证IP地址
        validator : function(value) {
            return /d+.d+.d+.d+/i.test(value);
        },
        message : 'IP地址格式不正确'
    },
    name : {// 验证姓名，可以是中文或英文
        validator : function(value) {
            return /^[\Α-\￥]+$/i.test(value)|/^\w+[\w\s]+\w+$/i.test(value);
        },
        message : '请输入姓名'
    },
    msn:{
        validator : function(value){
            return /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(value);
        },
        message : '请输入有效的msn账号(例：abc@hotnail(msn/live).com)'
    },
    // filebox验证文件大小的规则函数  
    // 如：validType : ['fileSize[1,"MB"]']  
    fileSize : {  
        validator : function(value, array) {  
            var size = array[0];  
            var unit = array[1];  
            if (!size || isNaN(size) || size == 0) {  
                $.error('验证文件大小的值不能为 "' + size + '"');  
            } else if (!unit) {  
                $.error('请指定验证文件大小的单位');  
            }  
            var index = -1;  
            var unitArr = new Array("bytes", "kb", "mb", "gb", "tb", "pb", "eb", "zb", "yb");  
            for (var i = 0; i < unitArr.length; i++) {  
                if (unitArr[i] == unit.toLowerCase()) {  
                    index = i;  
                    break;  
                }  
            }  
            if (index == -1) {  
                $.error('请指定正确的验证文件大小的单位：["bytes", "kb", "mb", "gb", "tb", "pb", "eb", "zb", "yb"]');  
            }  
            // 转换为bytes公式  
            var formula = 1;  
            while (index > 0) {  
                formula = formula * 1024;  
                index--;  
            }  
            // this为页面上能看到文件名称的文本框，而非真实的file  
            // $(this).next()是file元素  
            return $(this).next().get(0).files[0].size < parseFloat(size) * formula;  
        },  
        message : '文件大小必须小于 {0}{1}'  
    }
});
 
/**
 *
 * @requires jQuery,EasyUI
 *
 * 扩展tree，使其支持平滑数据格式
 */
$.fn.tree.defaults.loadFilter = function(data, parent) {
    var opt = $(this).data().tree.options;
    var idFiled, textFiled, parentField;
    if (opt.parentField) {
        idFiled = opt.idFiled || 'id';
        textFiled = opt.textFiled || 'text';
        parentField = opt.parentField;
        var i, l, treeData = [], tmpMap = [];
        for (i = 0, l = data.length; i < l; i++) {
            tmpMap[data[i][idFiled]] = data[i];
        }
        for (i = 0, l = data.length; i < l; i++) {
            if (tmpMap[data[i][parentField]] && data[i][idFiled] != data[i][parentField]) {
                if (!tmpMap[data[i][parentField]]['children'])
                    tmpMap[data[i][parentField]]['children'] = [];
                data[i]['text'] = data[i][textFiled];
                tmpMap[data[i][parentField]]['children'].push(data[i]);
            } else {
                data[i]['text'] = data[i][textFiled];
                treeData.push(data[i]);
            }
        }
        return treeData;
    }
    return data;
};
 
/**
 *
 * @requires jQuery,EasyUI
 *
 * 扩展treegrid，使其支持平滑数据格式
 */
$.fn.treegrid.defaults.loadFilter = function(data, parentId) {
    var opt = $(this).data().treegrid.options;
    var idFiled, textFiled, parentField;
    if (opt.parentField) {
        idFiled = opt.idFiled || 'id';
        textFiled = opt.textFiled || 'text';
        parentField = opt.parentField;
        var i, l, treeData = [], tmpMap = [];
        for (i = 0, l = data.length; i < l; i++) {
            tmpMap[data[i][idFiled]] = data[i];
        }
        for (i = 0, l = data.length; i < l; i++) {
            if (tmpMap[data[i][parentField]] && data[i][idFiled] != data[i][parentField]) {
                if (!tmpMap[data[i][parentField]]['children'])
                    tmpMap[data[i][parentField]]['children'] = [];
                data[i]['text'] = data[i][textFiled];
                tmpMap[data[i][parentField]]['children'].push(data[i]);
            } else {
                data[i]['text'] = data[i][textFiled];
                treeData.push(data[i]);
            }
        }
        return treeData;
    }
    return data;
};
 
/**
 * @author wfire
 *
 * @requires jQuery,EasyUI
 *
 * 扩展combotree，使其支持平滑数据格式
 */
$.fn.combotree.defaults.loadFilter = $.fn.tree.defaults.loadFilter;
 
/**
 *
 * @requires jQuery,EasyUI
 *
 * 防止panel/window/dialog组件超出浏览器边界
 * @param left
 * @param top
 */
var easyuiPanelOnMove = function(left, top) {
    var l = left;
    var t = top;
    if (l < 1) {
        l = 1;
    }
    if (t < 1) {
        t = 1;
    }
    var width = parseInt($(this).parent().css('width')) + 14;
    var height = parseInt($(this).parent().css('height')) + 14;
    var right = l + width;
    var buttom = t + height;
    var browserWidth = $(window).width();
    var browserHeight = $(window).height();
    if (right > browserWidth) {
        l = browserWidth - width;
    }
    if (buttom > browserHeight) {
        t = browserHeight - height;
    }
    $(this).parent().css({/* 修正面板位置 */
        left : l,
        top : t
    });
};
$.fn.dialog.defaults.onMove = easyuiPanelOnMove;
$.fn.window.defaults.onMove = easyuiPanelOnMove;
$.fn.panel.defaults.onMove = easyuiPanelOnMove;
 
/**
 *
 * @requires jQuery,EasyUI,jQuery cookie plugin
 *
 * 更换EasyUI主题的方法
 *
 * @param themeName
 *            主题名称
 */
wjc.changeTheme = function(themeName) {
    var $easyuiTheme = $('#easyuiTheme');
    var url = $easyuiTheme.attr('href');
    var href = url.substring(0, url.indexOf('themes')) + 'themes/' + themeName + '/easyui.css';
    $easyuiTheme.attr('href', href);
 
    var $iframe = $('iframe');
    if ($iframe.length > 0) {
        for ( var i = 0; i < $iframe.length; i++) {
            var ifr = $iframe[i];
            $(ifr).contents().find('#easyuiTheme').attr('href', href);
        }
    }
 
    $.cookie('easyuiThemeName', themeName, {
        expires : 7
    });
};
 
 
wjc.serializeObject = function(form) {
    var o = {};
    $.each(form.serializeArray(), function(index) {
        if (o[this['name']]) {
            o[this['name']] = o[this['name']] + "," + this['value'];
        } else {
            o[this['name']] = this['value'];
        }
    });
    return o;
};
 
/**
 *
 * 增加formatString功能
 *
 * 使用方法：formatString('字符串{0}字符串{1}字符串','第一个变量','第二个变量');
 *
 * @returns 格式化后的字符串
 */
wjc.formatString = function(str) {
    for ( var i = 0; i < arguments.length - 1; i++) {
        str = str.replace("{" + i + "}", arguments[i + 1]);
    }
    return str;
};
 
 
wjc.stringToList = function(value) {
    if (value != undefined && value != '') {
        var values = [];
        var t = value.split(',');
        for ( var i = 0; i < t.length; i++) {
            values.push('' + t[i]);/* 避免他将ID当成数字 */
        }
        return values;
    } else {
        return [];
    }
};
 
 
//$.ajaxSetup({
//  type : 'POST',
//  error : function(XMLHttpRequest, textStatus, errorThrown) {
//      $.messager.progress('close');
//      $.messager.alert('错误', errorThrown);
//  }
//});
/**
 * @author
 *
 * @requires jQuery
 *
 * 判断浏览器是否是IE并且版本小于8
 *
 * @returns true/false
 */
wjc.isLessThanIe7 = function() {
    return ($.browser.msie && $.browser.version < 7);
};
 
//时间格式化
wjc.dateFormat = function (format) {
    /*
     * eg:format="yyyy-MM-dd hh:mm:ss";
     */
    if (!format) {
        format = "yyyy-MM-dd hh:mm:ss";
    }
     
    var o = {
        "M+" : this.getMonth() + 1, // month
        "d+" : this.getDate(), // day
        "h+" : this.getHours(), // hour
        "m+" : this.getMinutes(), // minute
        "s+" : this.getSeconds(), // second
        "q+" : Math.floor((this.getMonth() + 3) / 3), // quarter
        "S" : this.getMilliseconds()
        // millisecond
    };
     
    if (/(y+)/.test(format)) {
        format = format.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    }
     
    for (var k in o) {
        if (new RegExp("(" + k + ")").test(format)) {
            format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length));
        }
    }
    return format;
};

$.extend($.fn.datagrid.defaults.editors, {
    datetimebox: $.extend($.fn.datagrid.defaults.editors.datetimebox, {
        setValue: function (target, value) {
            if (typeof(value)=='number' || value.match(/^\d+$/g)) {
                value = $.custom.utils.formatTime('yyyy-MM-dd hh:mm:ss', parseInt(value));
            }
            $(target).datetimebox("setValue", value);
        }
    }),
    datebox: $.extend($.fn.datagrid.defaults.editors.datebox, {
        setValue: function (target, value) {
            if (value.match(/^\d+$/g)) {
                value = $.custom.utils.formatTime('yyyy-MM-dd', parseInt(value));
            }
            $(target).datebox("setValue", value);
        }
    })
});

function easyuiFuncDatagridReload(dgId) {
    var oTarget = $.custom.easyui.datagrid.getById(dgId);
    oTarget.datagrid('reload');
}

function easyuiFuncDatagridSaveModified(dgId, url, alertTitle, alertMessage) {
    var oTarget = $.custom.easyui.datagrid.getById(dgId);
    var opts = oTarget.datagrid('options');
    var insertChanges = oTarget.datagrid('getChanges', 'inserted');
    var updatedChanges = oTarget.datagrid('getChanges', 'updated');
    
    // the submit data would have too many fields and long data, so force use post method.
    var method = 'post';
    
    if (alertTitle === undefined || alertTitle == '') {
        alertTitle = $.custom.utils.lan.defaults.titleWarning;
    }
    if (alertMessage === undefined || alertMessage == '') {
        alertMessage = $.custom.utils.lan.defaults.msgAreYouSureToSaveChangedItems;
    }
    
    if (updatedChanges.length + insertChanges.length > 0) {
        $.messager.confirm(alertTitle, alertMessage, function (r){
            if (r) {
                var updateUrl = url;
                var saveUrl = url;
                if (updateUrl === undefined || updateUrl == '') {
                    updateUrl = opts.updateUrl;
                }
                if (saveUrl === undefined || saveUrl == '') {
                    saveUrl = opts.saveUrl;
                }
                if (updatedChanges.length > 0) {
                    if (updateUrl === undefined) {
                        $.messager.alert($.custom.utils.lan.defaults.titleError, $.custom.utils.lan.defaults.msgUpdateUrlNotConfiguredSoSkip,'error');
                        oTarget.datagrid('rejectChanges');
                        delete opts.curEditingIndex;
                    }
                }
                if (insertChanges.length > 0) {
                    if (saveUrl === undefined) {
                        $.messager.alert($.custom.utils.lan.defaults.titleError, $.custom.utils.lan.defaults.msgSaveUrlNotConfiguredSoSkip,'error');
                        oTarget.datagrid('rejectChanges');
                        delete opts.curEditingIndex;
                    }
                }
                
                var count = 0;
                var i;
                var k;
                var sendDataArray = new Array();
                
                var modelName = undefined;
                var customParams = {};
                if (opts.customData) {
                    if (opts.customData.queryParams) {
                        for (k in opts.customData.queryParams) {
                            customParams[k] = opts.customData.queryParams[k]
                        }
                    }
                    
                    if (opts.customData.modelName) {
                        modelName = opts.customData.modelName;
                    }
                }
                
                for (i in updatedChanges) {
                    var o = updatedChanges[i];
                    var params = $.extend({action:'update'}, customParams);
                    var _params = params;
                    if (modelName !== undefined && modelName != '') {
                        params[modelName] = {};
                        _params = params[modelName];
                    }
                    for (k in o) {
                        _params[k] = o[k];
                    }

                    // send per item
                    count++;
                    var msg = $.custom.easyui.format($.custom.utils.lan.defaults.msgSavingItemByIndex, count);

                    sendDataArray.push({url:opts.updateUrl, param:params, msg:msg});
                }
                for (i in insertChanges) {
                    var o = insertChanges[i];
                    var params = $.extend({action:'insert'}, customParams);
                    var _params = params;
                    if (modelName !== undefined && modelName != '') {
                        params[modelName] = {};
                        _params = params[modelName];
                    }
                    for (k in o) {
                        _params[k] = o[k];
                    }

                    // send per item
                    count++;
                    var msg = $.custom.easyui.format($.custom.utils.lan.defaults.msgSavingItemByIndex, count);

                    sendDataArray.push({url:opts.saveUrl, param:params, msg:msg});
                }

                for (i in sendDataArray) {
                    var obj = sendDataArray[i];
                    easyuiFuncAjaxLoading(obj.msg);
                    $.ajax({
                        type:method,
                        url:obj.url,
                        data:obj.param,
                        success: function (data) {
                            easyuiFuncAjaxEndLoading();
                            easyuiFuncOnProcessSuccessEvents(data, undefined, function(obj){
                                easyuiFuncDatagridReject(dgId);
                                easyuiFuncDatagridReload(dgId);
                            });
                        },
                        error: function (e) {
                            easyuiFuncAjaxEndLoading();
                            easyuiFuncDatagridReject(dgId);
                            easyuiFuncDatagridReload(dgId);
                            easyuiFuncOnProcessErrorEvents(e);
                        }
                    });
                }

                oTarget.datagrid('acceptChanges');
                delete opts.curEditingIndex;
            }
            else {
                oTarget.datagrid('rejectChanges');
                delete opts.curEditingIndex;
            }
        });
    }
}

function easyuiFuncDatagridSetOptionsValue(dgId, fieldName, fieldValue) {
    var oTarget = $.custom.easyui.datagrid.getById(dgId);
    var _params = oTarget.datagrid('options').queryParams;
    if (_params === {}) {
        _params = {};   // set a new instance to avoid affect all datagrid.
        oTarget.datagrid('options').queryParams = _params;
    }
    //_params[fieldName] = fieldValue;
    if (fieldValue == undefined) {
        oTarget.datagrid('options').queryParams[fieldName] = fieldValue;
    }
    else {
        var _x = {};
        _x[fieldName] = fieldValue;
        oTarget.datagrid('options').queryParams = $.extend({}, _params, _x);
    }
}

function easyuiFuncDatagridSetOptionsCustomValue(dgId, fieldName, fieldValue) {
    var oTarget = $.custom.easyui.datagrid.getById(dgId);
    var _params = oTarget.datagrid('options').customData;
    if (_params === undefined || _params === {}) {
        _params = {};   // set a new instance to avoid affect all datagrid.
        oTarget.datagrid('options').customData = _params;
    }
    //_params[fieldName] = fieldValue;
    if (fieldValue == undefined) {
        oTarget.datagrid('options').customData[fieldName] = fieldValue;
    }
    else {
        var _x = {};
        _x[fieldName] = fieldValue;
        oTarget.datagrid('options').customData = $.extend({}, _params, _x);
    }
}

function easyuiFuncDatagridClearOptionsValue(dgId, fieldName) {
    var oTarget = $.custom.easyui.datagrid.getById(dgId);
    delete oTarget.datagrid('options').queryParams[fieldName];
}

function easyuiFuncDatagridSetOptionsValueMultiple(dgId, fieldName, fieldValue) {
    var oTarget = $.custom.easyui.datagrid.getById(dgId);
    var _params = oTarget.datagrid('options').queryParams;
    var value = _params[fieldName];
    if (value == undefined) {
        if (_params === {}) {
            _params = {};   // set a new instance to avoid affect all datagrid.
            oTarget.datagrid('options').queryParams = _params;
        }
        _params[fieldName] = fieldValue;
    }
    else {
        var vlist = value.split(',');
        var found = false;
        for (var x in vlist) {
            if (vlist[x] == fieldValue) {
                found = true;
                break;
            }
        }
        if (!found) {
            value += ',' + fieldValue;
            _params[fieldName] = value;
        }
    }
}

function easyuiFuncDatagridOnSelectOptionsValueMultiple(dgId, fieldName, fieldValue) {
    var oTarget = $.custom.easyui.datagrid.getById(dgId);
    var _params = oTarget.datagrid('options').queryParams;
    var value = _params[fieldName];
    if (value == undefined) {
        if (_params === {}) {
            _params = {};   // set a new instance to avoid affect all datagrid.
            oTarget.datagrid('options').queryParams = _params;
        }
        _params[fieldName] = fieldValue;
    }
    else {
        var vlist = value.split(',');
        var foundIndex = -1;
        for (var x in vlist) {
            if (vlist[x] == fieldValue) {
                foundIndex = x;
                break;
            }
        }
        if (foundIndex == -1) {
            value += ',' + fieldValue;
            _params[fieldName] = value;
        }
        else {
            vlist.splice(foundIndex,1);
            value = vlist.join(',');
            if (value == '') {
                delete _params[fieldName];
            }
            else {
                _params[fieldName] = value;
            }
        }
    }
}

function easyuiFuncDatagridDelOptionsValueMultiple(dgId, fieldName, fieldValue) {
    var oTarget = $.custom.easyui.datagrid.getById(dgId);
    var value = oTarget.datagrid('options').queryParams[fieldName];
    if (value != undefined) {
        var vlist = value.split(',');
        for (var x in vlist) {
            if (vlist[x] == fieldValue) {
                vlist.splice(x,1);
                break;
            }
        }
        value = vlist.join(',');
        if (value == '') {
            delete oTarget.datagrid('options').queryParams[fieldName];
        }
        else {
            oTarget.datagrid('options').queryParams[fieldName] = value;
        }
    }
}

function easyuiFuncDatagridOnDblClickCellDoEdit(index, field, val) {
    var oTarget = $.custom.easyui.datagrid.getById(dgId);
    var dgId = '#' + $(this).context.id;
    var opts = oTarget.datagrid('options');
    var editIndex = opts.curEditingIndex;
    //var editIndex = easyuiFuncDatagridGetEditingIndex(dgId);
    if (editIndex != index){
        if ($.custom.easyui.datagrid.endEditing(dgId)){
            oTarget.datagrid('selectRow', index)
                    .datagrid('beginEdit', index);
            var ed = oTarget.datagrid('getEditor', {index:index,field:field});
            if (ed) {
                ($(ed.target).data('textbox') ? $(ed.target).textbox('textbox') : $(ed.target)).focus();
            }
            
            opts.curEditingIndex = index;
        } else {
            oTarget.datagrid('selectRow', editIndex);
        }
    }
}

function easyuiFuncDatagridAppend(dgId) {
    if ($.custom.easyui.datagrid.endEditing(dgId)){
        var oTarget = $.custom.easyui.datagrid.getById(dgId);
        var opts = oTarget.datagrid('options');
        var columns1 = oTarget.datagrid('getColumnFields', true);
        var columns2 = oTarget.datagrid('getColumnFields');
        var initDatas = {};
        for (var i in columns1) {
            initDatas[columns1[i]] = '';
        }
        for (var i in columns2) {
            initDatas[columns2[i]] = '';
        }
        if (opts.customData) {
            if (opts.customData.defaultValues) {
                for (var k in opts.customData.defaultValues) {
                    initDatas[k] = opts.customData.defaultValues[k];
                }
            }
        }
        oTarget.datagrid('appendRow', initDatas);
        var editIndex = oTarget.datagrid('getRows').length-1;
        opts.curEditingIndex = editIndex;
        oTarget.datagrid('selectRow', editIndex)
                .datagrid('beginEdit', editIndex);
        
        $.custom.easyui.datagrid.setCustomEvents(dgId);
    }
}

function easyuiFuncDatagridDeleteSelected(dgId, url, alertTitle, alertMessage, confirmMessage) {
    var oTarget = $.custom.easyui.datagrid.getById(dgId);
    var opts = oTarget.datagrid('options');
    var editIndex = opts.curEditingIndex;
    if (editIndex == undefined){
        var ids = new Array();
        var idField = opts.idField;
        if (idField === undefined || idField == '') {
            idField = 'id';
        }
        var rows = oTarget.datagrid('getSelections');
        for (var i = 0; i < rows.length; i++) {
            ids.push(rows[i][idField]);
        }
        
        if (ids.length > 0) {
            if (alertTitle == undefined || alertTitle == '') {
                alertTitle = $.custom.utils.lan.defaults.titleWarning;
            }
            if (alertMessage == undefined || alertMessage == '') {
                alertMessage = $.custom.utils.lan.defaults.msgAreYouSureToDeleteSelectedItems;
            }
            if (confirmMessage == undefined || confirmMessage == '') {
                confirmMessage = $.custom.utils.lan.defaults.msgPleaseConfirmAgainToDeleteItems;
            }
            
            var params = {};
            if (ids.length == 1) {
                params.id = ids[0];
            }
            else {
                params.ids = ids;
            }
            if (url == undefined || url == '') {
                if (opts.destroyUrl != undefined) {
                    url = opts.destroyUrl;
                }
            }
            
            var urlMethod = 'post';
            if (opts.method !== undefined && opts.method != '') {
                urlMethod = opts.method;
            }
            
            $.messager.confirm(alertTitle, alertMessage, function(r){
                if (r){
                    $.messager.confirm($.custom.utils.lan.defaults.titlePrompt, confirmMessage, function(r){
                        if (r){
                            easyuiFuncAjaxSendData(url, urlMethod, params, function(){oTarget.datagrid('reload');});
                        }
                    });
                }
            });
        }
        else {
            $.messager.alert($.custom.utils.lan.defaults.titleWarning, $.custom.utils.lan.defaults.msgYouShouldSelectARow,'error');
        }
    }
    else {
        $.messager.alert($.custom.utils.lan.defaults.titleWarning, $.custom.utils.lan.defaults.msgPleaseFinishEditingCellFirst,'error');
    }
}

function easyuiFuncDatagridEdit(dgId, url){
    if ($.custom.easyui.datagrid.endEditing(dgId)){
        var oTarget = $.custom.easyui.datagrid.getById(dgId);
        var opts = oTarget.datagrid('options');
        var row = oTarget.datagrid('getSelected');
        if (row == null) {
            $.messager.alert($.custom.utils.lan.defaults.titleWarning, $.custom.utils.lan.defaults.msgYouShouldSelectARow,'error');
            return;
        }
        var editIndex = oTarget.datagrid('getRowIndex', row);
        opts.curEditingIndex = editIndex;
        oTarget.datagrid('selectRow', editIndex)
                .datagrid('beginEdit', editIndex);
        
        $.custom.easyui.datagrid.setCustomEvents(dgId);
    }
}

function easyuiFuncDatagridAccept(dgId, url, alertTitle, alertMessage){
    $.custom.easyui.datagrid.endEditing(dgId);
    easyuiFuncDatagridSaveModified(dgId, url, alertTitle, alertMessage);
}
function easyuiFuncDatagridReject(dgId){
    var oTarget = $.custom.easyui.datagrid.getById(dgId);
    oTarget.datagrid('rejectChanges');
    var opts = oTarget.datagrid('options');
    delete opts.curEditingIndex;
}
function easyuiFuncDatagridGetChanges(dgId){
    var oTarget = $.custom.easyui.datagrid.getById(dgId);
    var rows = oTarget.datagrid('getChanges');
    $.messager.alert($.custom.utils.lan.defaults.titlePrompt, $.custom.easyui.format($.custom.utils.lan.defaults.msgNumRowsAreChanged, '' + rows.length));
}

/*
function easyuiFuncDatagridGetSelectedFormatUrlParam(dgId, url) {
    var oTarget = $.custom.easyui.datagrid.getById(dgId);
    var opts = oTarget.datagrid('options');
    var selected = oTarget.datagrid('getSelected');
    var params = {};
    if (selected !== null) {
        if (opts.idField !== undefined && selected[opts.idField] !== undefined) {
            params[opts.idField] = selected[opts.idField];
        }
        else {
            for (var k in selected) {
                if (k.substr(-2).toLowerCase() == 'id' || k.substr(0, 2).toLowerCase() == 'id') {
                    params[k] = selected[k];
                }
            }
        }
    }
    
    var sep = '?';
    if (url.indexOf('?') >= 0) {
        sep = '&';
    }
    
    for (var k in params) {
        url += sep + encodeURI(k) + '=' + encodeURI(params[k]);
        sep = '&';
    }
    return url;
}
*/

function easyuiFuncDatagridExecuteByFormatUrlWithSelected(callback, dgId, url, ifShouldSelect) {
    if (ifShouldSelect !== undefined && ifShouldSelect) {
        var oTarget = $.custom.easyui.datagrid.getById(dgId);
        if (oTarget.datagrid('getSelected') === null) {
            $.messager.alert($.custom.utils.lan.defaults.titlePrompt, $.custom.utils.lan.defaults.msgYouShouldSelectARow);
            return;
        }
    }
    url = $.custom.easyui.datagrid.getSelectedFormatUrlParam(dgId, url);
    callback(url);
}

function easyuiFuncDatagridOpenInDialog(dgId, dialogId, url, title, ifShouldSelect) {
    easyuiFuncDatagridExecuteByFormatUrlWithSelected(function (url) {
        easyuiFuncOpenDialog(dialogId, url, title);
    }, dgId, url, ifShouldSelect);
    /*if (ifShouldSelect !== undefined && ifShouldSelect) {
        var oTarget = $.custom.easyui.datagrid.getById(dgId);
        if (oTarget.datagrid('getSelected') === null) {
            $.messager.alert($.custom.utils.lan.defaults.titlePrompt, $.custom.utils.lan.defaults.msgYouShouldSelectARow);
            return;
        }
    }
    url = $.custom.easyui.datagrid.getSelectedFormatUrlParam(dgId, url);
    easyuiFuncOpenDialog(dialogId, url, title)*/;
}

function easyuiFuncDatagridOpenInWindow(dgId, windowId, url, title, ifShouldSelect) {
    easyuiFuncDatagridExecuteByFormatUrlWithSelected(function (url) {
        easyuiFuncOpenWindow(windowId, url, title);
    }, dgId, url, ifShouldSelect);
    /*if (ifShouldSelect !== undefined && ifShouldSelect) {
        var oTarget = $.custom.easyui.datagrid.getById(dgId);
        if (oTarget.datagrid('getSelected') === null) {
            $.messager.alert($.custom.utils.lan.defaults.titlePrompt, $.custom.utils.lan.defaults.msgYouShouldSelectARow);
            return;
        }
    }
    url = $.custom.easyui.datagrid.getSelectedFormatUrlParam(dgId, url);
    easyuiFuncOpenWindow(windowId, url, title);*/
}

function easyuiFuncDatagridOpenInTab(dgId, url, title, ifShouldSelect, tabPanelId, isOpenInIframe) {
    easyuiFuncDatagridExecuteByFormatUrlWithSelected(function (url) {
        easyuiFuncNavTabAddDoNotKnownId(title, url, tabPanelId, isOpenInIframe);
    }, dgId, url, ifShouldSelect);
    /*if (ifShouldSelect !== undefined && ifShouldSelect) {
        var oTarget = $.custom.easyui.datagrid.getById(dgId);
        if (oTarget.datagrid('getSelected') === null) {
            $.messager.alert($.custom.utils.lan.defaults.titlePrompt, $.custom.utils.lan.defaults.msgYouShouldSelectARow);
            return;
        }
    }
    url = $.custom.easyui.datagrid.getSelectedFormatUrlParam(dgId, url);
    easyuiFuncNavTabAddDoNotKnownId(title, url, tabPanelId, isOpenInIframe);*/
}

function easyuiFuncDatagridOpenInAjax(dgId, url, callback, alertMessage, ifShouldSelect, method) {
    var oTarget = $.custom.easyui.datagrid.getById(dgId);
    if (ifShouldSelect !== undefined && ifShouldSelect) {
        if (oTarget.datagrid('getSelected') === null) {
            $.messager.alert($.custom.utils.lan.defaults.titlePrompt, $.custom.utils.lan.defaults.msgYouShouldSelectARow);
            return;
        }
    }
    if (typeof(callback) == 'function') {
        callback(dgId, url, undefined, alertMessage);
    }
    else {
        var params = $.custom.easyui.datagrid.getSelectedUrlParams(dgId, true);
        var urlMethod = 'get';
        if (method !== undefined && method != '') {
            urlMethod = method;
        }
        if (alertMessage === undefined || alertMessage == '') {
            easyuiFuncAjaxSendData(url, urlMethod, params, function(){oTarget.datagrid('reload');});
        }
        else {
            $.messager.confirm($.custom.utils.lan.defaults.titlePrompt, alertMessage, function(r){
                if (r){
                    easyuiFuncAjaxSendData(url, urlMethod, params, function(){oTarget.datagrid('reload');});
                }
            });
        }
    }
}

function easyuiFuncDatagridInitializeQueryParams(dgId, jsonParams) {
    if (jsonParams == '') return;
    var obj = eval('(' + jsonParams + ')');
    var oTarget = $.custom.easyui.datagrid.getById(dgId);
    var opts = oTarget.datagrid().datagrid('options');
    var params = {};
    for (var k in opts.queryParams) {
        params[k] = opts.queryParams[k];
    }
    for (var k in obj) {
        params[k] = obj[k]
    }
    opts.queryParams = params;
}

function easyuiFuncDatagridToolLinkButtonFormatUrl(dgId) {
    var oTarget = $.custom.easyui.datagrid.getById(dgId);
    var opts = oTarget.datagrid('options');
    var url = $(this).url;
    var params = opts.queryParams;
    url = easyuiFuncReformatUrlWithParams(url, params);
    
    $(this).url = url;
}

function easyuiFuncDatagridShowDetailedViewFormatter(index, row) {
    return '<div style="padding:2px"><table class="ddv"></table></div>';
}

function easyuiFuncDatagridShowDetailedColumns(dgObj, dgId, columns, fields, index, row) {
    var ddv = $(dgObj).datagrid('getRowDetail',index).find('table.ddv');
    var dtData0 = new Array();
    for (var i in fields) {
        dtData0[fields[i]] = row[fields[i]];
    }
    var dtData = [dtData0];
    ddv.datagrid({
        fitColumns:true,
        height:'auto',
        columns:columns,
        data:dtData,
        onResize:function(){
            $('#' + dgId).datagrid('fixDetailRowHeight',index);
        },
        onLoadSuccess:function(){
            setTimeout(function(){ $('#' + dgId).datagrid('fixDetailRowHeight',index); },0);
        }
    });
    $(dgObj).datagrid('fixDetailRowHeight',index);
}

function easyuiFuncOnProcessSuccessEvents(data, successCallback, unsuccessCallback) {
    if (data == '') return;
    var msg;
    var msgIcon = 'info';
    var alertType = 'info';     // statusCode == 200
    var alertTitle = '     ';
    var obj = undefined;
    var alertParam = {showType:'slide', showSpeed:400, timeout:3000, resizable:true,
        style:{right:'',top:document.body.scrollTop+document.documentElement.scrollTop,bottom:''}
    };
    if ($.type(data) == 'string') {
        try {
            obj = eval('(' + data + ')');
        }
        catch (e) {
            obj = undefined;
        }
    }

    if (obj) {
        if (obj.statusCode != 200) {
            if (obj.statusCode == 300) {
                alertType = 'error';
            }
            else {
                alertType = 'warning';
            }
            
            if (unsuccessCallback && typeof(unsuccessCallback) == 'function') {
                setTimeout(function() { unsuccessCallback(obj); }, 10);
            }
        }
        else {
            // other params
            // obj.navTabId
            // obj.rel
            // obj.callbackType
            // obj.forwardUrl
            if (obj.navTabId != undefined && '' != obj.navTabId) {
                if (obj.navTabId.substr(0, 4) == 'page') {
                    obj.navTabId = obj.navTabId.substr(4)
                }
                setTimeout(function(){easyuiFuncNavTabShowByTabPanelId(obj.navTabId);}, 100);
            }
            
            if ('closeCurrent' == obj.callbackType) {
                //setTimeout(easyuiFuncCloseCurrent(obj.navTabId), 100);
                easyuiFuncCloseCurrent(obj.navTabId);
            }
            else if ('refreshCurrentX' == obj.callbackType) {
                easyuiFuncCloseCurrent(obj.navTabId, true);
            }
            else if ('refreshCurrent' == obj.callbackType) {
                easyuiFuncNavTabRefreshCurTab();
            }
            else if ('closeNavTab' == obj.callbackType) {
                easyuiFuncCloseCurrent(obj.navTabId, true, true);
            }
            else if ('forward' == obj.callbackType) {
                easyuiFuncNavTabReloadCurTab(obj.forwardUrl, obj.forwardTitle);
            }
            else if ('forwardConfirm' == obj.callbackType) {
                if (obj.confirmMsg) {
                    $.messager.confirm($.custom.utils.lan.defaults.titlePrompt, obj.confirmMsg, function(r){
                        if (r){
                            easyuiFuncNavTabReloadCurTab(obj.forwardUrl, obj.forwardTitle);
                        }
                        else {
                            easyuiFuncCloseCurrent(obj.navTabId);
                        }
                    });
                }
            }
            
            if (successCallback && typeof(successCallback) == 'function') {
                setTimeout(function() { successCallback(obj); }, 10);
            }
            
            if (obj.message == '') {
                return;
            }
        }
        
        msg = obj.message;
        msgIcon = alertType;
    }
    else {
        alertTitle = $.custom.utils.lan.defaults.titleWarning;
        alertType = '';
        msg = '' + data;
        msgIcon = 'warning';
        alertParam.timeout = 0;
    }
    
    $.custom.easyui.alert.show(msg, alertTitle, alertType, msgIcon);
}

function easyuiFuncOnProcessErrorEvents(e) {
    if ($.custom.easyui.preProcessLoadError(e)) {
        return;
    }
    var statusCode = e.status;
    var statusText = e.statusText;
    var responseText = e.responseText;
    var msg = $.custom.easyui.format($.custom.utils.lan.defaults.msgGotResponseFailedError, ' code:' + statusCode + ' error:' + statusText) + responseText;
    if (msg.length < 380) {
        $.messager.alert($.custom.utils.lan.defaults.titleError, msg,'error');
    }
    else {
        var msg = $.custom.easyui.format($.custom.utils.lan.defaults.msgGotResponseFailedError, ' code:' + statusCode + ' error:' + statusText) + responseText;
        $.custom.easyui.alert.show(msg, $.custom.utils.lan.defaults.titleError, 'error', 'error', 0);
        //var alertParam = {showType:'show', showSpeed:400, timeout:0, resizable:true,
        //    style:{right:'',top:document.body.scrollTop+document.documentElement.scrollTop,bottom:''}
        //};
        //if (msg.length > 4096) {
        //    alertParam.width = 800;
        //    alertParam.height = 600;
        //}
        //else if (msg.length > 2048) {
        //    alertParam.width = 300;
        //    alertParam.height = 400;
        //}
        //else if (msg.length > 512) {
        //    alertParam.width = 260;
        //    alertParam.height = 300;
        //}
        //msg = '<div class="messager-icon messager-error"></div><div>' + msg + '</div><div style=\"clear:both;\"/>';
        //alertParam.title = $.custom.utils.lan.defaults.titleError;
        //alertParam.msg = msg;
        //alertParam.icon = 'error';
        //
        //$.messager.show(alertParam);
    }
}

function easyuiFuncAjaxLoading(loadingMsg) {
    if (loadingMsg === undefined || loadingMsg == '') {
        loadingMsg = $.fn.datagrid.defaults.loadMsg;
    }
    $("<div class=\"datagrid-mask\"></div>").css({display:"block",width:"100%",height:$(window).height()}).appendTo("body");
    $("<div class=\"datagrid-mask-msg\"></div>").html(loadingMsg).appendTo("body").css({display:"block",left:($(document.body).outerWidth(true) - 190) / 2,top:($(window).height() - 45) / 2});
}

function easyuiFuncAjaxEndLoading() {
    $(".datagrid-mask").remove();
    $(".datagrid-mask-msg").remove();
}

function easyuiFuncAjaxSendData(url, method, params, succeesFunc, errorFunc) {
    $.ajax({
        type:method,
        url:url,
        data:params,
        beforeSend:easyuiFuncAjaxLoading,
        success: function (data) {
            easyuiFuncAjaxEndLoading();
            easyuiFuncOnProcessSuccessEvents(data);
            if (succeesFunc) {
                succeesFunc(data);
            }
        },
        error: function (e) {
            easyuiFuncAjaxEndLoading();
            easyuiFuncOnProcessErrorEvents(e);
            if (errorFunc) {
                errorFunc(e);
            }
        }
    });
}

function easyuiFuncAjaxSendDataWithoutAlert(url, method, params, succeesFunc, errorFunc) {
    $.ajax({
        type:method,
        url:url,
        data:params,
        beforeSend:easyuiFuncAjaxLoading,
        success: function (data) {
            easyuiFuncAjaxEndLoading();
            if (succeesFunc) {
                succeesFunc(data);
            }
        },
        error: function (e) {
            easyuiFuncAjaxEndLoading();
            easyuiFuncOnProcessErrorEvents(e);
            if (errorFunc) {
                errorFunc(e);
            }
        }
    });
}

function easyuiFuncDebugThisValue(callerThis, param1, param2) {
    var o = $(callerThis);
    var p = o;
    var x = p;
}

function easyuiFuncNavTabAdd(tabId, title, url, tabPanelId) {
    if ($.custom.easyui.config.openTabInIframe) {
        $.custom.easyui.navtab.show(tabId, title, url, tabPanelId, true);
    }
    else {
        $.custom.easyui.navtab.show(tabId, title, url, tabPanelId, false);
    }
}

function easyuiFuncNavTabAddHref(tabId, title, url, tabPanelId) {
    $.custom.easyui.navtab.show(tabId, title, url, tabPanelId, false);
}

function easyuiFuncNavTabAddIframe(tabId, title, url, tabPanelId) {
    $.custom.easyui.navtab.show(tabId, title, url, tabPanelId, true);
}

function easyuiFuncNavTabShowByTabPanelId(tabPanelId) {
    var obj = $('.easyui-tabs');
    if (obj.length == 0) {
        return;
    }
    
    for (var i = obj.length - 1; i >= 0; i--) {
        var tabId = '#' + obj[i].id;
        var tab = $(tabId);
        var allTabs = tab.tabs('tabs');
        var j;
        var curTab;
        for (j in allTabs) {
            curTab = allTabs[j];
            if (curTab[0].id == tabPanelId) {
                var index = tab.tabs('getTabIndex', curTab);
                tab.tabs('select', index);
                //tab.tabs('update', {tab:curTab, options:{}});
                var dgObj = $('.easyui-datagrid', curTab);
                if (dgObj && dgObj.length > 0) {
                    dgObj.datagrid('reload');
                }
                else {
                    var selectedTab = tab.tabs('getSelected');  // get selected panel
                    selectedTab.panel('refresh', undefined);
                }
                break;
            }
        }
    }
}

function easyuiFuncNavTabReloadCurTab(forwardUrl, forwardTitle) {
    var v = easyuiFuncGetCurrentTabsAndTabPanel(true);
    var tabs = v[0];
    var curTab = v[1];
    if (tabs === undefined || curTab === null) {
        return;
    }
    
    if (forwardUrl !== undefined && forwardUrl != '') {
        var opts = curTab.panel("options");
        if (opts.content != undefined && opts.content != '') {
            var options = {};
            if (forwardTitle !== undefined && forwardTitle != '') {
                options.title = forwardTitle;
            }
            options.content = '<iframe scrolling="yes" frameborder="0" style="width:100%;height:100%" src="'+forwardUrl+'"></iframe>';
            tabs.tabs('update', {tab:curTab, options:options});
        }
        else if (forwardTitle !== undefined && forwardTitle != '') {
            var options = {
                title: forwardTitle,
                href: forwardUrl
            };
            tabs.tabs('update', {tab:curTab, options:options});
            curTab.panel('refresh', forwardUrl);
        }
        else {
            curTab.panel('refresh', forwardUrl);
        }
    }
}

function easyuiFuncNavTabRefreshCurTab(forceReloadTab) {
    var v = easyuiFuncGetCurrentTabsAndTabPanel();
    var tabs = v[0];
    var curTab = v[1];
    if (tabs === undefined || curTab === null) {
        return;
    }
    
    if (forceReloadTab) {
        var selectedTab = tabs.tabs('getSelected');  // get selected panel
        selectedTab.panel('refresh', undefined);
        return;
    }
    
    do
    {
        var dgObj = $('.easyui-datagrid', curTab);
        if (dgObj && dgObj.length == 1) {
            dgObj.datagrid('reload');
            break;
        }
        dgObj = $('.easyui-treegrid', curTab);
        if (dgObj && dgObj.length == 1) {
            dgObj.treegrid('reload');
            break;
        }
        var selectedTab = tabs.tabs('getSelected');  // get selected panel
        if (selectedTab && selectedTab.length > 0) {
            selectedTab.panel('refresh', undefined);
            break;
        }
    }while(0);
}

function easyuiFuncNavTabRefreshCurTabGridData() {
    var v = easyuiFuncGetCurrentTabsAndTabPanel();
    var tabs = v[0];
    var curTab = v[1];
    if (tabs === undefined || curTab === null) {
        return;
    }
    
    do
    {
        var dgObj = $('.easyui-datagrid', curTab);
        if (dgObj && dgObj.length == 1) {
            dgObj.datagrid('reload');
            break;
        }
        dgObj = $('.easyui-treegrid', curTab);
        if (dgObj && dgObj.length == 1) {
            dgObj.treegrid('reload');
            break;
        }
        
    }while(0);
}

function easyuiFuncNavTabAddDoNotKnownId(title, url, tabPanelId, isOpenInIframe) {
    var obj = $('.easyui-tabs');
    if (obj.length == 0) {
        return;
    }
    
    var tabId = obj[0].id;
    if (isOpenInIframe === undefined) {
        easyuiFuncNavTabAdd(tabId, title, url, tabPanelId);
    }
    else {
        if (isOpenInIframe) {
            $.custom.easyui.navtab.show(tabId, title, url, tabPanelId, true);
        }
        else {
            $.custom.easyui.navtab.show(tabId, title, url, tabPanelId, false);
        }
    }
}

function easyuiFuncNavTabReloadCurTabWithDatagridParams(forwardUrl, dgId) {
    var v = easyuiFuncGetCurrentTabsAndTabPanel();
    var tabs = v[0];
    var curTab = v[1];
    if (tabs === undefined || curTab === null) {
        return;
    }
    
    var dgObj = $(dgId, curTab);
    var queryParams = dgObj.datagrid('options').queryParams;
    forwardUrl = easyuiFuncReformatUrlWithParams(forwardUrl, queryParams);
    
    if (forwardUrl !== undefined && forwardUrl != '') {
        var opts = curTab.panel("options");
        if (opts.content != undefined && opts.content != '') {
            var options = {};
            options.content = '<iframe scrolling="yes" frameborder="0" style="width:100%;height:100%" src="'+forwardUrl+'"></iframe>';
            tabs.tabs('update', {tab:curTab, options:options});
        }
        else {
            curTab.panel('refresh', forwardUrl);
        }
    }
}

function easyuiFuncOpenDialog(dlgId, newUrl, newTitle) {
    if (dlgId.indexOf('#') !== 0) {
        dlgId = '#' + dlgId;
    }

    var curParent = undefined;
    var tabs = $('.easyui-tabs');
    if (tabs.length > 0) {
        var curTab = tabs.tabs('getSelected');
        if (curTab !== null) {
            curParent = tabs;
        }
    }

    var dlgObj = $(dlgId, curParent);
    if (dlgObj === undefined) {
        return ;
    }
    else if(dlgObj.length == 0) {
        return ;
    }
    
    if (newTitle) {
        dlgObj.dialog('setTitle', newTitle);
    }
    dlgObj.dialog('open');
    if (newUrl) {
        dlgObj.dialog('refresh', newUrl);
    }
}

function easyuiFuncOpenWindow(wndId, newUrl, newTitle) {
    if (wndId.indexOf('#') !== 0) {
        wndId = '#' + wndId;
    }

    var curParent = undefined;
    var tabs = $('.easyui-tabs');
    if (tabs.length > 0) {
        var curTab = tabs.tabs('getSelected');
        if (curTab !== null) {
            curParent = tabs;
        }
    }

    var wndObj = $(wndId, curParent);
    if (wndObj === undefined) {
        return ;
    }
    else if(wndObj.length == 0) {
        return ;
    }
    
    if (newTitle) {
        wndObj.window('setTitle', newTitle);
    }
    wndObj.window('open');
    if (newUrl) {
        wndObj.window('refresh', newUrl);
    }
}

function easyuiFuncCloseCurrent(skipMainNavTabPanelId, isRefresh, preferCloseRootTab) {
    var v = easyuiFuncGetCurrentTabsAndTabPanel(preferCloseRootTab);
    var tabs = v[0];
    var curTab = v[1];
    if (tabs === undefined || curTab === null) {
        return;
    }
    
    var obj = $('.easyui-dialog', curTab);
    if (obj.length > 0) {
        for (var i = obj.length - 1; i >= 0; i--) {
            var dlgId = obj[i].id;
            var dlg = undefined;
            if (dlgId != '') {
                dlgId = '#' + dlgId;
                dlg = $(dlgId);
            }
            if (dlg !== undefined) {
                var opts = dlg.dialog('options');
                if (opts.closed === false) {
                    dlg.dialog('close');
                    
                    if (isRefresh) {
                        easyuiFuncNavTabRefreshCurTab();
                    }
                    return;
                }
            }
        }
    }
    
    obj = $('.easyui-window', curTab);
    if (obj.length > 0) {
        for (var i = obj.length - 1; i >= 0; i--) {
            var wndId = obj[i].id;
            var wnd = undefined;
            if (wndId != '') {
                wndId = '#' + wndId;
                wnd = $(wndId);
            }
            if (wnd !== undefined) {
                var opts = wnd.window('options');
                if (opts.closed === false && opts.modal === true) {
                    wnd.window('close');
                    if (isRefresh) {
                        easyuiFuncNavTabRefreshCurTab();
                    }
                    return;
                }
            }
        }
    }

    // find the sub-tabs
    if ((skipMainNavTabPanelId != '') && (curTab[0].id == skipMainNavTabPanelId)) {
        if (isRefresh) {
            easyuiFuncNavTabRefreshCurTab();
        }
        return;
    }
    // not found sub-tabs, close the current root tab.
    setTimeout(function(){
        var index = tabs.tabs('getTabIndex',curTab);
        tabs.tabs('close',index);
        if (isRefresh) {
            easyuiFuncNavTabRefreshCurTab();
        }
    },100);
}

function easyuiFuncCreateDialogOnCurTab(dlgId, dlgTitle) {
    var curParent = undefined;
    var tabs = $('.easyui-tabs');
    if (tabs.length > 0) {
        var curTab = tabs.tabs('getSelected');
        if (curTab !== null) {
            curParent = tabs;
        }
    }

    if (curParent === undefined) {
        return ;
    }

    var dlgObj = $('#'+dlgId, curParent);
    if (dlgObj && dlgObj.length > 0) {
        return ;
    }

    if (!dlgTitle) {
        dlgTitle = '';
    }
    var html = '<div id="'+dlgId+'" class="easyui-dialog" title="'+dlgTitle+'" style="width:auto;height:auto;padding:5px 10px 5px 10px;" data-options="iconCls:\'icon-save\',modal:true,resizable:true,inline:true,closed:true,onLoadError:$.custom.easyui.dialog.onLoadError">'+
    '<div style="width:800px;height:400px"></div>'+
    '</div>';
    curParent.append(html);
    $('#'+dlgId, curParent).dialog();
}

function easyuiFuncCreateWindowOnCurTab(wndId, wndTitle) {
    var curParent = undefined;
    var tabs = $('.easyui-tabs');
    if (tabs.length > 0) {
        var curTab = tabs.tabs('getSelected');
        if (curTab !== null) {
            curParent = tabs;
        }
    }

    if (curParent === undefined) {
        return ;
    }

    var wndObj = $('#'+wndId, curParent);
    if (wndObj && wndObj.length > 0) {
        return ;
    }

    if (!wndTitle) {
        wndTitle = '';
    }
    var html = '<div id="'+wndId+'" class="easyui-window" title="'+wndTitle+'" style="width:600px;height:400px;padding:10px;" data-options="iconCls:\'icon-save\',modal:true,inline:true,closed:true,onLoadError:$.custom.easyui.window.onLoadError">'+
    '<div style="width:800px;height:400px"></div>'+
    '</div>';
    curParent.append(html);
    $('#'+wndId, curParent).window();
}

function easyuiFuncFormOnSubmit(formId, params, successCallback, onsubmitCallback) {
    var formObj = undefined;
    if (formId.indexOf('#') !== 0) {
        formId = '#' + formId;
    }
    var v = easyuiFuncGetCurrentTabsAndTabPanel();
    var curTab = v[1];
    if (curTab === null) {
        formObj = $(formId);
    }
    else {
        formObj = $(formId, curTab);
    }
    
    if (formObj === undefined) {
        return;
    }
    
    formObj.form('submit', {
        onSubmit:function(param) {
            var r = $(this).form('validate');
            if (r == false) {
                return r;
            }
            if (typeof onsubmitCallback == 'function') {
                r = onsubmitCallback(param);
                if (r == false) {
                    return r;
                }
            }
            if (params != undefined && typeof params == 'object') {
                for (var k in params) {
                    param[k] = params[k];
                }
            }
            easyuiFuncAjaxLoading();
            return r;
        },
        success:function(data){
            easyuiFuncAjaxEndLoading();
            if (typeof successCallback == 'function') {
                successCallback(data, params);
            }
            else {
                easyuiFuncOnProcessSuccessEvents(data);
            }
            if (data == '') {
                var alertType = 'error';
                var msg = '<div class="messager-icon messager-warning' + alertType + '"></div><div>' + $.custom.utils.lan.defaults.msgRequestGotEmptyData + '</div><div style=\"clear:both;\"/>';
                $.messager.show({title:$.custom.utils.lan.defaults.titleWarning, msg:msg, icon:alertType, showType:'slide', 
                    showSpeed:400, timeout:3000,
                    style:{right:'',top:document.body.scrollTop+document.documentElement.scrollTop,bottom:''}
                });
            }
        },
        error:function (e) {
            easyuiFuncAjaxEndLoading();
            easyuiFuncOnProcessErrorEvents(e);
        }
    });
}

function easyuiFuncGetCurrentTabsAndTabPanel(getRootTabOnly) {
    var curTabs = undefined;
    var curPanel = null;
    var tabs = $('.easyui-tabs');

    while (tabs.length > 0) {
        curTabs = tabs;

        curPanel = tabs.tabs('getSelected');
        if (curPanel === null) {
            break;
        }

        tabs = $('.easyui-tabs', curPanel);
        
        if (getRootTabOnly) {
            break;
        }
    }
    
    return [curTabs, curPanel];
}

function easyuiFuncOpenUrlFromCurTabPrefer(type, url, title) {
    var v = easyuiFuncGetCurrentTabsAndTabPanel();
    var tabs = v[0];
    var curTab = v[1];
    if (tabs === undefined || curTab === null) {
        return;
    }
    
    if (type == 'dialog') {
        var obj = $('.easyui-dialog', curTab);
        if (obj.length > 0) {
            for (var i = obj.length - 1; i >= 0; i--) {
                var dlgId = obj[i].id;
                var dlg = undefined;
                if (dlgId != '') {
                    dlgId = '#' + dlgId;
                    dlg = $(dlgId, curTab);
                }
                if (dlg !== undefined) {
                    var opts = dlg.dialog('options');
                    if (opts.closed !== false) {
                        if (title) {
                            dlg.dialog('setTitle', title);
                        }
                        dlg.dialog('open');
                        if (url) {
                            dlg.dialog('refresh', url);
                        }
                        return;
                    }
                }
            }
        }
    }
    else if (type == 'window') {
        var obj = $('.easyui-window', curTab);
        if (obj.length > 0) {
            for (var i = obj.length - 1; i >= 0; i--) {
                var wndId = obj[i].id;
                var wnd = undefined;
                if (wndId != '') {
                    wndId = '#' + wndId;
                    wnd = $(wndId, curTab);
                }
                if (wnd !== undefined) {
                    var opts = wnd.window('options');
                    if (opts.closed !== false) {
                        if (title) {
                            wnd.window('setTitle', title);
                        }
                        wnd.window('open');
                        if (url) {
                            wnd.window('refresh', url);
                        }
                    }
                    return;
                }
            }
        }
    }
    else {
        $.custom.easyui.navtab.showTab(tabs, title, url);
    }
}

function easyuiFuncOpenUrlFromCurTabForDebug(type, url, title) {
    if (type === 'dialog' || type === 'window') {
        return easyuiFuncOpenUrlFromCurTabPrefer(type, url, title);
    }
    var v = easyuiFuncGetCurrentTabsAndTabPanel();
    var tabs = v[0];
    var curTab = v[1];
    if (tabs === undefined || curTab === null) {
        return;
    }
    $.custom.easyui.navtab.showTab(tabs, title, url, undefined, true);
}

function easyuiFuncQueryUrlAjax(url, alertMessage, method, callback) {
    if (method != 'get' && method != 'post') {
        method = 'get';
    }

    var succeedCallback = undefined;
    if (callback !== undefined && callback) {
        succeedCallback = callback;
    }

    if (alertMessage !== undefined && alertMessage != '') {
        $.messager.confirm($.custom.utils.lan.defaults.titlePrompt, alertMessage, function(r){
            if (r){
                easyuiFuncAjaxSendData(url, method, {}, succeedCallback);
            }
        });
    }
    else {
        easyuiFuncAjaxSendData(url, method, {}, succeedCallback);
    }
}

function easyuiFuncReformatUrlWithParams(url, params) {
    var p = url.split('?');
    var newParams = {};
    var newUrl = p[0];
    if (p.length > 1) {
        var v = p[1];
        var v1 = v.split('&');
        for (var i in v1) {
            var v2 = v1[i].split('=');
            newParams[decodeURI(v2[0])] = decodeURI(v2[1]);
        }
    }
    
    for (var k in params) {
        newParams[k] = params[k];
    }
    
    var p2 = new Array();
    for (var k in newParams) {
        p2.push(encodeURI([k]) + '=' + encodeURI(newParams[k]));
    }
    
    if (p2.length > 0) {
        url = newUrl + '?' + p2.join('&');
    }
    
    return url;
}

function easyuiFuncCheckboxUpdateSelectValue(name, value, act, parent) {
    var checkboxes = $("[name='" + name + "']", parent);
    if (checkboxes.length == 0) {
        return;
    }
    if (act === 'all') {
        for(var i in checkboxes) {
            var o = checkboxes[i];
            if (value[o.value] !== undefined) {
                o.checked=true;
            }
        }
    }
    else if (act === 'none') {
        for(var i in checkboxes) {
            var o = checkboxes[i];
            if (value[o.value] !== undefined) {
                o.checked=false;
            }
        }
    }
    else if (act === 'select') {
        for(var i in checkboxes) {
            var o = checkboxes[i];
            if (o.value === value) {
                o.checked=true;
                break;
            }
        }
    }
    else if (act === 'unselect') {
        for(var i in checkboxes) {
            var o = checkboxes[i];
            if (o.value === value) {
                o.checked=false;
                break;
            }
        }
    }
}

function easyuiFuncFormatTableDisplayHtml(lineDatas) {
    var htmlArray = new Array();
    htmlArray.push('<div style=\'display:table-row-group\'>');
    for (var i in lineDatas) {
        var line = lineDatas[i];
        htmlArray.push('<div style=\'display:table-row\'>');
        //var columnWidth = parseInt(100 / line.length);
        for (var j in line) {
            var c = line[j];
            htmlArray.push('<div style=\'display:table-cell;padding-left:2px;\'><font>'+c.label+':</font></div>');
            htmlArray.push('<div style=\'display:table-cell;padding-left:8px;padding-right:24px\'><font>'+c.value+'</font></div>');
        }
        htmlArray.push('</div>');
    }
    htmlArray.push('</div>');
    return htmlArray.join('');
}

function easyuiFuncFormatProgressbarHtml(value, text, width, color, clsName) {
    var cls = 'easyui-progressbar';
    if (clsName != undefined) {
        cls += ' '+clsName;
    }
    if (value == undefined) {
        value = 0;
    }
    var dataOpts = 'value:'+value;
    if (text != undefined) {
        dataOpts += ',text:'+"'"+text+"'";
    }
    var extra = '';
    if (width != undefined) {
        extra += 'width:'+width+';';
    }
    if (color != undefined) {
        extra += 'background-color:'+color+';';
    }
    if (extra != '') {
        extra = 'style="'+extra+'"';
    }
    return '<div class="'+cls+'" data-options="'+dataOpts+'"'+extra+'></div>';
}
