/*
* PHPWind PAGE JS
* @Copyright Copyright 2011, phpwind.com
* @Descript: 后台全局功能js（在footer.htm模板引用）
* @Author	: chaoren1641@gmail.com linhao87@gmail.com
* @Depend	: core.js、jquery.js(1.7 or later)
* $Id: common.js 12615 2012-06-25 03:44:51Z chris.chencq $		:
*/
//kindEditor编辑器
var EDom = {
	editorid : "#J_editor_content",
	J_iseditor : $(".J_iseditor"),
	editorhtml : new Array(),
	editoritem : "",
	getItems : function(eitem){
		switch (eitem){
			case "J_etype_one" :
			return ['source','|','fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
			'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
			'insertunorderedlist', '|', 'emoticons', 'image','link','baidumap','|','fullscreen'];
			break;
			case "J_etype_two" :
			return ['source','fullscreen','baidumap'];
			break;
			default :
			return ['source', '|', 'undo', 'redo', '|', 'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
			'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
			'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
			'superscript', 'clearhtml', 'quickformat', 'selectall', '|', 'fullscreen', '/',
			'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
			'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|', 'image', 'multiimage',
			'flash', 'media', 'insertfile', 'table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
			'anchor', 'link', 'unlink', '|', 'about'];
			break;
		}
	}
};
(function() {
	//全局ajax处理
	$.ajaxSetup({
		complete: function(jqXHR) {
			//登录失效处理
			if(jqXHR.responseText.state === 'logout') {
				location.href = GV.URL.LOGIN;
			}
		},
		data : {
			csrf_token : GV.TOKEN
		},
		error : function(jqXHR, textStatus, errorThrown){
			//请求失败处理
			alert(errorThrown ? errorThrown : '操作失败');
		}
	});

	//不支持placeholder浏览器下对placeholder进行处理
	if(document.createElement('input').placeholder !== '') {
		$('[placeholder]').focus(function() {
			var input = $(this);
			if(input.val() == input.attr('placeholder')) {
				input.val('');
				input.removeClass('placeholder');
			}
		}).blur(function() {
			var input = $(this);
			if(input.val() == '' || input.val() == input.attr('placeholder')) {
				input.addClass('placeholder');
				input.val(input.attr('placeholder'));
			}
		}).blur().parents('form').submit(function() {
			$(this).find('[placeholder]').each(function() {
				var input = $(this);
				if(input.val() == input.attr('placeholder')) {
					input.val('');
				}
			});
		});
	}

	//jquery.lint, 开发时检查代码，只在webkit浏览器有用
	if($.browser.webkit) {
		//Wind.js('{@G:url.js}/js/dev/jquery.lint.js?v{@G:c.version}');
	}

	//所有加了dialog类名的a链接，自动弹出它的href
	if( $('a.J_dialog').length ) {
		Wind.use('dialog',function() {
			$('.J_dialog').on( 'click',function(e) {
				e.preventDefault();
				var _this = $(this);
				Wind.dialog.open( $(this).prop('href') ,{
					onClose : function() {
						_this.focus();//关闭时让触发弹窗的元素获取焦点
					},
					title:_this.prop('title')
				});
			});

		});
	}

	//所有的ajax form提交,由于大多业务逻辑都是一样的，故统一处理
	if( $('form.J_ajaxForm').length || $('form.J_custom_ajaxForm').length) {
		Wind.use('dialog','jquery.form',function() {
			$('button.J_ajax_submit_btn').on('click', function(e){
				e.preventDefault();

				if(EDom.J_iseditor.length){
					EDom.J_iseditor.each(function(i){
						i++;
						var ehtml = EDom.editorhtml[i].html();
						$(EDom.editorid+i).html(ehtml);
					});
				}

				var btn = $(this),
				form = btn.data('form') &&  btn.data('form') != 'undefined' ? btn.parents('form.'+btn.data('form')) : btn.parents('form.J_ajaxForm');
				form.ajaxSubmit({
					url : btn.data('url') ? btn.data('url') : form.attr('action'),			//按钮上是否自定义提交地址(多按钮情况)
					dataType	: 'json',
					beforeSubmit: function(arr, $form, options) {
						var text = btn.text();

						//按钮文案、状态修改
						btn.text(text +'中...').prop('disabled',true).addClass('disabled');
					},
					success		: function(data, statusText, xhr, $form) {
						var text = btn.text();

						//按钮文案、状态修改
						btn.removeClass('disabled').text(text.replace('中...', '')).parent().find('span').remove();

						if( data.state === 'success' ) {
							$( '<span class="tips_success">' + data.message + '</span>' ).appendTo(btn.parent()).fadeIn('slow').delay( 1000 ).fadeOut(function() {
								if(data.referer) {
									//返回带跳转地址
									if(window.parent.Wind.dialog) {
										//iframe弹出页
										window.parent.location.href = data.referer;
									}else {
										window.location.href = data.referer;
									}
								}else {
									if(window.parent.Wind.dialog) {
										reloadPage(window.parent);
									}else {
										reloadPage(window);
									}
								}
							});
						}else if( data.state === 'fail' ) {
							$( '<span class="tips_error">' + data.message + '</span>' ).appendTo(btn.parent()).fadeIn( 'fast' );
							btn.removeProp('disabled').removeClass('disabled');
						}
					}
				});
			});

		});
	}


	//所有的删除操作，删除数据后刷新页面
	if( $('a.J_ajax_del').length ) {
		Wind.use('dialog',function() {

			$('.J_ajax_del').on('click',function(e) {
				e.preventDefault();
				var $this = $(this), href = $this.prop('href'), msg = $this.data('msg');
				var params = {
					message	: msg ? msg : '确定要删除吗？',
					type	: 'confirm',
					isMask	: false,
					follow	: $(this),//跟随触发事件的元素显示
					onOk	: function() {
						$.getJSON(href).done(function(data) {
							if(data.state === 'success') {
								if(data.referer) {
									location.href = data.referer;
								}else {
									reloadPage(window);
								}
							}else if( data.state === 'fail' ) {
								Wind.dialog.alert(data.message);
							}
						});
					}
				};
				Wind.dialog(params);
			});

		});
	}

	//所有的请求刷新操作
	var ajax_refresh = $('a.J_ajax_refresh');
	if( ajax_refresh.length ) {
		ajax_refresh.on('click', function(e){
			e.preventDefault();
			$.getJSON($(this).attr('href')).done(function(data) {
				if(data.state === 'success') {
					if(data.referer) {
						location.href = data.referer;
					}else {
						reloadPage(window);
					}
				}else if( data.state === 'fail' ) {
					Wind.dialog.alert(data.message);
				}
			});
		});
	}

	//字体配置
	if($('.J_font_config').length) {
		Wind.use('colorPicker',function() {
			var elem = $('.color_pick'),panel = elem.parent('.J_font_config');
			elem.colorPicker({
				default_color : elem.find('em').css('background-color'),
				callback:function(color) {
					elem.find('em').css('background-color',color);
					panel.find('.case').css('color',color);
					panel.find('.J_hidden_color').val(color);
				}
			});
		});
		//加粗、斜体、下划线的处理
		$('.J_bold,.J_italic,.J_underline').on('click',function() {
			var panel = $(this).parents('.J_font_config');
			var c = $(this).data('class');
			if( $(this).prop('checked') ) {
				panel.find('.case').addClass(c);
			}else {
				panel.find('.case').removeClass(c);
			}
		});
	}

	/*复选框全选(支持多个，纵横双控全选)。
	*实例：版块编辑-权限相关（双控），验证机制-验证策略（单控）
	*说明：
	*	"J_check"的"data-xid"对应其左侧"J_check_all"的"data-checklist"；
	*	"J_check"的"data-yid"对应其上方"J_check_all"的"data-checklist"；
	*	全选框的"data-direction"代表其控制的全选方向(x或y)；
	*	"J_check_wrap"同一块全选操作区域的父标签class，多个调用考虑
	*/

	if($('.J_check_wrap').length) {
		var total_check_all = $('input.J_check_all');

		//遍历所有全选框
		$.each(total_check_all, function(){
			var check_all = $(this), check_items;

			//分组各纵横项
			var check_all_direction = check_all.data('direction');
			check_items = $('input.J_check[data-'+ check_all_direction +'id="'+ check_all.data('checklist') +'"]');

			//点击全选框
			check_all.change(function (e) {
				var check_wrap = check_all.parents('.J_check_wrap'); //当前操作区域所有复选框的父标签（重用考虑）

				if ($(this).attr('checked')) {
					//全选状态
					check_items.attr('checked', true);

					//所有项都被选中
					if( check_wrap.find('input.J_check').length === check_wrap.find('input.J_check:checked').length) {
						check_wrap.find(total_check_all).attr('checked', true);
					}

				} else {
					//非全选状态
					check_items.removeAttr('checked');

					//另一方向的全选框取消全选状态
					var direction_invert = check_all_direction === 'x' ? 'y' : 'x';
					check_wrap.find($('input.J_check_all[data-direction="'+ direction_invert +'"]')).removeAttr('checked');
				}

			});

			//点击非全选时判断是否全部勾选
			check_items.change(function(){

				if($(this).attr('checked')) {

					if(check_items.filter(':checked').length === check_items.length) {
						//已选择和未选择的复选框数相等
						check_all.attr('checked', true);
					}

				}else{
					check_all.removeAttr('checked');
				}

			});


		});

	}

	/*li列表添加&删除(支持多个)，实例(“验证机制-添加验证问题”，“附件相关-添加附件类型”)：
	<ul id="J_ul_list_verify" class="J_ul_list_public">
	<li><input type="text" value="111" ><a class="J_ul_list_remove" href="#">[删除]</a></li>
	<li><input type="text" value="111" ><a class="J_ul_list_remove" href="#">[删除]</a></li>
	</ul>
	<a data-related="verify" class="J_ul_list_add" href="#">添加验证</a>

	<ul id="J_ul_list_rule" class="J_ul_list_public">
	<li><input type="text" value="111" ><a class="J_ul_list_remove" href="#">[删除]</a></li>
	<li><input type="text" value="111" ><a class="J_ul_list_remove" href="#">[删除]</a></li>
	</ul>
	<a data-related="rule" class="J_ul_list_add" href="#">添加规则</a>
	*/
	var ul_list_add = $('a.J_ul_list_add');
	if(ul_list_add.length) {
		var new_key = ul_list_add.data('count');
		
		//添加
		ul_list_add.click(function(e){
			e.preventDefault();
			new_key++;
			var $this = $(this);

			//"new_"字符加上唯一的key值，_li_html 由列具体页面定义
			var $li_html = $(_li_html.replace(/NEW_/g, 'NEW_'+new_key));

			$('#J_ul_list_'+ $this.data('related')).append($li_html);
			$li_html.find('input.input').first().focus();
		});

		//删除
		$('ul.J_ul_list_public').on('click', 'a.J_ul_list_remove', function(e) {
			e.preventDefault();
			$(this).parents('li').remove();
		});
	}

	//日期选择器
	var dateInput = $("input.J_date")
	if(dateInput.length) {
		Wind.use('datePicker',function() {
			dateInput.datePicker();
		});
	}

	//日期+时间选择器
	var dateTimeInput = $("input.J_datetime");
	if(dateTimeInput.length) {
		Wind.use('datePicker',function() {
			dateTimeInput.datePicker({time:true});
		});
	}

	//图片上传预览
	if($("input.J_upload_preview").length) {
		Wind.use('jquery.uploadPreview',function() {
			$("input.J_upload_preview").uploadPreview();
		});
	}

	//代码复制
	var copy_btn = $('a.J_copy_clipboard'); //复制按钮
	if(copy_btn.length) {
		Wind.use('dialog', 'jquery.textCopy', function() {
			copy_btn.textCopy();
		});
	}

	//iframe弹出层的父层阻止滚动，后台均为iframe弹层
	var scroll_fixed = $('.J_scroll_fixed');
	if(scroll_fixed.length) {
		Wind.use('jquery.scrollFixed', function(){
			$(window).scrollFixed({
				win : true
			});
		});
	}

	//tab
	var tabs_nav = $('ul.J_tabs_nav');
	if(tabs_nav.length) {
		Wind.use('tabs',function() {
			tabs_nav.tabs('.J_tabs_contents > div');
		});
	}

	//radio切换显示对应区块
	var radio_change = $('.J_radio_change');
	if(radio_change.length){
		//页面载入
		change($('.J_radio_change input:checked').data('arr'));

		//切换radio
		$('.J_radio_change input:radio').on('change', function(){
			change($(this).data('arr'));
		});
	}
	function change(str) {
		$('tbody.J_radio_tbody').hide();
		if(str) {
			var arr= new Array();
			arr = str.split(",");

			$.each(arr, function(i, o){
				$('#'+ o).show();
			});
		}
	}

	if(EDom.J_iseditor.length){
		KindEditor.ready(function(K) {
			EDom.J_iseditor.each(function(i){
				i++;
				EDom.editoritem = $(this).attr("class").replace("J_iseditor ","");
				var w = $(EDom.editorid+i).attr("width");
				var h = $(EDom.editorid+i).attr("height");
				$(EDom.editorid+i).css({"width":w+"px","height":h+"px","visibility":"hidden"})
				EDom.editorhtml[i]= K.create(EDom.editorid+i, {
					resizeType : 2,
					allowPreviewEmoticons : true,
					allowImageUpload : true,
					allowFileManager : true,
					items : EDom.getItems(EDom.editoritem)
				});
			});
		});
	}

    //ajax双击修改内容
	var list_table = $('.J_listTable');
	if(list_table.length) {
        Wind.use('jquery.listTable',function() {
			list_table.listTable({});
		});
	}
})();

//重新刷新页面，使用location.reload()有可能导致重新提交
function reloadPage(win) {
	var location = win.location;
	location.href = location.pathname + location.search;
}

//浮出提示_居中
function resultTip(options) {

	var cls = (options.error ? 'warning' : 'success');
	var pop = $('<div style="left:50%;top:30%;" class="pop_showmsg_wrap"><span class="pop_showmsg"><span class="' + cls + '">' + options.msg + '</span></span></div>');

	pop.appendTo($('body')).fadeIn(function () {
		pop.css({
			marginLeft :  - pop.innerWidth() / 2
		}); //水平居中
	}).delay(1500).fadeOut(function () {
		pop.remove();

		//回调
		if (options.callback) {
			options.callback();
		}
	});
}