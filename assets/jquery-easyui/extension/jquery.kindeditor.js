/**
 * kindeditor - jQuery EasyUI
 * 
 * Licensed under the GPL:
 *   http://www.gnu.org/licenses/gpl.txt
 *
 * Copyright 2016-2017 www.ekeei.com 
 * 
 */
(function ($, K) {
    if (!K)
        throw "KindEditor undefined!";

    function create(target) {
        var opts = $.data(target, 'kindeditor').options;
        var editor = K.create(target, opts);
        $.data(target, 'kindeditor').options.editor = editor;
    }

    $.fn.kindeditor = function (options, param) {
        if (typeof options == 'string') {
            var method = $.fn.kindeditor.methods[options];
            if (method) {
                return method(this, param);
            }
        }
        options = options || {};
        return this.each(function () {
            var state = $.data(this, 'kindeditor');
            if (state) {
                $.extend(state.options, options);
            } else {
                state = $.data(this, 'kindeditor', {
                    options: $.extend({}, $.fn.kindeditor.defaults, $.fn.kindeditor.parseOptions(this), options)
                });
            }
            create(this);
        });
    }

    $.fn.kindeditor.parseOptions = function (target) {
        return $.extend({}, $.parser.parseOptions(target, []));
    };

    $.fn.kindeditor.methods = {
        editor: function (jq) {
            return $.data(jq[0], 'kindeditor').options.editor;
        }
    };

    $.fn.kindeditor.defaults = {
        resizeType: 1,
        allowPreviewEmoticons: false,
        allowImageUpload: false,
        items: [
            'undo', 'redo', '|', 'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
            'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
            'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
            'superscript', 'quickformat', 'selectall', '|', '/',
            'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
            'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|', 'image', 'multiimage',
            'flash', 'media', 'insertfile', 'table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
            'anchor', 'link', 'unlink'
        ],
        afterChange: function () {
            this.sync();
        },
        afterBlur: function () { this.sync(); }
    };
    $.parser.plugins.push("kindeditor");
})(jQuery, KindEditor);
