/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-帖子管理
 * @Author	: linhao87@gmail.com
 * @Depend	: jquery.js(1.7 or later), jquery.form, TID
 * $Id: manage_threads.js 12388 2012-06-21 03:02:51Z hao.lin $
 */
manageThreads();
function manageThreads(){
	
	//点击“置顶”等文字显示&隐藏相应内容
	$('label.J_toggle').on('click', function(e){
			$(this).parents('li').addClass('current')
				.siblings('li.current').removeClass('current');
	});
	
	$('input.J_toggle').on('change', function(){
		var $this = $(this);
		if($this.attr('checked')) {
			$this.parents('li').addClass('current')
				.siblings('li.current').removeClass('current');
		}
	});
	
	//选择“取消置顶”后禁用有效期
	
	$('#J_topped_select').on('change', function(){
		var v = $(this).val(),
			topped_time = $('#J_topped_time'),
			J_topped_forums = $('#J_topped_forums');
		if(v === '0') {
			topped_time.addClass('disabled').attr('disabled', 'disabled').val('');
			J_topped_forums.hide();
		}else if(v === '3') {
			J_topped_forums.show();
		}else{
			topped_time.removeClass('disabled').removeProp('disabled');
			J_topped_forums.hide();
		}
	});
	
	//加亮 字体
	$('a.J_font_style').on('click', function(e){
		e.preventDefault();
		var $this = $(this);
		$this.toggleClass('current');
		if($this.hasClass('current')) {
			$('#' +$this.data('id')).attr('checked', 'checked');
		}else{
			$('#' +$this.data('id')).removeAttr('checked');
		}
	});
	
	//提前时间，仅限数字，判断输入或粘贴
	var uptime = $('#J_uptime');
	if(uptime.length) {
		uptime.on('keyup', function(e){
			var $this = $(this), v = $this.val();
			v = v.replace(/[^\d]/g,'');
			$this.val(v);
		});
	
		uptime[0].onpaste = function(){
			setTimeout(function(){
				var v = uptime.val();
				v = v.replace(/[^\d]/g,'');
				uptime.val(v);
			}, 150);
		}
	}
	
	
	//点击写入操作理由
	$('#J_resson_select').on('change', function(){
		$('#J_resson_input').val($(this).val());
	});
	
	Wind.use('colorPicker', 'datePicker', function() {
		$("input[type=date]").datePicker();
		
		var elem = $('.J_color_pick');
		elem.colorPicker({
			zIndex : 12,
			callback:function(color) {
				elem.find('em').css('background-color',color);
				$('#J_light_color').val(color);
			}
		});
	});
	
	
	//获取所有被选择帖子的fid并提交
	$('#J_sub_topped').on('click', function(e){
		e.preventDefault();
		var checks = $('#J_posts_list input.J_check:checked'),
				xid_arr = [],
				role = $(this).data('role'),
				type = $(this).data('type'),
				_data = {};

		$.each(checks, function(){
			xid_arr.push($(this).val());
		});

		//区分传输数据
		if(role == "read") {
			//阅读页
			_data['tid'] = TID;
			_data['pids[]'] = xid_arr;
		}else{
			_data['tids[]'] = xid_arr;
		}

		$('#J_post_manage_ajaxForm').ajaxSubmit({
			dataType	: 'json',
			data : _data,
			success		: function(data){
				if(data.state === 'success') {
					Wind.dialog.closeAll();
					resultTip({
						msg : data.message,
						callback : function(){
							Wind.dialog.closeAll();
							if(role == "read") {
								if(type == 'delete') {
									checks.parents('div.J_read_floor').remove();
									window.location.reload();
								}
							}else{
								window.location.reload();
							}
						}
					});
				}else if(data.state === 'fail'){
					resultTip({
						error : true,
						msg : data.message
					});
				}
			}
		});
	});
	
	
	
	
	//关闭操作
	$('#J_post_manage_ajaxForm .J_close').on('click', function(){
		$('.J_check_wrap input:checkbox:checked').removeAttr('checked');
	});
	
}