var validate = {
        check: function (e) {
            var n = e.target.name;
            var v = e.target.value;
            switch(n){
                case 'name':
                    validate.valiName(v);
                    break;
                case 'phone':
                    validate.valiPhone(v);
                    break;
                case 'password':
                    validate.valiPassword(v);
                    break;
                case 'passwordQR':
                    validate.valiPaswordQR(v);
                    break;
            }
        },
        valiPhone: function (s) {
             var reg = /^1[34578]\d{9}$/;
             var r = s.match(reg);
            if(s==""){
                $("input[name='phone']").parents('.form-group').popover('show');
            }else if(r!=s){
                $("input[name='phone']").popover('show');
             } else {
                 $("input[name='phone']").popover('hide');
                 $("input[name='phone']").parents('.form-group').popover('hide');
             }
        },
        valiName: function (s) {
            var reg = new RegExp("[\u4E00-\u9FA5]{2,6}(?:·[\u4E00-\u9FA5]{2,5})*");
            var r = s.match(reg);
            if(s==""){
                $("input[name='name']").parents('.form-group').popover('show');
            }else if(r!=s){
                $("input[name='name']").popover('show');
            } else {
                $("input[name='name']").popover('hide');
                $("input[name='name']").parents('.form-group').popover('hide');
            }
        },
        valiPassword: function (s) {
            var strongRegex = new RegExp("^(?=.{6,16})([0-9A-Za-z]*[^0-9A-Za-z][0-9A-Za-z]*){2,}$", "g");
            var mediumRegex = new RegExp("^(?=.{6,16})[0-9A-Za-z]*[^0-9A-Za-z][0-9A-Za-z]*$", "g");
            var enoughRegex = new RegExp("^[0-9A-Za-z]{6,16}$", "g");
            if(s==''){
                $("input[name='password']").parents('.form-group').popover('show');
            } else {
                $("input[name='password']").parents('.form-group').popover('hide');
            }
            if(strongRegex.test(s)){
                $('.box-strength span').removeClass('active');
                $('.box-strength span').eq(2).addClass('active');
            }else if(mediumRegex.test(s)){
                $('.box-strength span').removeClass('active');
                $('.box-strength span').eq(1).addClass('active');
            }else if(enoughRegex.test(s)){
                $('.box-strength span').removeClass('active');
                $('.box-strength span').eq(0).addClass('active');
            } else if(s==''){
                $('.box-strength span').removeClass('active');
            }
        },
        valiPaswordQR: function (s) {
            if($("input[name='passwordQR']").val()==$("input[name='password']").val()){
                $("input[name='passwordQR']").parents('.form-group').popover('hide');
            }else {
                $("input[name='passwordQR']").parents('.form-group').popover('show');
            }
        },
        complete: function(e){
            var f = $(e.target).parents('form');
            var ilist = f.find('input');
            for(var i=0;i<ilist.length;i++){
                if($(ilist[i]).val()==""&&!($(ilist[i]).hasClass("optional"))){
                    $(ilist[i]).parents('.form-group').popover('show');
                    //alert($(ilist[i]).attr('alt')+"不能为空");
                }
            }
        }
    }