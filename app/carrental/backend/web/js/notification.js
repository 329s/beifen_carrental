(function($){
    if ($.carrental == undefined) {
        $.carrental = {};
    }
    $.carrental.notifications = {
        opened:true,
        urlCheck:'notification/check',

        originWaitingOrder:0,

        check:function() {
            //if (Pace !== undefined) {
            //    Pace.ignore(function() {$.carrental.notifications._queryCheck();});
            //}
            //else {
                $.carrental.notifications._queryCheck();
            //}
        },

        _queryCheck() {
            $.ajax({
                url:$.carrental.notifications.urlCheck,
                type:'get',
                success:function (data) {
                    var obj;
                    try {
                        obj = eval('(' + data + ')');
                        $.carrental.notifications.notify(obj);
                    }
                    catch (e) {
                        obj = undefined;
                    }
                    if ($.carrental.notifications.opened) {
                        setTimeout($.carrental.notifications.check, 5000);
                    }
                },
                error: function (e) {
                    if ($.carrental.notifications.opened) {
                        setTimeout($.carrental.notifications.check, 5000);
                    }
                }
            });
        },

        notify:function(data) {
            var notificationsCount = 0;
            for (var k in data) {
                if (k.substr(0, 8) == 'vehicle-') {
                    var oTarget = $('#tabtips-'+k);
                    if (oTarget.length) {
                        oTarget.html(data[k]!=0?data[k]:'');
                    }
                }
                if (k.substr(0, 6) == 'order-') {
                    var oTarget = $('#header-notifications-'+k);
                    if (oTarget.length) {
                        if (data[k]!=0){
                            notificationsCount++;
                            oTarget.find('span.badge').html(data[k]);
                            oTarget.css({display:'list-item'});
                        } else {
                            oTarget.find('span.badge').html('');
                            oTarget.css({display:'none'});
                        }
                    }
                }
            }
            var oTarget = $('#header-notifications-count');
            if (oTarget) {
                oTarget.html(notificationsCount);
            }
            oTarget = $('#header-notifications-count-label');
            if (oTarget) {
                oTarget.html(notificationsCount?notificationsCount:'');
            }

            if (data['order-waiting-count'] != undefined) {
                var n = parseInt(data['order-waiting-count']);
                if (n > $.carrental.notifications.originWaitingOrder) {
                    var pop = new XPopup('有新的订单', '/', '您有新的订单待处理，请及时处理。');
                }
                $.carrental.notifications.originWaitingOrder = n;
            }
        }
    }
})(jQuery);

$(function() {
    if ($.carrental.notifications.opened) {
        setTimeout($.carrental.notifications.check, 5000);
    }
});
