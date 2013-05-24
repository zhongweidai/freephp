/*
 * PHPWind WindEditor Plugin
 * @Copyright 	: Copyright 2011, phpwind.com
 * @Descript	: 上传图片插件
 * @Author		: chaoren1641@gmail.com
 * @Depend		: jquery.js(1.7 or later)
 * $Id: windeditor.js 4472 2012-02-19 10:41:01Z chris.chencq $			:
 */
;(function ( $, window, undefined ) {
	if(!window.attachConfig) {
		$.error('imageConfig没有定义，图片上传需要提供配置对象');
		return;
	}
	var WindEditor = window.WindEditor;
	
	var pluginName = 'insertPhoto',
		dialog = $('\
		<div class="edit_menu">\
			<div class="edit_menu_photo">\
					<div class="edit_menu_top">\
						<a href="" class="edit_menu_close J_close">关闭</a>\
						<ul>\
							<li class="current" data-show="J_upload"><a href="">本地上传</a></li>\
							<li data-show="J_upload_album"><a href="">相册上传</a></li>\
							<li data-show="J_network"><a href="">网络图片</a></li>\
						</ul>\
						<span class="edit_tips" title="rar:2000kb zip:200kb"></span>\
					</div>\
				<!--==========上传==========-->\
				<div id="J_upload" class="J_tab_content">\
					<div class="edit_menu_cont">\
						<div class="edit_uping">\
							<span class="num">还可上传<em id="J_num2"></em>个</span>\
							<span id="J_buttonPlaceHolder2" ><input type="file" value="单个上传"/></span>\
						</div>\
						<div class="eidt_uphoto">\
							<ul class="cc" id="J_photo_list">\
							</ul>\
						</div>\
					</div>\
					<div class="edit_menu_bot">\
						<button type="button" class="edit_menu_btn J_close">提交</button>点击图片可插入到帖子，点击编辑可编辑图片效果\
					</div>\
				</div>\
				<!--=========相册选择===========-->\
				<div style="display:none;" class="J_tab_content" id="J_upload_album">\
					<div class="edit_menu_cont">\
						<div class="edit_uping">\
							<select><option>默认相册</option></select>\
						</div>\
						<div class="eidt_uphoto">\
							<ul class="cc">\
								<li>\
									<div class="get">\
										<img src="" width="78" height="98" />\
									</div>\
								</li>\
							</ul>\
						</div>\
					</div>\
					<div class="edit_menu_bot">\
						点击图片可插入到帖子\
					</div>\
				</div>\
				<!--=========网络图片===========-->\
				<div class="edit_menu_cont J_tab_content" style="display:none;" id="J_network">\
					<div class="edit_online_photo">\
						<em>图片地址：</em><input name="" type="text" id="J_input_net_photo" class="input" value="" placeholder="http://">\
					</div>\
					<div class="tac mb20"><button type="button" class="edit_menu_btn" id="J_insert_net_photo">插入图片</button></div>\
				</div>\
				<!--=========结束===========-->\
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
			var plugin_icon = $('<div class="wind_icon"><span class="'+ pluginName +'" title="插入图片"></span></div>').appendTo( big_icon_box );
			
			var file_list = attachConfig.list,
				has_file,
				has_file_num = 0;
			plugin_icon.on('click',function() {
				if($(this).hasClass('disabled')) {
					return;
				}
				if(!$.contains(_self.container[0],dialog[0]) ) {
					dialog.appendTo( _self.container );
					var swfupload_root = window.GV.JS_ROOT + "util_libs/swfupload/";
					Wind.js(swfupload_root + 'swfupload.js', swfupload_root + 'plugins/swfupload.queue.js', swfupload_root + '/plugins/swfupload.cookies.js',function() {
						SWFUpload.CURSOR = {//鼠标状态枚举
							ARROW : -1,
							HAND : -2
						};
						var settings = {
							flash_url : swfupload_root + "Flash/swfupload.swf",
							upload_url: attachConfig.uploadUrl,//attachConfig为网页中提供的上传变量
							post_params: attachConfig.postData,
							file_types : (function() {
								var arr = [];
								for(var i in imageConfig.filetype) {
									if(i) {
										arr.push('*.' + i);
									}
								}
								return arr.join(';');
							})(),
							file_types_description : "可上传的图片类型",
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
							button_placeholder_id: "J_buttonPlaceHolder2"
						};
						
						swfu = new SWFUpload(settings);
						//显示可上传数量
						dialog.find('#J_num2').text(settings.file_queue_limit);
						var max = attachConfig.attachnum,html = [];
						for(var i = 0 ;i < max; i++) {
							html.push('<li class="J_empty"><div class="no">暂无</div></li>');
						}
						$('#J_photo_list').html(html.join(''));
						
						//如果是编辑帖子，那么显示贴子中已有的附件
						$.each(file_list,function(i,obj) {
							var name = obj.name;
							var file_extension = name.substring(name.lastIndexOf('.') + 1,name.length);
							has_file = true;
							has_file_num ++; 
							var serverData = {aid:i,path:obj.path};
							var upladedLi = $('<li class="uploaded"><div class="get">\
															<a href="" class="del">删除</a>\
															<!--a href="" class="edit">编辑</a-->\
															<img alt="已上传的" data-id="'+ serverData.aid +'" src="'+ serverData.path +'" width="78" height="98" />\
															<input style="width:68px" placeholder="请输入描述" type="text" name="oldatt_desc['+ serverData.aid +']" />\
														</div></li>').data('serverData',serverData);
							$('#J_photo_list > li.J_empty:eq(0)').replaceWith(upladedLi);
						});
						
						//如果是编辑贴且有附件，那么显示有附件指示标
						if(has_file) {
							//plugin_icon.after('<div class="wind_attachn"><span></span></div>');
							dialog.find('#J_num2').text(attachConfig.attachnum - has_file_num);
						}else {
							dialog.find('#J_num2').text(attachConfig.attachnum);
						}
					});
				}
				_self.showDialog(dialog);
			});
			
			//弹窗的关闭事件 
			dialog.find('.edit_menu_close').on('click',function(e) {
				e.preventDefault();
				_self.hideDialog();
			});
			
			//顶部的tab选项卡
			dialog.find('.edit_menu_top li').on('click',function(e) {
				e.preventDefault();
				$(this).addClass('current').siblings().removeClass('current');
				dialog.find('.J_tab_content').hide();
				dialog.find('#'+$(this).data('show')).show();
			});

			//插入网络图片
			dialog.find('#J_insert_net_photo').on('click',function(e) {
				e.preventDefault();
				var url = $('#J_input_net_photo').val();
				if( url.indexOf('http')!== 0 ) {
					alert('路径格式不正确，请重新输入');
					return;
				}
				_self.insertHTML('<img src="'+ url +'" />').hideDialog();
			});

			//上传好的图片点击插入
			dialog.find('#J_upload').on('click', 'img', function() {
				_self.insertHTML('<img class="J_file_img" data-id="'+ $(this).data('id') +'" style="max-width:500px" src="'+ this.src +'" />');
			});
			
			//删除已经上传好的图片
			dialog.find('div.eidt_uphoto').on('click','a.del',function(e) {
				e.preventDefault();
				$(this).parent().parent().remove();
				update_num();
			});
			
			//编辑已经上传好的图片
			dialog.find('div.eidt_uphoto').on('click','a.edit',function(e) {
				e.preventDefault();
				alert('编辑图片还没做');
			});
			
			//提交按钮关闭弹窗口
			dialog.find('.edit_menu_btn').on('click',function() {
				_self.hideDialog();
			});
			
			/* **********************
			   swfupload 批量上传过程中的事件处理
			   ********************** */
			function fileDialogStart() {
				/* I don't need to do anything here */
			}
			
			function fileQueued(file) {
				var file_list_box = $('#J_photo_list');
				//填充图片显示位置
				var empty_box = $('#J_photo_list > li.J_empty:eq(0)'),
					name = file.name,
					file_extension = name.substring(name.lastIndexOf('.') + 1,name.length).toLowerCase();
				var invalid = false,tip = '';
				var allowSize = parseInt(attachConfig.filetype[file_extension])*1024;
				var allowFileCount = parseInt(attachConfig.attachnum);
				if(empty_box.length) {
					//判断文件大小是否超过上传限制
					if(allowSize && file.size > allowSize) {
						tip = '大小超限制('+ allowSize/1024 +'kb)';
						invalid = true;
					}else if(!allowSize) {
						tip = '不允许上传此类型文件('+ this.settings.file_types +')';
						invalid = true;
					}else if(file_list_box.find('li').size() - file_list_box.find('li.invalid').size() > allowFileCount) {
						tip = '上传数量超出限制('+ allowFileCount +'个)';
						invalid = true;
					}else {
						invalid = false;
						empty_box.replaceWith('<li id="'+ file.id +'"><div class="schedule"><em>0%</em><span style="width:0%;"></span></div></li>');
					}
					//如果是无效的则取消上传
					if(invalid) {
						this.cancelUpload(file.id);
						empty_box.before('<li class="invalid"><div class="error" title="'+ tip +'">'+ tip +'<a href="" class="del">删除</a></div></li>');
					}
				}else {
					tip = '上传数量超出限制('+ allowFileCount +'个)';
					invalid = true;
					file_list_box.append('<li class="invalid"><div class="error" title="'+ tip +'">'+ tip +'<a href="" class="del">删除</a></div></li>');
				}
				
			}
			
			function fileQueueError(file, errorCode, message) {
				$.error(message);
			}
			
			function fileDialogComplete(numFilesSelected, numFilesQueued) {
				try {
					if (this.getStats().files_queued > 0) {
						//显示可上传数量
						dialog.find('#J_num2').text(attachConfig.attachnum - this.getStats().successful_uploads);
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
					file_detail.find('em').text(percent + '%');//显示进度
					file_detail.find('span').css('width',percent + '%');//使用宽度来显示进度条
				} catch (ex) {
					$.error(ex);
				}
				//显示可上传数量
				update_num();
			}
			
			function uploadSuccess(file, serverData) {
				try {
					var file_detail = $('#'+file.id);
					var json = $.parseJSON(serverData);
					if(json.state !== 'success') {
						var message = json.message[0];
						file_detail.html('<div class="no" style="color:red">'+ message +'</div>');
						return;
					}
					var data = json.data;
					file_list[''+ data['aid']] = {name : file.name, size : file.size, path : data.path, desc : ''};
					file_detail.data('serverData',data).addClass('uploaded');
					file_detail.html('<div class="get">\
												<a href="" class="del">删除</a>\
												<!--a href="" class="edit">编辑</a-->\
												<img alt="上传完成" data-id="'+ data.aid +'" src="'+ data.path +'" width="78" height="98" />\
												<input style="width:68px" placeholder="请输入描述" type="text" name="flashatt['+ data.aid +'][desc]" />\
											</div>');
					//上传成功后，点击可改描述
					//更新显示可上传数量
					update_num();
				} catch (ex) {
					$.error(ex);
				}
			}
			
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
			
			function uploadError(file, errorCode, message) {
				$.error(message);
			}

			//更新显示可上传数量
			function update_num () {
				dialog.find('#J_num2').text(attachConfig.attachnum - dialog.find('li.uploaded').size());
			}
	});
})( jQuery, window);