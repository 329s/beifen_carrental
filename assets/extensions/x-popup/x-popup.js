//兼容ie6的fixed代码 
//jQuery(function($j){
//	$j('#pop').positionFixed()
//})
(function($j){
    $j.positionFixed = function(el){
        $j(el).each(function(){
            new fixed(this);
        });
        return el;                  
    };
    $j.fn.positionFixed = function(){
        return $j.positionFixed(this);
    };
    var fixed = $j.positionFixed.impl = function(el){
        var o=this;
        o.sts={
            target : $j(el).css('position','fixed'),
            container : $j(window)
        };
        o.sts.currentCss = {
            top : o.sts.target.css('top'),              
            right : o.sts.target.css('right'),              
            bottom : o.sts.target.css('bottom'),                
            left : o.sts.target.css('left')             
        };
        if(!o.ie6)return;
        o.bindEvent();
    };
    $j.extend(fixed.prototype,{
        ie6 : $.browser && $.browser.msie && $.browser.version < 7.0,
        bindEvent : function(){
            var o=this;
            o.sts.target.css('position','absolute');
            o.overRelative().initBasePos();
            o.sts.target.css(o.sts.basePos);
            o.sts.container.scroll(o.scrollEvent()).resize(o.resizeEvent());
            o.setPos();
        },
        overRelative : function(){
            var o=this;
            var relative = o.sts.target.parents().filter(function(){
                if($j(this).css('position')=='relative')return this;
            });
            if(relative.size()>0)relative.after(o.sts.target);
            return o;
        },
        initBasePos : function(){
            var o=this;
            o.sts.basePos = {
                top: o.sts.target.offset().top - (o.sts.currentCss.top=='auto'?o.sts.container.scrollTop():0),
                left: o.sts.target.offset().left - (o.sts.currentCss.left=='auto'?o.sts.container.scrollLeft():0)
            };
            return o;
        },
        setPos : function(){
            var o=this;
            o.sts.target.css({
                top: o.sts.container.scrollTop() + o.sts.basePos.top,
                left: o.sts.container.scrollLeft() + o.sts.basePos.left
            });
        },
        scrollEvent : function(){
            var o=this;
            return function(){
                o.setPos();
            };
        },
        resizeEvent : function(){
            var o=this;
            return function(){
                setTimeout(function(){
                    o.sts.target.css(o.sts.currentCss); 
                    o.initBasePos();
                    o.setPos();
                },1);
            };
        }
    });
})(jQuery);

function XPopup(title,url,intro){
    this.title=title;
    this.url=url;
    this.intro=intro;
    this.apearTime=1000;
    this.hideTime=500;
    this.delay=10000;
    //添加信息
    this.init();
    //显示
    this.open();
    //关闭
    this.closeDiv();
}

XPopup.prototype={
  init:function(){
    if ($('x-popup').length) {
        $("#x-popup-title a").attr('href',this.url).html(this.title);
        $("#x-popup-intro").html(this.intro);
        $("#x-popup-more a").attr('href',this.url);
    }
    else {
        var htmls = new Array();
        htmls.push('<div id="x-popup" style="display:none;">');
        htmls.push('<div id="x-popup-head"> <a id="x-popup-close" title="关闭">关闭</a>');
        htmls.push('<h2>提示</h2>');
        htmls.push('</div>');
        htmls.push('<div id="x-popup-content">');
        htmls.push('<dl>');
        htmls.push('<dt id="x-popup-title"><a href="javascript:void(0)">'+this.title+'</a></dt>');
        htmls.push('<dd id="x-popup-intro">'+this.intro+'</dd>');
        htmls.push('</dl>');
        htmls.push('<p id="x-popup-more"><a href="javascript:void(0)">查看 »</a></p>');
        htmls.push('</div>');
        htmls.push('</div>');
        $('body').append(htmls.join("\n"));
    }
  },
  open:function(time){
    if (!($.browser && $.browser.msie && ($.browser.version == "6.0") && !$.support.style)) {
      $('#x-popup').slideDown(this.apearTime).delay(this.delay).fadeOut(400);
    } else{//调用jquery.fixed.js,解决ie6不能用fixed
      $('#x-popup').show();
        jQuery(function($j){
            $j('#x-popup').positionFixed();
        });
    }
  },
  closeDiv:function(){
      $("#x-popup-close").click(function(){
        $('#x-popup').hide();
      }
    );
  }
};
