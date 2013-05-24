/*
 * PHPWind util Library 
 * @Copyright 	: Copyright 2011, phpwind.com
 * @Descript	: 拖拽功能组件
 * @Author		: chaoren1641@gmail.com
 * @Depend		: jquery.js(1.7 or later)
 * $Id: jquery.draggable.js 10120 2012-05-18 02:19:06Z hao.lin $			:
 */
;(function ( $, window, document, undefined ) {
    var pluginName = 'draggable';
    var defaults = {
		handle	: '.handle'	// 要拖拽的手柄
    };
	var lastMouseX,lastMouseY;
	
	//当前窗口内捕获鼠标操作
    function capture(elem) {
        elem.setCapture ? elem.setCapture() : window.captureEvents(Event.MOUSEMOVE | Event.MOUSEUP);
    }
    function release(elem) {
        elem.releaseCapture ? elem.releaseCapture() : window.releaseEvents(Event.MOUSEMOVE | Event.MOUSEUP);
    }
    function getMousePosition(e) {
		var posx = 0;
		var posy = 0;

		if (!e) { var e = window.event; }

		if (e.pageX || e.pageY) {
			posx = e.pageX;
			posy = e.pageY;
		}
		else if (e.clientX || e.clientY) {
			posx = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
			posy = e.clientY + document.body.scrollTop  + document.documentElement.scrollTop;
		}

		return { 'x': posx, 'y': posy };
	}
	
	function Plugin(element,options) {
        this.element = element;
        this.options = $.extend( {}, defaults, options) ;
        this.handle = element.find(options.handle);
        this.init();
	}
        
    Plugin.prototype.init = function() {
    	var handle = this.handle,
    		options = this.options,
    		element = this.element;
    	handle.css({cursor:'move'});
    	var el = handle[0].setCapture ? handle : $(document);
        handle.on('mousedown',function(e) {
        	 if(options.mousedown) {
		        	options.mousedown(element);
		        }
        	if($.browser.msie){
		        //设置鼠标捕获
		        handle[0].setCapture();
		    }else{
		        //焦点丢失
		        //$(window).blur();
		        //阻止默认动作
		        e.preventDefault();
		    };
        	capture(this);
        	e.preventDefault();
        	lastMouseX = e.pageX;
		    lastMouseY = e.pageY;
		    el.on('mousemove',function(e) {
		    	window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();
	        	var mousePostion = getMousePosition(e),
	            	mouseY = mousePostion.y, 
	            	mouseX = mousePostion.x,
		            top = parseInt(element.css('top')),
		            left = parseInt(element.css('left')),
		            currentLeft = left + mouseX - lastMouseX,
		            currentTop = top + (mouseY - lastMouseY);
		        element.css({ left : currentLeft + "px", top : currentTop + "px" });
		        lastMouseX = mouseX;
		        lastMouseY = mouseY;
		        if(options.mousemove) {
		        	options.mousemove(element, left, top);
		        }
       		}).on('mouseup',function (e) {
	            release(this);
	            $(el).unbind('mousemove').unbind('mouseup');
	            if(options.mouseup) {
		        	options.mouseup(element);
		        }
			});
        });
	}

    $.fn[pluginName] = Wind[pluginName]= function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName, new Plugin( $(this), options ));
            }
        });
    }

})( jQuery, window, document );
