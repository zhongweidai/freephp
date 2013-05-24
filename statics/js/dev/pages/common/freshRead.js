/**
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-新鲜事阅读（新鲜事、个人空间）
 * @Author	: linhao87@gmail.com
 * @Depend	: core.js、jquery.js(1.7 or later), FRESH_DOREPLY页面定义
 * $Id$
 */
;
(function () {
/*
 * 删除
*/
$('a.J_fresh_del').on('click', function(e){
	e.preventDefault();
	var $this = $(this);

	//global.js
	ajaxConfirm({
		elem : $this,
		href : this.href,
		beforeOk : function(){
			//global.js
			ajaxMaskShow();
		},
		callback : function(){
			//global.js
			ajaxMaskRemove();

			$this.parents('dl').slideUp(function(){
				$(this).remove();
			});
		}
	});
});

	//回复列表部分html
	var feed_part_html = '<div class="feed_repeat_arrow">\
									<em>◆</em>\
									<span>◆</span>\
								</div><form action="'+ FRESH_DOREPLY +'" method="post"><input name="id" type="hidden" value="_ID" /><div class="feed_repeat_textarea">\
									<div class="input_area"><textarea id="J_fresh_emotion__ID" name="content" class="J_feed_textarea" style="overflow-y:hidden;"></textarea></div>\
									<div class="addition">\
										<a href="#" class="icon_face J_fresh_emotion" data-emotiontarget="#J_fresh_emotion__ID">表情</a>\
										<label><input type="checkbox" value="1" name="transmit">告诉我的粉丝</label>\
									</div>\
									<div class="enter"><button class="btn btn_submit J_feed_sub">提 交</button></div>\
		</div></form><div class="feed_repeat_list J_feed_repeat_list">_DATA</div>';
		
	var $loading_html = $('<div class=""><span class="tips_loading">正在loading</span></div>');
	
	var id;
	//显示载入回复列表
	$('a.J_feed_toggle').on('click', function(e){
		e.preventDefault();
		$('#J_emotions_pop').hide();
		var $this =  $(this);
		
		id = $this.data('id');
		var	list = $('#J_feed_list_'+ id);

		if(list.children().length) {
			list.hide().empty();
		}else{
			$.ajax({
				url : $this.attr("href"),
				type : 'post',
				dataType : 'html',
				beforeSend : function(){
					list.html($loading_html[0]).show();
				},
				success : function(data) {
					if (ajaxTempError(data, function(){list.hide()})) {
						list.html(feed_part_html.replace(/_ID/g, id).replace('_DATA', data));
						buttonStatus(list.find('textarea.J_feed_textarea'), list.find('button.J_feed_sub'));
						list.find('textarea').focus();
					}

				}
			});
			
		}
	});
	
	
	$('.J_feed_list').on('click', 'button.J_feed_sub', function(e){
	
		//回复提交
		e.preventDefault();
		var $this = $(this);

		if(!GV.U_ID) {
			//global.js
			gQuickLogin();
		}else{
			$this.parents('form').ajaxSubmit({
				dataType	: 'html',
				data : {
						csrf_token : GV.TOKEN
				},
				beforeSubmit: function(arr, $form, options) { 
					
				},
				success : function(data, statusText, xhr, $form){
					if(ajaxTempError(data, null, $this)) {
						var repeat_wrap = $form.siblings('.J_feed_repeat_list'),
						repeat_list = repeat_wrap.children();
					if(repeat_list.length >= 10) {
						//超过十条则删除最底下的一条
						repeat_list.last().remove();
					}
				
					//写入最新回复到顶部
					repeat_wrap.prepend(data);
					$form.find('textarea.J_feed_textarea').val('');
				
					//统计+1
					var feed_count = $('#J_feed_count_'+ id), c = Number(feed_count.text());
					feed_count.text(c+1);
					}
				
				}
			});
		}

		$('#J_emotions_pop').hide();
	}).on('click', 'a.J_feed_single', function(e){
	
		//回复单条
		e.preventDefault();
		var $this = $(this), user = $this.data('user'),
				textarea = $this.parents('.J_feed_list').find('textarea');

		textarea.val('@'+ user +'：');
		if(!$.browser.msie) {
			//chrome 光标定位最后
			textarea[0].setSelectionRange(100,100);
		}
		textarea.focus();
		$('#J_emotions_pop').hide();
	}).on('focus', 'textarea.J_feed_textarea', function(){
		//回复框聚焦后高度自适应
		var $this = $(this), _this = this;
		
		$this.on('keydown keyup', function(){
		//timer = setInterval(function(){

			var height,
				sc_height = $.browser.msie ? _this.scrollHeight -8 : _this.scrollHeight,		//ie 减去padding值
				this_style=_this.style;

				//每次都先重置高度, ff & chrome
				this_style.height =  18 + 'px';

			if (sc_height > 18) {
				//暂定200为最大高度
				if (sc_height > 180) {
					height = 180;
					this_style.overflowY = 'scroll';
				} else {
					height = $.browser.msie ? _this.scrollHeight -8 : _this.scrollHeight;	//重新获取
					this_style.overflowY = 'hidden';
				}

				this_style.height = height  + 'px';
		
			}

		//}, 300);
		});
		
	}).on('blur', 'textarea.J_feed_textarea', function(){
		//回复框失焦
		//clearInterval(timer);
	});
	
	//阅读&收起 全部
	$('a.J_read_all').on('click', function(e){
		e.preventDefault();
		var $this = $(this),
			content = $('#'+ $this.data('id'));
		if($this.data('dir') === 'down') {
			//阅读全部
			$.ajax({
				url : $this.prop('href'),
				dataType : 'html',
				beforeSend : function(){
					$loading_html.insertAfter(content);
				},
				success : function(data){
					//global.js
					if(ajaxTempError(data)) {
						content.hide().siblings('.J_content_all').html(data).show();
						$loading_html.remove();
						$this.text('收起↑').data('dir', 'up');
					}
				}
			});
		}else{
			//收起
			content.show().siblings('.J_content_all').hide().empty();
			$this.text('阅读全部↓').data('dir', 'down');
		}
		
	});

/*
 * 喜欢
*/
	$('a.J_fresh_like').on('click', function(e){
		e.preventDefault();
		var $this = $(this);
		$.post($this.attr('href'), function(data){
			var em = $this.children('em'),
					org_v = parseInt(em.text());

			if(data.state == 'success') {
				em.text(org_v+1);

				//global.js
				resultTip({
					msg : data.message[0],
					follow : $this
				});

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
	
})();
