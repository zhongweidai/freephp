/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-帖子列表
 * @Author	: linhao87@gmail.com
 * @Depend	: jquery.js(1.7 or later), jquery.form, TID
 * $Id: threadForum_index.js 12561 2012-06-23 09:02:50Z hao.lin $
 */

;(function(){

	//帖子菜单
	$('li.J_menu_drop').on('mouseenter', function(e){
		$(this).children('div.J_menu_drop_list').show();
	}).on('mouseleave', function(e){
		$(this).children('div.J_menu_drop_list').hide();
	});

	//var	iframe_poped = false,											//表示帖子面板里的具体操作未弹出
	var posts_checkbox = $('#J_posts_list input.J_check'),		//所有帖子选择框
		post_manage_main = $('#J_post_manage_main'),					//帖子操作面板
		post_checked_count = $('#J_post_checked_count'),				//帖子操作面板里的选中篇数
		is_ie6 = $.browser.msie && $.browser.version < 7,				//ie6
		checkall = $('input.J_check_all');								//全选
		
	//点击帖子框
	posts_checkbox.on('change', function() {
		var $this = $(this), checked_length = posts_checkbox.filter(':checked').length;
		
		//判断选择&取消复选框，帖子操作面板或面板里的具体操作是否已弹出
		if($this.prop('checked') && !$('#J_post_manage_main:visible').length && !$('#J_posts_manage_pop').length) {
		
			//global.js
			popPos(post_manage_main);
			
			//窗口拖动
			post_manage_main.draggable( { handle : '.pop_top'} );
			
		}else if(!checked_length) {

			//取消所有复选框
			post_manage_main.hide();
			Wind.dialog.closeAll();
			//iframe_poped = false; //表示帖子面板里的具体操作未弹出
			
		}
		
		//选中篇数
		post_checked_count.text(checked_length);
		$('#J_read_checked_count').text(checked_length);
	});
	
	
	//帖子操作面板_全选&取消全选
	$('#J_post_manage_checkall').on('click', function(e) {
		e.preventDefault();
		var $this = $(this);
		
		if($this.text() === '全选') {
		
			posts_checkbox.attr('checked', 'checked');
			checkall.attr('checked', 'checked');
			$this.text('取消全选');
			post_checked_count.text(posts_checkbox.length);
			
		}else{
		
			posts_checkbox.removeAttr('checked');
			checkall.removeAttr('checked');
			$this.text('全选');
			post_checked_count.text('0');
			
		}
		
	});
	
	
	//关闭帖子操作面板
	$('#J_post_manage_close').on('click', function(e){
		e.preventDefault();
		post_manage_main.hide();
		posts_checkbox.removeAttr('checked');
		checkall.removeAttr('checked');
		post_checked_count.text('0');
	});
	
	//帖子操作iframe弹窗，考虑创建前台的common.js
	$('a.J_dialog_post').on( 'click',function(e) {
		e.preventDefault();
		var posts_checked = posts_checkbox.filter(':checked'),
				role = $(this).data('role');

		//取消全选，未选择帖子时点击操作弹出提示
		if(!posts_checked.length) {
			resultTip({
				error : true,
				msg : '请至少选择一个帖子'
			});
			return false;
		}
		
		var $this = $(this),
			xid_arr = [];
			
		$.each(posts_checked, function(i, o){
			xid_arr.push($(this).val());
		});

		//区分传输数据
		var _data = {};
		if(role == "read") {
			//阅读页
			_data['tid'] = TID;
			_data['pids[]'] = xid_arr;
		}else{
			_data['tids[]'] = xid_arr;
		}

		$.post($this.prop('href'), _data, function(data) {
			if(ajaxTempError(data)){
				//成功
				Wind.dialog.html(data, {
					id : 'J_posts_manage_pop',
					position	: 'fixed',	//固定定位
					isMask		: false,	//无遮罩
					isDrag : true,
					callback	: function(){
						Wind.use('jquery.form', function(){
							post_manage_main.hide();
							if($.isFunction(window.manageThreads)) {
								manageThreads();
							}else{
								Wind.js(GV.JS_ROOT +'pages/bbs/manage_threads.js?v='+ GV.JS_VERSION);
							}
						});
					}
				});
			}
			
		}, 'html');
		/*Wind.dialog.open( $this.prop('href') ,{
			position	: 'fixed',
			width		: 500,
			onShow		: function(){
			
				post_manage_main.hide(); //隐藏帖子操作面板
				iframe_poped = true; //表示帖子面板里的具体操作已弹出
				
			},
			onClose		: function() {
			
				$this.focus(); //关闭时让触发弹窗的元素获取焦点
				posts_checkbox.removeAttr('checked'); //取消所有帖子选择
				iframe_poped = false; //表示帖子面板里的具体操作未弹出
				
			},
			title		: $this.prop('title'),
			isMask		: false //无遮罩
		});*/
		
	});
	
	
})();