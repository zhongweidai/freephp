/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-阅读回复
 * @Author	: linhao87@gmail.com, TID
 * @Depend	: jquery.js(1.7 or later), global.js, userCard.js, report.js
 * $Id$
 */


;(function(){

/*
 * 主楼快速回复
*/
	var reply_quick_ta = $('#J_reply_quick_ta'),
			reply_quick_btn = $('#J_reply_quick_btn'),
			reply_ft = $('#J_reply_ft'),
			read_0 = $('#read_0');				//回复主楼

	//global.js
	buttonStatus(reply_quick_ta, reply_quick_btn);

	//回复框聚焦
	reply_quick_ta.on('focus', function(){
		reply_ft.fadeIn();
	});
	
	//提交回复
	reply_quick_btn.on('click', function(e){
		e.preventDefault();
		$.post($(this).data('action'), {
			atc_content : reply_quick_ta.val(),
			tid : $(this).data('tid')
		}, function(data){
			if(ajaxTempError(data, function(){
				//审核提示
				reply_ft.fadeOut();
				reply_quick_ta.val('');
			})) {
				reply_ft.fadeOut();
				reply_quick_ta.val('');
				read_0.after(data);

				$('a.J_like_btn').like();

				//global.js
				avatarError(read_0.next().find('img.J_avatar'));
				userCard();
			}
		});
	});


/*
 * 查看回复
*/
	var lock = false,
			posts_list = $('#J_posts_list');

	posts_list.on('click', 'a.J_read_reply', function(e){
		e.preventDefault();
		var $this = $(this),
				pid = $this.data('pid'),
				wrap = $('#J_reply_wrap_'+ pid);			//列表容器

		wrap.toggle();

		//锁定 或 已请求
		if(lock || $this.data('load')) {
			return false;
		}
		lock = true;

		$.post(this.href, function(data){
			//global.js
			lock = false;

			if(ajaxTempError(data))	{
				wrap.html(data);
				location.hash = 'read_'+ pid;			//锚点跳转
				$this.data('load', true);					//已请求标识

				replyFn();

				//userCard.js
				userCard();
			}
		});
	});


	posts_list.on('click', 'a.J_insert_emotions' ,function(e){
		//表情
		e.preventDefault();
		insertEmotions($(this), $($(this).data('emotion')));
	}).on('click', 'a.J_read_reply_single' ,function(e){
		//回复单个
		e.preventDefault();
		//var wrap = $(this).parents('div.J_reply_wrap'),
		var wrap = $(this).parent().parent().parent().parent().parent().parent(),
				username = $(this).data('username'),
				textarea = wrap.find('textarea');

			textarea.val('@'+ username +'：');
			if(!$.browser.msie) {
				//chrome 光标定位最后
				textarea[0].setSelectionRange(100,100);
			}
			textarea.focus();
	}).on('click', 'button.J_reply_sub' ,function(e){
		//提交
		e.preventDefault();
		var $this = $(this),
				pid = $this.data('pid'),
				textarea = $('#J_reply_ta_'+ pid);

		//global.js
		ajaxBtnDisable($this);

		$.post($(this).data('action'), {
			atc_content : textarea.val(),
			tid : TID,
			pid : pid
		}, function(data){
			//global.js
			ajaxBtnEnable($this);
			if(ajaxTempError(data, function(){
				//审核提示
				textarea.val('');
				$this.addClass('disabled').prop('disabled', true);
				$('#J_emotions_pop').hide();
			})) {
				$('#J_reply_ul_'+ pid).prepend(data);
				textarea.val('');
				$this.addClass('disabled').prop('disabled', true)
				$('#J_emotions_pop').hide();

				location.hash = 'read_'+ pid;			//锚点跳转

				//userCard.js
				userCard();
			}
			
		});
	}).on('click', 'div.J_pages_wrap a' ,function(e){
		//翻页
		e.preventDefault();
		var list = $(this).parents('.J_reply_page_list'),
				clone = list.clone();

		//跳楼
		location.hash = $(this).parents('.J_read_floor').attr('id');
		
		list.html('<div class="pop_loading"></div>');

		$.post(this.href, function(data){
			if(!ajaxTempError(data)) {
				//失败则恢复原内容
				list.html(clone.html());
				return false;
			}

			list.html(data);
		})
	});


/*
 *回复列表公共方法
*/
	function replyFn(){
		var btn = $('button.J_reply_sub');
		btn.each(function(){
			//global.js
			buttonStatus($('#J_reply_ta_'+ $(this).data('pid')), $(this));
		});
		
	}

})();