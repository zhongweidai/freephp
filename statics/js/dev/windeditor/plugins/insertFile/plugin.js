/*
 * PHPWind WindEditor Plugin
 * @Copyright 	: Copyright 2011, phpwind.com
 * @Descript	: 上传附件插件
 * @Author		: chaoren1641@gmail.com
 * $Id: windeditor.js 4472 2012-02-19 10:41:01Z chris.chencq $			:
 */
;(function ( $, window, undefined ) {
	if(!window.attachConfig) {
		$.error('attachConfig没有定义，附件上传需要提供配置对象');
		return;
	}
	if(!window.editConfig) {
		$.error('editConfig没有定义，附件上传需要提供配置对象');
		return;
	}
	var WindEditor = window.WindEditor,
		browser = $.browser,
		ie = browser.msie,
		ie6 = ie && browser.version < 7,
		mozilla = browser.mozilla,
		webkit = browser.webkit,
		opera = browser.opera;
	
	var pluginName = 'insertFile',img_max_width = 500;
	var credit = editConfig.sell.credit;
	var creditSelect = '';
	for(i in credit) {
		creditSelect += '<option value="'+ i +'">'+ credit[i] +'</option>';
	}
	var	dialog = $('<div class="edit_menu" style="display:none;">\
				<div class="edit_menu_file">\
					<div class="edit_menu_top">\
						<a href="" class="edit_menu_close">关闭</a>\
						<strong>附件上传</strong>\
						<span class="edit_tips" title="可上传格式和大小 rar:2000kb zip:200kb"></span>\
					</div>\
					<div class="edit_menu_cont">\
						<div class="edit_uping">\
							<span class="num">还可上传<em id="J_num"></em>个</span>\
							<span id="J_buttonPlaceHolder" ><input type="file" value="单个上传"/></span>\
						</div>\
						<div class="edit_menu_upfile">\
							<dl id="J_file_list">\
								<dt>\
									<span class="span_1">附件名</span>\
									<span class="span_2">附件信息</span>\
									<span class="span_3">操作</span>\
								</dt>\
							</dl>\
						</div>\
					</div>\
					<div class="edit_menu_bot">\
						<button type="button" class="edit_menu_btn">提交</button>\
					</div>\
				</div>\
			</div>');
	
	
	WindEditor.plugin(pluginName,function() {
		var _self = this, swfu;
		var editorDoc = _self.editorDoc = _self.iframe[0].contentWindow.document,
			editorToolbar = _self.toolbar,
			//toolbar中的icon容器
			icon_ul = editorToolbar.find('ul.wind_editor_icons'),
			big_icon_box = icon_ul.find('li.wind_icon_big');
			//此插件的icon需放在大icon容器中
			if(!big_icon_box.length) {
				big_icon_box = $('<li class="wind_icon_big"></li>').appendTo( icon_ul );
			}
			var plugin_icon = $('<div class="wind_icon"><span class="'+ pluginName +'" title="插入附件"></span></div>').appendTo( big_icon_box );
			

			//如果是编辑帖子，那么显示贴子中已有的附件
			var file_list = attachConfig.list,
				has_file,
				has_file_num = 0;
			$.each(file_list,function(i,obj) {
				var name = obj.name;
				var file_extension = name.substring(name.lastIndexOf('.') + 1,name.length);
				has_file = true;
				has_file_num ++; 
				var serverData = {aid:i,path:obj.path};
				$('\
				<dd style="background-position: 0px 0px;">\
					<span class="span_1 file_icon"><span class="file_icon_'+ file_extension +'"></span>\
						<em class="file_title">'+ (obj.name) +'</em>\
					</span>\
					<span class="span_2"> <input type="text" class="input" name="oldatt_desc['+ i +']"  value="'+ (obj.desc) +'"></span>\
					<span class="span_3"><a href="#" data-type="insert">插入</a><a href="#" data-type="del">删除</a><a data-type="sell" href="#">出售</a></span>\
					<span class="span_4" style="display:none;">\
						<input class="input input_sell" name="oldatt_needrvrc['+ i +']" type="number" min="0" value="'+ obj.price +'">\
						<em><select name="oldatt_ctype['+ i +']" class="mr5 J_unit">'+ creditSelect +'</select></em><button class="J_confirm">确认</button><button class="J_cancel">取消</button>\
					</span>\
				</dd>').data('serverData',serverData).appendTo(dialog.find('#J_file_list'));
			});

			//如果是编辑贴且有附件，那么显示有附件指示标
			if(has_file) {
				plugin_icon.after('<div class="wind_attachn"><span></span></div>');
				dialog.find('#J_num').text(attachConfig.attachnum - has_file_num);
			}else {
				dialog.find('#J_num').text(attachConfig.attachnum);
			}
			//点击插件图标
			plugin_icon.on('click',function() {
				if($(this).hasClass('disabled')) {
					return;
				}

				//发布时附件aid无法提交
				/*if(!$.contains(document.body,dialog[0]) ) {
					dialog.appendTo( document.body );*/
				if(!$.contains(_self.container[0],dialog[0]) ) {
					dialog.appendTo( _self.container );
					//加载上传组件
					var swfupload_root = window.GV.JS_ROOT + "util_libs/swfupload/";
					Wind.js(swfupload_root + 'swfupload.js', swfupload_root + 'plugins/swfupload.queue.js', swfupload_root + '/plugins/swfupload.cookies.js',function() {
						SWFUpload.CURSOR = {//鼠标状态枚举
							ARROW : -1,
							HAND : -2
						};
						var settings = {
							flash_url : swfupload_root + "Flash/swfupload.swf",
							upload_url: attachConfig.uploadUrl+'&_json=1',//attachConfig为网页中提供的上传变量
							post_params: attachConfig.postData,
							file_types : (function() {
								var arr = [];
								for(var i in attachConfig.filetype) {
									if(i) {
										arr.push('*.' + i);
									}
								}
								return arr.join(';');
							})(),
							file_types_description : "可上传的附件类型",
							//file_upload_limit : attachConfig.attachnum,
							//file_queue_limit : attachConfig.attachnum,//可上传的最大数量
							debug: false,
							
							file_dialog_start_handler : fileDialogStart,
							file_queued_handler : fileQueued,
							//file_queue_error_handler : fileQueueError,
							file_dialog_complete_handler : fileDialogComplete,
							upload_start_handler : uploadStart,
							upload_progress_handler : uploadProgress,
							upload_error_handler : uploadError,
							upload_success_handler : uploadSuccess,
							upload_complete_handler : uploadComplete,

							// Button settings
							button_width: "80",
							button_height: "25",
							button_cursor : SWFUpload.CURSOR.HAND,
							button_image_url: swfupload_root + "button80x25.png",
							button_placeholder_id: "J_buttonPlaceHolder"
						};
						swfu = new SWFUpload(settings);
					});
				}
				_self.showDialog(dialog);
			});
			
			
			//弹窗的关闭事件 
			dialog.find('a.edit_menu_close').on('click',function(e) {
				e.preventDefault();
				_self.hideDialog();
			});
			
			//上传成功后后面的操作按钮
			dialog.on('click','.span_3 > a',function(e) {
				e.preventDefault();
				var type = $(this).data('type');
				if(type === 'insert') {
					var dd = $(this).parent().parent();
					var serverData = dd.data('serverData');
					var desc = dd.find(':text').val();
					_self.insertHTML('<img class="J_file_img" src="'+ serverData.path +'" data-id="'+ serverData.aid +'" alt="'+ desc +'"" style="max-width:500px;"/>');
				}else if(type === 'del') {
					$(this).parent().parent().remove();
					update_num();

				}else if(type === 'sell') {
					var dd = $(this).parent().parent();
					dd.find('.span_4').show();
					dd.find('.span_2,.span_3').hide();
				}
			});
			
			//取消上传某个队列中的文件
			dialog.on('click','a.J_del_queue',function(e) {
				e.preventDefault();
				var dd = $(this).parent().parent();
				swfu.cancelUpload(dd.attr('id'));
				dd.fadeOut().remove();
				update_num();
			});
			
			//取消出售信息
			dialog.on('click','.J_cancel',function(e) {
				e.preventDefault();
				var dd = $(this).parent().parent();
				dd.find('.span_4').hide();
				dd.find('.span_2,.span_3').show();
			});
			
			//确认出售信息
			dialog.on('click','.J_confirm',function(e) {
				e.preventDefault();
				var dd = $(this).parent().parent();
				dd.find('.span_4').hide();
				dd.find('.span_2,.span_3').show();
				var sell_val = dd.find('.input_sell');
				var sell_unit = dd.find('.J_unit > option:selected').text();
				if(parseInt( sell_val.val() )) {
					dd.find('.span_2').text('售价：' + sell_val.val() + '个' + sell_unit);
				}
			});
			
			//提交按钮关闭弹窗口
			dialog.find('.edit_menu_btn').on('click',function() {
				_self.hideDialog();
			});
			
			//切换成可见即所得模式时变成html
			function wysiwyg() {
				var reg = /\[attachment=(\d+)\]/ig;
				var html = $(editorDoc.body).html();
				html = html.replace(reg,function(all, $1) {
					var path;
					if(file_list[$1]) {
						path = file_list[$1].path;
					}else {
						return '';
					}
					var extName = path.substr(path.lastIndexOf('.')),imgsArr = ['.jpg', '.gif', '.png', '.jpeg', '.bmp'];
					if($.inArray(extName,imgsArr) >= 0 ) {
						return '<img src="'+ path +'" data-id="'+ $1 +'" class="J_file_img" style="max-width:500px;"/>';
					} else {
						return '<img src="images/wind/file/zip.gif" alt="附件" attachment="' + $1 + '">';
					}
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
				$(editorDoc.body).find('img.J_file_img').each(function() {
					$(this).replaceWith('[attachment='+ $(this).attr('data-id') +']');
				});
			});
			
			
			
			/***********************
			   swfupload 批量上传过程中的事件处理
			 ********************** */
			function fileDialogStart() {
				/* I don't need to do anything here */
			}
			
			function fileQueued(file) {
				var file_list_box = $('#J_file_list');
					name = file.name,
					file_extension = name.substring(name.lastIndexOf('.') + 1,name.length).toLowerCase();
				file_list_box.append('\
					<dd style="background-position:-480px 0;" id="'+ file.id +'">\
						<span class="span_1 file_icon">\
							<span class="file_icon_'+ file_extension +'"></span>\
							<em class="file_title">'+ file.name +'</em>\
						</span>\
						<span class="span_2"><em>等待上传</em></span>\
						<span class="span_3"><span>——</span><a href="#" class="J_del_queue">删除</a><span>——</span></span>\
						<span class="span_4" style="display:none;">\
							<input class="input input_sell" type="number" min="0" placeholder="请输入价格">\
							<em><select class="mr5 J_unit">'+ creditSelect +'</select></em><button class="J_confirm">确认</button><button class="J_cancel">取消</button>\
						</span>\
					</dd>');
				var allowSize = parseInt(attachConfig.filetype[file_extension])*1024;
				var allowFileCount = parseInt(attachConfig.attachnum);
				var file_detail = $('#'+file.id);
				//判断文件大小是否超过上传限制
				if(allowSize && file.size > allowSize) {
					var tip = '大小超限制('+ allowSize/1024 +'kb)';
					file_detail.find('.span_2').html('<span style="color:red" title="'+ tip +'">'+ tip +'</span>');
					swfu.cancelUpload(file.id);
					file_detail.addClass('invalid');
				}else if(!allowSize) {
					file_detail.find('.span_2').html('<span style="color:red">不允许上传此类型文件</span>');
					swfu.cancelUpload(file.id);
					file_detail.addClass('invalid');
				}else if(file_list_box.find('dd').size() - file_list_box.find('dd.invalid').size() > allowFileCount) {
					file_detail.find('.span_2').html('<span style="color:red">上传数量超出限制</span>');
					swfu.cancelUpload(file.id);
					file_detail.addClass('invalid');
				}
			}
			
			/*function fileQueueError(file, errorCode, message) {
				//return true;
			}*/
			
			function fileDialogComplete(numFilesSelected, numFilesQueued) {
				try {
					if (this.getStats().files_queued > 0) {
						//显示可上传数量
						dialog.find('#J_num').text(attachConfig.attachnum - this.getStats().successful_uploads);
					}
					//选择文件完成后自动上传
					this.startUpload();
				} catch (ex)  {
			       $.error(ex);
				}
			}
			
			function uploadStart(file) {
				return true;
			}
			
			function uploadProgress(file, bytesLoaded, bytesTotal) {
				try {
					var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
					var file_detail = $('#'+file.id);
					file_detail.find('.span_2').text(percent + '%');//显示进度
					file_detail.css('backgroundPosition',-480 + percent*48 + 'px 0');//使用背景来显示进度条，-480为0%，0为100%
				} catch (ex) {
					$.error(ex);
				}
			}
			
			function uploadSuccess(file, serverData) {
				var json = $.parseJSON(serverData);
				var data = json.data;
				var file_detail = $('#'+file.id);
				if(json.state !== 'success') {
					var message = json.message[0];
					file_detail.find('.span_2').html('<span style="color:red">'+message+'</span>');
					return;
				}
				file_list[''+ data['aid']] = {name : file.name, size : file.size, path : data.path, desc : ''};
				
				file_detail.attr('id','file_' + data.aid);
				file_detail.data('serverData',data).addClass('uploaded');
				file_detail.find('.span_2').html('<input type="text" class="input J_file_desc" value="" placeholder="请输入描述">');
				file_detail.find('.span_3').html('<a href="#" data-type="insert">插入</a><a href="#" data-type="del">删除</a><a data-type="sell" href="#">出售</a>');
				file_detail.css('backgroundPosition','0px 0');//使用背景来显示进度条，-480为0%，0为100%
				file_detail.find('input.input_sell').attr('name',"flashatt["+ data.aid +"][needrvrc]");
				file_detail.find('input.J_file_desc').attr('name',"flashatt["+ data.aid +"][desc]");
				file_detail.find('select.J_unit').attr('name',"flashatt["+ data.aid +"][ctype]")
				//上传成功后，点击可改描述
				file_detail.find('.file_title').on('click',function(e) {
					e.preventDefault();
					$(this).hide().next().show().focus();
				});
				//更新显示可上传数量
				update_num();
			}
			//更新显示可上传数量
			function update_num (argument) {
				dialog.find('#J_num').text(attachConfig.attachnum - dialog.find('dd.uploaded').size());
			}
			//上传完毕
			function uploadComplete(file) {
				try {
					//如果上传完成后，还有未上传的队列，那和继续自动上传
					if (this.getStats().files_queued === 0) {
					} else {	
						this.startUpload();
					}
				} catch (ex) {
					$.error(ex);
				}
			}
			//上传错误
			function uploadError(file, errorCode, message) {
				$.error('上传错误!,'+ message);
			}
	});
})( jQuery, window);