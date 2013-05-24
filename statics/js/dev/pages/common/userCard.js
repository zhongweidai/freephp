/**
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台用 - 小名片
 * @Author	: linhao87@gmail.com
 * @Depend	: core.js、jquery.js(1.7 or later)
 * $Id: userCard.js 12527 2012-06-23 05:31:56Z hao.lin $
 */
 
userCard();
function userCard() {
	var user_card_show = $('a.J_user_card_show'),
		card_wrap = '<div class="pop_card J_pop_card" id="_ID"><div class="arrow J_card_arrow"><em></em><span></span><strong></strong></div><div class="pop_loading J_pop_loading"></div></div>';
	
	var lock_hide = false,		//隐藏锁定, true表示不隐藏
		timeout;
	
	//经过用户名或头像触发
	var i = 0;
	user_card_show.off('mouseenter').on('mouseenter', function(e){
		e.preventDefault();
		i += 1;
		var $this = $(this),
				uid = $this.data('uid'),
				uname = $this.data('username'),
				param = uid ? uid : 'c' + i;			//不存在uid 则用随机数命名

		if(!$this.data('param')) {
			//存入数据
			$this.data('param', param);
		}
		
		var card_item = $('#J_user_card_'+ $this.data('param'));
		

		lock_hide = true;
		
		timeout = setTimeout(function(){
			//先隐藏所有小名片
			$('div.J_pop_card').hide();
			
			if(card_item.length) {
				//已存在则显示
				card_item.show();
				cardPos($this, card_item);
			}else{
				//不存在则请求
				$.ajax({
					url : GV.URL.USER_CARD + (uname ? '&username=' + uname : ('&uid=' + (uid ? uid : ''))),			//head.htm
					dataType : 'html',
					success : function(data){
						//global.js
						if(ajaxTempError(data)) {
						
							$('body').append(card_wrap.replace('_ID', 'J_user_card_'+ param));
							$('#J_user_card_'+ param).append(data).find('.J_pop_loading').remove()

							cardPos($this, $('#J_user_card_'+ param));
						}
					}
				});
			
			}
			
		}, 300);
		
	}).on('mouseleave', function(e){
		//离开
		clearTimeout(timeout);		//清理ajax
		lock_hide = false;				//触发隐藏
		
		var $this = $(this),
				card = $('#J_user_card_'+ $this.data('param'));

		timeout = setTimeout(function(){
			if(!lock_hide){
				card.hide();
			}
		}, 300);
	});
	
	$('body').on('mouseenter', 'div.J_pop_card', function(){
		//进入小名片
		lock_hide = true;
	}).on('mouseleave', 'div.J_pop_card', function(){
		//离开小名片
		var $this = $(this);
		lock_hide = false;
		
		setTimeout(function(){
			if(!lock_hide){
				$this.hide();
			}
		}, 300);
	});

	//定位
	function cardPos(elem, wrap){
		var left,																			//名片水平位置
				top,
				cls = 'arrow',														//三角class，正
				_cls = 'arrow_bottom',										//三角class，反
				elem_offset_left = elem.offset().left,
				elem_offset_top = elem.offset().top,
				wrap_width = wrap.outerWidth(),						//名片宽度
				wrap_height = wrap.outerHeight() + 15,		//名片高度 15为三角高度
				win_width = $(window).width(),
				arror_left = elem.innerWidth() / 2 - 9;		//小三角水平位置
			
		//判断右侧宽度是否足够
		if(win_width - elem_offset_left < wrap_width) {
			left = win_width - wrap_width;
			arror_left = elem_offset_left - left + elem.innerWidth() / 2 - 9;
		}else{
			left = elem_offset_left;
		}
		
		//判断窗口下方高度是否足够
		var elem_window_top = elem_offset_top - $(document).scrollTop(),										//触发元素到窗口顶部距离
			elem_window_bottom = $(window).height() - elem_window_top - elem.innerHeight();			//触发元素到窗口底部距离
		
		//默认显示在上方
		top = elem.offset().top + elem.innerHeight() + 10;
		
		if(wrap_height > elem_window_bottom && wrap_height <= elem_window_top) {
			//显示在上方
			top = elem_offset_top - wrap_height;
			cls = 'arrow_bottom';
			_cls = 'arrow'
		}
		
		//小名片位置
		wrap.css({
			left : left,
			top: top
		});
		
		//小三角位置
		wrap.find('.J_card_arrow').css({
			left : arror_left
		}).removeClass(_cls).addClass(cls);
		
		//发消息，验证函数是否已存在
		if($.isFunction(window.sendMsgPop)) {
			sendMsgPop(true);
		}else{
			Wind.js(GV.JS_ROOT+ 'pages/common/sendMsgPop.js?v='+ GV.JS_VERSION);
		}
		
		//关注与取消
		var lock = false;
		$('a.J_card_follow').off('click').on('click', function(e){
			if(lock) {
				return false;
			}
			lock = true;
			e.preventDefault();
			var $this = $(this);
			$.getJSON($this.attr('href'), function(data){
				if(data.state == 'success') {
					$this.parent('.J_follow_wrap').hide().siblings('.J_follow_wrap').show();
				}else if(data.state == 'fail') {
					//global.js
					resultTip({
						error : true,
						msg : data.message[0]
					});
				}
				lock = false;
			});
		});
	}
	
};
