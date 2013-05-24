/**
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-打卡
 * @Author	: linhao87@gmail.com
 * @Depend	: core.js、jquery.js(1.7 or later)
 * $Id$
 */

;(function(){
	var punch_main_tip = $('#J_punch_main_tip'),		//我的打卡提示
			mouseleave = false;													//

	$('#J_punch_mine').on('click', function(e){
		//打卡
		e.preventDefault();
		var $this = $(this);

		if($("#J_punch_widget").hasClass('punch_widget_disabled')) {
			return false;
		}else{
			$.getJSON($this.attr('href'), function(data){
				var d = data.data;
				if(data.state == 'success') {
					$("#J_punch_widget").addClass('punch_widget_disabled');
					$this.text('连续'+ d.behaviornum +'天打卡');
					resultTip({
						msg : '恭喜获得' +d.reward,
						follow : $this
					});
				}else if(data.state == 'fail'){
					resultTip({
						error : true,
						msg : data.message[0]
					});
				}
			});
		}
	}).on('mouseenter', function(){
		var $this = $(this);
		mouseleave = false;
		if($("#J_punch_widget").hasClass('punch_widget_disabled') || $this.data('role') == 'space') {
			return false;
		}else{
			if(punch_main_tip.children().length) {
				punch_main_tip.removeClass('dn');
			}else{
				$.getJSON($this.data('punchurl'), function(data){
					if(data.state == 'success') {
						var punch_data = data.data;
						punch_main_tip.html('<div class="tips"><div class="core_arrow_top"><em></em><span></span></div>今天可领取'+ punch_data.todaycNum + punch_data.cUnit + 	
punch_data.cType +'<br />明天可领取'+ punch_data.tomorrowcNum + punch_data.cUnit + punch_data.cType +'<br />连续打卡每天增加'+ punch_data.step +'，上限'+ punch_data.max)
						if(!mouseleave){
								punch_main_tip.removeClass('dn');
						}
					}
				});
			}
			
		}
	}).on('mouseleave', function(){
		mouseleave = true;
		punch_main_tip.addClass('dn');
	});

	//帮ta打卡
	$('#J_punch_friend').on('click', function(e){
		e.preventDefault();
		var $this = $(this),
				punch_friend_pop = $('#J_punch_friend_pop');
		if(punch_friend_pop.length){
			//状态重置
			$('#J_friend_selected').empty();
			punch_friend_pop.find('dd.J_friend_item').removeClass('in');

			//定位 global.js
			popPos(punch_friend_pop);

			//隐藏显示
			punch_friend_pop.show();
		}else{
			$.post($(this).attr('href'), function(data){
				if(ajaxTempError(data, undefined, $this)) {
					$('body').append(data);

					Wind.use('jquery.draggable', 'jquery.form', function(){
						punchFriend();
					});
				}
			}, 'html');
		}
		
	});

	function punchFriend(){
		var punch_friend_pop = $('#J_punch_friend_pop'),
				friend_selected = $('#J_friend_selected'),
				max = punch_friend_pop.data('max');							//最多选择

		//定位 global.js
		popPos(punch_friend_pop);


		//拖拽
		punch_friend_pop.draggable( { handle : '.J_drag_handle'} );

		//关闭
		$('a.J_punch_close').on('click', function(e){
			e.preventDefault();
			punch_friend_pop.hide();
		});

		//展开 收起
		$('dt.J_friend_dt').on('click', function(){
			var $this = $(this),
					parent = $this.parent();
			parent.toggleClass('current').siblings().removeClass('current');

			if(!$this.siblings().length) {
				//未载入
				$.getJSON($this.data('fanurl'), function(data){
					if(data.state == 'success') {
						var arr = [];
						$.each(data.data, function(i, o){
							arr.push('<dd data-id="'+ o.touid +'" data-name="friend" class="J_friend_item" id="J_firend_dd_'+ o.touid +'">'+ o.username +'</dd>')
						});

						$this.after(arr.join(''));
					}
				});
			}
		});

		punch_friend_pop.on('click', 'dd.J_friend_item', function(){
			//选择好友
			var $this = $(this),
					id = $this.data('id');

			if($this.hasClass('disabled')) {
				return false;
			}

			if(!$this.hasClass('in') && friend_selected.children().length < max) {
				friend_selected.append('<li id="J_friend_'+ id +'"><input type="hidden" name="friend[]" value="'+ id +'" /><a href="#">'+ $this.text() +'<span data-id="'+ id +'" class="J_friend_del">×</span></a></li>')
				$this.addClass('in');
			}else{
				$this.removeClass('in');
				$('#J_friend_'+ id).remove();
			}
		}).on('click', '.J_friend_del', function(){
			//删除选择
			$(this).parents('li').fadeOut('fast', function(){
				$(this).remove();
			});
			$('#J_firend_dd_'+ $(this).data('id')).removeClass('in');
		}).on('click', 'a', function(e){
			e.preventDefault();
		});

		//提交
		$('#J_punch_friend_form').ajaxForm({
			dataType : 'json',
			success : function(data){
				if(data.state == 'success') {
					var _data = data.data;
					if(_data) {
						punch_friend_pop.remove();
						var tip = $('<div class="pop_credit_tips J_tips_new"><div class="cc"><a href="" class="pop_close J_pop_mimi_close">关闭</a></div>\
	<div class="content">\
		<p class="title">恭喜您，帮'+ _data.usernames +'打卡成功！</p>\
		<p class="num">奖励：<strong id="J_task_reward" class="green">'+ _data.reward +'</strong></p>\
	</div>\
	<div class="reward"></div></div>');
						tip.appendTo('body').delay(3000).fadeOut('fast', function(){
							$(this).remove();
						});

						//global.js
						popPos($('div.J_tips_new'));

						//关闭提示
						$('a.J_pop_mimi_close').on('click', function(e){
							e.preventDefault();
							$(this).parents('.J_tips_new').remove();
						});
					}

				}else{
					//global.js
					resultTip({
						error : true,
						msg : data.message[0]
					});
				}
			}
		});
		
	}
	
})();
