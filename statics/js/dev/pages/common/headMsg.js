/**
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-头部消息窗
 * @Author	: linhao87@gmail.com
 * @Depend	: core.js、jquery.js(1.7 or later), global, jquery.form, jquery.scrollFixed, insertEmotions.js
 * $Id: headMsg.js 12907 2012-06-27 08:23:39Z hao.lin $
 */
;(function(){
	var $hm_wrap = $('#J_head_msg'),			//消息窗父容器
			hm_home = '#J_hm_home',						//消息窗首页列表
			hm_list = '.J_hm_list',						//消息窗各页面列表区
			hm_max_height = 400,							//消息窗高
			hm_loading = $('<div class="pop_loading" style="position:absolute;left:50%;top:50%;margin:-40px 0 0 -25px;"></div>'),
			lock = false;											//消息窗请求锁定
	
	getHeadMsgHome();

	/*
	 *获取消息窗首页
	*/
	function getHeadMsgHome() {
		
		//列表是否已载入完毕
		/*if ($hm_wrap.data('load')) {
			return;
		}*/

		//请求锁定
		if(lock) {
			return;
		}

		//请求消息窗首页
		$.ajax({
			dataType : 'html',
			beforeSend : function(jqXHR, settings) {
				lock = true;
			},
			url : GV.URL.HEAD_MSG.LIST, //head.htm
			success : function (data) {

				//global.js
				if(!ajaxTempError(data)) {
					$hm_wrap.html('<div class="core_nocontent" style="margin:20px 0 20px 60px;">出错啦，请稍后刷新再试</div>');
					return false;
				}

				//if (ajaxTempError(data)) {
					Wind.use('jquery.form', 'jquery.scrollFixed', function(){
						Wind.js(GV.JS_ROOT+ 'pages/common/insertEmotions.js?v='+ GV.JS_VERSION, function(){
							$hm_wrap.html(data);//data('load', 'load');

					var $hm_list = $(hm_list)					
					IE6Height();
					
					$hm_list.scrollFixed();
					
					if($.browser.msie && $.browser.version < 7) {
						$hm_list.on('mouseenter', 'li', function(){
						$(this).addClass('current');
					}).on('mouseleave', 'li', function(){
							$(this).removeClass('current');
						});
					}

					$('#J_hm_home li').on('click', function (e) {
						getPage($(this).data('url'));
					});
						});
					});
			}
		});

	}


	/*
	 * 消息窗内操作绑定
	*/

	//绑定所有返回按钮
		$hm_wrap.on('click', 'a.J_hm_back', function (e) {
			e.preventDefault();
			showHeadmsgHome();
		});
		
		//
		$hm_wrap.on('click', 'a.J_hm_ajaxlink', function (e) {
			e.preventDefault();
			var $this = $(this);
			$.getJSON($this.attr('href'), function(data){
				if(data.state === 'success') {
					resultTip({
						msg : data.message[0]
					});
				}else if(data.state === 'fail'){
					resultTip({
						error : true,
						msg : data.message[0]
					});
				}
			});
		});

		//加入黑名单&屏蔽 带操作提示
		$hm_wrap.on('click', 'a.J_hm_ajaxtip', function (e) {
			e.preventDefault();
			var $this = $(this),
					role = $this.data('role'),				//类型
					name = $this.data('name'),				//操作对象
					referer = $this.data('referer');	//跳转地址

			$.getJSON($this.attr('href'), function(data){
				if(data.state === 'success') {
					var tip_text, btn_text;
					
					if(role == 'blacklist') {
						tip_text = '已把 '+ name +' 列入黑名单，您不会再收到Ta的私信。';
						btn_text = '查看黑名单';
					}else if(role == 'app'){
						tip_text = '您将不会再收到 '+ name +' 通知';
						btn_text = '查看通知设置';
					}

					$('#J_hm_top').after('<div class="tips">'+ tip_text +'</div>');

					//修改按钮状态，移除绑定class
					$this.text(btn_text).removeClass('J_hm_ajaxtip').attr('href', referer);

				}else if(data.state === 'fail'){
					resultTip({
						error : true,
						msg : data.message[0]
					});
				}
			});
		});

		//表情
		$hm_wrap.on('click', 'a.J_insert_emotions', function(e){
			e.preventDefault();
			var head_msg_pop = $('#J_head_msg_pop'),
					$this = $(this);

			if(!$('#J_emotions_pop').length) {
				insertEmotions($this, $('#J_head_msg_textarea'), head_msg_pop);
			}

			//重新定位
			$('#J_emotions_pop').show().css({
				left : -10,
				top : $this.offset().top - head_msg_pop.offset().top + $this.height() + 5
			});
		}).on('click', 'a.J_msg_follow', function(e){
			//加关注
			e.preventDefault();
			var $this = $(this);
			$.post(this.href, function(data){
				if(data.state == 'success') {
					$this.replaceWith('<span class="core_unfollow">已关注</span>');
				}else if(data.state == 'fail'){
					//global.js
					resultTip({
						error : true,
						msg : data.message[0],
						follow : $this
					});
				}
			}, 'json');
		});

		//写私信

		
		//发送
		$hm_wrap.on('click', '#J_message_reply_btn', function (e) {
			e.preventDefault();
			var $this = $(this),
					msg_success = $('#J_msg_success'),
					textarea = $('#J_msg_textarea');
			
			$('#J_emotions_pop').hide();

			$this.parents('form').ajaxSubmit({
				dataType : 'json',
				success : function(data){
					if(data.state === 'success') {
						$('#J_msg_dialog_list').prepend('<div class="my cc">\
			<div class="face"><a href=""><img height="25" width="25" onerror="this.onerror=null;this.src=\''+GV.U_AVATAR_DEF+'\'" data-type="small" src="'+ GV.U_AVATAR +'" class="J_avatar"></a></div>\
			<div class="bubble">\
				<div class="arrow"><em></em><span></span></div>\
				<a class="b" href="http://www.phpwind.dev/index.php?m=space&amp;uid=2">我</a>：'+ $.trim(textarea.val()) +'<div class="io"><span class="time">刚刚</span></div>\
			</div>\
		</div>');
						textarea.val('');
						msg_success.fadeIn().delay(1500).fadeOut();
					}else if(data.state === 'fail'){
						resultTip({
							error : true,
							msg : data.message[0]
						});
					}
				}
			});
		});
		
		//统一处理所有ajax页面请求
		$hm_wrap.on('click', 'a.J_hm_page', function (e) {
			e.preventDefault();
			getPage($(this).attr('href'));
		});


	/*
	 * 设置ie6最大高度
	*/
	function IE6Height() {
		if ($.browser.msie && $.version === '6.0') {
			//var list = $(this.list);
			
			if ($(hm_list).height() > this.max_height) {
				$(hm_list).css('height', this.max_height);
			} else {
				//list.css('height', 'auto');
			}
		}
	}


	/*
	 * 更换页面
	*/
	function getPage(url) {
		//var $hm_wrap = $(hm_wrap);
		$('#J_emotions_pop').hide();
		$.ajax({
			url : url,
			beforeSend : function () {
				hm_loading.appendTo($hm_wrap);
			},
			success : function (data) {
				$(hm_home).hide().siblings().remove();
				$hm_wrap.append(data).find(hm_loading).remove();

				//绑定发私信
				if($hm_wrap.find('a.J_send_msg_pop').length) {
					//发消息，验证函数是否已存在
					if($.isFunction(window.sendMsgPop)) {
						sendMsgPop(true);
					}else{
						Wind.js(GV.JS_ROOT+ 'pages/common/sendMsgPop.js?v='+ GV.JS_VERSION);
					}
				}

				//绑定按钮状态 global.js
				if($('#J_head_msg_textarea').length) {
					buttonStatus($('#J_head_msg_textarea'), $('#J_message_reply_btn'));
				}
				
				IE6Height();
				$(hm_list).scrollFixed();
			}
		});
	}

})();


/*
 * 返回消息窗首页
*/
function showHeadmsgHome() {
	$('#J_emotions_pop').hide();
	$('#J_hm_home').show().siblings().remove();
}

