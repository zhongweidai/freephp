 /**
 * PHPWind util Library 
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 阻止弹出层的父层滚动
 * @Author	: linhao87@gmail.com
 * @Depend	: core.js、jquery.js(1.7 or later)
 * $Id$
 */
;(function ( $, window, document, undefined ) {
    var pluginName = 'scrollFixed';
	var defaults = {
		win : false			//是否弹出iframe，默认否
	};
	
	//滚轮事件
	var a = ["DOMMouseScroll", "mousewheel"];
	$.event.special.mousewheel = {
		setup : function () {
			if (this.addEventListener) {
				for (var d = a.length; d; ) {
					this.addEventListener(a[--d], b, false);
				}
			} else {
				this.onmousewheel = b;
			}
		},
		teardown : function () {
			if (this.removeEventListener) {
				for (var d = a.length; d; ) {
					this.removeEventListener(a[--d], b, false);
				}
			} else {
				this.onmousewheel = null;
			}
		}
	};
	/*c.fn.extend({
		mousewheel : function (d) {
			//console.log(d);
			//return d ? this.bind("mousewheel", d) : this.trigger("mousewheel")
		},
		unmousewheel : function (d) {
			return this.unbind("mousewheel", d)
		}
	});*/
	function b(e) {
		var d = [].slice.call(arguments, 1),
		g = 0,
		//e = true;
		e = $.event.fix(e || window.event);
		e.type = "mousewheel";

		//滚轮方向
		if (e.originalEvent.wheelDelta) {//IE/Opera/Chrome
			g = e.originalEvent.wheelDelta / 120;
		}
		if (e.originalEvent.detail) {//firefox
			g = -e.originalEvent.detail / 3;
		}

		d.unshift(e, g);
		return $.event.handle.apply(this, d);
	}

	function Plugin(element, options) {
		this.element = element;
		this.options = $.extend({}, defaults, options);
        this.init();
    }
	
    Plugin.prototype.init = function () {
		var _this = this,
			element = this.element,
			options = this.options;

		element.on('mousewheel', function(event, d){
			var height_all = (options.win ? $('body').height() : element[0].scrollHeight );		//iframe用body高度，否则用元素高度
				
			if(d === -1 && element.height() + element.scrollTop() < height_all) {
				return true;
			}else if(d === 1 && element.scrollTop() !== 0) {
				return true;
			}else{
				return false;
			}

		});
    };

	
    $.fn[pluginName] = Wind[pluginName]= function (options ) {
        return this.each(function () {
			new Plugin( $(this), options );
        });
    };

})(jQuery, window ,document);
