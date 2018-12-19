
var PaymentInputValidator = function() {
    var handleSubmit = function(formId, options) {
        var isLoading = false;
        var ajaxOptions = {
            type:'post',
            beforeSend:easyuiFuncAjaxLoading,
            success: function (data) {
                isLoading = false;
                easyuiFuncAjaxEndLoading();
                easyuiFuncOnProcessSuccessEvents(data);
                if (data == '') return;
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
                    if (obj.statusCode == 200) {
                        var objModal = $(formId).parent().parent().parent();
                        $(objModal).modal('hide');
                        $('.modal-backdrop').remove();
                        if (obj.attributes) {
                            if (obj.attributes.relet_id) {
                                // refresh relet list
                                easyuiFuncNavTabRefreshCurTab();
                            }
                            else {
                                // refresh order editer view.
                                if ('refreshorder' == obj.callbackType) {
                                    easyuiFuncNavTabReloadCurTab(obj.forwardUrl, obj.forwardTitle);
                                }
                                else {
                                    easyuiFuncNavTabRefreshCurTab(true);
                                }
                            }
                        }
                    }
                    else {
                        //easyuiFuncOnProcessSuccessEvents(data);
                    }
                }
            },
            error: function (e) {
                isLoading = false;
                easyuiFuncAjaxEndLoading();
                easyuiFuncOnProcessErrorEvents(e);
            }
        };
        
        var validateOptions = {
            errorElement : 'span',
            errorClass : 'help-block',
            focusInvalid : false,
            /*
             examples 
            rules : {
                name : {
                    required : true  
                },  
                password : {  
                    required : true  
                },  
                intro : {  
                    required : true  
                }  
            },  
            messages : {  
                name : {  
                    required : "Username is required."  
                },  
                password : {  
                    required : "Password is required."  
                },  
                intro : {  
                    required : "Intro is required."  
                }  
            },
            submitSuccess : function(data) {
            },
            submitFail : function(e) {
            },
            */
            highlight : function(element) {  
                $(element).closest('.form-group').addClass('has-error');  
            },  
            success : function(label) {  
                label.closest('.form-group').removeClass('has-error');  
                label.remove();  
            },  
            errorPlacement : function(error, element) {  
                element.parent('div').append(error);  
            },  
            submitHandler : function(form) {
                if (isLoading) {
                    return;
                }
                isLoading = true;
                $(form).ajaxSubmit(ajaxOptions);
            }
        };
        if (typeof(options) == 'object') {
            for (var k in options) {
                validateOptions[k] = options[k];
            }
        }
        $(formId).validate(validateOptions);
        // $(formId + ' input').keypress(function(e) {  
        //     if (e.which == 13) {
        //         easyuiFuncDebugThisValue();
        //         if ($(formId).validate().form()) {
        //             $(formId).ajaxSubmit(ajaxOptions);
        //         }  
        //         return false;  
        //     }  
        // });  
    }  
    return {
        init: function(formId, options) {  
            if (formId.indexOf('#') !== 0) {
                formId = '#' + formId;
            }
            handleSubmit(formId, options);  
        }
    };  
}();

var PaymentInputSummary = function() {
    return {
        summary: function(formId, scope) {
            if (formId.indexOf('#') !== 0) {
                formId = '#' + formId;
            }
            var objForm = $(formId);
            if (objForm.length == 0) {
                return;
            }
            var arrData = $.custom.utils.getFormData(formId);
            var hasScope = false;
            var fieldPrefix = '';
            var fieldKeyPos = 0;
            if (typeof(scope) == 'string' && scope != '') {
                hasScope = true;
                fieldPrefix = scope + '[';
                fieldKeyPos = fieldPrefix.length;
            }
            
            var sumAmount = 0;
            var sumDeposit = 0;
            
            for (var k in arrData) {
                // if (k.substr(fieldKeyPos, 6) == 'price_') {
                if (k.substr(24, 6) == 'price_') {
                    var v = parseFloat(arrData[k]);
                    if (isNaN(v)) { v = 0; }
                    
                    // if (k.substr(fieldKeyPos+6, 7) == 'deposit') {
                    if (k.substr(24+6, 7) == 'deposit') {
                        sumDeposit += v;
                    }
                    else {
                        sumAmount += v;
                    }
                }
            }
            
            var setFields = {
                summary_amount : sumAmount,
                summary_deposit : sumDeposit
            };
            
            for (var k in setFields) {
                var key = hasScope ? scope+'['+k+']' : k;
                var obj = $("input[name='"+key+"']",objForm);
                if (obj.length > 0) {
                    obj.val(setFields[k]);
                }
                // sjj
                var key2 = 'VehicleOrderPriceDetail'+'['+k+']';
                var obj2 = $("input[name='"+key2+"']",objForm);
                if (obj2.length > 0) {
                    obj2.val(setFields[k]);
                }
                //sjj
            }
        }
    };
}();
