/*!
 * PHPWind UI Library 
 * @Copyright 	: Copyright 2011, phpwind.com
 * @Descript	: dialog 对话框组件
 * @Author		: chaoren1641@gmail.com
 * @Depend		: core.js、jquery.js(1.7 or later)
 * $Id: dialog.js 8433 2012-04-18 12:23:53Z chris.chencq $			:
 */
;(function ( $, window, undefined ) {
    var pluginName = 'dialog';
    var	empty = $.noop;
    var is_ie6 = ($.browser.msie && $.browser.version === 6) ? 1 : 0;
    var defaults = {
            id              : '',                           //id
            type            : 'alert',						// 默认弹出类型
			cls				: 'wind_dialog core_pop_wrap',	//弹出容器默认class
			position		: 'absolute',
            message			: '',							// 弹出提示的文字
            autoHide		: 0,							// 是否自动关闭
            zIndex			: 10, 							// 层叠值
            width			: 0,							// 弹出内容的宽度
            height			: 0,							// 高度
            isDrag			: false,							// 是否允许拖拽
			callback		: undefined,					//回调
            onShow			: undefined,					// 显示时执行
            onOk			: undefined,
            onCancel		: undefined, 					// 点击取消时执行
            onClose			: undefined,					// 如果是iframe或者html,则有一个关闭的回调
            left			: undefined,					// 默认在中间
            top				: undefined,
            follow			: undefined,
            title			: '',							// 提示标题
            okText			: '确定',						// 确定按钮文字
            cancelText		: '取消',						// 取消文字，确认时用
            closeText		: '关闭',						// 关闭文字
            isMask			: 1,							// 是否显示背景遮罩
            opacity			: 0.6,							// 遮罩的透明度
            backgroundColor	: '#fff',						// 遮罩的背景色
            url				: ''							// 弹出来的iframe url
    };
    var template = '\
				<div class="core_pop" style="min-width:200px">\
					<% if(type === "iframe" || type === "html") {%>\
						<div class="pop_top J_drag_handle" style="display:none;overflow:hidden">\
							<a role="button" href="#" class="pop_close J_close" title="关闭弹出窗口">关闭</a>\
							<strong><%=title%></strong>\
						</div>\
					<% } %>\
					<% if(type === "iframe") { %>\
							<div class="pop_loading J_loading"></div>\
							<div class="J_dialog_iframe">\
                        		<iframe src="<%=url%>" frameborder="0" style="border:0;height:100%;width:100%;padding:0;margin:0;display:none;" scrolling="no"/>\
                        	</div>\
                    <% } else if(type === "confirm" || type === "alert"){ %>\
						<div class="pop_cont">\
	                    	<%=message%>\
						</div>\
						<div class="pop_bottom">\
							<% if(type === "confirm" || type === "alert") { %>\
								<button type="button" class="btn btn_submit mr10 J_btn_ok"><%=okText%></button>\
							<% } %>\
							<% if(type === "confirm") { %>\
								<button type="button" class="btn J_btn_cancel"><%=cancelText%></button>\
							<% } %>\
						</div>\
					<% } else if(type === "html") { %>\
						<%=message%>\
					<% } %>\
			</div>';

    function Plugin( options ) {
        //this.element = element;
        this.options = $.extend( {}, defaults, options) ;
        this.init();
    }

    Plugin.prototype.init = function () {
    	var options = this.options;
        var html = Wind.tmpl(template,options);//替换模板
        var elem = $('#' + options.id);
        var _this = this;

        if(elem.length) {
            //有设置id，只弹出一个
            _this.elem = elem;
            elem.html(html).show();
        }else {
            elem = _this.elem = $( '<div tabindex="0" id="'+ options.id +'" class="'+ options.cls +'" aria-labelledby="alert_title" role="alertdialog"/>' ).appendTo( 'body' ).html(html);
        }

        if(options.isMask) {//遮罩
    		var style = {
				width			: '100%',
				height			: $(window.document).height() + 'px',
				opacity			: options.opacity,
				backgroundColor	: options.backgroundColor,
				zIndex			: options.zIndex-1,
				position		: 'absolute',
				left			: '0px',
				top				: '0px'
			};
    		_this.mask = $('<div class="wind_dialog_mask"/>').css(style).appendTo('body');
    	}
    	
        //options.autoHide
        if(options.autoHide) {
        	setTimeout(function() {
        		_this.close();
        	},autoHide);
        }
        
        //init event
        if(options.onShow) {
            options.onShow();
        }
        
        //点击确定
        elem.find('.J_btn_ok').on('click',function(e) {
        	e.preventDefault();
			if(options.onOk) {
		        options.onOk();
		   }
		   _this.close();
        });
        
        //confirm取消按钮点击
        elem.find('.J_btn_cancel').on('click',function(e) {
        	e.preventDefault();
        	if(options.onCancel) {
                options.onCancel();
           	}
           _this.close();
        });
        
        if(options.type === 'iframe' || options.isDrag) {
        	var title = elem.find('.pop_top');
        	Wind.use('jquery.draggable',function() {
        		elem.draggable( { handle : '.J_drag_handle'} );
        	});
        }
        
        //关闭按钮
        elem.find('.J_close').on('click',function(e) {
        	e.preventDefault();
        	if(options.onClose) {
                options.onClose();
           	}
           _this.close();
        });
        
        //如果是iframe，则监听onload，让展示框撑开
        if(options.type === 'iframe' && options.url) {
        	var iframe = elem.find('iframe')[0];
        	try {
        		$(iframe).load( function() {
        			/*var body;
					if ( iframe.contentDocument ) { // FF
						body = iframe.contentDocument.getElementsByTagName('body')[0];
					} else if ( iframe.contentWindow ) { // IE
						body = iframe.contentWindow.document.getElementsByTagName('body')[0];
					}*/
					
					//firefox下，iframe隐藏的情况下取不到文档的高度
					$(iframe).show();
					elem.find('.J_loading').hide();
					elem.find('.pop_top').show();
                    try{
    					var body = iframe.contentWindow.document.body;
        				var width = $(body).width(),
        					height = $(body).height();
        					
        				//小于200证明没有取到其宽度，宽度需要在页面的body中定义
        				if(width < 200) {
        					width = 700;
        				}
        				if( height > 600 ) {
    	        			height = 600;
    	        			iframe.scrolling = 'yes';
    	        		}
                        elem.find('.J_dialog_iframe').css( {width : Math.max(width,400) + 'px', height : Math.max(height,200) + 'px' });
                        
                    }catch(e) {
                        $(iframe).css( {width : '800px', height : '600px' });
                        elem.find('.J_loading').hide();
                        elem.find('.pop_top').show();
                        iframe.scrolling = 'yes';
                        $(iframe).show();
                    }
        			show();
	        	});
        	} catch(e) {
                throw e;
        	}
        	
        }

        if(options.type === 'html' && options.title) {
            elem.find('.pop_top').show();
        }
        //ie6则调用bgiframe组件
        if (is_ie6) { 
    		Wind.use('jquery.iframe',function() {
    			elem.bgiframe();
    		}); 
    	}
		
		
    	
        function show() {
        	var follow_elem = options.follow,
	        	top,
	        	left,
	        	position = ($.browser.msie && $.browser.version < 7 ? 'absolute' : options.position),	//ie6 绝对定位
	        	zIndex = options.zIndex;

        	if(options.follow) {
        		var follow_elem = typeof options.follow === 'string' ? $(options.follow) : options.follow,
        			follow_elem_offset = follow_elem.offset(),
	        		follow_elem_width = follow_elem.width(), 
	        		follow_elem_height = follow_elem.height() , 
	        		win_width = $(window).width(), 
					body_height = $('body').height(),
	        		win_height = $(window).height(),
        			pop_width = elem.outerWidth(),
        			pop_height = elem.height();
					
        		//如果是跟随某元素显示，那么计算元素的位置，并不能超过显示窗口的区域
        		if((follow_elem_offset.top + follow_elem_height + pop_height) > body_height) {
        			top = follow_elem_offset.top - pop_height;	//高度超出
        		} else {
        			top = follow_elem_offset.top + follow_elem_height;
        		}

        		if((follow_elem_offset.left + follow_elem_width + pop_width) > win_width) {
					left = win_width - pop_width - 1;	//ie需要多减去1px
        		} else {
        			left = follow_elem_offset.left + follow_elem_width;
        		}
        	} else {
		　　　　　　	top = options.top ? options.top : ( $(window).height() - elem.height() ) / 2 + (position=='absolute' ? $(window).scrollTop() : 0);
		　　　　　　	left = options.left ? options.left : ( $(window).width() - elem.width() ) / 2 + $(window).scrollLeft() ;
	    	}
	    	//设置最终位置
			
	    	elem.css( {position:position, zIndex:zIndex, left:left + 'px', top:top + 'px'} );
			
			
    	}
    	
    	show();
		
    	//载入后回调
		if(options.callback) {
			options.callback();
		}
		
    	//如果是确认框，则让确定按钮取得焦点
        if(options.type === 'confirm') {
        	elem.find('.J_btn_ok').focus();
        }else{
        	elem.focus();//显示以后让其取得焦点
        }
        
        $(window).resize(function() {
        	show();
        });
    };
    
	Plugin.prototype.close = function() {
		this.elem.remove();
		this.mask && this.mask.remove();
	};
	
    /*$.fn[pluginName] = Wind[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName, new Plugin( this, options ));
            }
        });
    }*/
   	var Wind = window.Wind || {};
	var dialog = Wind[pluginName] = function(options) {
		return new Plugin( options );
	};
	
	dialog['alert'] = Wind['alert'] = function(message,callback) {//兼容api
		return new Plugin( { message:message, type:'alert', onOk:callback } );
	};
	dialog['confirm'] = Wind['confirm'] = function(message,okCallback,cancelCallback) {
		if(arguments.length === 1 && $.isPlainObject(arguments[0])) {
			return new Plugin( arguments[0] );
		}
		return new Plugin( { message:message, type:'confirm', onOk:okCallback ,onCancel:cancelCallback} );
	};
	dialog['open'] = Wind['showUrl'] = function(url,options) {
        options = options || {};
		options['type'] = 'iframe';
		options['url'] = url;
		return new Plugin( options );
	};
	dialog['html'] = Wind.showHTML = function(html,options) {
        options = options || {};
		options['type'] = 'html';
		options['message'] = html;
		return new Plugin( options );
	};
	dialog['closeAll'] = function() {
		$('.wind_dialog').remove();
		$('.wind_dialog_mask').remove();
	};
})( jQuery, window);
