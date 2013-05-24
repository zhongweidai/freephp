/**
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-举报
 * @Author	: linhao87@gmail.com
 * @Depend	: core.js、jquery.js(1.7 or later), dialog, jquery.form
 * $Id$
 */

;(function(){
	
	//点击举报
	$('#J_posts_list').on('click', 'a.J_report', function(e){
		e.preventDefault();
		var $this = $(this);

		var report_pop = $('#J_report_pop');
		if(report_pop.length) {
			report_pop.find('textarea').focus();
			return false;
		}

		//global.js
		ajaxMaskShow();

		$.post($(this).attr('href'), {type_id: $this.data('typeid')}, function(data){
			//global.js
			ajaxMaskRemove();

			//验证模板反馈 gloabl.js
			if(ajaxTempError(data)) {
				Wind.dialog.closeAll();
				Wind.dialog.html(data, {
					id : 'J_report_pop',
					position	: 'fixed',			//固定定位
					title : '举报',
					isMask		: false,			//无遮罩
					isDrag : true,
					callback		: function(){
						var report_form = $('#J_report_form');
						//按钮状态 global.js
						buttonStatus(report_form.find('textarea'), report_form.find('button:submit'));
						
						$('#J_report_typeId').val($this.data('pid'));
						
						//类型
						$('#J_pick_list > a').on('click', function(e){
							e.preventDefault();
							$(this).addClass('current').siblings('.current').removeClass('current');
						});
						
						//举报提交
						report_form.ajaxForm({
							dataType : 'json',
							success : function(data){
								if(data.state === 'success') {
									resultTip({
										msg : '举报成功'
									});
									Wind.dialog.closeAll();
								}else if(data.state === 'fail'){
								
									//global.js
									resultTip({
										error : true,
										msg : data.message[0]
									});
								}
							}
						});
						
					}
				});
				
			}
		});
	});
})();