/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
(function($){
    if ($.custom == undefined) {
        $.custom = {};
    }
    
    $.custom.dwz = {
        defaults: {
            paginationTextDisplay:'Display',
            paginationTextDisplayItems:'item(s),total{0}item(s)',
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

        getNodeAttribute:function(node, attrName) {
            var attrs = node.attributes;
            for (var i = 0; i < attrs.length; i++) {
                if (attrs[i].name == attrName) {
                    return attrs[i].value;
                }
            }
            return '';
        },
        
        _objectStr:function(obj) {
            var _rels = new Array();
            for (var _k in obj) {
                var _v = obj[_k];
                var _t = typeof(_v);
                if (_t == 'string') {
                    _v = "'"+_v+"'";
                }
                else if (_t == 'object') {
                    _v = $.custom.dwz._objectStr(_v);
                }
                _rels.push(_k+':'+_v);
            }
            return '{'+_rels.join(',')+'}';
        },
        
        _strToObject:function(str) {
            return new Function('return ' + str)();
        },
        
        table:{
            asynLoadData:function(tableId, url, method, options) {
                var params = {};
                if (options.numPerPage == undefined || options.numPerPage == '') {
                    options.numPerPage = options.rel.numPerPage;
                }
                else {
                    options.rel.numPerPage = parseInt(options.numPerPage);
                }
                if (options.pageNumShown == undefined || options.pageNumShown == '') {
                    options.pageNumShown = options.rel.pageNumShown;
                }
                else {
                    options.rel.pageNumShown = parseInt(options.pageNumShown);
                }
                if (options.data) {
                    var p = options.data;
                    if (p.pageNum != undefined && p.pageNum != '') {
                        params.page = parseInt(p.pageNum);
                        options.currentPage = params.page;
                    }
                    if (p.numPerPage != undefined && p.numPerPage != '') {
                        params.rows = parseInt(p.numPerPage);
                        options.numPerPage = params.rows;
                        options.rel.numPerPage = options.numPerPage;
                    }
                    if (p.orderField != undefined && p.orderField != '') {
                        params.sort = p.orderField;
                    }
                    if (p.orderDirection != undefined && p.orderDirection != '') {
                        params.order = p.orderDirection;
                    }
                }
                
                if (!params.page) {
                    params.page = 1;
                    options.currentPage = params.page;
                }
                if (!params.rows) {
                    if (options.numPerPage) {
                        params.rows = options.numPerPage;
                    }
                    else if (options.rel.numPerPage) {
                        params.rows = options.rel.numPerPage;
                    }
                }
                
                if (options.rel) {
                    if (options.rel.searchParamName) {
                        var searchParam = new Function('return '+options.rel.searchParamName)();
                        if (searchParam.cacheData == undefined) {
                            searchParam.cacheData = {};
                        }
                        if (options.currentPage) {
                            searchParam.cacheData.currentPage = options.currentPage;
                        }
                        if (options.numPerPage) {
                            searchParam.cacheData.numPerPage = options.numPerPage;
                        }
                        if (options.pageNumShown) {
                            searchParam.cacheData.pageNumShown = options.pageNumShown;
                        }
                        if (params.sort) {
                            searchParam.cacheData.orderField = params.sort;
                        }
                        else if (searchParam.cacheData.orderField) {
                            params.sort = searchParam.cacheData.orderField;
                        }
                        if (params.order) {
                            searchParam.cacheData.orderDirection = params.order;
                        }
                        else if (searchParam.cacheData.orderDirection) {
                            params.order = searchParam.cacheData.orderDirection;
                        }
                        
                        // search params
                        var skipKeys = ['rows', 'page', 'sort', 'order'];
                        for (var k in searchParam.params) {
                            if (k in skipKeys) {
                            }
                            else {
                                params[k] = searchParam.params[k];
                            }
                        }
                    }
                }
                
                method = method || 'POST';
                var postData = undefined;
                if (method.toLowerCase() == 'get') {
                    var sep = '&';
                    if (url.indexOf('?') === -1) {
                        sep = '?';
                    }
                    for (var k in params) {
                        url += sep + encodeURI(k) + '=' + encodeURI(params[k]);
                        sep = '&';
                    }
                }
                else {
                    var arr = new Array();
                    for (var k in params) {
                        arr.push(encodeURI(k) + '=' + encodeURI(params[k]));
                    }
                    postData = arr.join('&');
                }

                $.ajax({
                    type:method,
                    url:url,
                    dataType:"json",
                    data:postData,
                    cache: false,
                    success: function(data) {
                        $.custom.dwz.table.loadDataJson(tableId, options, data);
                    },
                    error: DWZ.ajaxError
                });
            },

            loadDataJson:function(tableId, options, jsonData) {
                var tblRootObj = $('#'+tableId);
                var tblTemplateObj = tblRootObj.children('table.table-template');
                var tblNode = tblRootObj.children('div.t-fill-position');
                //var tblNode = tblTemplateObj;
                if (tblTemplateObj.size() == 0) {
                    tblTemplateObj = tblRootObj.children('div').children('table.table-template');
                    //if (tblNode.size == 0) {
                    //    tblNode = tblTemplateObj.parent();
                    //}
                }
                var tblObj = tblRootObj.children('.grid');
                var tblHeight = 0;
                if (tblObj.size() > 0) {
                    tblNode = tblObj;
                    //tblObj.remove();
                    tblHeight = tblObj.children('.gridScroller').height();
                }
                else {
                    tblHeight = tblNode.height() - 23;
                }

                var orderField = false;
                var orderDirection = false;
                if (options.data) {
                    var p = options.data;
                    if (p.orderField != undefined && p.orderField != '') {
                        orderField = p.orderField;
                    }
                    if (p.orderDirection != undefined && p.orderDirection != '') {
                        orderDirection = p.orderDirection;
                    }
                }

                var tblHeads = tblTemplateObj.children('thead').children('tr').children('th');
                var tblBody = tblTemplateObj.children('tbody:first');
                var cols = new Array();
                for (var i = 0; i < tblHeads.length; i++) {
                    var e = tblHeads[i];
                    var k = e.getAttribute('field');
                    var formatter = e.getAttribute('formatter') || null;
                    if (!k) {
                        k = '';
                    }
                    if (formatter) {
                        formatter = new Function('return '+formatter)();
                    }

                    if (orderField && orderDirection) {
                        var fieldClass = e.className || '';
                        if (k == orderField) {
                            if (fieldClass.match(/\b(a|de)sc\b/)) {
                                fieldClass = fieldClass.replace(/(a|de)sc/, orderDirection);
                            }
                            else {
                                fieldClass += (fieldClass == '' ? '' : ' ') + orderDirection;
                            }
                            e.className = fieldClass;
                        }
                        else {
                            if (fieldClass.match(/\b(a|de)sc\b/)) {
                                fieldClass = fieldClass.replace(/(a|de)sc/, '');
                                e.className = fieldClass;
                            }
                        }
                    }

                    cols.push({field:k, formatter:formatter});
                }

                var tblLines = new Array();
                for (var i = 0; i < jsonData.rows.length; i++) {
                    var rowData = jsonData.rows[i];
                    var line = new Array();
                    for (var j = 0; j < cols.length; j++) {
                        var colCfg = cols[j];
                        var val = rowData[colCfg.field] || '';
                        if (colCfg.formatter) {
                            val = colCfg.formatter(val, rowData);
                        }
                        line.push('    <td>'+val+'</td>');
                    }
                    //tblLines.push('<tr target="sid_user" rel="1">\n' + line.join('\n') + '\n</tr>');
                    tblLines.push('<tr>\n' + line.join('\n') + '\n</tr>');
                }
                tblBody.html(tblLines.join('\n'));

                var attrs = tblTemplateObj[0].attributes;
                var _attrs = new Array();
                for (var i = 0; i < attrs.length; i++) {
                    if (attrs[i].name != 'class') {
                        if (attrs[i].name.toLowerCase() == 'tlayouth') {
                            _attrs.push('layoutH="' + attrs[i].value + '"');
                        }
                        else {
                            _attrs.push(attrs[i].name + '="' + attrs[i].value + '"');
                        }
                    }
                }
                _attrs.push('targetType="'+options.targetType+'"');
                _attrs.push('rel="'+$.custom.dwz._objectStr(options.rel)+'"');
                tblNode.before('<table class="table" '+_attrs.join(' ')+'></table>');
                //tblNode.hide();
                tblNode.remove();
                tblObj = tblRootObj.children('table.table');
                tblObj.html(tblTemplateObj.html());
                tblObj.jTable();
                var tblBodyObj = tblRootObj.children('.grid').children('.gridScroller');
                tblBodyObj.css('height', tblHeight);

                // initUI
                initUI('#'+tableId);
                
                var paginationObj = $('#'+tableId+'_pagination');
                var pageDisplay = paginationObj.children('.pages').children('span:eq(1)');
                pageDisplay.html($.custom.dwz.format($.custom.dwz.defaults.paginationTextDisplayItems, jsonData.total));
                var pageObj = paginationObj.children('.pagination');
                var op = $.extend({ targetType:"navTab", rel:"", callback:null}, options);
                op.totalCount = jsonData.total;
                pageObj.pagination(op);
            },
            
            getTableSearchParams:function(tableId) {
                var tblRootObj = $('#'+tableId);
                var tblSearchForm = tblRootObj.children('.pageHeader').children('form');
                var postParams = tblSearchForm.serializeArray();
                var params = {};
                for (var i in postParams) {
                    var e = postParams[i];
                    var k = e.name;
                    var v = e.value;
                    if (k) {
                        var _idx = false;
                        if (k.substr(k.length-1,1) == ']') {
                            if (k.substr(k.length-2,2) == '[]') {
                                k = k.substring(0, k.length-2);
                                if (params[k] == undefined) {
                                    v = [v];
                                }
                            }
                            else {
                                var pos = k.indexOf('[');
                                if (pos > 0) {
                                    _idx = k.substring(pos+1,k.length-2);
                                    k = k.substring(0, pos);
                                }
                            }
                        }
                        if (params[k]) {
                            var prev = params[k];
                            if (typeof(prev) != 'object') {
                                prev = new Array();
                                prev.push(params[k]);
                                params[k] = prev;
                            }
                            if (_idx !== false) {
                                prev[_idx] = v;
                            }
                            else {
                                prev.push(v);
                            }
                        }
                        else {
                            if (_idx !== false) {
                                var _o = {};
                                _o[_idx] = v;
                                params[k] = _o;
                            }
                            else {
                                params[k] = v;
                            }
                        }
                    }
                }
                var retParams = {};
                for (var k in params) {
                    var v = params[k];
                    if (typeof(v) == 'object') {
                        for (var j in v) {
                            retParams[k+'['+j+']'] = v[j];
                        }
                    }
                    else {
                        retParams[k] = v;
                    }
                }
                return retParams;
            },
            
            doSearch:function(tableId, url, options) {
                var op0 = {data:{pageNum:"", numPerPage:"", orderField:"", orderDirection:""}, callback:null};
                if (options.rel.searchParamName) {
                    var searchParam = new Function('return '+options.rel.searchParamName)();
                    op0.currentPage = searchParam.cacheData.currentPage;
                    op0.numPerPage = searchParam.cacheData.numPerPage;
                    op0.pageNumShown = searchParam.cacheData.pageNumShown;
                    op0.data.pageNum = op0.currentPage;
                    op0.data.numPerPage = op0.numPerPage;
                    if (searchParam.cacheData.orderField) {
                        op0.data.orderField = searchParam.cacheData.orderField;
                    }
                    if (searchParam.cacheData.orderDirection) {
                        op0.data.orderDirection = searchParam.cacheData.orderDirection;
                    }
                    
                    // get params
                    var postParams = $.custom.dwz.table.getTableSearchParams(tableId);
                    for (var k in postParams) {
                        searchParam.params[k] = postParams[k];
                    }
                }
                var op = $.extend(op0, options);
                $.custom.dwz.table.asynLoadData(tableId, url, 'post', op);
            },
            
            reloadWithUrl:function(tableId, url, targetType, searchParamName) {
                var params = new Array();
                if (searchParamName) {
                    var searchParam = new Function('return '+searchParamName)();
                    if (searchParam.cacheData.currentPage) {
                        params.push({name:'currentPage', value:searchParam.cacheData.currentPage});
                    }
                    if (searchParam.cacheData.numPerPage) {
                        params.push({name:'numPerPage', value:searchParam.cacheData.numPerPage});
                    }
                    if (searchParam.cacheData.orderField) {
                        params.push({name:'orderField', value:searchParam.cacheData.orderField});
                    }
                    if (searchParam.cacheData.orderDirection) {
                        params.push({name:'orderDirection', value:searchParam.cacheData.orderDirection});
                    }
                }

                // get params
                var postParams = $.custom.dwz.table.getTableSearchParams(tableId);
                for (var k in postParams) {
                    params.push({name:k, value:postParams[k]});
                }
                
                if (url) {
                    if (targetType == "dialog") {
                        $.pdialog.reload(url, {data: params, callback: null});
                    } else {
                        navTab.reload(url, {data: params, callback: null});
                    }
                }
            }
        },

        panel: {
            setTitle: function(panelId, title) {
                if (panelId.indexOf('#') !== 0) {
                    panelId = '#' + panelId;
                }
                var obj = $(panelId);
                if (obj.size() == 0) {
                    return;
                }
                var objTitleBar = obj.children('.panelBar.dwz-panel-title-bar');
                if (objTitleBar.size() == 0) {
                    return;
                }
                var objTitle = objTitleBar.children('span:first');
                objTitle.html(title);
            },
            reload: function(panelId, url, params) {
                if (panelId.indexOf('#') !== 0) {
                    panelId = '#' + panelId;
                }
                var obj = $(panelId);
                if (obj.size() == 0) {
                    return;
                }
                var panelContent = obj.children('.dwz-panel-content');
                if (panelContent.size() == 0) {
                    return;
                }
                if (!url) {
                    return;
                }
                var opts = obj.data('options');
                if (!opts) {
                    obj.data('options', {queryParams:{}});
                    opts = obj.data('options');
                }

                if (params) {
                    if (!opts.queryParams || $.custom.dwz.isObjectEmpty(opts.queryParams)) {
                        opts.queryParams = params;
                    }
                    else {
                        for (var k in params) {
                            opts.queryParams[k] = params[k];
                        }
                    }
                }

                var postParams = new Array();
                for (var k in opts.queryParams) {
                    postParams.push({name:k, value:opts.queryParams[k]});
                }

                panelContent.ajaxUrl({
                    type:"POST", url:url, data: postParams, callback:function(){
                        panelContent.find("[layoutH]").layoutH();
                    }
                });
            },
            clear:function(panelId) {
                if (panelId.indexOf('#') !== 0) {
                    panelId = '#' + panelId;
                }
                var obj = $(panelId);
                if (obj.size() == 0) {
                    return;
                }
                var panelContent = obj.children('.dwz-panel-content');
                if (panelContent.size() == 0) {
                    return;
                }
                panelContent.html('');
            }
        },

        combotree: {
            getSelectedValue: function(id) {
                if (id.indexOf('#') !== 0) {
                    id = '#' + id;
                }
                var obj = $(id);
                if (obj.size() == 0) {
                    return '';
                }
                return obj[0].value;
            },
        },
        
        datalist: {
            getSelectedValue: function(id) {
                var selValues = $.custom.dwz.datalist.getSelections(id);
                if (selValues.length == 0) {
                    return '';
                }
                else if(selValues.length == 1) {
                    return selValues[0].value;
                }
                var a = new Array();
                for (var k in selValues) {
                    a.push(selValues[k].value);
                }
                return a;
            },
            getSelectedNames: function(id) {
                var selValues = $.custom.dwz.datalist.getSelections(id);
                if (selValues.length == 0) {
                    return '';
                }
                else if(selValues.length == 1) {
                    return selValues[0].text;
                }
                var a = new Array();
                for (var k in selValues) {
                    a.push(selValues[k].text);
                }
                return a;
            },
            getSelections: function(id) {
                if (id.indexOf('#') !== 0) {
                    id = '#' + id;
                }
                var obj = $(id);
                var selValues = new Array();
                if (obj.size() == 0) {
                    return selValues;
                }
                var lst = obj.children('li');
                for (var i = 0; i < lst.length; i++) {
                    var li = lst[i];
                    if (li.className.match(/\s*selected\s*/)) {
                        selValues.push({value:$.custom.dwz.getNodeAttribute(li, 'value'), text:li.textContent});
                    }
                }
                return selValues;
            },
            onClick: function(e) {
                var e = e || window.event, target = e.target || e.srcElement;
                var currentTarget = e.currentTarget || this;
                currentTarget = $(currentTarget);
                var lst = currentTarget.children('li');
                var val = '';
                for (var i = 0; i < lst.length; i++) {
                    var li = lst[i];
                    if (target == li) {
                        li.className = 'selected';
                        val = $.custom.dwz.getNodeAttribute(li, 'value');
                    }
                    else {
                        li.className = '';
                    }
                }

                var callbackFunc = $.custom.dwz.getNodeAttribute(currentTarget[0], 'callback');
                if (callbackFunc) {
                    var func = new Function('return '+callbackFunc)();
                    func(val);
                }
            },
            bindOnClickEvent: function(id) {
                if (id.indexOf('#') !== 0) {
                    id = '#' + id;
                }
                var obj = $(id);
                if (obj.size() == 0) {
                    return;
                }
                obj[0].onclick = $.custom.dwz.datalist.onClick;
            }
        },
    }
    

    /**
     * extends
     */

    /** 扩展
     * 处理navTab中的分页和排序
     * targetType: navTab 或 dialog
     * rel: 可选 用于局部刷新div id号
     * data: pagerForm参数 {pageNum:"n", numPerPage:"n", orderField:"xxx", orderDirection:""}
     * callback: 加载完成回调函数
     */
    dwzPageBreak=function(options){
        var op = $.extend({ targetType:"navTab", rel:"", data:{pageNum:"", numPerPage:"", orderField:"", orderDirection:""}, callback:null}, options);
        
        if (op.targetType == 'ajaxCustom' && op.rel) {
            if (typeof(op.rel) == 'string') {
                op.rel = new Function('return ' + op.rel)();
            }
            var tableId = op.rel.tableId;
            var url = op.rel.url;
            var method = op.rel.method;
            $.custom.dwz.table.asynLoadData(tableId, url, method, op);
            return;
        }
        
        var $parent = op.targetType == "dialog" ? $.pdialog.getCurrent() : navTab.getCurrentPanel();
        if (op.rel) {
            var $box = $parent.find("#" + op.rel);
            var form = _getPagerForm($box, op.data);
            if (form) {
                $box.ajaxUrl({
                    type:"POST", url:$(form).attr("action"), data: $(form).serializeArray(), callback:function(){
                        $box.find("[layoutH]").layoutH();
                    }
                });
            }
        } else {
            var form = _getPagerForm($parent, op.data);
            var params = $(form).serializeArray();
            
            if (op.targetType == "dialog") {
                if (form) $.pdialog.reload($(form).attr("action"), {data: params, callback: op.callback});
            } else {
                if (form) navTab.reload($(form).attr("action"), {data: params, callback: op.callback});
            }
        }
    }

})(jQuery);
