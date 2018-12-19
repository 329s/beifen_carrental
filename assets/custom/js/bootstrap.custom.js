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
    
    $.custom.bootstrap = {
        
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
        
        showModal: function(modalId, remoteUrl, onHide) {
            if (modalId.indexOf('#') !== 0) {
                modalId = '#' + modalId;
            }
            if ($(modalId).length == 0) {
                return;
            }
            if (remoteUrl != '') {
                $(modalId).load(remoteUrl, function() {
                    $(modalId).modal('show');
                });
            }
            else {
                $(modalId).modal('show');
            }
            if (typeof onHide == 'function') {
                $(modalId).on('hidden.bs.modal', function(e) {
                    onHide($(modalId));
                });
            }
        },
        
        alert: function(message, title, callback, dt, alertType, msgIcon) {
            
            var model = '<div class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-hidden="false">' +
                '<div class="modal-dialog"><div class="modal-content">' +
                '<div class="modal-header">' +
                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
                '<h4 class="modal-title" id="myModalLabel">'+title+'</h4>' +
                '</div>' + 
                '<div class="modal-body">' + message + '</div>' + 
                '<div class="modal-footer">' + 
                //'<button type="button" class="btn btn-default" data-dismiss="modal">'+$.custom.lan.defaults.sys.cancel+'</button>' +
                '<button type="button" class="btn btn-primary" data-dismiss="modal">'+$.custom.lan.defaults.sys.ok+'</button>'+
                '</div>'+
                '</div></div></div>';
            var $modal = $(model).modal();
            $modal.on('hide.bs.modal', function() {
                $(this).remove();
                if (typeof callback == 'function') {
                    callback(true);
                }
            });
            if (dt != undefined) {
                setTimeout(function(){ $modal.modal('hide'); }, dt);
            }
        },
        
        confirm: function(message, title, confirmCallback) {
            var model = '<div class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-hidden="false">' +
                '<div class="modal-dialog"><div class="modal-content">' +
                '<div class="modal-header">' +
                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
                '<h4 class="modal-title" id="myModalLabel">'+title+'</h4>' +
                '</div>' + 
                '<div class="modal-body">' + message + '</div>' + 
                '<div class="modal-footer">' + 
                '<button type="button" class="btn btn-default" data-dismiss="modal">'+$.custom.lan.defaults.sys.cancel+'</button>' +
                '<button type="button" class="btn btn-primary btn-confirm">'+$.custom.lan.defaults.sys.ok+'</button>'+
                '</div>'+
                '</div></div></div>';
            var $modal = $(model).modal();
            $modal.on('shown.bs.modal', function(e) {
                if (typeof confirmCallback == 'function') {
                    $('.btn-confirm', $modal).click(function(e) {
                        $modal.modal('hide');
                        confirmCallback(true);
                    });
                }
            });
            $modal.on('hide.bs.modal', function(e) {
                $(this).remove();
            });
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
        
        defaultLoadError:function(e) {
            if ($.custom.bootstrap.preProcessLoadError(e)) {
                return;
            }
            var statusCode = e.status;
            var statusText = e.statusText;
            var responseText = e.responseText;
            var msg = $.custom.bootstrap.format($.custom.utils.lan.defaults.msgGotResponseFailedError, ' code:' + statusCode + ' error:' + statusText) + responseText;
            $.custom.bootstrap.alert(msg, $.custom.utils.lan.defaults.titleError);
        },
        
        tryParseReceivedAsJson: function(data, successCallback, unsuccessCallback) {
            if (data == '') return false;
            var msg;
            var msgIcon = 'info';
            var alertType = 'info';     // statusCode == 200
            var alertTitle = '     ';
            var obj = undefined;
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
                    if (successCallback && typeof(successCallback) == 'function') {
                        //setTimeout(function() { 
                            successCallback(obj);
                        //}, 10);
                    }
                    // other params
                    // obj.navTabId
                    // obj.rel
                    // obj.callbackType
                    // obj.forwardUrl
                    if (obj.navTabId != undefined && '' != obj.navTabId) {
                        if (obj.navTabId.substr(0, 4) == 'page') {
                            obj.navTabId = obj.navTabId.substr(4);
                        }
                        //setTimeout(function(){easyuiFuncNavTabShowByTabPanelId(obj.navTabId);}, 100);
                    }

                    if ('closeCurrent' == obj.callbackType) {
                        $.custom.bootstrap.closeCurrent();
                    }
                    else if ('refreshCurrentX' == obj.callbackType) {
                        $.custom.bootstrap.closeCurrent();
                        $.custom.bootstrap.reloadCurrent();
                    }
                    else if ('refreshCurrent' == obj.callbackType) {
                        //easyuiFuncNavTabRefreshCurTab();
                        $.custom.bootstrap.reloadCurrent();
                    }
                    else if ('closeNavTab' == obj.callbackType) {
                        $.custom.bootstrap.closeCurrent();
                    }
                    else if ('forward' == obj.callbackType) {
                        $.custom.bootstrap.reloadCurrent(obj.forwardUrl, obj.forwardTitle);
                    }
                    else if ('forwardConfirm' == obj.callbackType) {
                        if (obj.confirmMsg) {
                            //$.messager.confirm($.custom.utils.lan.defaults.titlePrompt, obj.confirmMsg, function(r){
                            //    if (r){
                            //        easyuiFuncNavTabReloadCurTab(obj.forwardUrl, obj.forwardTitle);
                            //    }
                            //    else {
                            //        easyuiFuncCloseCurrent(obj.navTabId);
                            //    }
                            //});
                        }
                    }

                    if (obj.message == '') {
                        return;
                    }
                }

                msg = obj.message;
                msgIcon = alertType;
            }
            else {
                return false;
            }
            
            $.custom.bootstrap.alert(msg, alertTitle, undefined, 3000, alertType, msgIcon);
            return true;
        },
        
        queryUrl: function(urlOrOptions, successCallback, unsuccessCallback) {
            var options = {
                success: function (result) {
                    if ($.custom.bootstrap.tryParseReceivedAsJson(result, successCallback, unsuccessCallback)) {
                        return;
                    }
                    if (typeof(successCallback) == 'function') {
                        successCallback(result);
                    }
                },
                error: function(e) {
                    $.custom.bootstrap.defaultLoadError(e);
                    if (typeof(unsuccessCallback) == 'function') {
                        unsuccessCallback(e);
                    }
                },
                type: 'GET'
            };
            if (typeof urlOrOptions == 'string') {
                options.url = urlOrOptions;
            }
            else if (typeof urlOrOptions == 'object') {
                options = $.extend(options, urlOrOptions);
            }
            $.ajax(options);
        },
        
        loadElement: function(selector, urlOrOptions, successCallback, unsuccessCallback) {
            var options = {
                success: function (result) {
                    if ($.custom.bootstrap.tryParseReceivedAsJson(result, successCallback, unsuccessCallback)) {
                        return;
                    }
                    if (selector) {
                        $(selector).html(result);
                    }
                    else {
                        $(document).html(result);
                    }
                    if (typeof(successCallback) == 'function') {
                        successCallback(result);
                    }
                },
                error: function(e) {
                    $.custom.bootstrap.defaultLoadError(e);
                    if (typeof(unsuccessCallback) == 'function') {
                        unsuccessCallback(e);
                    }
                }
            };
            if (typeof urlOrOptions == 'string') {
                options.url = urlOrOptions;
            }
            else if (typeof urlOrOptions == 'object') {
                options = $.extend(options, urlOrOptions);
            }
            $.custom.element.load(selector, options);
        },
        
        reloadCurrent: function(url, title) {
            var dom = $('.content-wrapper .content');
            var target = undefined;
            if (dom.length) {
                target = $(dom[0]);
            }
            
            if (target) {
                if (url == undefined) {
                    // TODO
                    return;
                }
                $.custom.bootstrap.loadElement(target, url);
                return;
            }
        },
        
        closeCurrent: function() {
            var modals = $('.modal');
            for (var i = modals.length-1; i >= 0; i--) {
                if ($(modals[i]).attr('aria-hidden') == 'false') {
                    $(modals[i]).modal('hide');
                    break;
                }
                else if ($(modals[i]).css('display') != 'none') {
                    $(modals[i]).modal('hide');
                    break;
                }
            }
        },
        
        treeview: {
            buildDomTree: function (srcdata, idField, valueField, selected) {
                var data = [];
                if (idField == undefined) { idField = 'id'; }
                if (valueField == undefined) { valueField = 'text'; }
                //if (selected == undefined) { selected = ''; }
                function walk(nodes, data) {
                    if (!nodes) {
                        return;
                    }
                    $.each(nodes, function(id, node) {
                        var obj = {
                            id : node[idField],
                            text : node[valueField] != null ? node[valueField] : ' - ',
                        };
                        for (var k in node) {
                            if (k != 'isLeaf' && k != 'children') {
                                obj[k] = node[k];
                            }
                        }
                        if (obj.checkable != undefined) {
                            if (!obj.checkable) {
                                obj.selectable = false;
                            }
                        }
                        if (selected && obj.id == selected) {
                            if (obj.state == undefined) {
                                obj.state = {};
                            }
                            obj.state.selected = true;
                        }
                        if (node.children != undefined && node.children) {
                            obj.nodes = [];
                            walk(node.children, obj.nodes);
                        }
                        data.push(obj);
                    });
                }

                walk(srcdata, data);
                return data;
            },
            
        },
        
        combotree : {
            init : function(treeData, fieldId, inputId, viewId) {
                var options = {
                    bootstrap2 : false,
                    showTags : true,
                    levels : 5,
                    //showCheckbox : true,
                    nodeIcon: 'glyphicon',
                    data : treeData,
                    onNodeSelected : function(event, data) {
                        var checkable = true;
                        if (data.checkable != undefined) {
                            if (!data.checkable) {
                                checkable = false;
                            }
                        }
                        else if (data.nodes) {
                            checkable = false;
                        }
                        if (!checkable) {
                            $(this).treeview('uncheckNode', data);
                            return;
                        }
                        
                        $(fieldId).val(data.id);
                        $(inputId).val(data.text);
                        $(this).hide();
                    },
                    onNodeUnselected : function(event, data) {
                        $(fieldId).val('');
                        $(inputId).val('');
                    }
                };

                $(viewId).css({width:$(inputId).css('width'),height:'300px'});
                $(viewId).treeview(options);
                $(viewId).hide();
                
                // initialize value
                var value = $(fieldId).val();
                if (value) {
                    var selected = $(viewId).treeview('getSelected');
                    for (var i in selected) {
                        $(inputId).val(selected[i].text);
                        break;
                    }
                }
                else {
                    $(inputId).val('');
                }
                
                $(inputId).click(function() {
                    if ($(viewId).css('display') == 'none') {
                        $(viewId).show();
                    } else {
                        $(viewId).hide();
                    }
                });
            }
        },
        
        yiigridview : {
            applyFilter : function ($grid, event, targetSelector, method) {
                if (targetSelector == undefined || !targetSelector) {
                    return;
                }
                var form = $grid.find('form.gridview-filter-form');
                if (form.length== 0) {
                    return;
                }
                
                if (method) {
                    form.attr({method:method});
                }
                event.result = false;
                //form.attr({method:'post'});
                
                var options = {
                    //beforeSubmit:undefined,
                    success: function(response) {
                        if ($.custom.bootstrap.tryParseReceivedAsJson(response, undefined, $.custom.bootstrap.preProcessLoadError)) {
                            return;
                        }
                        if (targetSelector) {
                            $(targetSelector).html(response);
                        }
                    },
                    error: $.custom.bootstrap.defaultLoadError
                };
                //form.ajaxSubmit(function(response) {
                //    $(targetSelector).html(response);
                //});
                form.ajaxSubmit(options);
            },
            
            queryUrl : function(gridId, url, needSelect, targetSelector) {
                if (gridId.indexOf('#') !== 0) {
                    gridId = '#' + gridId;
                }
                var selected = $(gridId).yiiGridView('getSelectedRows');
                var params = undefined;
                if (!selected) {
                    if (needSelect) {
                        $.custom.bootstrap.alert($.custom.utils.lan.defaults.msgYouShouldSelectARow, $.custom.utils.lan.defaults.titleWarning, 3000);
                        return;
                    }
                }
                else {
                    params = {
                        id : selected
                    };
                }
                
                $.custom.bootstrap.loadElement(targetSelector, {url:url, data:params});
            }
        },
        
        tabs : {
            init : function (selector) {
                var target = $(selector);
                if (target.length == 0) {
                    return;
                }
                var contentWrapper = target.next('.tab-content');
                
                $('a[data-href]', target).on('show.bs.tab', function(e) {
                    var curTab = $(e.target);
                    if (curTab.attr('data-href-loaded') != 'loaded') {
                        if (contentWrapper.length) {
                            var contentTarget = $(curTab.attr('href'), contentWrapper);
                            if (contentTarget.length) {
                                $.custom.bootstrap.loadElement(contentTarget, curTab.attr('data-href'), function(data){
                                    curTab.attr('data-href-loaded', 'loaded');
                                });
                            }
                        }
                    }
                });
                
                var activeTab = $('li.active a[data-href]', target);
                if (activeTab.length) {
                    var contentTarget = $(activeTab.attr('href'), contentWrapper);
                    if (contentTarget.length) {
                        $.custom.bootstrap.loadElement(contentTarget, activeTab.attr('data-href'), function(data){
                            activeTab.attr('data-href-loaded', 'loaded');
                        });
                    }
                }
            }
        },
        
        form: {
            onSubmit: function(form, params, successCallback, onsubmitCallback) {
                //var $form = $(form);
                //$form.preventDefault();
                setTimeout(function(){ $.custom.bootstrap.form.doSubmit(form, params, successCallback, onsubmitCallback); }, 100);
                return false;
            },
            
            doSubmit: function(form, params, successCallback, onsubmitCallback) {
                var $form = $(form);
                var target = $form.attr('target');
                var options = {
                    //beforeSubmit:undefined,
                    success: function(response) {
                        if ($.custom.bootstrap.tryParseReceivedAsJson(response, successCallback, $.custom.bootstrap.preProcessLoadError)) {
                            return;
                        }
                        if (target) {
                            $(target).html(response);
                        }
                    },
                    error: function(e) {
                        if ($.custom.bootstrap.preProcessLoadError(e)) {
                            return;
                        }
                    }
                };
                if (typeof(onsubmitCallback) == 'function') {
                    options.beforeSubmit = onsubmitCallback;
                }
                
                $form.ajaxSubmit(options);
            }
        }
    };
    
})(jQuery);

function bsCustomDebugThisValue(obj, param1, param2) {
    var p = $(obj);
}
