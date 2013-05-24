/**
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-快捷登录
 * @Author	: linhao87@gmail.com
 * @Depend	: core.js、jquery.js(1.7 or later), global.js, dialog, jquery.Form, jquery.draggable
 * $Id$
 */

;
(function () {
	var qlogin_pop = $('#J_qlogin_pop'),
			qlogin_tip = $('#J_qlogin_tip'),					//快捷登录提示
			qlogin_qa = $('#J_qlogin_qa'),							//验证问题容器
			$qa_html = $('<dl id="J_qa_wrap" class="cc">\
							<dt>安全问题</dt>\
							<dd><select id="J_login_question" name="question" class="select_4"></select></dd>\
						</dl>\
						<dl class="cc">\
							<dt>您的答案</dt>\
							<dd><input name="answer" type="text" class="input length_4" value=""></dd>\
		</dl>');
	
	var user_checked = false;									//用户名验证是否通过
	$('#J_qlogin_username').focus().on('blur', function(){
		//失焦验证用户名
		var $this = $(this);
		if($.trim($this.val())){
			$.getJSON($(this).data('check'), {username : $(this).val()}, function(data){
				if(data.state == 'success') {
					qlogin_tip.hide();
					user_checked = true;
					if(data.message.safeCheck){

						//写入验证问题
						var q_arr = [];
						$.each(data.message.safeCheck, function(i, o){
							q_arr.push('<option value="'+ i +'">'+ o +'</option>');
						});

						$qa_html.find('#J_login_question').html(q_arr.join(''));
						qlogin_qa.html($qa_html).show();

						//_statu = data.message._statu;
					}else{
						qlogin_qa.html('');
					}
				}else if(data.state == 'fail'){
					qlogin_tip.html('<span class="tips_error">'+ data.message[0] +'</span>').slideDown();
					user_checked = false;
				}
			});
		}
	});

	//自定义问题
	qlogin_qa.on('change', '#J_login_question', function(){
		if($(this).val() == '-4') {
			$('#J_qa_wrap').after('<dl id="J_myquestion_wrap" class="cc"><dt><label>自定义问题</label></dt><dd><input id="J_login_myquestion" type="text" name="myquestion" value="" class="input length_4"></dd><dd class="dd_r" id="J_u_login_tip_myquestion"></dd></dl>');
		}else{
			$('#J_myquestion_wrap').remove();
		}
	});
	
	//点击登录
	$('#J_qlogin_login').on('click', function(e){
		e.preventDefault();
		if(user_checked) {
			var qlogin_form = $('#J_qlogin_form'),
				referer = qlogin_form.data('referer');
			//用户名验证通过
			qlogin_form.ajaxSubmit({
				dataType : 'json',
				data : {
					backurl : referer ? referer : window.location.href		//跳转地址
				},
				success : function(data, statusText, xhr, $form){
					if(data.state === 'success') {
						if(data.message.check) {
							//设置验证问题
							$.get(data.message.check.url, function (data) {
								if(ajaxTempError(data)) {
									//隐藏登录弹窗
									qlogin_pop.hide();
									
									$('body').append(data);
									var question_set_wrap = $('#J_login_question_set_wrap');
									question_set_wrap.css({
										left : ($(window).width() - question_set_wrap.outerWidth())/2,
										top :  ($(window).height() - question_set_wrap.outerHeight())/2 + $(document).scrollTop()
									}).find('input:text:visible').focus();
								}
							}, 'html');
						}else{
							window.location.href = data.referer;
						}
					}else{
						qlogin_tip.html('<span class="tips_error"><span>' + data.message).slideDown();;
					}
				}
			});
		}
	});
	
	//关闭
	$('#J_qlogin_close').on('click', function(e){
		e.preventDefault();
		qlogin_pop.hide();
	});
})();