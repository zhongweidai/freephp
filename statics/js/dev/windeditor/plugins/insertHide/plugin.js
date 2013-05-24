/*
 * PHPWind WindEditor Plugin
 * @Copyright 	: Copyright 2011, phpwind.com
 * @Descript	: 上传图片插件
 * @Author		: chaoren1641@gmail.com
 * @Depend		: jquery.js(1.7 or later)
 * $Id: windeditor.js 4472 2012-02-19 10:41:01Z chris.chencq $			:
 */
;(function ( $, window, undefined ) {
	
	var WindEditor = window.WindEditor;
	
	var pluginName = 'insertHide',
		dialog = $('<div class="edit_menu" style="display:none;">\
					<div class="edit_menu_hide">\
						<div class="edit_menu_top"><a href="" class="edit_menu_close">关闭</a><strong>插入隐藏内容</strong></div>\
						<div class="edit_menu_cont">\
							<ul>\
								<li><label><input name="hide_type" type="radio" value="1">回复才可见</label></li>\
								<li><label><input name="hide_type" type="radio" value="2">铜币用户高于<input class="input" type="text" class="input length_1 mr5">时才显示</label></li>\
							</ul>\
							<textarea></textarea>\
						</div>\
						<div class="edit_menu_bot">\
							<button type="button" class="edit_menu_btn">提交</button><button type="button" class="edit_btn_cancel">取消</button>\
						</div>\
					</div>\
				</div>');
	
	WindEditor.plugin(pluginName,function() {
		var _self = this;
		var editorDoc = _self.editorDoc = _self.iframe[0].contentWindow.document,
		editorToolbar = _self.toolbar,
		//toolbar中的icon容器
		icon_ul = editorToolbar.find('ul');
		
		//自定义插入位置,插到insertBlockquote后面
		var plugin_icon = $('<div class="wind_icon" unselectable="on"><span class="'+ pluginName +'" title="插入隐藏内容"></span></div>').insertAfter( icon_ul.find('span.insertBlockquote').parent() );
		plugin_icon.on('click',function() {
			if($(this).hasClass('disabled')) {
				return;
			}
			//如果有选取内容，则不弹窗
			var node	= _self.getRangeNode('div.content_hidden'),
				text = _self.getRangeText();
			if(node && node.length) {
				node.find('h5').remove();
				node.replaceWith(node.text());
			}else if(text) {
				_self.insertHTML('<div class="content_hidden"><h5>本帖隐藏的内容</h5>'+ text +'</div>');
			}else {
				if(!$.contains(document.body,dialog[0]) ) {
					dialog.appendTo( document.body );
				}
				_self.showDialog(dialog);
			}
		});

		//弹窗的关闭事件 
		dialog.find('a.edit_menu_close,button.edit_btn_cancel').on('click',function(e) {
			e.preventDefault();
			_self.hideDialog();
		});

		//点击插入
		var head = editorDoc.head || editorDoc.getElementsByTagName( "head" )[0] || editorDoc.documentElement;
		var style = "<style>\
			.content_hidden {border:1px dashed #95c376;padding:10px 40px;margin:5px 0;background:#f8fff3;}.content_hidden h5 {font-size:12px;color:#669933;margin-bottom:5px;}</style>";
		$(head).append(style);

		dialog.find('.edit_menu_btn').on('click',function(e) {
			e.preventDefault();
			var textarea = dialog.find('textarea');
			if(textarea.val() === '') {
				alert('请输入要隐藏的帖子内容');return;
			}
			var val = '<div class="content_hidden"><h5>本帖隐藏的内容</h5>'+ textarea.val() +'</div>';
			//console.log(val);
			_self.insertHTML(val).hideDialog();
		});


		//切换成可见即所得模式时变成html
		function wysiwyg() {
			var reg = /\[post\]([\s\S]*?)\[\/post\]/ig;
			var html = $(editorDoc.body).html();
			html = html.replace(reg,function(all, $1) {
				return '<div class="content_hidden"><h5>本帖隐藏的内容</h5>'+ $1 +'</div>';
			});
			$(editorDoc.body).html(html);
		}

		//加载插件时把ubb转换成可见即所得
		$(_self).on('ready',function() {
			wysiwyg();
		});

		$(_self).on('afterSetContent',function(event,viewMode) {
			wysiwyg();
		});

		$(_self).on('beforeGetContent',function() {
			$(editorDoc.body).find('div.content_hidden').each(function() {
				$(this).find('h5').remove();
				$(this).replaceWith('[post]'+ this.innerHTML +'[/post]');
			});
		});

		//控件栏按钮的控制
    	$(_self.editorDoc.body).on('mousedown',function(e) {
    		if( $(e.target).closest('div.content_hidden').length ) {
    			plugin_icon.removeClass('disabled').addClass('activate');
    		}else {
    			_self.enableToolbar();
    			plugin_icon.removeClass('activate');
    		}
    	});
	});
	
	
})( jQuery, window);