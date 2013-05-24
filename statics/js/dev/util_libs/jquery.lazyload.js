/*
 * PHPWind util Library
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 懒加载组件，适应于图片和textarea
 * @Author	: chaoren1641@gmail.com
 * @Depend	: jquery.js(1.7 or later)
 * $Id: jquery.lazyload.js 3369 2011-12-20 13:19:30Z chris.chencq $		:
 */
;(function ( $, window, document, undefined ) {
    var pluginName = 'lazyload';
    var defaults = {
    		containner 	: window,
    		effect		: 'fadeIn',//显示效果，可以使用jquery自带的效果，也可使用自定义jQuery effect
            img_data	: 'data-src',
            area_cls	: 'wind-lazyLoad',
            delay 		: 100//resize时和socrll时延迟处理,以免频繁触发,100毫秒基本无视觉问题
    };

    function Plugin( element, options ) {
        this.element = element;
        this.options = $.extend( {}, defaults, options) ;
        this.init();
    }
    
    Plugin.prototype.init = function () {
    	var element = this.element,options = this.options,
    		lazyImgs = element,
    		lazyArea = element.find('texteara.' + options.area_cls),
			container = $(options.container);
			if(!container.length) {
				container = window;
			}
    	//resize时和socrll时延迟处理,以免频繁触发,100毫秒基本无视觉问题
		var load = function() {
			
			setTimeout(function() {
				//图片出现的临界点
				var threshold = container.offset().top + container.scrollTop() + container.height();
				
				//加载图片
				lazyImgs.each(function() {
					var src = $(this).attr( options.img_data );
					if( src ) {
						var top = $(this).offset().top;
						if(top <= threshold) {
							//如果超过显示临界点，那么向图片并显示
							$(this).prop('src',src)[options.effect]();
							$(this).removeAttr( options.img_data );
						}
					}
				});
				
				//如果有textarea就加载textarea
				lazyArea.each(function() {
					if(! $(this).attr('data-loaded') ) {//如果没有加载过
						$('<div>' + this.value + '</div>').insertAfter( $(this) );
						$(this).attr('data-loaded',true);
					}
				});
				
			},options.delay);
		};
		
    	//绑定滚动事件
		container.on('scroll resize',function(e) {
			load();
		});
		//首次运行加载一次；
		load();		
    };

    $.fn[pluginName] = Wind[pluginName]= function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName, new Plugin( $(this), options ));
            }
        });
    };

})( jQuery, window ,document );

