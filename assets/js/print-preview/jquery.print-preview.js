/*!
 * jQuery Print Previw Plugin v1.0.1
 *
 * Copyright 2016, Yang Jining
 * Licensed under the GPL Version 2 license
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Date: Wed Jan 25 00:00:00 2012 -000
 */
 
(function($) { 
    
    // Initialization
    $.fn.printPreview = function(options) {
        // EXTEND options for this button
        var pluginOptions = {
            attr : "href",
            url : false,
            selector : false,
            pageSize : 'A4',
            pageDirection : 'portrait', // portrait, landscape
            message: "Please wait while we create your document" ,
            margin: '2cm',
        };
        $.extend(pluginOptions, options);
        
        this.each(function() {
            $(this).bind('click', function(e) {
                e.preventDefault();
                if (!$('#w-print-preview-modal').length) {
                    $.printPreview.loadPrintPreview(this, pluginOptions);
                }
            });
        });
        return this;
    };
    
    // Private functions
    var mask, size, print_modal, print_controls;
    var body_origin_css;
    var print_frame_ref, print_frame;
    $.printPreview = {
        lan: {
            defaults: {
                printPage: 'Print page',
                closePrintPreview: 'Close print preview',
            }
        },
        
        loadPrintPreview: function(el, pluginOptions) {
            var url = (pluginOptions.url) ? pluginOptions.url : $(el).attr(pluginOptions.attr);
            
            // Declare DOM objects
            print_modal = $('<div id="w-print-preview-modal" class="w-print-preview-modal"></div>');
            print_controls = $('<div class="w-print-preview-modal-controls">' + 
                '<a href="#" class="print" title="'+$.printPreview.lan.defaults.printPage+'">Print page</a>' +
                '<a href="#" class="close" title="'+$.printPreview.lan.defaults.closePrintPreview+'">Close</a>').hide();
            /*var*/ print_frame = $('<iframe class="w-print-preview-modal-content" scrolling="no" border="0" frameborder="0" name="w-print-preview-frame" />');
            
            if (pluginOptions.pageSize == 'A4') {
                print_frame.width('794px');
                print_frame.height('921px');
            }
            
            // Raise print preview window from the dead, zooooooombies
            print_modal
                .hide()
                .append(print_controls)
                .append(print_frame)
                .appendTo('body');

            // The frame lives
            //var print_frame_ref;
            for (var i=0; i < window.frames.length; i++) {
                if (window.frames[i].name == "w-print-preview-frame") {    
                    print_frame_ref = window.frames[i].document;
                    break;
                }
            }
            
            if (url !== undefined && url) {
                print_frame.attr("src", url);
                print_frame.load(function() {
                    print_frame.height($('body', print_frame.contents())[0].scrollHeight);
                });
                //print_frame.attr("onload", "function(){ $.printPreview.onIframeLoaded(print_frame_ref, print_frame); }");
            }
            else {
                print_frame_ref.open();
                print_frame_ref.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' +
                    '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">' + 
                    '<head><title>' + document.title + '</title></head>' +
                    '<body></body>' +
                    '</html>');
                print_frame_ref.close();
                
                // Grab contents and apply stylesheet
                var $iframe_head = $('head link[media*=print], head link[media=all]').clone();
                var $iframe_body;
                if (pluginOptions.selector) {
                    $iframe_body = $(pluginOptions.selector).clone();
                }
                else {
                    $iframe_body = $('body > *:not(#w-print-preview-modal):not(script)').clone();
                }
                
                $iframe_head.each(function() {
                    $(this).attr('media', 'all');
                });
                if (!$.browser.msie && !($.browser.version < 7) ) {
                    $('head', print_frame_ref).append($iframe_head);
                    $('body', print_frame_ref).append($iframe_body);
                }
                else {
                    $iframe_body.each(function() {
                        $('body', print_frame_ref).append(this.outerHTML);
                    });
                    $('head link[media*=print], head link[media=all]').each(function() {
                        $('head', print_frame_ref).append($(this).clone().attr('media', 'all')[0].outerHTML);
                    });
                }
                
            }
            
            // Disable all links
            $('a', print_frame_ref).bind('click.printPreview', function(e) {
                e.preventDefault();
            });
            
            // Introduce print styles
            $('head').append('<style type="text/css">' +
                '@media print {' +
                    '/* -- Print Preview --*/' +
                    '#w-print-preview-modal-mask,' +
                    '#w-print-preview-modal {' +
                        'display: none !important;' +
                    '}' +
                '}' +
                '@page {' +
                '    size: ' + pluginOptions.pageSize + ' ' + pluginOptions.pageDirection + ';' +
               // '    margin: ' + pluginOptions.margin + ';' +
                '}' +
                '</style>'
            );

            // Load mask
            $.printPreview.loadMask();
            
            // Disable scrolling
            body_origin_css = {
                overflowY: $('body').css('overflow-y'),
                height: $('body').css('height')
            };
            if (body_origin_css.overflowY != 'hidden') {
                $('body').css({overflowY: 'hidden', height: '100%'});
            }
            $('img', print_frame_ref).load(function() {
                print_frame.height($('body', print_frame.contents())[0].scrollHeight);
            });
            
            // Position modal            
            var starting_position = $(window).height() + $(window).scrollTop();
            var css = {
                top:         starting_position,
                height:      '100%',
                overflowY:   'auto',
                zIndex:      10000,
                display:     'block'
            };
            print_modal
                .css(css)
                .animate({ top: $(window).scrollTop()}, 400, 'linear', function() {
                    print_controls.fadeIn('slow').focus();
                });
            
            // height
            print_frame.height($('body', print_frame.contents())[0].scrollHeight);
            
            // Bind closure
            $('a', print_controls).bind('click', function(e) {
                e.preventDefault();
                if ($(this).hasClass('print')) {
                    frames["w-print-preview-frame"].focus();
                    frames["w-print-preview-frame"].print();
                }
                else { $.printPreview.destroyPrintPreview(); }
            });
    	},
    	
        onIframeLoaded: function(print_frame_ref, print_frame) {
            
            $('img', print_frame_ref).load(function() {
                print_frame.height($('body', print_frame.contents())[0].scrollHeight);
            });
            var contentHeight = $('body', print_frame.contents())[0].scrollHeight;
            print_frame.height(contentHeight);
        },
        
    	destroyPrintPreview: function() {
    	    print_controls.fadeOut(100);
    	    print_modal.animate({ top: $(window).scrollTop() - $(window).height(), opacity: 1}, 400, 'linear', function(){
    	        print_modal.remove();
                if (body_origin_css.overflowY != 'hidden') {
                    $('body').css($.extend(body_origin_css, {height: 'auto'}));
                }
    	    });
    	    mask.fadeOut('slow', function()  {
    		mask.remove();
            });				

            $(document).unbind("keydown.printPreview.mask");
            mask.unbind("click.printPreview.mask");
            $(window).unbind("resize.printPreview.mask");
	},
	    
    	/* -- Mask Functions --*/
	loadMask: function() {
	    size = $.printPreview.sizeUpMask();
            mask = $('<div id="w-print-preview-modal-mask" />').appendTo($('body'));
    	    mask.css({
                position:           'absolute', 
                top:                0, 
                left:               0,
                width:              size[0],
                height:             size[1],
                display:            'none',
                opacity:            0,					 		
                zIndex:             9999,
                backgroundColor:    '#000'
            });

            mask.css({display: 'block'}).fadeTo('400', 0.75);
    		
            $(window).bind("resize.printPreview.mask", function() {
                $.printPreview.updateMaskSize();
            });
			
            mask.bind("click.printPreview.mask", function(e)  {
                $.printPreview.destroyPrintPreview();
            });

            $(document).bind("keydown.printPreview.mask", function(e) {
                if (e.keyCode == 27) {  $.printPreview.destroyPrintPreview(); }
            });
        },
    
        sizeUpMask: function() {
            if ($.browser && $.browser.msie) {
            	// if there are no scrollbars then use window.height
            	var d = $(document).height(), w = $(window).height();
            	return [
                    window.innerWidth || 						// ie7+
                    document.documentElement.clientWidth || 	// ie6  
                    document.body.clientWidth, 					// ie6 quirks mode
                    d - w < 20 ? w : d
            	];
            } else { return [$(document).width(), $(document).height()]; }
        },
    
        updateMaskSize: function() {
            var size = $.printPreview.sizeUpMask();
            mask.css({width: size[0], height: size[1]});
        }
    }
})(jQuery);