/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-勋章
 * @Author	: linhao87@gmail.com
 * @Depend	: jquery.js(1.7 or later), MEDAL_JSON由页面定义
 * $Id: medal_index.js 12650 2012-06-25 06:56:18Z yanchixia $
 */
 
$(function(){
	//弹窗模板
	var template = '<div class="hd J_drag_handle">\
					<a href="#" class="close J_close">关闭</a>\
					<strong>勋章说明</strong>\
				</div>\
				<div class="ct">\
					<form id="J_medal_pop_form" method="post" action="#">\
					<dl class="cc">\
						<dt id="J_medal_pop_img"></dt>\
						<dd>\
							<p id="J_medal_pop_name" class="name"></p>\
							<p class="type">勋章类型：<span id="J_medal_pop_type"></span></p>\
							<p id="J_medal_pop_time_row" style="display:none;">有效时长：<span id="J_medal_pop_time_wrap"></span></p>\
							<p class="descrip">勋章描述：<span id="J_medal_pop_description"></span></p>\
							<p id="J_medal_pop_progress" style="display:none;">当前进度：<span id="J_progress_wrap"></span></p>\
						</dd>\
					</dl>\
					<!--div class="mb15 s6"><div class="tips_error mb5">您当前的用户组不支持领取该勋章。</div><strong>可领取勋章用户组：</strong>骑士、圣骑士、精灵王、风云、雄霸、步惊云</div-->\
					<textarea   style="display:none;"id="J_medal_pop_textarea"  name="content">我获得了“_NAME”勋章。现在有_COUNT个勋章啦，赶紧去领勋章，比比谁的多！查看：[url=_PAGE_URL]查看[/url]</textarea>\
					<div id="J_medal_pop_op" style="display:none;" class="operate">\
						<button type="submit" class="btn btn_success btn_big" data-action="'+ MEDAL_AWARD_URL +'">领取勋章</button><label><input name="isfresh" type="checkbox" checked="checked" value="1">告诉我的粉丝</label>\
					</div>\
					<div id="J_medal_pop_close" style="display:none;" class="tac"><button type="button" class="btn btn_big J_close">关闭</button></div>\
					<div id="J_medal_pop_disable" style="display:none;" class="tac"><button type="button" class="btn btn_big disabled" disabled>已申请</button></div>\
					<div id="J_medal_pop_can" style="display:none;" class="tac"><button type="submit" class="btn btn_big btn_success" data-action="'+ MEDAL_APPLY_URL +'">申请</button></div>\
					</form>\
		</div>';
		
	//查看&领取勋章
	$('#J_medal_card_wrap').on('click', 'a.J_medal_card', function(e){
		e.preventDefault();
		var $this = $(this),
			role = $this.data('role'),
			id = $this.data('id'),
			data = medel_data[id],
			logid = (data.logid !== '' ? data.logid : '');		//勋章中心 领取提交参数

		var dialog = Wind.dialog.html(template.replace('_NAME', data.name).replace('_COUNT', parseInt(MEDAL_COUNT)+1).replace('_PAGE_URL', MEDAL_PAGE_URL),{
			id : 'J_medel_pop',
			cls			: 'pop_medal',		//容器class
			position	: 'fixed',			//固定定位
			isDrag : true,
			isMask		: false,			//无遮罩
			onShow		: function(){
				//是否输入框和各种按钮判断

				if(role === 'receive') {
					//领取
					$('#J_medal_pop_textarea').show();	//文本域
					$('#J_medal_pop_op').show();		//转发提交区
				}else if(role === 'apply'){
					//可以申请
					$('#J_medal_pop_can').show();		//申请按钮
				}else if(role === 'applied'){
					//已申请
					$('#J_medal_pop_disable').show();	//已申请 不可用
				}else if(role === 'show'){
					//关闭
					$('#J_medal_pop_close').show();		//关闭按钮
				}
				
				var type = (data.type === '1' ? '自动勋章' : '手动勋章');
				
				$('#J_medal_pop_img').html('<img src="'+ data.big +'" />');	//图片
				$('#J_medal_pop_name').text(data.name);						//名称
				$('#J_medal_pop_description').text(data.description);		//描述
				$('#J_medal_pop_type').text(type);						//类型
				
				//有效期
				if(data.time) {
					var time = (data.time === '0' ? '长期有效' : data.time +'天');
					$('#J_medal_pop_time_row').show();
					$('#J_medal_pop_time_wrap').text(time);
				}
				
				//进度，近自动勋章
				if(data.type === '1') {
					var medal_pop_progress = $('#J_medal_pop_progress');
					if(data.status == '1' || data.status == '0' && data.behavior){
						medal_pop_progress.show();
						$('#J_progress_wrap').text(data.behavior +'/'+ data.condition);
					}else{
						medal_pop_progress.hide();
					}
				}
				
			}
		});
		
		var medal_pop_form = $('#J_medal_pop_form');
		medal_pop_form.find('button:submit').on('click', function(e){
			e.preventDefault();

			var btn = $(this);
			medal_pop_form.ajaxSubmit({
				url : btn.data('action'),
				data : {
					id : $this.data('subid'),														//申请
					logid : (logid ? logid : $this.data('logid')),			//领取
					csrf_token : GV.TOKEN
				},
				dataType	: 'json',
				beforeSubmit: function(arr, $form, options) {
				
				},
				success		: function(data, statusText, xhr, $form) {
					if(data.state === 'success') {
						resultTip({
							msg : data.message[0],
							callback : function(){
								window.location.reload();
								/* dialog.close();
								$this.parents('li').fadeOut('slow', function(){
									$(this).remove();
								}); */
							}
						});
					}else if(data.state === 'fail'){
						resultTip({
							error : true,
							msg : data.message[0]
						});
					}
				}
			});
		});
		
		//拖拽
		$('#J_Wind_dialog').draggable( { handle : '.J_pop_handle'} );
	});
	
	
	
	
});