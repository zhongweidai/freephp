/*!
 * PAGE JS
 * @Descript: 前台-页面设计
 * @Depend	: jquery.js(1.7 or later), core,js
 */
 
;(function(){
	var mod_wrap = $('div.J_mod_wrap'),
			design_move_temp = $('#J_design_move_temp'),									//移动模板
			design_move_height = design_move_temp.outerHeight(),
			design_move_half_height = design_move_temp.outerHeight()/2,		//
			design_move_half_width = design_move_temp.outerWidth()/2,			//
			move_lock = true,																							//移动锁定
			layout_a = $('#J_layout_sample a'),
			module_a = $('#J_module_sample a'),
			module_url = null,																						//挂件请求地址
			mudule_box,
			modlayout = {},
			module_id = '',
			uniqueid = $('#J_uniqueid').val(),
			page_id = $('#J_page_id').val(),
			dtype = $('#J_type').val(),
			uri = $('#J_uri').val(),
			title_clone = '',													//标题html_添加
			menu_pop = $('div.J_menu_pop'),						//菜单弹窗
			doc = $(document);

/*
 * 结构拖拽
*/
	var layout_temp = {
		'100' : '<div role="structure__ID" data-lcm="100" class="design_layout_style J_mod_layout" style="display:none;">\
			<div role="editbar" class="design_layout_edit"><a class="J_layout_edit" data-role="title" href="">移除</a></div>\
			<h2 role="titlebar" class="design_layout_hd cc J_layout_hd"><span>区块</span></h2>\
			<div class="design_layout_ct mod_box J_mod_box"></div></div>',
		'1_1' : '<div role="structure__ID" data-lcm="1_1" class="design_layout_style J_mod_layout design_layout_1_1" style="display:none;">\
			<div role="editbar" class="design_layout_edit"><a class="J_layout_edit" data-role="title" href="">移除</a></div>\
			<h2 role="titlebar" class="design_layout_hd cc J_layout_hd"><span>区块</span></h2>\
			<div class="design_layout_ct"><div class="design_layout_1_1_left mod_box J_mod_box"></div><div class="design_layout_1_1_right mod_box J_mod_box"></div></div></div>',
		'1_2' : '<div role="structure__ID" data-lcm="1_2" class="design_layout_style J_mod_layout design_layout_1_2" style="display:none;">\
			<div role="editbar" class="design_layout_edit"><a class="J_layout_edit" data-role="title" href="">移除</a></div>\
			<h2 role="titlebar" class="design_layout_hd cc J_layout_hd"><span>区块</span></h2>\
			<div class="design_layout_ct"><div class="design_layout_1_2_left  mod_box J_mod_box"></div><div class="design_layout_1_2_right mod_box J_mod_box"></div></div></div>',
		'2_1' : '<div role="structure__ID" data-lcm="2_1" class="design_layout_style J_mod_layout design_layout_2_1" style="display:none;">\
			<div role="editbar" class="design_layout_edit"><a class="J_layout_edit" data-role="title" href="">移除</a></div>\
			<h2 role="titlebar" class="design_layout_hd cc J_layout_hd"><span>区块</span></h2>\
			<div class="design_layout_ct"><div class="design_layout_2_1_left mod_box J_mod_box"></div><div class="design_layout_2_1_right mod_box J_mod_box"></div></div></div>',
		'1_3' : '<div role="structure__ID" data-lcm="1_3" class="design_layout_style J_mod_layout design_layout_1_3" style="display:none;">\
			<div role="editbar" class="design_layout_edit"><a class="J_layout_edit" data-role="title" href="">移除</a></div>\
			<h2 role="titlebar" class="design_layout_hd cc J_layout_hd"><span>区块</span></h2>\
			<div class="design_layout_ct"><div class="design_layout_1_3_left mod_box J_mod_box"></div><div class="design_layout_1_3_right mod_box J_mod_box"></div></div></div>',
		'3_1' : '<div role="structure__ID" data-lcm="3_1" class="design_layout_style J_mod_layout design_layout_3_1" style="display:none;">\
			<div role="editbar" class="design_layout_edit"><a class="J_layout_edit" data-role="title" href="">移除</a></div>\
			<h2 role="titlebar" class="design_layout_hd cc J_layout_hd"><span>区块</span></h2>\
			<div class="design_layout_ct"><div class="design_layout_3_1_left mod_box J_mod_box"></div><div class="design_layout_3_1_right mod_box J_mod_box"></div></div></div>',
		'1_1_1' : '<div role="structure__ID" data-lcm="1_1_1" class="design_layout_style J_mod_layout design_layout_1_1_1" style="display:none;">\
			<div role="editbar" class="design_layout_edit"><a class="J_layout_edit" data-role="title" href="">移除</a></div>\
			<h2 role="titlebar" class="design_layout_hd cc J_layout_hd"><span>区块</span></h2>\
			<div class="design_layout_ct"><div class="design_layout_1_1_1_left mod_box J_mod_box"></div><div class="design_layout_1_1_1_cont mod_box J_mod_box"></div><div class="design_layout_1_1_1_right mod_box J_mod_box"></div></div></div>',
		'1_1_1_1' : '<div role="structure__ID" data-lcm="1_1_1_1" class="design_layout_style J_mod_layout design_layout_1111" style="display:none;">\
			<div role="editbar" class="design_layout_edit"><a class="J_layout_edit" data-role="title" href="">移除</a></div>\
			<h2 role="titlebar" class="design_layout_hd cc J_layout_hd"><span>区块</span></h2>\
			<div class="design_layout_ct"><div class="design_layout_1111_left mod_box J_mod_box"></div><div class="design_layout_1111_left mod_box J_mod_box"></div><div class="design_layout_1111_right mod_box J_mod_box"></div><div class="design_layout_1111_right mod_box J_mod_box"></div></div></div>'//,
		/*'tab' : '<div role="structure__ID" data-lcm="tab" class="design_layout_style J_mod_layout J_tab_wrap" style="display:none;">\
			<div role="editbar" class="design_layout_edit"><a class="J_layout_edit" data-structure="tab" data-role="title" href="">移除</a></div>\
			<h2 role="titlebar" class="design_layout_hd cc J_layout_hd">\
				<ul class="J_tabs_nav"><li class="current"><a href="">栏目1</a></li><li><a href="">栏目2</a></li></ul>\
			</h2>\
			<div class="J_tabs_ct"><div class="design_layout_ct mod_box J_mod_box"></div>\
			<div class="design_layout_ct mod_box J_mod_box" style="display:none;"></div></div></div>'*/
	},
	insert_holder = $('<div id="J_insert_holder" class="insert_holder"></div>'),
	layout_class_mapping = {
		'100' : 'design_layout_style J_mod_layout',
		'1_1' : 'design_layout_style J_mod_layout design_layout_1_1',
		'1_2' : 'design_layout_style J_mod_layout design_layout_1_2',
		'2_1' : 'design_layout_style J_mod_layout design_layout_2_1',
		'1_3' : 'design_layout_style J_mod_layout design_layout_1_3',
		'3_1' : 'design_layout_style J_mod_layout design_layout_3_1',
		'1_1_1' : 'design_layout_style J_mod_layout design_layout_1_1_1',
		'1_1_1_1' : 'design_layout_style J_mod_layout design_layout_1111',
		'tab' : 'design_layout_style J_mod_layout'
	};

	//移除头部的消息和发帖下拉
	$('#J_head_msg_pop, #J_head_forum_pop, #J_head_user_menu, #J_head_nav_my_list').remove();

	//离开页面提示
	window.onbeforeunload = function() {
		return '您确定要退出页面设计状态？确定退出后，已修改的数据将不会保存。';
	}

	//已有编辑挂件 循环结构数据
	var mod_wrap_len = mod_wrap.length;
	for(i=0;i<mod_wrap_len;i++){
		if(mod_wrap[i].lastChild){
			var layouts = $(mod_wrap[i]).find('.J_mod_layout');
			eachModLayout(layouts.parent());
		}
	}

	//防止链接元素被拖拽
	$('#J_layout_sample a, #J_module_sample a').each(function(){
		this.ondragstart = function (e) {
			return false;
		};
	});



/*
 * 点击结构项
*/
	layout_a.on('mousedown', function(e){

		//解锁
		move_lock = false;

		//显示移动模板
		design_move_temp.show().css({
			left : e.pageX - 20,
			top : e.pageY - 20
		}).data('name', $(this).data('name'));

		//鼠标拖动
		doc.off('mousemove').on('mousemove', function(e){
			if(!move_lock) {
				var leftx = e.pageX - 10,
						topx = e.pageY - 10;

				//模板定位
				design_move_temp.show().css({
					left : leftx,
					top : topx
				});

				var move_c_left = leftx + design_move_half_width,
						move_c_top = topx + design_move_half_height;

				moveTemp(design_move_temp, move_c_left, move_c_top);

			}
		});

	});


/*
 * 点击挂件
*/
	var model;
	module_a.on('mousedown', function(e){
		module_url = this.href;
		model = this.id;
		//解锁
		move_lock = false;

		//显示移动模板
		design_move_temp.show().css({
			left : e.pageX - 20,
			top : e.pageY - 20
		});

//

		//鼠标拖动
		doc.off('mousemove').on('mousemove', function(e){
			if(!move_lock) {
				var leftx = e.pageX - 10,
						topx = e.pageY - 10;

				//模板定位
				design_move_temp.show().css({
					left : leftx,
					top : topx
				});

				var move_c_left = leftx + design_move_half_width,
						move_c_top = topx + design_move_half_height;

				moveTemp(design_move_temp, move_c_left, move_c_top);

			}
		});

	});


	//鼠标抬起 取消移动
	doc.on('mouseup', function(e){
		move_lock = true;

		doc.off('mousemove');
		
		var wrap = $('#J_insert_holder').parent(),
				insert_holder_visible = $('#J_insert_holder:visible'),
				layout_move = $('#J_layout_move');												//已有的结构

		if(insert_holder_visible.length) {
			//有占位模板

			if(layout_move.length) {
				//移动已有结构
				layout_move.attr({
					'id' : layout_move.data('randomid'),
					'style' : layout_move.data('orgstyle')		//恢复样式
				});
				layout_move.hide().insertAfter(insert_holder_visible).fadeIn('200');
				insert_holder_visible.remove();

				/*//父框架
				if(layout_move.parent().hasClass('J_mod_wrap')) {
					//layout_move.parent().removeClass('tempplace').addClass('tempplacein');
				}*/
						
				eachModLayout(layout_move.parent());
			}else{
				if(module_url) {
					//请求挂件
					mudule_box = insert_holder.parent();
					getModule(mudule_box, model);
					insert_holder.remove();
					
				}else{
					//插入新结构
					var randomid = testrandom(),
							name = design_move_temp.data('name');
					$(layout_temp[name].replace(/_ID/, randomid)).insertAfter(insert_holder).fadeIn('200').attr('id', randomid);
					insert_holder_visible.remove();
					//wrap.removeClass('tempplace').addClass('tempplacein');
					eachModLayout(wrap);

					if(name == 'tab') {
						var $randomid = $('#'+ randomid);
						tabsBind($randomid);
					}
					
				}
				

				//dragLayout();
			}
			
		}else{
			//恢复原结构位置
			layout_move.removeAttr('style').attr({
				'id' : layout_move.data('randomid'),
				'style' : layout_move.data('orgstyle')		//恢复样式
			});
		}

		//隐藏拖拽
		design_move_temp.hide();
		module_url = null;

	});

/*
 * 拖拽已添加的结构
*/
	mod_wrap.off('mousedown').on('mousedown', '.J_layout_hd', function(e){
			var wrap = $(this).parent(),
					wrap_left = wrap.offset().left,
					wrap_top = wrap.offset().top,
					org_style = wrap.attr('style');			//原样式

			//点击链接不拖动
			if(e.target.tagName.toLowerCase() == 'a') {
				return false;
			}

			move_lock = false;

			wrap.css({
				width : wrap.width(),
				position : 'absolute',
				zIndex : 1,
				opacity : 0.6,
				left :wrap_left,
				top : wrap_top
			}).data({
				'randomid' : wrap.attr('id'),
				'orgstyle' : org_style ? org_style : ''
			}).attr('id', 'J_layout_move');

			var dis_left = e.pageX - wrap_left,
					dis_top = e.pageY - wrap_top;
//console.log(e.pageY)
			doc.off('mousemove').on('mousemove', function(e){
				if(move_lock) {
					return false;
				}
				window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();
				var leftx = e.pageX - 10,
						topx = e.pageY - 10;

				//模板定位
				wrap.css({
					left : e.pageX - dis_left,
					top : e.pageY - dis_top
				});

				//var elem_half_left = parseInt(wrap.css('left')) + wrap.outerWidth()/2,		//模板中心点x轴距
						//elem_half_top = parseInt(wrap.css('top')) + wrap.outerHeight()/2;			//模板中心点y轴距
				var elem_half_left = parseInt(wrap.css('left')) + 35,		//拖动触发点x轴距
						elem_half_top = parseInt(wrap.css('top')) + 35;			//拖动触发点y轴距
				moveTemp(wrap, elem_half_left, elem_half_top);
			});
		});


/*
 * 结构编辑
*/
	//显示隐藏结构编辑
	mod_wrap.on('mouseenter', 'div.J_mod_layout', function(e){
		e.stopPropagation();
		if(!move_lock) {
			return false;
		}

		var tar = $(e.target);

		//移进子结构
		
		if(tar.hasClass('J_mod_box') || tar.parents('div.J_mod_box').length) {
			$('a.J_layout_edit').hide();
		}

		var edit = $(this).children().children('a.J_layout_edit');
		edit.show();
		edit.parent().show();
	}).on('mouseleave', 'div.J_mod_layout', function(e){
		if(!move_lock) {
			return false;
		}
		var rel_tar = $(e.relatedTarget);

		//移进父结构
		if(rel_tar.hasClass('J_mod_layout')) {
			rel_tar.children().children('a.J_layout_edit').show().parent().show();
		}
		if(rel_tar.parents('div.J_mod_layout').length) {
			rel_tar.parents('div.J_mod_layout').children().children('a.J_layout_edit').show().parent().show();
		}

		var edit = $(this).children().children('a.J_layout_edit');
		edit.hide();
		edit.parent().hide();
	});

	//显示隐藏挂件编辑
	mod_wrap.on('mouseenter', 'div.J_mod_box', function(e){
		$('a.J_layout_edit').hide();
		$(this).find('a.J_module_edit').show();
	}).on('mouseleave', 'div.J_mod_box', function(e){
		$(this).find('a.J_module_edit').hide();

		/*var rel_tar = $(e.relatedTarget);
		if(rel_tar.hasClass('J_mod_layout')) {
			rel_tar.children().children('a.J_layout_edit').show().parent().show();
		}

		if(rel_tar.parents('div.J_mod_layout').length) {
			rel_tar.parents('div.J_mod_layout').children().children('a.J_layout_edit').show().parent().show();
		}*/
	});

	
	var layout_edit_pop = $('#J_layout_edit_pop'),											//弹窗
			layout_edit_nav = $('#J_layout_edit_nav'),											//弹窗导航
			layout_edit_contents = $('#J_layout_edit_contents');						//弹窗内容
			design_name = $('#J_design_name'),															//弹窗名
			design_del = $('#J_design_del');																//删除

	layout_edit_pop.draggable( { handle : '.J_drag_handle'} );

	//点击结构编辑
	var layout_id, btn, structure;
	mod_wrap.on('click', 'a.J_layout_edit', function(e){
		e.preventDefault();
		var $this = $(this),
				index = 0,
				content = layout_edit_contents.children(':eq('+ index +')'),
				current_nav = layout_edit_nav.children(':eq('+ index +')'),
				role = current_nav.children().data('role');

				structure = $this.data('structure'),

		menu_pop.hide();

		//tab内容 暂不缓存
		layout_edit_nav.find('a').data('load', false);

		layout_id = $this.parents('.J_mod_layout').attr('id'),
		design_name.text('结构管理');
		design_del.text('删除该结构').data('role', 'layout').show();
		btn = $this;
		layout_edit_nav.children().children().data('load', false);

		//global.js
		popPos(layout_edit_pop);

		current_nav.click().show().children().attr('data-submit', false);	//默认未提交
		current_nav.siblings().hide();
		current_nav.next().next().show();
		//layout_edit_nav.children(':eq('+ (index+1) +')').children();
		/*layout_edit_nav.children(':lt('+ index +')').hide();
		layout_edit_nav.children(':gt('+ (index+1) +')').hide();*/

		//if(!$this.data('load')) {
			//还没加载或已提交
			$.post(LAYOUT_EDID_TITLE, {name : layout_id, page_id : page_id}, function(data){
				if(ajaxTempError(data)) {
					content.html(data);

					//jquery.scrollFixed
					content.find('.J_scroll_fixed').scrollFixed();

					$this.attr('data-load', true);	//
					//$this.data('submit', true);
					//btn.data('datatitle', data);	//存入html

					//editFn(content, role);
					editFn(content, 'layouttitle');
					commonFn();

					title_clone = content.find('div.J_mod_title_cont').clone().html();		//添加新标题的html复制
					$('div#J_layout_edit_pop').hide();
				}
			});
		/*}else{
			//已请求过
			layout_edit_contents.find('form').resetForm();
		}*/
		$('div#J_layout_edit_pop').hide();
		$('a#J_design_del').click();
	});

	//点击挂件编辑
	mod_wrap.on('click', 'a.J_module_edit', function(e){
		e.preventDefault();
		var $this = $(this),
				//index = 3,
				//content = layout_edit_contents.children(':eq('+ index +')'),
				current_nav = layout_edit_nav.children(':eq(0)'),
				current_content = layout_edit_contents.children(':eq(0)'),
				role = current_nav.children().data('role');

		menu_pop.hide();

		//tab内容 暂不缓存
		layout_edit_nav.find('a').data('load', false);
		//layout_id = $this.parents('.J_mod_layout').attr('id'),
		module_id = this.id;
		design_name.text('挂件管理');
		$.post(MODULE_INFO, {
			module_id : module_id,
			page_id:page_id
		}, function(data){
			if(data.state == 'success') {
				design_name.text('挂件管理['+ module_id + '][' + data.data + ']');
			} else{
				//global.js
				resultTip({
					error : true,
					msg : data.message[0]
				});
			}
		}, 'json');
		//design_name.text('挂件管理-'+ module_id);
		mudule_box = $this.parent();
		design_del.text('删除该挂件').data('role', 'module').show();
		//btn = $this;
		//layout_edit_nav.children().children().data('load', false);

		//global.js

		//判断tab显示
		$.post(MODULE_EDIT_JUDGE, {
			module_id : module_id,
			page_id:page_id
		}, function(data){
			if(data.state == 'success') {
				//global.js
				popPos(layout_edit_pop);

				var rank = parseInt(data.data);			//权限值
				if(rank < 4) {
					$('#J_design_del').hide();
				}

				current_nav.click().show()//.children().attr('data-submit', false);	//默认未提交
				layout_edit_nav.children().show();

				//获取第一个tab内容
				$.post(current_nav.children().attr('href'), {
					module_id : module_id,
					page_id:page_id
				}, function(data){
					if(ajaxTempError(data)) {
						current_content.html(data);
						current_nav.children().data('load', true);
						
						//jquery.scrollFixed
						current_content.find('.J_scroll_fixed').scrollFixed();

						//editFn(content, role);
						commonFn();
					}
				});

				//判断显示的tab项
				if(rank == 2) {
					layout_edit_nav.children(':gt(2)').hide();
				}else if(rank >= 3){
					layout_edit_nav.children().show();
				}
				$('#J_layouttitle, #J_layoutstyle, #J_moduleproperty_add').hide();

			}else{
				//global.js
				resultTip({
					error : true,
					msg : data.message[0]
				});
			}
		}, 'json');
		

		
		

		
		/**/
	});


	//点击弹窗tab
	layout_edit_nav.find('a').on('click', function(){
		var $this = $(this),
				role = $this.data('role'),
				type = $this.data('type'),
				index = $this.parent().index(),
				current_content = layout_edit_contents.children(':eq('+ index +')');		//当前内容区

		if(!$this.data('load')) {
			//还没加载
			//current_content.html('<div class="pop_loading"></div>');

			if(role == 'layoutstyle') {
				//结构样式
				$.post(LAYOUT_EDID_STYLE, {name : layout_id}, function(data){
					if(ajaxTempError(data)) {
						current_content.html(data)/*.find('form').ajaxForm({
							dataType : 'json',
							success : function(data){

							}
						})*/;

						//jquery.scrollFixed
						current_content.find('.J_scroll_fixed').scrollFixed();

						$this.data('load', true);
						editFn(current_content, role, '');
						commonFn();
					}
				});
			}else if(role == 'module') {
				//挂件tab
				$.post(this.href, {module_id : module_id}, function(data){
					if(ajaxTempError(data)) {
						current_content.html(data)/*.find('form').ajaxForm({
							dataType : 'json',
							success : function(data){

							}
						})*/;

						//jquery.scrollFixed
						current_content.find('.J_scroll_fixed').scrollFixed();

						//挂件模板 global.js
						buttonStatus($('#J_design_temp_name'), $('#J_design_temp_sub'));

						$this.data('load', true);
						commonFn();

						if(type == 'title') {
							title_clone = current_content.find('div.J_mod_title_cont').clone().html();		//添加新标题的html复制
						}
					}
				});
			}
		}

		//layout_edit_contents.children(':eq('+ $this.parent().index() +')').children()
	});

	//关闭编辑
	$('#J_layout_edit_close').on('click', function(e){
		e.preventDefault();
		popHide();

		//先清空为loading效果
		layout_edit_contents.children().html('<div class="pop_loading"></div>');
	});

	//删除结构&挂件
	design_del.on('click', function(e){
		e.preventDefault();
		var role = $(this).data('role');
		if(role == 'layout') {
			//删除结构
			if(confirm('删除结构后将清空此结构下的所有挂件数据，确定删除？')) {
				//console.log();
				$('#'+ layout_id).fadeOut(200, function(){
					$(this).remove();
				});

				popHide(true);
			}
		}else{
			//删除挂件
			if(confirm('您确定要删除本挂件吗？删除后将不可恢复！')) {
				//global.js
				ajaxMaskShow();

				$.post(MODULE_BOX_DEL, {module_id: module_id, page_id :page_id}, function(data){
					//global.js
					ajaxMaskRemove();

					if(data.state == 'success') {
						mudule_box.children().fadeOut(200, function(){
							$(this).remove();
						});
					}else{
						//global.js
						resultTip({
							error : true,
							msg : data.message[0]
						});
					}
					popHide(true);
				}, 'json');


			}
		}
		
	});

	//分别设置
	layout_edit_contents.on('click', 'input.J_set_part', function(){
		var $this = $(this),
				last = $this.parents('dd').children(':last'),
				prev_last = last.prev();
		if($this.prop('checked')) {
			last.show();
			prev_last.hide();
		}else{
			last.hide();
			prev_last.show();
		}
	});


	//弹窗内方法
	function editFn(content, role){
		//form添加标识
		content.children('form').attr('data-role', role);

		//提交
		$('form.J_design_layout_form').ajaxForm({
			dataType : 'json',
			data : {
				structure : structure ? structure : ''
			},
			beforeSubmit : function(){
				//global.js
				ajaxMaskShow();
			},
			success : function(data, statusText, xhr, $form){
				if(data.state == 'success') {
					//global.js
					ajaxMaskRemove();

					popHide();

					//提交后不tab内容重载
					layout_edit_nav.find('a').data('load', false);
					layout_edit_contents.children(':not(:visible)').html('<div class="pop_loading"></div>');
					
					var layout = $('#'+ layout_id),																		//编辑的结构
							//vis_content = layout_edit_contents.children(':visible'),			//当前编辑内容
							role = $form.data('role');																		//编辑的区域角色

					if(role == 'layouttitle') {
						//写入标题
						layout.find('.design_layout_hd, J_layout_hd').html(data.data);
						btn.attr('data-titleload', false);
						//重新存入html
						//btn.data('datatitle', vis_content.html());
					}else if(role == 'layoutstyle') {
						//写入样式
						var prev_style = layout.prev('style'),
								layout_style = '<style type="text/css" class="_tmp">#'+ data.data.styleDomId[0] +'{'+ data.data.styleDomId[1] +'}#'+ data.data.styleDomId[0] +' a{'+ data.data.styleDomIdLink[1] +'}</style>';
						if(prev_style.length) {
							//已有style标签
							prev_style.replaceWith(layout_style);
						}else{
							layout.before(layout_style);
						}

						if(data.data.styleDomClass) {
							//写入class
							layout.attr('class', layout_class_mapping[layout.data('lcm')]+' '+data.data.styleDomClass);
						}
						
					}
					
				}
				
			}
		});

	}


/*
 * 弹窗内公共方法&组件
*/
	function commonFn(){
		//拾色器 colorPicker.js
		$('span.J_color_pick').each(function(){
			$(this).colorPicker({
				zIndex : 99999,
				callback:function(color) {
					$(this).find('em').css('background-color',color);
					$(this).next('input:hidden').val(color);
				}
			});
		});

		//字体配置
		if($('.J_font_config').length) {
			var elem = $('.color_pick'),panel = elem.parent('.J_font_config');
			elem.colorPicker({
				zIndex : 99999,
				callback:function(color) {
					elem.find('em').css('background-color',color);
					panel.find('.J_case').css('color',color);
					panel.find('.J_hidden_color').val(color);
				}
			});

			//加粗、斜体、下划线的处理
			$('.J_bold,.J_italic,.J_underline').on('click',function() {
				var panel = $(this).parents('.J_font_config');
				var c = $(this).data('class');
				if( $(this).prop('checked') ) {
					panel.find('.J_case').addClass(c);
				}else {
					panel.find('.J_case').removeClass(c);
				}
			});
		}

		//全选
		var design_check_all = $('input.J_design_check_all');
		if(design_check_all.length) {
			var check_wrap = design_check_all.parents('table'),
					checks = check_wrap.find('input.J_design_check');
			//点击全选
			design_check_all.on('change', function(){
				if(this.checked) {
					checks.prop('checked', true);
				}else{
					checks.prop('checked', false);
				}
			});

			//点击单项
			checks.on('change', function(){
				if(this.checked) {
					if(checks.filter(':checked').length == checks.length) {
						design_check_all.prop('checked', true);
					}
				}else{
					design_check_all.prop('checked', false);
				}
			});
		}
	}

	
	//移动模板结构
	function moveTemp(move_elem, left, top){

		//获取移动中心点元素(有闪烁手势问题)
		move_elem.hide();
		var elem_point = document.elementFromPoint(left, top - $(document).scrollTop());
		move_elem.show();

		var $elem_point = $(elem_point),
				wrap_parent = $elem_point.parents('.J_mod_wrap');

		if($elem_point.hasClass('J_mod_wrap')) {

			//位置判断 非移动挂件
			if(!module_url) {
				insertJudge($elem_point, top);
			}

		}else if(wrap_parent.length){
			//设计区的J_mod_box内
			if($elem_point.hasClass('J_mod_box')) {
				if(module_url){
					//移动模板且为空
					if(!$elem_point.children().length) {
						insertJudge($elem_point, top);
					}
					
				}else{
					var parents_mod_layout_len = $elem_point.parentsUntil('div.J_mod_wrap').filter('.J_mod_layout').length,	//已有的J_mod_layout层数
							move_mod_layout_len = move_elem.find('.J_mod_layout').length;																				//移动模板里的J_mod_layout层数
					if(parents_mod_layout_len < 2 && move_mod_layout_len < 1){
						//设计区小于2层

						//位置判断
						insertJudge($elem_point, top);

						//重新计算区域结构数据
						//eachModLayout($elem_point.parents('div.J_mod_wrap'));
					}
				}

				

			}

		}else{
			//设计区外
			insert_holder.remove();
		}
	}

	//位置判断
	function insertJudge(elem, top){
		//设计区为空
		if(!elem.find('#J_insert_holder').length) {
			elem.append(insert_holder);
		}

		//设计区有子结构
		if(elem.find('.J_mod_layout').length) {
			var layout_arr = elem.data('data'),								//当前区域结构数据
					layout_arr_len = layout_arr.length,
					insert_wrap = elem;

			for(i=0; i<layout_arr_len; i++) {

				if(top > layout_arr[i][0] && top < layout_arr[i][1]) {
					//进入可添加区内

					if(i===0) {
						//顶部
						insert_holder.prependTo(elem);
					}else{
						insert_holder.insertAfter(elem.children('.J_mod_layout:eq('+ (i-1) +')'));
					}

					break;
				}
			}	
		}

		//重新计算区域结构数据
		eachModLayout(elem);
	}

	//获取挂件
	function getModule(mudule_box, model){
		//index = 7,																															//当前tab项索引值
		var current_nav = $('#J_moduleproperty_add'),							//当前tab项
				current_content = layout_edit_contents.children(':eq('+ current_nav.index() +')');			//当前内容项
		current_nav.show().click().siblings().hide();

		

		menu_pop.hide();

		design_name.text('挂件管理');
		design_del.text('删除该挂件').data('role', 'module');

		design_del.hide();
		//global.js
		popPos(layout_edit_pop);

		$.post(module_url, {
			page_id :page_id,
			model : model
		}, function(data){
			if(ajaxTempError(data)) {
				current_content.html(data);

				//jquery.scrollFixed
				current_content.find('.J_scroll_fixed').scrollFixed();

				//提交
				$('form.J_design_module_form').ajaxForm({
					dataType : 'json',
					beforeSubmit : function(){
						ajaxMaskShow();
					},
					success : function(data, statusText, xhr, $form){
						if(data.state == 'success') {
							module_id = data.data;

							//写入挂件html
							$.post(MODULE_BOX_UPDATE, {module_id : module_id, page_id :page_id}, function(data){
								if(ajaxTempError(data)) {
										ajaxMaskRemove();
										popHide();
										mudule_box.html('<a id="'+ module_id +'" class="J_module_edit" style="display:none;" href="#">编辑</a><div class="design_list_1 J_module_con_list" id="module_'+ module_id +'">'+ data +'</div>').attr('id', 'J_mod_'+ module_id);
								}
							});
						}else if(data.state == 'fail'){
							//global.js
							ajaxMaskRemove();
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

	//更新弹窗列表内容
	function updatePopList(updatemod){
		$.post(layout_edit_nav.children('.current').children().attr('href'), {
			module_id : module_id,
			page_id:page_id
		}, function(data){
			//global.js
			ajaxMaskRemove();

			if(ajaxTempError(data)) {
				//重新列表
				var current_content = layout_edit_contents.children(':visible');
				current_content.html(data);
				current_content.find('.J_scroll_fixed').scrollFixed();
				commonFn();

				//是否更新挂件
				if(updatemod && mudule_box.children().length > 1) {
					updateModuleList(module_id, mudule_box);
				}
			}
		});

	}

	//更新挂件列表内容
	function updateModuleList(module_id, mudule_box){
		//有列表内容
		var list = mudule_box.find('.J_module_con_list'),
				clone = list.clone();
		list.html('<div class="pop_loading"></div>');
		$.post(MODULE_BOX_UPDATE, {module_id : module_id, page_id :page_id}, function(data){
			//global.js
			ajaxMaskRemove();

			if(ajaxTempError(data), function(){list.html(clone)}) {
				list.html(data);
			}
		});
	}


	


	//循环结构坐标数据
	function eachModLayout(wrap){
		var arr = [],
				childs = wrap.children('.J_mod_layout').filter(':not(#J_layout_move)');

		childs.filter(':not(#J_layout_move)').each(function(i, o){
			var scroll_top = $(document).scrollTop(),
					win_top = $(this).offset().top,
					height = $(this).outerHeight(),
					mb = parseInt($(this).css('marginBottom').replace('px', '')),
					height_bot = win_top + height,
					height_mb = win_top + height + mb;
					//console.log(win_top);
			if(i <= 0){
				//第一个
				arr.push([0, win_top]);
				arr.push([height_bot, height_mb]);
			}else if(i > 0 && i === (childs.length-1)){
				//最后一个
				arr.push([height_bot, 9999]);
			}else{
				arr.push([height_bot, height_mb]);
			}
			
		});

		wrap.data('data', arr);
		//console.log(wrap.data());
		//modlayout[wrap.data('id')] = arr;
		//console.log(modbox);
	}

/*
 * 生成6位随机字母
*/
	function testrandom(){
		var cha = '';
		for(i=1;i<=6;i++){
			cha += String.fromCharCode(Math.floor( Math.random() * 26) + "a".charCodeAt(0));
		}
		return cha;
	}


/*
 * 挂件管理
*/
	
	layout_edit_contents.on('click', 'a.J_data_edit', function(e){
		//显示内容编辑
		e.preventDefault();
		var id = $(this).data('id'),
				//current_edit = $('#J_module_data_'+ id),					//当前编辑内容
				module_data_list = $('#J_module_data_list'),			//显示内容列表
				module_data_edit = $('#J_module_data_edit');			//显示内容编辑区

		module_data_list.hide();
		module_data_edit.show();
		$.get(this.href, function(data){
			//global.js
			if(ajaxTempError(data)) {
				module_data_edit.html(data);
				
				commonFn();
			}
		});

	}).on('click', 'button.J_mod_title_add', function(e){
		//添加新标题
		e.preventDefault();
		var wrap = $(this).parent().next();		//默认项
		$('<div style="padding:5px;" class="fr"><a style="margin:5px;" class="fr J_mod_title_del" href="">删除此标题</a></div><div class="J_title_cont_wrap">'+title_clone +'</div>').insertAfter(wrap).find('input, .J_color_pick >em').val('').removeAttr('style');
	}).on('click', 'a.J_mod_title_del', function(e){
		//删除标题
		e.preventDefault();
		var del_wrap = $(this).parent();
		del_wrap.next().remove();del_wrap.remove();
	}).on('click', '.J_pages_wrap > a', function(e){
		//推送翻页
		e.preventDefault();

		//global.js
		ajaxMaskShow();

		$.post(this.href, function(data){
			//global.js
			ajaxMaskRemove();

			if(ajaxTempError(data)) {
				$('#J_data_push_cont').html(data);
				commonFn();
			}
		});
	}).on('click', 'a.J_design_data_ajax', function(e){
		//公共ajax更新
		e.preventDefault();
		var noupdate = $(this).data('noupdate');		//是否更新挂件列表

		//global.js
		ajaxMaskShow();

		$.post(this.href, function(data){
			if(data.state == 'success') {
				//更新弹窗列表
				updatePopList(noupdate ? false : true);
			}else if(data.state == 'fail'){
				//global.js
				ajaxMaskRemove();
				resultTip({
					error : true,
					msg : data.message[0]
				});
			}
		}, 'json');
	}).on('click', '#J_design_temp_sub', function(e){
		//挂件模板保存
		e.preventDefault();
		var $this = $(this),
				temp_save = $('#J_design_temp_sub'),
				temp_name = $('#J_design_temp_name');

		//global.js
		ajaxMaskShow();

		$.post($this.data('action'), {
			tpl : $('#J_design_temp_tpl').val(),
			tplname : $('#J_design_temp_name').val(),
			module_id : module_id
		}, function(data){
			//global.js
			ajaxMaskRemove();

			if(data.state == 'success') {
				//global.js
				resultTip({
					msg : '保存成功',
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
	}).on('click', 'div.J_sign_items', function(e){
		//点击模板挂件属性
		$('#J_design_temp_tpl').insertContent(this.innerHTML);
	});

	//返回内容列表
	layout_edit_contents.on('click', '#J_module_data_back', function(e){
		e.preventDefault();
		$('#J_module_data_list').show();
		$('#J_module_data_edit').hide().html('<div class="pop_loading"></div>');
	});

	//挂件编辑提交
	layout_edit_contents.on('click', 'button.J_module_sub', function(e){
		e.preventDefault();
		var $this = $(this),
				form = $this.parents('form'),
				role = $this.data('role'),
				action = $this.data('action'),
				update = $this.data('update');			//更新对象

		form.ajaxSubmit({
			url : action ? action : form.attr('action'),
			dataType : 'json',
			beforeSubmit : function(){
				ajaxMaskShow();
			},
			success : function(data, statusText, xhr, $form){
				if(data.state == 'success') {
					//提交后不tab内容重载
					layout_edit_nav.find('a').data('load', false);
					layout_edit_contents.children(':not(:visible)').html('<div class="pop_loading"></div>');

					if(update == 'mod'){
						//更新挂件列表
						updateModuleList(module_id, mudule_box);

						popHide(true);
					}else if(update == 'title'){
						//编辑标题
						ajaxMaskRemove();
						var tmode = mudule_box.find('.tmode_list');
						if(tmode.find('h2').length) {
							tmode.find('h2').replaceWith(data.data);
						}else{
							tmode.prepend(data.data);
						}

						popHide(true);
					}else if(update == 'style'){
						//编辑样式
						ajaxMaskRemove();
						
						//更新class
						if(data.data.styleDomClass) {
							mudule_box.attr('class', 'box J_mod_box '+ data.data.styleDomClass);
						}

						//移除老的style
						mudule_box.find('style').remove();

						//写入style 样式
						mudule_box.prepend('<style type="text/css" class="_tmp">#'+ data.data.styleDomId[0] +'{'+ data.data.styleDomId[1] +'}#'+ data.data.styleDomId[0] +' a{'+ data.data.styleDomIdLink[1] +'}</style>');

						popHide(true);
					}else{
						ajaxMaskRemove();
						if(update == 'pop') {
							//更新弹窗列表
							updatePopList(false);
						}else if(update == 'all') {
							//更新弹窗&挂件列表
							updatePopList(true);
						}
					}
				}else if(data.state == 'fail'){
					//global.js
					ajaxMaskRemove();
					resultTip({
						error : true,
						msg : data.message[0]
					});
				}
			}
		});
	});

/*
 * 隐藏弹窗并恢复内容为loading
*/
	function popHide(loading){
		layout_edit_pop.hide();
		if(loading) {
			layout_edit_contents.children().html('<div class="pop_loading"></div>');
		}
	}

/*
 * 右上角菜单
*/
	//global.js
	hoverToggle({
		wrap : $('#J_top_design'),					//容器
		a : $('#J_design_top_arrow'),		//hover元素
		b : $('#J_design_top_list')
	});


/*
 * 保存
*/
	var savedata = {}, dialog_html = '';
	savedata.page_id = page_id;
	savedata.uri = uri;
	savedata.uniqueid = uniqueid;

	//点击保存
	$('#J_design_submit').on('click', function(){
		var $this = $(this);

		//获取提交内容
		var ii = 0;
		for(i=0;i<mod_wrap.length;i++){
			var clone = $(mod_wrap[i]).clone(),
					box = clone.find('div.J_module_con_list');
			clone.find('div.J_mod_layout').removeAttr('style');			//外框的style不提交
			clone.find('style').remove();														//拿掉style标签

			//替换挂件内容
			for(k=0;k<box.length;k++) {
				var _id = box[k].id
				if(_id) {
					//var el_module = document.createElement('module');
					widgetid = _id.replace('module_', '');
					var el_module = ' {pc:widget action="detail" widgetid="'+widgetid+'" pageid="$page_id"}{/pc}';
					$(box[k]).html(el_module);
				}
			}

			var html = clone[0].innerHTML;
			savedata['segment['+ mod_wrap[i].id +']'] = html;
			if(html) {
				ii++;
			}
		}

		if(dtype == 'forum') {
			dialog_html = '<div class="pop_cont">是否应用于其他**？</div>\
				<div class="pop_bottom">\
					<button class="btn btn_submit mr10 J_design_check" data-value="forum" type="button">是</button>\
					<button class="btn J_design_check" data-value="" type="button">否</button>\
				</div>\
			</div>';
		}else if(dtype == 'read'){
			dialog_html = '<div class="pop_cont">请先选择应用于以下哪一项</div>\
				<div class="pop_bottom">\
					<button class="btn btn_submit J_design_check" data-value="" type="button">所有阅读页</button>\
					<button class="btn btn_submit J_design_check" data-value="forum" type="button">当前页所属版块</button>\
					<button class="btn btn_submit J_design_check" data-value="read" type="button">当前页</button>\
				</div>';
		}else{
			dialog_html = '';
		}


		if(dialog_html){
			Wind.dialog.html(dialog_html, {
				isMask	: true,
				zIndex : 12,
				callback : function(){
					$('button.J_design_check').on('click', function(e){
						e.preventDefault();
						savedata.type = $(this).data('value');
						Wind.dialog.closeAll();
						save($this, savedata);
					});
				}
			});
		}else{
			//delete savedata.uniqueid;
			savedata.type = dtype;
			save($this, savedata);
		}

	});

	//保存方法
	function save(elem, savedata){
		$.ajax({
			url : elem.data('action'),
			beforeSend : function(){
				//global.js
				ajaxMaskShow();
			},
			type : 'post',
			dataType : 'json',
			data : savedata,
			success : function(data){
				//global.js
				ajaxMaskRemove();

				if(data.state == 'success') {
					if(data.referer) {
						window.onbeforeunload = null;
						location.href = data.referer;
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


/*
 * 退出
*/
	$('#J_design_quit').on('click', function(e){
		e.preventDefault();
		if(confirm('您确定要退出页面设计状态？确定退出后，已修改的数据将不会保存。')) {
			$.post(this.href, {page_id: page_id, uri : uri}, function(data){
				window.onbeforeunload = null;
				location.href = data.referer;
			}, 'json');
		}
 });

	
/*
 * 菜单公共方法
*/
	//关闭
	$('a.J_pop_close').on('click', function(e){
		e.preventDefault();
		menu_pop.hide();
	});
	//拖拽
	menu_pop.draggable( { handle : '.J_drag_handle'} );


/*
 * 恢复备份
*/
	$('#J_design_restore').on('click', function(e){
		e.preventDefault();
		if(confirm('您确定要恢复为上一个版本的备份结果吗？')) {
			restoreLoop(this.href, 1);
		}
	});

	//循环请求备份信息
	function restoreLoop(url, step){
		$.post(url, {page_id: page_id, step : step}, function(data){
			if(data.state == 'success') {
				var status = parseInt(data.data);
				if(status < 7) {
					/*var text;
					switch(status){
						case 1 : text = '正在恢复挂件';break;
						case 2 : text = '正在更新数据';break;
						case 3 : text = '正在恢复结构';break;
						case 4 : text = '正在恢复模版';break;
						case 5 : text = '正在生成页面';break;
						case 6 : text = '';break;
					}*/

					//global.js
					resultTip({
						msg : data.message[0]
					});

					restoreLoop(url, step+1);
				}else{
					resultTip({
						msg : '恢复成功！',
						callback : function(){
							window.onbeforeunload = null;
							//global.js
							//reloadPage(window);
						}
					});
				}
			}else if(data.state == 'fail'){
				resultTip({
					error : true,
					msg : data.message[0]
				});
			}
		}, 'json');
	}


/*
 * 导出
*/
	$('#J_design_export').on('click', function(e){
		e.preventDefault();
		window.open(this.href +'&page_id='+ page_id);
	});


/*
 * 导入
*/
	var design_import_pop = $('#J_design_import_pop');
	$('#J_design_import').on('click', function(e){
		e.preventDefault();
		popPos(design_import_pop);
	});

	//导入提交
	$('#J_design_import_form').ajaxForm({
		dataType : 'json',
		beforeSubmit : function (arr, $form, options) {
			//global.js
			ajaxBtnDisable($form.find('button:submit'));
		},
		data : {
			page_id : page_id
		},
		success : function (data, statusText, xhr, $form) {
			//global.js
			ajaxBtnEnable($form.find('button:submit'));

			if(data.state == 'success') {
				resultTip({
					msg : '导入成功',
					callback : function(){
						window.onbeforeunload = null;
						//global.js
						reloadPage(window);
					}
				});
			}else if(data.state == 'fail'){
				//global.js
				resultTip({
					error : true,
					msg : data.message[0]
				});
			}
		}
	});


/*
 * 更新
*/
	$('#J_design_cache').on('click', function(e){
		e.preventDefault();
		$.post(this.href, {page_id : page_id}, function(data){
			if(data.state == 'success') {
				resultTip({
					msg : '更新成功',
					callback : function(){
						window.onbeforeunload = null;
						//global.js
						reloadPage(window);
					}
				});
			}else if(data.state == 'fail'){
				//global.js
				resultTip({
					error : true,
					msg : data.message[0]
				});
			}
		}, 'json');
	});


/*
 * 清空
*/
	$('#J_design_clear').on('click', function(e){
		e.preventDefault();
		if(confirm('您确定要清空页面上的所有挂件？清空后将不可恢复！')) {
			$.post(this.href, {page_id : page_id}, function(data){
				if(data.state == 'success') {
					mod_wrap.html('');
				}else if(data.state == 'fail') {
					resultTip({
						error : true,
						msg : data.message[0]
					});
				}
			}, 'json');
		}
	});

/*
 * 编辑模式锁定轮循请求
*/
	designLockLoop();
	function designLockLoop(){
		$.post(DESIGN_LOCK, {page_id: page_id}, function(data){
			if(data.state == 'success') {
				setTimeout(function(){
					designLockLoop();
				}, 60000);
			}
		}, 'json');
	}

})();