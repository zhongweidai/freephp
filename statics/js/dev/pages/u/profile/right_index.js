/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-设置-权限
 * @Author	: linhao87@gmail.com
 * @Depend	: jquery.js(1.7 or later), global.js
 * $Id$
 */
 
;(function(){
	//点击显示
	$.each($('a.J_right_toggle'), function(i, o){
		var $this = $(this),
			list = $('#'+ $this.data('id'));
		//global.js
		clickToggle({
			elem : $this,
			list : list,
			callback_show : function(){
				list.css({
					left : $this.offset().left
				});
			}
		});
	});
	
	//切换当前用户组
	var change_group = $('#J_change_group'),
		change_group_pop = $('#J_change_group_pop');
	
	change_group.on('click', function(e){
		e.preventDefault();
		
		change_group_pop.show().css({
			top : change_group.offset().top + change_group.innerHeight(),
			left : change_group.offset().left
		});
		$('#J_right_my').hide();
	});
	
	//关闭
	$('#J_change_group_close').on('click', function(e){
		e.preventDefault();
		change_group_pop.hide();
	});
	
	//提交
	$('#J_change_group_form').ajaxForm({
		dataType : 'json',
		success : function(data){
			if(data.state == 'success') {
				//global.js
				resultTip({
					msg : '切换成功',
					callback : function(){
						window.location.reload();
					}
				})
			}else if(data.state == 'fail') {
				resultTip({
					error : true,
					msg : data.message[0]
				})
			}
		}
	});
	
})();