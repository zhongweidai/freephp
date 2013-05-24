/**
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台全局功能js（www\template\common\foot.htm引用）
 * @Author	: linhao87@gmail.com
 * @Depend	: core.js、jquery.js(1.7 or later)
 * $Id: global.js 12947 2012-06-27 12:19:51Z hao.lin $
 */
;
(function () {

	//全局ajax处理
	$.ajaxSetup({
		complete: function(jqXHR) {
			//登录失效处理
		    /* if(jqXHR.responseText.state === 'logout') {
		    	location.href = login_url;
		    } */
  	},
  	data : {
  		__hash__ : GV.TOKEN
  	},
		error : function(jqXHR, textStatus, errorThrown){
			//请求失败处理
			if(errorThrown) {
				//移除ajax请求遮罩
				ajaxMaskRemove();

				//移除按钮提交中状态
				var btn = $('button.disabled:submit');
				for(i=0, len = btn.length; i<len; i++) {
					if($(btn[i]).data('sublock')) {
						ajaxBtnEnable($(btn[i]));
						break;
					}
				}

				resultTip({
					error : true,
					msg : errorThrown
				});
			}
		}
	});

	//不支持placeholder浏览器下对placeholder进行处理
	if(document.createElement('input').placeholder !== '') {
		$('head').append('<style>.placeholder{color: #aaa;}</style>');
		$('[placeholder]').focus(function() {
			var input = $(this);

			if(input.val() == input.attr('placeholder')) {
				input.val('');
				input.removeClass('placeholder');
			}
		}).blur(function() {
			var input = $(this);
			//密码框空
			if(this.type === 'password') {
				return false;
			}
			if(input.val() == '' || input.val() == input.attr('placeholder')) {
				input.addClass('placeholder');
				input.val(input.attr('placeholder'));
			}
		}).blur().parents('form').submit(function() {
			$(this).find('[placeholder]').each(function() {
				var input = $(this);
				if(input.val() == input.attr('placeholder')) {
					input.val('');
				}
			});
		});
	}

	//侧栏登录
	var username = $('#J_username');
	if (username.length) {

		Wind.use('jquery.draggable', 'jquery.form', function () {

			var password = $('#J_password'),
				login_tips = $('#J_login_tips');

			$("#J_login_form").ajaxForm({
				dataType : 'json',
				beforeSubmit : function (arr, $form, options) {},
				success : function (data, statusText, xhr, $form) {
					if (data.state === 'success') {
						if (data.message.check.url) {
							//验证问题

							$.get(data.message.check.url, function (data) {
								//引入所需组件并显示弹窗
								$('body').append(data);

								//获得焦点
								var question_wrap = $('#J_login_question_set_wrap, #J_login_question_wrap');

								//global.js
								popPos(question_wrap);
								question_wrap.find('input:text:visible').focus();
							}, 'html');

						} else {
							window.location.href = data.referer;
						}

					} else {

						$('#J_login_tips_content').text(data.message[0]);
						login_tips.fadeIn(200, function () {
							setTimeout(function () {
								login_tips.hide();
							}, 3000);
						});

					}
				}

			});

		});

	}

	//判断触发快捷登录
	if(!GV.U_ID) {
		$('a.J_qlogin_trigger, button.J_qlogin_trigger').on('click', function(e){
			e.preventDefault();
			var referer = $(this).data('referer');					//登录后跳转还是刷新

			//global.js
			gQuickLogin( referer ? this.href : null );
		});
	}

	//select控件关联日期组件
	var date_select = $('.J_date_select');
	if (date_select.length) {
		Wind.use('jquery.dateSelect', function () {
			date_select.dateSelect();
		});
	}

	//全选
	if ($('.J_check_wrap').length) {

		//遍历所有全选框
		$.each($('input.J_check_all'), function (i, o) {
			var $o = $(o),
				check_wrap = $o.parents('.J_check_wrap'), //当前操作区域所有复选框的父标签
				check_all = check_wrap.find('input.J_check_all'), //当前操作区域所有(全选)复选框
				check_items = check_wrap.find('input.J_check'); //当前操作区域所有(非全选)复选框

			//点击全选框
			$o.change(function (e) {

				if ($(this).attr('checked')) {
					//全选
					check_items.attr('checked', true);

					if (check_items.filter(':checked').length === check_items.length) {
						check_all.attr('checked', true); //所有全选打钩
					}

				} else {
					//取消全选
					check_items.removeAttr('checked');
					check_all.removeAttr('checked');
				}

			});

			//点击(非全选)复选框
			check_items.change(function () {

				if ($(this).attr('checked')) {

					if (check_items.filter(':checked').length === check_items.length) {
						check_all.attr('checked', true); //所有全选打钩
					}

				} else {
					check_all.removeAttr('checked'); //取消全选
				}

			});

		});

	}

	//侧栏勋章载入
	var medal_widget_ul = $('#J_medal_widget_ul');
	$('#J_medal_widget_more').on('click', function(e){
		e.preventDefault();
		var $this = $(this),
			role = $(this).data('role');
		if(role === 'down') {
			medal_widget_ul.removeClass('hidden');
			$this.data('role', 'up');
			$this.removeClass('more').addClass('unmore');
		}else{
			medal_widget_ul.addClass('hidden');
			$this.data('role', 'down');
			$this.removeClass('unmore').addClass('more');
		}
	});

	var head_msg_pop = $('#J_head_msg_pop');		//消息弹窗
	//header区域hover
	/*hoverToggle({
		wrap_name : '#J_header',										//容器
		elems : $('#J_header .J_header_toggle'),		//hover元素
		callback : function($a, $b){
			if(GV.U_ID) {
				head_msg_pop.hide();
				//headMsg.js
				showHome();
			}
		}
	});*/
	//hover顶部用户
	//global.js
	var header = $('#J_header').parent(),
			header_pos = header.css('position');
	hoverToggle({
		wrap : $('#J_header'),					//容器
		a : $('#J_head_user_a'),		//hover元素
		b : $('#J_head_user_menu'),
		callback : function($a,$b) {
			if(header_pos == 'static') {
				$b.css({
					position : 'absolute',
					top : $a.offset().top + $a.height() +15
				});
			}

			//导航定位
			if(GV.U_ID) {
				head_msg_pop.hide();
				//headMsg.js
				if(window.showHeadmsgHome) {
					showHeadmsgHome();
				}
			}
		}
	});

	//hover顶部我的导航
	hoverToggle({
		wrap : $('#J_header'),					//容器
		a : $('#J_head_nav_my_a'),		//hover元素
		b : $('#J_head_nav_my_list'),
		callback : function($a, $b){
			//导航定位
			var _hei;
			if(header_pos == 'static') {
				//发帖页
				_hei = 0;
			}else{
				//其他
				_hei = $('#J_header').offset().top;
			}

			$b.css({
				left : $a.offset().left - 20,
				//top : $a.offset().top - $(document).scrollTop() + _hei + 5,
				top : top = $a.offset().top + $a.outerHeight() - _hei + 5,
				margin : 0,
				position : 'absolute'
			});

			if(GV.U_ID) {
				head_msg_pop.hide();
				//headMsg.js
				if(window.showHeadmsgHome) {
					showHeadmsgHome();
				}
			}
		}
	});

	/*hoverToggle({
		wrap_name : '#J_header',					//容器
		elems : $('a.J_nav_toggle'),		//hover元素
		callback : function($a, $b){
			//导航定位
			$b.css({
				left : $a.offset().left - 20,
				top : $a.offset().top - $(document).scrollTop() + $a.outerHeight() + 5,
				margin : 0
			});

			if(GV.U_ID) {
				head_msg_pop.hide();
				//headMsg.js
				showHome();
			}

		}
	});*/

	//载入头部消息js
	var head_msg_btn = $('#J_head_msg_btn');		//消息按钮
	if(head_msg_btn.length) {

		//载入头部消息js
		//Wind.use('jquery.form', 'jquery.scrollFixed', function() {
			//Wind.js(GV.JS_ROOT+ 'pages/common/insertEmotions.js?v='+ GV.JS_VERSION, function(){

				var	header = $('#J_header'),
						lock = false,
						hmtimeout;

				//经过消息
				head_msg_btn.on('mouseenter', function(){
					if (hmtimeout) {
						//清理延时
						clearTimeout(hmtimeout);
					}

					hmtimeout = setTimeout(function(){
						//console.log();

						//定位 显示
						head_msg_pop.css({
							left : header.width() + header.offset().left - head_msg_pop.outerWidth(),
							top : head_msg_btn.offset().top+head_msg_btn.height()+10 - $(document).scrollTop()
						}).show().focus();

						//headMsg是否已加载
						if(!$.isFunction(window.showHeadmsgHome)) {
							Wind.js(GV.JS_ROOT+ 'pages/common/headMsg.js?v='+ GV.JS_VERSION);
						}

					}, 200);


				}).on('mouseleave', function(){
					if (hmtimeout) {
						clearTimeout(hmtimeout);
					}
				});

				head_msg_pop.on('mouseenter', function(){
					lock = true;
				}).on('mouseleave', function(){
					lock = false;
					head_msg_pop.focus();
				}).on('blur', function(){
					if(!lock) {
						head_msg_pop.hide();

						//显示列表 headMsg.js
						if(window.showHeadmsgHome) {
							showHeadmsgHome();
						}

					}
				});

			//});
		//});
	}

	//头部发帖
	if(GV.U_ID) {
		var head_forum_post = $('#J_head_forum_post');	//头部发帖按钮
		clickToggle({
			elem : head_forum_post,
			list : $('#J_head_forum_pop'),
			callback_show : function(){
				//wind.js不会重复加载
				Wind.js(GV.JS_ROOT +'pages/common/headPost.js?v='+ GV.JS_VERSION);
//console.log(head_forum_post.offset().top + head_forum_post.height() - $(document).scrollTop());
				var position,
						top,
						header_pos = $('#J_header').parent().css('position');

				if(header_pos == 'static') {
					//发帖页
					position = 'absolute';
				}else{
					if($.browser.msie && $.browser.version < 7) {
						position = 'absolute';
					}else{
						position = 'fixed';
					}
				}

				if(position == 'absolute') {
					top = head_forum_post.offset().top + head_forum_post.height();
				}else{
					top = head_forum_post.offset().top + head_forum_post.height() - $(document).scrollTop();
				}

				$('#J_head_forum_pop').css({
					position : position,
					top : head_forum_post.offset().top + head_forum_post.height() - $(document).scrollTop()
				})
			},
			callback_hide : function(){}
		});
	}

	//侧栏勋章滚动
	var medal_widget_ul = $('#J_medal_widget_ul');
	if(medal_widget_ul.length){
		var sidebar_medal_ta = $('#J_sidebar_medal_ta'),
				sidebar_medal_arr = sidebar_medal_ta.text().split(','),
				sidebar_medal_len = sidebar_medal_arr.length,
				splice = sidebar_medal_arr.splice(0, sidebar_medal_len-1),
				sidebar_medal_ul_len = medal_widget_ul.children().length;

		//总数大于列表可见数
		if(splice.length > sidebar_medal_ul_len) {
			Wind.use('jquery.lazySlide', function(){
				$('#J_medal_widget').lazySlide({
					step_length : sidebar_medal_ul_len,
					html_arr : splice
				});
			});
		}

	}


	//喜欢组件
	var like_btn = $('.J_like_btn');
	if (like_btn.length && GV.U_ID) {
		Wind.use('like', function () {
			like_btn.like();
		});
	}

	//发消息_弹窗
	var send_msg_btn = $('a.J_send_msg_pop');
	if(send_msg_btn.length && GV.U_ID) {
		Wind.use('jquery.form', 'jquery.draggable', function(){
			Wind.js(GV.JS_ROOT+ 'pages/common/sendMsgPop.js?v='+ GV.JS_VERSION);
		});
	}

	//日历组件
	if($("input.J_date").length) {
		Wind.use('datePicker',function() {
			$("input.J_date").datePicker();
		});
	}

	//tab组件
	var tab_wrap = $('.J_tab_wrap');
	if(tab_wrap.length) {
		tabsBind(tab_wrap);
	}

	//所有的删除操作，删除数据后刷新页面
	var ajax_del = $('a.J_ajax_del');
	if( ajax_del.length ) {
		Wind.use('dialog',function() {
			ajaxDel(ajax_del);
		});
	}

	//侧栏手风琴
	$('dt.J_sidebar_toggle').on('click', function(){
		var this_dl = $(this).parent();
		this_dl.toggleClass('current');
		this_dl.siblings('dl.current').removeClass('current');
	});

	//小名片
	var user_card_show = $('a.J_user_card_show');
	if(user_card_show.length) {
		Wind.js(GV.JS_ROOT+ 'pages/common/userCard.js');
	}

	//用户输入标签组件
	if ($('.J_user_tag_wrap').length) {
		Wind.js(GV.JS_ROOT+ 'pages/common/userTag.js');
	}

	//邮箱自动匹配
	var email_match = $('input.J_email_match');
	if(email_match.length) {
		email_match.attr('autocomplete', 'off');
		Wind.use('jquery.emailAutoMatch', function(){
			email_match.emailAutoMatch();
		});
	}

	//input只能输入数字
	$('input.J_input_number').on('keyup', function(){
		var v = $(this).val();
		$(this).val(v.replace(/\D/g,''));
	});

	//举报
	var report = $('a.J_report');
	if(report.length && GV.U_ID) {
		Wind.use('jquery.form', function(){
			Wind.js(GV.JS_ROOT+ 'pages/common/report.js?v='+ GV.JS_VERSION);
		});
	}

	//地区组件
	var region_set = $('.J_region_set');
	if(region_set.length) {
		Wind.js(GV.JS_ROOT +'pages/common/region.js?v='+ GV.JS_VERSION, function(){
			//regionInit();
		});
	}

	//学校组件
	var input_school = $('input.J_plugin_school');
	if(input_school.length) {
		Wind.js(GV.JS_ROOT +'pages/common/region.js?v='+ GV.JS_VERSION, GV.JS_ROOT +'pages/common/school.js?v='+ GV.JS_VERSION);
	}

	//打卡
	var punch_mine = $('#J_punch_mine');
	if(punch_mine.length) {
		Wind.js(GV.JS_ROOT+ 'pages/common/punch.js?v='+ GV.JS_VERSION);
	}

	//计划任务 全局执行请求
	if(GV.URL.CRON_AJAX) {
		$.post(GV.URL.CRON_AJAX);
	}

	//表情插入
	var insert_emotions = $('a.J_insert_emotions');
	if(insert_emotions.length) {
		Wind.js(GV.JS_ROOT+ 'pages/common/insertEmotions.js?v='+ GV.JS_VERSION, function(){
			insert_emotions.on('click', function(e){
				e.preventDefault();
				insertEmotions($(this), $($(this).data('emotiontarget')));
			});
		});
	}

	//图片上传预览
	if($("input.J_upload_preview").length) {
		Wind.use('jquery.uploadPreview',function() {
			$("input.J_upload_preview").uploadPreview();
		});
	}

	//幻灯片
	var gallery_list = $('ul.J_gallery_list');
	if(gallery_list.length) {
		Wind.use('jquery.gallerySlide', function(){
			gallery_list.gallerySlide();
		});
	}

	/*
	 * 默认头像
	*/
	var avas = $('img.J_avatar');
	if(avas.length) {
		avatarError(avas);
	}

	/*
	 * 广告管家iframe
	*/
	var ad_iframes_div = $('div.J_ad_iframes_div'),
			ad_iframes_len = ad_iframes_div.length;
	if(ad_iframes_len) {
		for(i=0; i<ad_iframes_len; i++) {
			var ad_item = $(ad_iframes_div[i]),
					ad_iframe = document.createElement('iframe');
			$(ad_iframe).attr({
				src : ad_item.data('src'),
				frameborder	: '0',
				scrolling	: 'no',
				height		: ad_item.data('height'),
				width		: ad_item.data('width')
			});

			ad_item.replaceWith(ad_iframe);
		}
	}

	//公告滚动
	/*var an_slide_auto = $('ul.J_slide_auto'),
			an_lock = false,											//滚动锁定
			an_timer;

	an_slide_auto.hover(function(){
		//鼠标进入，锁定
		an_lock = true;
	}, function(){
		//鼠标进入，解锁 执行
		an_lock = false;
		anMove();
	});
	anMove();

	function anMove(){
		clearTimeout(an_timer);
		if(an_lock || an_slide_auto.children().length <= 1) {
			//锁定时不执行
			return false;
		}
		var li = an_slide_auto.children(':eq(0)');

		an_timer = setTimeout(function(){
			if(!an_lock) {
				li.animate({height : 0}, function(){
					$(this).appendTo(an_slide_auto).removeAttr('style');
					anMove();
				});
			}
		}, 5000);
	}*/

})();


/*
 * 全局公共方法
*/

/*//hover显示隐藏内容
function hoverToggle(options) {
	var elems = options.elems,							//元素
		delay = (options.delay ? options.delay : 0);	//延时

	if(!elems.length) {
		return false;
	}

	//遍历所有hover元素
	$.each(elems, function (i, o) {

		var wrap = $(this).parents(options.wrap_name),		//父容器
			a = '#' + $(o).attr('id'),
			$a = $(o),										//hover元素
			$textarea = $('#' + $a.data('taid')),			//隐藏的textarea容器
			b = a.replace(/_A$/, '_B');						//待显示元素

		var timeout;

		wrap.on('mouseenter', a +', '+ b, function (e) {
			if (timeout) {
				//清理延时
				timeout = clearTimeout(timeout);
			}

			timeout = setTimeout(function () {

				if ($a.data('show')) {
					//html已添加则直接显示
					 $(b).show();

				} else {
					//添加textarea里的html代码
					wrap.prepend($textarea.text());
					$a.data('show', 'show');
				}

				//回调，传回两个元素
				if(options.callback) {
					options.callback($a, $(b));
				}

			}, delay);
		}).on('mouseleave', a +', '+ b, function (e) {
			//鼠标离开

			if (timeout) {
				timeout = clearTimeout(timeout);
			}

			timeout = setTimeout(function () {
				$(b).css('display', 'none');
			}, delay);
		});

	});
}*/

/*
 * hover显示隐藏内容
*/
function hoverToggle(options) {
	try{
		var $a = options.a,																	//触发元素
				$b = options.b,																	//隐藏列表
				delay = (options.delay ? options.delay : 200);	//延时

		var timeout;

		$a.on('mouseenter keydown', function (e) {
			//无障碍处理
			if(e.type === 'keydown' && e.keyCode !== 40) {
				//如果不是按的down键，return
				return;
			}else {
				e.preventDefault();
			}
			if (timeout) {
				//清理延时
				timeout = clearTimeout(timeout);
			}

			timeout = setTimeout(function () {
				$b.show();

				//回调，传回两个元素
				if(options.callback) {
					options.callback($a, $b);
				}
			}, delay);
		}).on('mouseleave keydown', function (e) {
			//无障碍处理
			if(e.type === 'keydown' && e.keyCode !== 27) {
				//如果不是按的ESC键，return
				return;
			}else {
				e.preventDefault();
			}
			//鼠标离开
			if (timeout) {
				timeout = clearTimeout(timeout);
			}

			timeout = setTimeout(function () {
				$b.hide();
			}, delay);
		});

		$b.on('mouseenter', function (e) {
			if (timeout) {
				//清理延时
				timeout = clearTimeout(timeout);
			}
		}).on('mouseleave keydown', function (e) {
			//无障碍处理
			if(e.type === 'keydown' && e.keyCode !== 27) {
				//如果不是按的ESC键，return
				return;
			}else {
				e.preventDefault();
				$a.focus();
			}
			timeout = setTimeout(function () {
				$b.hide();
				if(e.type === 'keydown') {
					$a.focus();
				}
			}, delay);
		});
	}catch(e){
			$.error(e);
	}
}

//点击显示隐藏
function clickToggle(options) {
	var elem = options.elem,								//触发元素
		list = options.list,										//隐藏列表
		callback_show = options.callback_show,		//显示后回调
		callback_hide = options.callback_hide,			//隐藏后回调
		lock = false;												//隐藏锁定，默认否

	elem.on('click keydown', function(e) {
		//点击触发
		if(e.type === 'keydown' && e.keyCode !== 13) {
			return;
		}else {
			e.preventDefault();
		}
		var $this = $(this);

		//非a标签添加 tabIndex，聚焦用
		if($this[0].tagName.toLowerCase() !== 'a') {
			$this.attr('tabindex', '0');
		}
		list.toggle();

		//回调
		if(!list.filter(':hidden').length) {
			lock = false;
			if(callback_show) {
				callback_show();
			}
		}

		if(list.is(':visible')) {
			list.focus();
		}
	})/*.on('blur', function(e){
		//失焦
		if(!lock) {
			list.hide();

			//回调
			if(callback_hide) {
				callback_hide();
			}
		}
	})*/;

	list.on('mouseenter', function(e){
		//鼠标进入，锁定
		lock = true;
	}).on('mouseleave', function(){
		//鼠标离开，触发元素聚焦，解除锁定
		elem.focus();
		lock = false;
	}).focusout(function(e) {
		if(e.target.tagName.toLowerCase() === 'button') {
			list.hide();
			elem.focus();
		}
		//!TODO:有问题
		//elem.focus();
	});
}

//强制刷新
function reloadPage(win) {
	var location = win.location;
	location.href = location.pathname + location.search;
}


/*
 * 前台成功提示
*/
function resultTip(options) {
	var elem = options.elem || options.follow,			//触发按钮, 曾经是options.follow
			error = options.error,											//正确或错误
			msg = options.msg,													//内容
			follow = options.follow,										//是否跟随显示
			callback = options.callback,								//回调
			cls = (error ? 'warning' : 'success');			//弹窗class

	var pop = $('<div tabindex="0" style="left:50%;top:30%;" role="alertdialog" class="pop_showmsg_wrap"><span class="pop_showmsg"><span class="' + cls + '">' + msg + '</span></span></div>');

	pop.appendTo($('body')).fadeIn(function () {
		if(follow){
			//跟踪定位
			var elem_offset_left = elem.offset().left,
					pop_width = pop.innerWidth(),
					win_width = $(window).width(),
					left;

			if(win_width - elem_offset_left < pop_width) {
				left = win_width - pop_width
			}else{
				left = elem_offset_left - (pop_width - elem.innerWidth())/2;
			}

			pop.css({
				left: left,
				top: elem.offset().top - $(document).scrollTop() - pop.height() - 15
			});

		}else{
			//水平居中
			pop.css({
				marginLeft :  - pop.innerWidth() / 2
			});
		}

	}).focus().delay(1500).fadeOut(function () {
		pop.remove();

		//触发元素重新聚焦
		if(elem) {
			elem.focus();
		}

		//回调
		if (callback) {
			callback();
		}
	});

}

//更换验证码，CODE_IMG_URL 在foot.htm定义
function changeCodeImg() {
	$('#J_code_change, #J_code_img').on('click', function (e) {
		e.preventDefault();
		//图片地址加随机数
		var img_src = GV.URL.CHECK_IMG + '&nowtime=' + new Date().getTime();
		$('#J_code_img').attr('src', img_src);

		//清空验证码表单
		$('#' + $('#J_code_change').data('input')).val('');

		//清空验证提示
		$('#' + $('#J_code_change').data('tip')).empty();
	});
}

//ajax载入模板html出错判断
function ajaxTempError(data, callback, follow) {
	//空内容
	if($.trim(data) === '') {
		return true;
	}
	var error = $($(data).slice(-1)).find('#J_html_error');
	if(error.length) {
		resultTip({
			error : true,
			msg : error.text(),
			follow : follow,
			callback : function(){
				if(callback) {
					callback();
				}
			}
		});
		return false;
	}else{
		return true;
	}
}

//按钮状态
function buttonStatus(input, btn){
	var timer;

	//默认为按钮禁用状态
	if(!input.val()) {
		btn.addClass('disabled').attr('disabled', 'disabled');
	}

	//聚焦
	input.on('focus', function(){
		var $this = $(this),
			tagname = input[0].tagName.toLowerCase(),
			type_input = false;

		//输入内容是否来自表单控件或div
		if(tagname == 'textarea' || tagname == 'input') {
			type_input = true;
		}
		//计时器开始
		timer = setInterval(function(){
			var trim_v = $.trim( type_input ? $this.val() : $this.text() );

			if(trim_v.length) {
				//有内容
				btn.removeClass('disabled').removeAttr('disabled', 'disabled');
			}else{
				//空内容
				btn.addClass('disabled').attr('disabled', 'disabled');
			}
		}, 200);

	});

	//输入失焦，解除计时
	input.on('blur', function(){
		clearInterval(timer);
	});
}

//所有的确认提交操作（删除、加入黑名单等）
function ajaxConfirm(option) {
	var params = {
		message : option.msg ? option.msg : '确定要删除吗？',
		type : 'confirm',
		isMask : false,
		follow : option.elem, //跟随触发事件的元素显示
		onOk : function () {
			if(option.beforeOk) {
				option.beforeOk();
			}

			$.getJSON(option.href).done(function (data) {
				if (data.state === 'success') {
					if (option.callback) {
						//回调处理
						option.callback();
					} else {
						//默认刷新
						if (data.referer) {
							location.href = data.referer;
						} else {
							reloadPage(window);
						}
					}
				} else if (data.state === 'fail') {
					resultTip({
						error : true,
						msg : data.message[0],
						follow : option.elem
					});
				}
			});
		}
	}
	Wind.dialog(params);
}

//ajax删除 刷新操作
function ajaxDel(elem){
			elem.on('click',function(e) {
				e.preventDefault();
				var $this = $(this), href = $this.prop('href'), msg = $this.data('msg');
				var params = {
					message	: msg ? msg : '确定要删除吗？',
					type	: 'confirm',
					isMask	: false,
					follow	: $(this),		//跟随触发事件的元素显示
					onOk	: function() {
						$.getJSON(href, function(data) {
							if(data.state === 'success') {
							if(data.referer) {
								location.href = data.referer;	//跳转
							}else {
								if($this.data('reloadpage')) {
									reloadPage(window);				//强制刷新
								}else{
									window.location.reload();
								}
							}
						}else if( data.state === 'fail' ) {
							resultTip({
								error : true,
									msg : data.message[0]
								});
							}
						});
					}
				};
				Wind.dialog(params);
			});
			}

/*
 * @弹窗快速登录 需要登录后跳转&刷新
 * @Depend: dialog, jquery.form, referer表示登录后跳转还是刷新
*/
function gQuickLogin(referer){

	if(GV.U_ID) {
		//存在uid 已登录
		return;
	}
	var qlogin_pop = $('#J_qlogin_pop');
	if(qlogin_pop.length) {
		//快捷登录已弹出

		//global.js
		popPos(qlogin_pop);

		$('#J_qlogin_username').focus();
		$('#J_qlogin_form').data('referer', referer).resetForm();		//登录后跳转地址
	}else{

		//global.js
		ajaxMaskShow();

		//未登录，获取登录html, QUICK_LOGIN head.htm
		$.get(GV.URL.QUICK_LOGIN, function(data){
			//global.js
			if(ajaxTempError(data)) {
				Wind.use('jquery.form', 'dialog', function(){
					//global.js
					ajaxMaskRemove();

					Wind.dialog.html(data, {
						id : 'J_qlogin_pop',
						cls : 'pop_login core_pop_wrap',
						position : 'fixed',
						isMask : false,
						isDrag : true,
						callback : function(){
							var qlogin_pop = $('#J_qlogin_pop');

							if(!$($(data).slice(-1)).find('#J_qlogin_username').length) {
								//登录后点击后退，进缓存的未登录页
								window.location.href = referer;
								return false;
							}

							//global.js
							changeCodeImg();

							$('#J_qlogin_username').focus();

							//登录后跳转地址
							$('#J_qlogin_form').data('referer', referer);

							Wind.js(GV.JS_ROOT +'pages/common/quickLogin.js?v='+ GV.JS_VERSION);

						}
					});

				});

			}
		}, 'html');
	}
}


//弹窗定位 obj ,width  height 定位偏移 非ie6 fixed定位
function popPosT(wrap, obj, width, height){
	var ie6 = false;
	if($.browser.msie && $.browser.version < 7) {
			ie6 = true;
	}

	var top = obj.offset().top;    //获取div的居上位置
	var left = obj.offset().left;    //获取div的居左位置

	wrap.css({
		top : top + (ie6 ? $(document).scrollTop() : 0) - height,
		left : left - width
	}).show();
}

//弹窗居中定位 非ie6 fixed定位
function popPos(wrap){
	var ie6 = false,
			top,
			win_height = $(window).height(),
			wrap_height = wrap.outerHeight();

	if($.browser.msie && $.browser.version < 7) {
		ie6 = true;
	}

	if(win_height < wrap_height) {
		top = 0;
	}else{
		top = ($(window).height() - wrap.outerHeight())/2;
	}

	wrap.css({
		top : top + (ie6 ? $(document).scrollTop() : 0),
		left : ($(window).width() - wrap.innerWidth())/2
	}).show();
}


/*
 * 拾色器函数
*/
function colorPickerBind(elems, zindex) {
	Wind.use('colorPicker', function() {
		elems.each(function(){
			$(this).colorPicker({
				zIndex : zindex ? zindex : 12,
				callback:function(color) {
					$(this).find('.J_bg').css('background-color',color);
					$(this).next('.J_hidden_color').val(color);
				}
			});
		});
	});
}


/*
 * 字体配置函数
*/
function fontConfigBind(wrap, zindex) {
	Wind.use('colorPicker', function() {
		//拾色器
		var elem = wrap.find('.J_color_pick'),
				panel = elem.parent('.J_font_config');
		elem.colorPicker({
			zIndex : 12,
			callback:function(color) {
				elem.find('.J_bg').css('background-color', color);
				panel.find('.J_case').css('color', color);
				panel.find('.J_hidden_color').val(color);
			}
		});

		//加粗、斜体、下划线的处理
		wrap.find('.J_bold, .J_italic, .J_underline').on('change',function() {
			var panel = $(this).parents('.J_font_config');
			var c = $(this).data('class');
			if( $(this).prop('checked') ) {
				panel.find('.J_case').addClass(c);
			}else {
				panel.find('.J_case').removeClass(c);
			}
		});
	});

}

/*
 * tab组件方法
*/
function tabsBind(wraps){
	Wind.use('tabs', function(){
		wraps.find('ul.J_tabs_nav').tabs(wraps.find('div.J_tabs_ct > div'));
	});
}

/*
 * 按钮提交不可用
*/
function ajaxBtnDisable(btn){
	var org_text = btn.text();
	btn.text(org_text +'中...').prop('disabled', true).addClass('disabled').data('sublock', true);
}

/*
 * 按钮提交可用
*/
function ajaxBtnEnable(btn){
	var org_text = btn.text();
	btn.text(org_text.replace(/中.../, '')).removeAttr('disabled').removeClass('disabled').data('sublock', false);;
}

/*
 * 显示ajax操作全页遮罩
*/
function ajaxMaskShow(zindex){
	var $maskhtml = $('<div id="J_ajaxmask" style="display:none;"><div class="pop_loading" style="position:fixed;left:50%;top:50%;margin:-40px 0 0 -40px;"></div></div>');

	//ie6的定位
	if($.browser.msie && $.browser.version < 7) {
		var child = $maskhtml.children();
		child.css({
			position : 'absolute',
			top : ($(window).height() - child.height())/2,
			margin : '0 0 0 -40px'
		});
	}

	//写入
	$maskhtml.appendTo('body').css({
		position : 'absolute',
		zIndex : zindex ? zindex : 12,
		left : '0px',
		top : '0px',
		width : '100%',
		height : $(window.document).height(),
		opacity : 0.4,
		backgroundColor	: '#000'
	}).show();
}

/*
 * 移除ajax操作全页遮罩
*/
function ajaxMaskRemove(){
	$('#J_ajaxmask').remove();
}

/*
 * 头像的错误处理
*/
function avatarError(avatars){
	avatars.each(function() {
		this.onerror = function() {
			this.onerror = null;
			this.src = GV.URL.IMAGE_RES + '/face/face_' + $(this).data('type') + '.jpg';//替代头像
			this.setAttribute('alt','默认头像');
		}
		this.src = this.src;
	});
}

//无障碍快捷键处理
/*$(function() {
	var menu = $('a[aria-haspopup]');
	if(menu.length) {
		menu.each(function() {
			$(this).on('keydown',function(e) {
				console.log(e.keyCode)
				if(e.keyCode === 40) {
					e.preventDefault();
					var id = $(this).attr('aria-haspopup');
					$('#'+id).show();
				}
			});
		});
	}
});*/