/*
 * PHPWind WindEditor 
 * @Copyright 	: Copyright 2011, phpwind.com
 * @Descript	: windeditor 中文名：高帅富文本编辑器
 * @Author		: chaoren1641@gmail.com
 * @Depend		: jquery.js(1.7 or later)
 * $Id: windeditor.js 12959 2012-06-27 13:40:59Z chris.chencq $			:
 */
;(function ( $, window, undefined ) {
	
	/**
	* 必须依赖jQuery
	*/
	if( !$ ) { return ;}
	
	/**
	* 判断是否是手机，是手机则不加载编辑器
	*/
	var deviceAgent = navigator.userAgent.toLowerCase(),
		isMobile = deviceAgent.indexOf('mobile') >= 0,
		browser = $.browser,
		ie = browser.msie,
		ie6 = ie && browser.version < 7,
		mozilla = browser.mozilla,
		webkit = browser.webkit,
		opera = browser.opera;
	/**
	* 非webkit手机不加载
	*/
	if(isMobile && !webkit) { return ; }
	
	/**
	* ie6图片强制缓存
	*/
	try {
	    if(ie6) {
			document.execCommand('BackgroundImageCache', true, false);
	    }
	} catch(ex) {}
	
	/**
	* js模版引擎(author: John Resig http://ejohn.org/blog/javascript-micro-templating/)
	*/
	var cache = {};
	var tmpl = function (str, data) {
        var fn = !/\W/.test(str) ? cache[str] = cache[str] || tmpl(str) :
        new Function("obj", "var p=[],print=function(){p.push.apply(p,arguments);};" +
        "with(obj){p.push('" +
        str.replace(/[\r\t\n]/g, " ").split("<%").join("\t").replace(/((^|%>)[^\t]*)'/g, "$1\r").replace(/\t=(.*?)%>/g, "',$1,'").split("\t").join("');").split("%>").join("p.push('").split("\r").join("\\'") + "');}return p.join('');");
        return data ? fn(data) : fn;
    };
    
    /**
	* rgb转16进制
	*/
    var formatColor = function(color) {
		if (String(color).slice(0, 3) === 'rgb') {
            var ar = color.slice(4, -1).split(','),
				r = parseInt(ar[0]),
                g = parseInt(ar[1]),
                b = parseInt(ar[2]);
            return ['#', r < 16 ? '0' : '', r.toString(16), g < 16 ? '0' : '', g.toString(16), b < 16 ? '0' : '', b.toString(16)].join('');
        }
	    return color;
	}
    /**
	* HTML编码
	*/
	var htmlEncode = function(html) {
		var temp = document.createElement ("div"); 
		(temp.textContent != null) ? (temp.textContent = html) : (temp.innerText = html); 
		var output = temp.innerHTML; 
		temp = null; 
		return output; 
	};
	
	/**
	* HTML解码
	*/
	var htmlDecode = function() {
		var temp = document.createElement("div"); 
		temp.innerHTML = text; 
		var output = temp.innerText || temp.textContent; 
		temp = null; 
		return output; 
	};
	
	/**
	* 编辑器默认配置
	*/
	var	defaults = {
		editor_path	: '/js/windeditor/',//editor存放的目录，可以根据实际项目配置
        toolbar 	: ['fontName fontSize removeFormat | bold italic underline strikeThrough foreColor backColor subscript superscript',
						'justifyLeft justifyCenter justifyRight horizontal | insertOrderedList insertUnorderedList indent outdent',
						'partIndent insertBlockquote | insertLink unlink insertTable undo redo'
						],
		mini		: 'fontName fontSize bold italic underline forecolor insertLink',
		theme		: 'default',
		lang		: 'zh-CN',
		viewMode	: 'default',	//当前显示的模式，有两种default、html，ubb作为插件
		codeMode	: 'html',		//默认情况下，代码模式使用html，有可能使用ubb，markdown等
		iframeCss	: [],			//编辑器iframe中的css
		initialStyle:'body {margin:0;padding:5px;overflow-x:hidden;font-family: Arial,Helvetica,sans-serif;word-wrap: break-word;white-space:normal;}img {max-width:700px;}',//加载时的初始化样式
		onInit		: null, 		//初始化事件
		autoHeight	: true, 		//是否启用自动增高
		localSave	: true, 		//是否把当前编辑的内容保存在本地
		fixedToolbar: true, 		//默认工具栏固定（ie6设为true也没用，不鸟ie6）
		undoLength	: 100, 			//记录历史（可撤销次数）
		plugins		: [],			//默认什么插件也不加载，作为一个通用编辑器
		filterTags	: ['style','script','link','object','applet','input','meta','base','button','select','textarea','#comment','map','area']//xss安全过滤
		
    };
	
	/**
	* WindEditor类
	*/
    function WindEditor( textarea, options ) {
        this.textarea = textarea;
        /**
		* 包含编辑器的主容器，动态生成的编辑器结构
		* <div class="wind_editor_wrap">
		* 	<div class="wind_editor_toolbar"></div>
		* 	<div class="wind_editor_body">
		*		<iframe class="wind_editor_iframe"/>
		*		<textarea/>
		*	</div>
		* 	<div class="wind_editor_statusbar"></div>
		* </div>
		*/
        this.container = null;
        this.toolbar = null;
        this.body = null;
        this.statusbar = null;
        this.iframe = null;
        this.editorDoc = null;
        this.undoStack = [];//撤销队列（浏览器自带的撤销命令不靠谱）
        this.undoIndex = 0; // 默认记录索引为0，也就是没有undo历史
        this.options = $.extend( {}, defaults, options) ;
        this.viewMode = this.options.viewMode;//记录当前的显示模式
        this.codeMode = this.options.codeMode;//记录当前的代码模式
        this.init();
    }
	
    WindEditor.prototype = {
    	/**
		* HTML检查，摘自cheditor(http://docs.cksource.com/ckeditor_api/symbols/src/core_dtd.js.html)
		*/
		dtd : (function() {
			
		})(),
		
		/**
		* 编辑器命令配置
		* @link http://www.quirksmode.org/dom/execCommand.html
		*/
		controls : {
			//字体选择
			fontName: {
				style	: 'fontFamily',
				command	: 'fontname',
				element	: (function() {//动态生成下拉元素
					var fontList = {
						items:{
							'宋体'				: '宋体',
							'新宋体' 			: '新宋体',
							'楷体'				: '楷体',
							'黑体'  				: '黑体',
							'微软雅黑'			: '微软雅黑',
							'Arial'				: 'arial,helvetica,sans-serif',
							'Courier New'		: 'courier new,courier,monospace',
							'Georgia'			: 'georgia,times new roman,times,serif',
							'Tahoma'			: 'tahoma,arial,helvetica,sans-serif',
							'Times New Roman'	: 'times new roman,times,serif',
							'Verdana'			: 'verdana,arial,helvetica,sans-serif',
							'impact'			: 'impact'
						},
						defaultItem:'Arial'
					};
					var template = '<div style="float:left" >\
										<div style="float:left;margin-top:21px;">\
											<div class="edit_menu" style="display:none">\
												<ul class="edit_menu_select">\
													<% for(var i in items) { %>\
													<li unselectable="on" data-value="<%=items[i]%>"><a href="" style="font-family:<%=i%>"><%=i%></a></li>\
													<% } %>\
												</ul>\
											</div>\
										</div>\
										<div class="wind_select"><span unselectable="on" class="fontName" title="设置字体"><%=defaultItem%></span></div>\
									</div>';
					return $( tmpl(template,fontList) );
				})(),
				bindEvent:function(control) {
					var _self = this,
						elem = control.element,
						menu = elem.find('.edit_menu'),
						select = elem.find('.wind_select');
					select.on('click',function(e) {
						menu.show();
					});
					elem.find('.edit_menu_select li').on('click',function(e) {
						e.preventDefault();
						var fontName = $(this).data('value');
						_self.execCommand('FontName',false,fontName);
						menu.hide();
						select.find('span').text(fontName);
					});
					//点击其它地方隐藏菜单
			        $(document.body).on('mousedown',function(e) {
			        	if( !$.contains(elem,e.target) ) {
			        		menu.hide();
			        	}
			        });
				},
				exec:function(control) {
					var elem = control.element;
					elem.find('.wind_select').trigger('click');
				}
			},
			//字体大小
			fontSize: {
				command	: 'fontsize',
				style 	: 'fontSize',
				element	: (function() {
					var sizeList = {
						items:{'默认':'','10px':'1','12px':'2','16px':'3','18px':'4','24px':'5','32px':'6','48px':'7'},
						defaultItem:'12px'
					},
					template = '<div style="float:left">\
									<div style="float:left;margin-top:21px;">\
										<div class="edit_menu" style="display:none;">\
											<ul class="edit_menu_select">\
												<% for(var i in items) { %>\
												<li unselectable="on" data-value="<%=items[i]%>"><a href="" style="font-size:<%=i%>"><%=i%></a></li>\
												<% } %>\
											</ul>\
										</div>\
									</div>\
									<div class="wind_select"><span  unselectable="on" class="fontSize" title="设置字体大小"><%=defaultItem%></span></div>\
								</div>';
					return $( tmpl(template,sizeList) );
				})(),
				bindEvent:function(control) {
					var _self = this,
						elem = control.element,
						menu = elem.find('.edit_menu'),
						select = elem.find('.wind_select');
					select.on('click',function(e) {
						_self.toolbar.find('.edit_menu').hide();
						menu.show();
					});
					elem.find('.edit_menu_select li').on('click',function(e) {
						e.preventDefault();
						var fontSize = $(this).data('value'),fontText = $(this).text();
						_self.execCommand(control.command,false,fontSize);
						//var html = _self.getRangeHTML();
						//alert(html)
						menu.hide();
						select.find('span').text(fontText);
					});
					//点击其它地方隐藏菜单
			        $(document.body).on('mousedown',function(e) {
			        	if( !$.contains(elem,e.target) ) {
			        		menu.hide();
			        	}
			        });
				},
				exec:function(control) {
					var elem = control.element;
					elem.find('.wind_select').trigger('click');
				}
			},
			
			horizontal: {
				command	: 'inserthorizontalrule',
				tags: ['hr'],
				tooltip: '分隔线'
			},

			bold: {
				command	: 'bold',
				tags: ["b", "strong"],
				css: {
					fontWeight: "bold"
				},
				tooltip: '加粗'
			},
			
			strikeThrough: {
				command : 'strikeThrough',
				tags: ['s','del','strike'],
				css: { textDecoration: 'line-through' },
				tooltip: '删除线'
			},
			
			italic: { 
				command : 'italic',
				tags: ['i','em'],
				tooltip: '斜体',
				css: { fontStyle: 'italic' }
			},
			
			underline: {
				command : 'underline',
				tag: ['u'],
				css: { textDecoration: 'underline' },
				tooltip: '下划线'
			},
			
			foreColor: {
				command : 'forecolor',
				tooltip: '字体颜色',
				element:(function() {
					return $('<div class="wind_icon"><span class="foreColor" title="字体颜色"><em class="edit_acolorlump" style="background:#FF0000;"></em></span><em class="edit_arrow"></em></div>');
				})(),
				bindEvent:function(control) {
					var _self = this,
						element = control.element;
					element.find('.edit_arrow').on('click',function() { 
						var height = element.height(),
							offset = element.offset(),
							left = offset.left,
							top = offset.top,
							colorPanel = _self.colorPanel();
						colorPanel.css({left:left, top:top + height}).show();
						colorPanel.unbind('click').bind('click',function(e) {
							if(e.target.nodeName === 'STRONG') {
								var color = formatColor(e.target.style.backgroundColor);
								_self.execCommand(control.command,null,color);
								element.find('.edit_acolorlump').css('backgroundColor',color);
							}else if(e.target.className === 'color_initialize') {
								var color = formatColor($(_self.editorDoc.body).css('color'));
								_self.execCommand(control.command, null, color);
							}
							colorPanel.hide();
						});
					});
					element.find('.foreColor').on('click',function() {
						var color = formatColor( $(this).find('.edit_acolorlump').css('backgroundColor') );
						_self.execCommand(control.command,null,color);
					});
				}
			},
			
			backColor:{
				command : ie ? 'backColor' :'hilitecolor',
				tooltip: '背景色',
				element:(function() {
					return $('<div class="wind_icon"><span class="backColor" title="背景色"><em class="edit_acolorlump" style="background:#FFFF00;"></em></span><em class="edit_arrow"></em></div>');
				})(),
				bindEvent:function(control) {
					var _self = this,
						element = control.element;
					element.find('.edit_arrow').on('click',function() { 
						var height = element.height(),
							offset = element.offset(),
							left = offset.left,
							top = offset.top,
							colorPanel = _self.colorPanel();
						colorPanel.css({left:left, top:top + height}).show();
						colorPanel.unbind('click').bind('click',function(e) {
							if(e.target.nodeName === 'STRONG') {
								var color = formatColor(e.target.style.backgroundColor);
								_self.execCommand(control.command,null,color);
								element.find('.edit_acolorlump').css('backgroundColor',color);
							}else if(e.target.className === 'color_initialize') {
								var color = $(_self.editorDoc.body).css('backgroundColor');
								_self.execCommand(control.command, false, 'transparent');
							}
							colorPanel.hide();
						});
					});
					element.find('.backColor').on('click',function() {
						var color = formatColor( $(this).find('.edit_acolorlump').css('backgroundColor') );
						_self.execCommand(control.command,null,color);
					});
				}
			},
			
			insertOrderedList: {
				command : 'insertOrderedList',
				tags: ['ol'],
				tooltip: '有序列表'
			},	
				
			insertUnorderedList: {
				command : 'insertUnorderedList',
				tags: ['ul'],
				tooltip: '无序列表'
			},	
					
			justifyCenter: {
				command : 'justifyCenter',
				tags: ['center'],
				css: { textAlign: 'center' },
				tooltip: '居中对齐'
			},
			
			/*justifyFull: {
				command : 'justifyFull',
				css: { textAlign: 'justify' },
				tooltip: '两边对齐'
			},*/
			justifyLeft: {
				command : 'justifyLeft',
				css: { textAlign: 'left' },
				tooltip: '左对齐'
			},

			justifyRight: {
				command : 'justifyRight',
				css: { textAlign: 'right' },
				tooltip: '右对齐'
			},

			partIndent:{
				tooltip: '首行缩进',
				exec:function() {
					var node = this.getRangeNode(),
						html = this.getRangeHTML();
					var p = node.closest('p');
					if (p.length && parseInt(p.css('text-indent')) > 0) {
						p.css('text-indent','0em');
					} else if(p.length) {
						p.css('text-indent','2em');
					}else if(node.is('body')) {
						this.insertHTML('<p style="text-indent:2em;">'+ (html || '&nbsp;') +'</p>');
					}
				}
			},

			indent: {
				tags:['blockquote'],
				command : 'indent',
				tooltip: '缩进',
				css: { textIndent: '2em' }
				
			},

			outdent: { 
				command : 'outdent',
				tooltip: '取消缩进'
			},
			
			
			subscript: {
				command : 'subscript',
				tags: ['sub'],
				tooltip: '下标'
			},
			
			superscript: {
				command : 'superscript',
				tags: ['sup'],
				tooltip: '上标'
			},
			
			removeFormat: {
				exec: function (control) {
					this.execCommand("removeFormat");
					this.execCommand("unlink");
					if(webkit) {//webkit 默认不能清除背景色
						this.execCommand("hilitecolor", false, "transparent");
					}
				},
				tooltip: "清除格式"
			},
			
			undo: { 
				exec:function(control) {

					var undoStack = this.undoStack,
						undoIndex = this.undoIndex;
					if(undoIndex > 1) {
						this.undoIndex--;
						var stack = undoStack[this.undoIndex-1];
						var range = stack.range;
						this.editorDoc.body.innerHTML = stack.html;
						//还原range
						
						this.restoreRange(range);
					}
				},
				tooltip: '撤销' 
			},
			
			redo: { 
				exec:function(control) {
					var undoStack = this.undoStack,
						undoIndex = this.undoIndex;
					if(undoStack.length > undoIndex) {
						this.undoIndex++;
						this.editorDoc.body.innerHTML = undoStack[this.undoIndex-1].html;
					}
				},
				tooltip: '重做' 
			},
			
			unlink:{
				command:'unlink',
				tooltip: '取消链接'
			},
			//插入链接
			insertLink: {
				tags:['a'],
				exec:function(control) {
					var _self = this,
						LinkPanel = _self.insertLinkPanel(),
						form = LinkPanel.find('form'),
						
						titleInput = form.find('.J_title'),
						urlInput = form.find('.J_url'),
						protocol = form.find('.J_protocol'),
						isDownloadInput = form.find('.J_isDownload');
						//如果是链接编辑，则找到当前的链接
					var node = $(_self.getRangeNode('a'));
					//console.log(node);
					if(node.length) {
						titleInput.val(node.text());
						var href = node.attr('href');
						href.replace(/(\w+):\/\/(\w+)/,function($1,$2,$3) {
							//TODO:IE下有兼容问题
							titleInput.val(node.html());
							urlInput.val($3)
							protocol.find('option[value='+ $2 +']').prop('selected','selected');
						});
					}else {
						form[0].reset();
						titleInput.val(_self.getRangeText());//否则取当前选中的文本
					}
					_self.showDialog(LinkPanel);
					
					LinkPanel.find('.edit_menu_btn').unbind('click').bind('click',function(e) {
					 	var title = form.find('.J_title').val(),
					 		url = form.find('.J_url').val(),
					 		//isBlank = form.find('.J_isBlank').is(':checked'),
					 		protocol = form.find('.J_protocol option:selected').html(),
					 		isDownload = form.find('.J_isDownload').is(':checked');
					 	if(url && title) {
					 		_self.insertHTML('<a href="'+ protocol + url +'" target="_blank">'+ title +'</a>');
					 	}else {
					 		_self.execCommand("unlink");
					 	}
					 	_self.hideDialog();
					});
				},
				tags: ['a'],
				tooltip: '创建链接'
			},
			
			//插入表格
			insertTable:{
				tags: ['table'],
				exec:function(control) {
					var _self = this,
						is_insert = true,//新增或编辑,默认新增
						tablePanel = _self.insertTablePanel(),
						form = tablePanel.find('form'),
						//表格设置框获取
						rowsCountInput = form.find('input.J_rowsCount'),//行数
						tableWidthInput = form.find('input.J_width'),//表格宽度
						colsCountInput = form.find('input.J_colsCount'),//列数
						borderInput = form.find('input.J_border'),//表格边框
						paddingInput = form.find('input.J_padding'),//内间距
						borderColorInput = form.find('input.J_borderColor'),//边框颜色
						backgroundColorInput = form.find('input.J_backgroundColor'),//背景颜色
						tableAlignSelect = form.find('select.J_tableAlign'),//表格对齐方式
						colAlignSelect = form.find('select.J_colAlign');//表格对齐方式
					//如果是表格编辑，则找到当前的表格
					_self.showDialog(tablePanel);
					var node = _self.getRangeNode('table')
					if(node.length) {
						is_insert = false;
						var table = node[0];
						//编辑表格时不能编辑行和列
						rowsCountInput.val(table.rows.length).prop('disabled',true);
						colsCountInput.val(table.rows[0].cells.length).prop('disabled',true);
						borderInput.val(table.getAttribute('border'));
						tableWidthInput.val(table.getAttribute('width'));
						paddingInput.val(table.getAttribute('cellpadding'));
						borderColorInput.val(table.getAttribute('borderColor'));
						backgroundColorInput.val(table.getAttribute('bgColor'));
						tableAlignSelect.find('option[value='+ table.getAttribute('align')+']').attr('selected','selected');
						//colAlignSelect.find('options[value='+ table.align +']').attr('selected','selected');
					}else {
						rowsCountInput.prop('disabled',false);
						colsCountInput.prop('disabled',false);
						form[0].reset();
					}
					tablePanel.find('.edit_menu_btn').unbind('click').bind('click',function(e) {
						var rowsCount = rowsCountInput.val(),
							tableWidth = tableWidthInput.val() || '100%',
							colsCount = colsCountInput.val(),
							border  = borderInput.val() || '1',
							padding = paddingInput.val() || '0',
							borderColor = borderColorInput.val() || '#dddddd',
							backgroundColor = backgroundColorInput.val() || '#ffffff',
							tableAlign = tableAlignSelect.val(),
							colAlign = colAlignSelect.val();
							//生成表格并插入
							
							if (Number(rowsCount) < 1 || Number(colsCount) < 1) {
								alert('请输入正确的行数和列数');return;
							}
							var html = ['<table width="'+ tableWidth +'" border="'+ border +'" align="'+ tableAlign +'" cellpadding="'+ padding +'" bordercolor="'+ borderColor +'" bgcolor="'+ backgroundColor +'"><tbody>'],
							i,j;
							for(i = 0;i < rowsCount; i++) {
								html.push('<tr>');
								for(j = 0;j < colsCount;j++) {
									html.push('<td style="width:'+ (100/colsCount).toFixed(2) +'%"><br/></td>');
								}
								html.push('</tr>');
							}
							html.push('</tbody></table>');
							if(is_insert) {
								_self.insertHTML(html.join(''));
							}else {
								var table = node[0];
								table.setAttribute('border',borderInput.val());
								table.setAttribute('width',tableWidthInput.val());
								table.setAttribute('cellpadding',paddingInput.val());
								table.setAttribute('borderColor',borderColorInput.val());
								table.setAttribute('bgColor',backgroundColorInput.val());
								table.setAttribute('align',tableAlignSelect.find('option:selected').val());
							}
							_self.hideDialog().focus();
					});
				},
				tooltip: "插入表格"
			},
			
			insertBlockquote: {
				selector: 'blockquote.blockquote',
				tooltip: '插入引用',
				exec:function(control) {
					var node = this.getRangeNode('blockquote.blockquote'),
						html = this.getRangeHTML();
					if (node && node.length) {
						node.replaceWith(node.html());
					} else if(html) {
						this.insertHTML('<blockquote class="blockquote">'+ html +'</blockquote>');
					} else {
						var node = $('<blockquote class="blockquote"><br/></blockquote>');
						this.insertNode(node);
						this.setFocus(node);
						//this.insertHTML('<blockquote class="blockquote"><br/></blockquote>');
					}
				}
			}
		},
		
		/**
		* 颜色选择面板
		*/
		colorPanel : function() {
			var _self = this,
				colorPanel = $('.editor_color_panel');
			//如果已经有颜色选择器，那么直接返回之
			if(colorPanel.length) {
				return colorPanel;
			}
			var pre = '<strong style="background-color:#',
				suf = ';" unselectable="on"><span></span></strong>';
			var htmlGen = [pre, 'ffffff,000000,eeece1,1f497d,4f81bd,c0504d,9bbb59,8064a2,4bacc6,f79646'.split(',').join(suf + pre), suf].join('');
	    
			var htmlList = [pre, 'f2f2f2,7f7f7f,ddd9c3,c6d9f0,dbe5f1,f2dcdb,ebf1dd,e5e0ec,dbeef3,fdeada,d8d8d8,595959,c4bd97,8db3e2,b8cce4,e5b9b7,d7e3bc,ccc1d9,b7dde8,fbd5b5,bfbfbf,3f3f3f,938953,548dd4,95b3d7,d99694,c3d69b,b2a2c7,92cddc,fac08f,a5a5a5,262626,494429,17365d,366092,953734,76923c,5f497a,31859b,e36c09,7f7f7f,0c0c0c,1d1b10,0f243e,244061,632423,4f6128,3f3151,205867,974806'.split(',').join(suf + pre), suf].join('');
	    
			var htmlStandard = [pre, 'c00000,ff0000,ffc000,ffff00,92d050,00b050,00b0f0,0070c0,002060,7030a0'.split(',').join(suf + pre), suf].join('');
	    
			var htmlGeneralPanel = ['<div class="editor_color_panel edit_menu" style="display:none;z-index:12;"><div class="color_initialize" unselectable="on"><em>■</em>恢复默认</div><div class="color_general">', htmlGen, '</div><div class="color_list">', htmlList, '</div><div class="color_standard">', htmlStandard, '</div></div>'].join('');
			
			return $( htmlGeneralPanel ).appendTo(document.body);
		},
		
		/**
		* 表格插入面板
		*/
		insertTablePanel:function(){
			var _self = this,
				insertTablePanel = _self.toolbar.find('div.editor_table_panel');
			if(insertTablePanel.length) {
				return insertTablePanel;
			}
			var html = '<div class="edit_menu editor_table_panel style="display:none">\
							<div class="edit_menu_insertTable">\
								<div class="edit_menu_top">\
									<a href="" class="edit_menu_close">关闭</a>\
									<strong>插入表格</strong>\
								</div>\
								<form>\
									<div class="edit_menu_cont">\
										<ul class="cc">\
											<li><em>行数</em><input name="" type="number" class="input J_rowsCount" min="1" max="100"></li>\
											<li><em>表格宽度</em><input name="" type="number" class="input J_width" min="1" max="100">px</li>\
											<li><em>列数</em><input name="" type="number" class="input J_colsCount" min="1" max="100"></li>\
											<li><em>表格边框</em><input name="" type="number" class="input J_border" min="1" max="100">px</li>\
											<li><em>内间距</em><input name="" type="number" class="input J_padding" min="1" max="100">px</li>\
											<li><em>边框颜色</em><input name="" type="text" class="input J_colorPicker J_borderColor" ></li>\
											<li><em>背景颜色</em><input name="" type="text" class="input J_colorPicker J_backgroundColor"></li>\
											<li><em>表格对齐</em>\
												<select class="J_tableAlign">\
												<option value="">默认</option>\
												<option value="left">居左</option>\
												<option value="center">居中</option>\
												<option value="right">居右</option>\
												</select>\
											</li>\
											<!--<li><em>内容对齐</em>\
												<select class="J_colAlign">\
												<option value="left">左对齐</option>\
												<option value="center">居中</option>\
												<option value="right">右对齐</option>\
												</select>\
											</li>-->\
										</ul>\
									</div>\
								</form>\
								<div class="edit_menu_bot">\
									<button type="button" class="edit_menu_btn">提交</button>\
									<button type="button" class="edit_btn_cancel">取消</button>\
								</div>\
							</div>\
						</div>';
			var panel =  $( html );
			panel.find('a.edit_menu_close,button.edit_btn_cancel').on('click',function(e) {
				e.preventDefault();
				_self.hideDialog();
			});
			
			//rgb转换为HEX码
		    function uniform(color) {
		        if (String(color).slice(0, 3) == 'rgb') {
		            var ar = color.slice(4, -1).split(','),
		                r = parseInt(ar[0]),
		                g = parseInt(ar[1]),
		                b = parseInt(ar[2]);
		            return ['#', r < 16 ? '0' : '', r.toString(16), g < 16 ? '0' : '', g.toString(16), b < 16 ? '0' : '', b.toString(16)].join('');
		        }
		        return color;
		    }
		    
			panel.find('input.J_colorPicker').on('focus',function(e) {
				e.preventDefault();
				var input = this;
				var height = $(input).outerHeight(),
					offset = $(input).offset(),
					left = offset.left,top = offset.top,
					colorPanel = _self.colorPanel();
					colorPanel.css({left:left, top:top + height}).show();
					colorPanel.unbind('click').bind('click',function(e) {
						if(e.target.nodeName === 'STRONG') {
							var color = e.target.style.backgroundColor;
							$(input).val(uniform(color));
							colorPanel.hide();
						}
					});
			});
			//拖拽
			//_self.dragdorp(panel);
			return panel.appendTo( document.body );
		},
		
		/**
		* 链接插入面板
		*/
		insertLinkPanel:function(){
			var _self = this,
				insertLinkPanel = $('div.editor_Link_panel');
			if(insertLinkPanel.length) {
				return insertLinkPanel;
			}
			var html = '<div class="edit_menu_insertLink edit_menu" style="display:none;">\
							<div class="edit_menu_top">\
								<a href="" class="edit_menu_close">关闭</a>\
								<strong>插入链接</strong>\
							</div>\
							<form>\
								<div class="edit_menu_cont">\
									<dl class="cc http_dl">\
										<dt>地址：</dt>\
										<dd>\
											<select class="J_protocol"><option value="http" selected>http://</option><option value="https">https://</option></select>\
											<input name="" type="text" class="input J_url">\
										</dd>\
									</dl>\
									<dl class="cc">\
										<dt>标题：</dt>\
										<dd><input type="text" class="input length_6 J_title"></dd>\
									</dl>\
									<!--<dl class="cc">\
										<dt>设置：</dt>\
										dd>\
											<label><input type="checkbox" value="" class="J_isDownload">作为下载链接</label>\
										</dd>\
									</dl-->\
								</div>\
							</form>\
							<div class="edit_menu_bot">\
								<button type="button" class="edit_menu_btn">提交</button>\
								<button type="button" class="edit_btn_cancel">取消</button>\
							</div>\
						</div>';
			var panel =  $( html );
			panel.find('a.edit_menu_close,.edit_btn_cancel').on('click',function(e) {
				e.preventDefault();
				_self.hideDialog();
			});
			//拖拽
			//_self.dragdorp(panel);
			return panel.appendTo( document.body );
		},

		/**
		* 编辑器初始化
		*/
		init:function() {
			//加载皮肤
			this.loadTheme( this.options.theme );
			
			var _self = this,
				textarea = _self.textarea,
				options = _self.options,
				width = textarea.width(),
				height = _self.textarea.outerHeight();
			_self.container = $('<div class="wind_editor_wrap" dir="ltr" role="application" tabindex="0" aria-labelledby="wind_editor_arialbl" accesskey="p"><span id="wind_editor_arialbl" style="display:none">所见即所得编辑器</span></div>').appendTo( textarea.parent() ).css( {'width': width + 'px' });
			_self.toolbar = $('<div class="wind_editor_toolbar" unselectable="on" role="presentation" onmousedown="return false;"/>').appendTo( _self.container );
			_self.body = $('<div class="wind_editor_body" role="presentation"/>').appendTo( _self.container ).css( { height : height + 'px' } );
			_self.statusbar = $('<div class="wind_editor_statusbar" role="presentation"/>').appendTo( _self.container );
			_self.iframe = $('<iframe class="wind_editor_iframe" style="display:block;height:100%" frameborder="0" width="100%" scroll="no" title="所见即所得编辑器" allowTransparency="true"/>').appendTo( _self.body );
			textarea.addClass('wind_editor_textarea').css('font-size','14px').appendTo( _self.body );
			_self.codeContainer = textarea;//代码容器,不一定要是textarea，在插件中有可能被替换
			
			//根据默认显示模式显示
			if( _self.viewMode !== 'default' ) {
				_self.iframe.hide();
			}else {
				textarea.hide();
			}
			
			var editorDoc = _self.editorDoc = _self.iframe[0].contentWindow.document;
			//editorDoc.designMode = 'on';
			editorDoc.open();
			var iframeCss_str = (function() {
				var str = [];
				for(var i = 0,j = options.iframeCss.length; i < j; i++) {
					str.push('<link rel="stylesheet" type="text/css" href="' + options.iframeCss[i] + '"/>');
				}
				return str.join('');
			})() || '';

			editorDoc.write('<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml"><head>' + iframeCss_str +
                '<style type="text/css">'+ options.initialStyle +'</style></head><body contenteditable="true"></body></html>' );

			//editorDoc.body.contentEditable = true;
			if(!ie) {
				editorDoc.body.spellcheck = false;
			}
			editorDoc.body.style.minHeight = height + 'px';
			var val = _self.textarea.val();
			if(val === '') {
				//检查是否有未发出去的草稿
				/*setTimeout(function() {
					var local_draft = unescape(_self.localStorage.get('windeditor'));
					if(local_draft) {
						editorDoc.body.innerHTML = local_draft;
					}else {
						//editorDoc.body.innerHTML = '<p>'+ ( ie ? '' : '<br/>' ) +'</p>';
					}
				},1000);*/
				editorDoc.body.innerHTML = '<p>'+ ( ie ? '' : '<br/>' ) +'</p>';
			}else {
				editorDoc.body.innerHTML = val;
			}
			editorDoc.close();
			
			if (options.css) {
			    $('<link href="'+ options.css +'" rel="stylesheet"/>').appendTo( $(editorDoc.head[0]) );
			}
			//_self.focus();

			$(function() {
				//提交时再同步内容
				var form = $(_self.textarea.prop('form')),interVal;

				//定时保存内容在本地浏览器中
				if(_self.options.localSave) {
					interVal = setInterval(function() {
						//!TODO:多个实例编辑器时，不能使用同一个localStorage名字
						var html = editorDoc.body.innerHTML;
						if(html) {
							_self.localStorage.set('windeditor',escape(html));
						}
					},1000);
				}

				if( form.length ) {
					form.on('submit',function(e) {
						_self.saveContent();
						_self.clear_local_data();
						clearInterval(interVal);
					});
				}
			});

			//防止鼠标在某个标签中点不出来的情况
			setTimeout(function() {
				var lastChild = _self.editorDoc.body.lastChild;
				/*if(lastChild && lastChild.tagName !=='BR') {
					$(_self.editorDoc.body).append('<br/>');
				}*/
			},64);
			
			//编辑器主体的事件绑定和底部状态栏
			setTimeout(function() {
				_self.initEvent();
				_self.initStatusbar();
			},10);

			_self.initToolbar();//初始化工具条
			setTimeout(function(){
				_self.initHotkey();//初始化快捷键
			},16);
			_self.options.onInit && _self.options.onInit.call(_self);
		},
		
		/**
		* 加载指定的皮肤
		*/
		loadTheme:function(themeName) {
			$('link.windeditor_theme').remove();
			var url = this.options.editor_path + 'themes/' + themeName + '/' + themeName + '.css';
			$('<link href="'+ url +'" class="windeditor_theme" rel="stylesheet"/>').appendTo( $('head') );
			return this;
		},
		
		/**
		* 弹出框弹出
		*/
		showDialog:function(element) {
			var _self = this;
			/*var mask = $('#editor_mask');
			if(!mask.length) {
				mask = $('<div id="editor_mask" />');
				mask.css({
					width:'100%',
					height:$(document).height(),
					backgroundColor:'#ffffff',
					opacity:'0.5',
					position:'absolute',
					left:'0',
					top:'0',
					zIndex:'6'
				});
				mask.appendTo(document.body);
			}else{
				mask.show();
			}*/
			if( !element.data('draggable') ) {
				_self.dragdorp(element);
				element.data('draggable',true)
			}
			element.css({
　　　　　　		position:'absolute',
				zIndex:'7',
　　　　　　		top: ( $(window).height() - element.height() ) / 2 + $(window).scrollTop() + 'px',
　　　　　　		left:( $(window).width() - element.width() ) / 2 + $(window).scrollLeft() + 'px',
				display:''
　　　　		}).attr({'aria-labelledby':"alert_title",'role':"alertdialog",'z-index':'0','tabindex':'0'}).focus();
		},
		
		/**
		* 关闭弹窗
		*/
		hideDialog:function() {
			$('div.edit_menu').hide();//find(':text').val('');//.end.find(':checkbox').prop('checked',false);
			//$('#editor_mask').hide();
			this.focus();
			return this;
		},
		
		/**
		* 编辑器主体的事件绑定
		*/
		initEvent:function() {
			var _self = this,
				editorDoc = _self.editorDoc,
				rng,
				undoTimer;
			$(editorDoc.body).on({
				keydown	: function(e) {
					var emptyContentRegex;

					if($(this).is('body')) {
						//var html = editorDoc.body.innerHTML;
						//$(editorDoc.body).html('').apendChild('<p><br/>'+ html +'</p>').focus();
						//_self.focus();
					}
				},
				keyup	: function(e) {
					clearTimeout(undoTimer);
					undoTimer = setTimeout(function() {
						_self.addToUndoStack();
					}, 200);
					_self.updateToolbar();
				},
				paste:function(e) {
					//e.preventDefault();
					//var html = _self.filterHTML();
					if(ie) {
						_self.pasteCache4IE(e);
					}else {
						_self.pasteCache(e);
					}
					_self.addToUndoStack();
				},
				click: function(e) {
					
				},
				mousedown:function(e) {
					if(e.target.tagName === 'IMG') {
						/*var range = _self.getRange();
						var sel = _self.getSelection();
						if(range.selectNode) {
							range.selectNode(e.target);
							sel.removeAllRanges();
                        	sel.addRange(rng);
						}else if(range.select) {
							range.select();
						}*/
						_self.selectNode($(e.target));
					}
					//点击时更新工具栏状态
					setTimeout(function() {
						_self.updateToolbar();
						//_self.hideDialog();
					},64);
				},
				
				focus:function(e) {
					_self.updateToolbar();
				}
			});
			
			// 记录当前选区，ie中使用beforedeactivate事件来存储选区
			// http://www.cnblogs.com/rubylouvre/archive/2009/08/04/1537164.html
            if (ie) {
				var bookmark;
				//记录IE的编辑光标
				_self.iframe.bind('beforedeactivate',function() {//在文档失去焦点之前
					var range = editorDoc.selection.createRange();
					bookmark = range.getBookmark ? range.getBookmark() : null;
				});
				//恢复IE的编辑光标
				_self.iframe.bind('activate',function() {
					if(bookmark) {
						var range = editorDoc.body.createTextRange();
						range.moveToBookmark(bookmark);
						range.select();
						bookmark = null;
					}
				});
               
            }
            
            //自动增高
			if(_self.options.autoHeight) {
				var body = $(_self.editorDoc.body);
				$(_self.editorDoc.documentElement).css('overflow','hidden');
				body.css('overflow','hidden');
				_self.iframe[0].scroll = 'no';
				_self.iframe.css('overflow','hidden');
				var height = _self.textarea.outerHeight();
				setInterval(function() {
					var newHeight = body.outerHeight();
					if(newHeight > height) {
						_self.body.css('height',newHeight);
						_self.textarea.css('height',newHeight);
					}else {
						_self.body.css('height',height);
						_self.textarea.css('height',height);
					}
				},100);
			}
			
            //无障碍支持
		},
		
		/**
		* 初始化快捷键
		*/
		initHotkey:function() {
			var _self = this;
			$( _self.editorDoc ).on('keydown',function(e) {
				//Ctrl+Z执行undo操作
				if ( (e.metaKey || e.ctrlKey) && e.keyCode === 90) {
					e.preventDefault();
					_self.triggerControl('undo');
				}
				//Ctrl+Y执行redo操作
				if (e.ctrlKey && e.keyCode == 89) {
					e.preventDefault();
					_self.triggerControl('redo');
				}
				//按回车，产生一个p
				if (e.keyCode === 13) {
					var blockquote = _self.getRangeNode('blockquote');
					if(blockquote.length) {
						_self.execCommand('formatblock',false,'<br>');
					}/*else {
						_self.execCommand('formatblock',false,'<p>');
					}*/
					//兼容dz
					//_self.execCommand('formatblock',false,'<br>');
				}

				//按shift+回车，产生一个<br/>
				if ( e.shiftKey  && e.keyCode === 13) {
					_self.execCommand('formatblock',false,'<br>');
				}
			});
		},
		
		/**
		* 检查当前鼠标所在的元素，是否含有定义的toolbar的状态
		*/
		updateToolbar:function(element) {
			var _self = this;
			//undo redo按钮状态的更新
			if(_self.undoIndex > 1) {
				_self.setEnabled('undo');
			}else {
				_self.setDisabled('undo');
			}
			if(_self.undoIndex < _self.undoStack.length) {
				_self.setEnabled('redo');
			}else {
				_self.setDisabled('redo');
			}
			var element = this.getRangeNode();
			element = element[0];//转为dom对象
			if(!element || !element.nodeType) { return this; }
			var controls = _self.controls;
			for(var name in controls) {
				var control = controls[name],
					tags = control.tags,
					css = control.css,
					controlElem = control.element,
					isActive = false,
					command = controls[name].command;
				if(!controlElem || !command) { continue; }
				(function() {
					/*if(_self.queryCommandValue(command)) {
						isActive = true;
						return;
					}*/
					if(_self.queryCommandState(command)) {
						isActive = true;
						return;
					}
					if(tags) {
						if( $.inArray(element.tagName.toLowerCase(),tags) >= 0) {
							isActive = true;
							return;
						} else {
							//看看是否被符合的标签所包含
							for(var i = 0;i < tags.length;i++) {
								if($(element).closest(tags[i]).length) {
									isActive = true;
									return;
								}
							}
						}
					}
					if(css) {
						for(var style in css) {
							var val = $(element).css(style).toString();//ie6下，fontWeight取出来为数字
							if(val && val.toString().toLowerCase() === css[style]) {
								isActive = true;
								return;
							}
						}
					}
				})();
				if(isActive) {
					controlElem.addClass('activate');
				}else {
					controlElem.removeClass('activate');
				}
			}

			//TODO:字体和字体大小的激活状态
			var fontName = $(element).css('font-family');
			var mapSize = {
		    	'1':10,
		    	'2':12,
		    	'3':16,
		    	'4':18,
		    	'5':24,
		    	'6':32,
		    	'7':48
	    	}
			var fontSize = $(element).css('font-size');
			if(mapSize[fontSize]) {
				fontSize = mapSize[fontSize] + 'px';//ie取font-size出来是 1、2、3这样
			}
			controls['fontName'].element.find('.wind_select span').text(fontName);
			controls['fontSize'].element.find('.wind_select span').text(fontSize);
		},
		
		/**
		* 还原选区
		*/
		restoreRange:function(range) {
			var _self = this, sel;
			if (range !== null) {
				var win = _self.iframe.get(0).contentWindow;
				if (win.getSelection) { //non IE and there is already a selection
					_self.focus();
					sel = win.getSelection();
					if (sel.rangeCount > 0) {
						sel.removeAllRanges();
					}
					try {
						//console.log(newRange)
						sel.addRange(range);
					} catch (e) {
						$.error(e);
					}
				} else if (_self.editorDoc.createRange) { // non IE and no selection
					win.getSelection().addRange(range);
				} else if (_self.editorDoc.selection) { //IE
					range.select();
				}
			}
		},
		
		/**
		 * 取得当前光标处所在的非文本DOM节点
		 */
		getRangeNode:function(filterTagName) {
			var _self = this,
				range = _self.getRange();
			if(!range) {
				return $;
				//this.setFocus($(this.editorDoc.body));
				//range = _self.getRange();
			}
			var node;
			//在IE下，如果网页没有获得焦点，也能取到range，所以得判断range有没有parentElement
			if(range.parentElement) {
				node = range.parentElement();
			}else if(range.commonAncestorContainer) {
				node = range.commonAncestorContainer;
			}else if(range.commonParentElement) {
				node = range.commonParentElement();
			}else {
				return $;
			}
			//防止文本节点
			while(node.nodeType === 3) {
				node = node.parentNode;
			}
			if(!filterTagName) {
				return $(node);
			}else {
				if($(node).is(filterTagName)) {
					return $(node);
				}
				filterTagName = filterTagName.toLowerCase();
				return $(node).closest(filterTagName);
			}
		},
		
		
		/**
		* 执行document.execCommand
		*/
		execCommand: function(a, b, c) {
			if (mozilla) {
				try {
					this.editorDoc.execCommand("styleWithCSS", false, true);
				} catch (e) {
					try {
						this.editorDoc.execCommand("useCSS", false, true);
					} catch (e2) {
					}
				}
			}
            this.editorDoc.execCommand(a, b || false, c || null);
            var _self = this;
            setTimeout(function() {
				if(_self.editorDoc.body.lastChild.tagName !=='BR') {
					$(_self.editorDoc.body).append('<br/>');
				}
			},100);
			ie && this.focus();
            return this;
       	},
       
        /**
		* 返回document.execCommand执行后的值
		*/
        queryCommandState: function(a) {
            if(a) {
            	var state;
            	try{
            		state = this.editorDoc.queryCommandState(a);//如果命令是不存在的，firefox会报错
            	}catch(e) {
            	}
            	return state;
        	}
        },
		/**
		* 返回document.execCommand执行后的状态
		*/
        queryCommandValue: function(a) {
            if(a) {
            	return this.editorDoc.queryCommandValue(a);
        	}
        },
		
		/**
		* 加载工具条
		*/
		initToolbar:function() {
			//console.time('initToolbar');
			var _self = this,
				toolbarConfig = _self.options.toolbar,
				toolbar = _self.toolbar,
				ul = $('<ul class="wind_editor_icons"/>');
				//生成toolbar 按钮
				for(var i = 0; i < toolbarConfig.length; i++ ) {
					
					var controls = toolbarConfig[i].split(' '),
						li = $('<li class="wind_editor_small_icons"/>').appendTo(ul);
					
					for(var j = 0;j < controls.length; j++ ) {
						
						var controlName = controls[j];
						
						//如果不是“|”，那证明是普通的编辑器按钮
						if(controlName !== '|') {
							var	control = _self.controls[controlName];
							
							if(!control) {
								//$.error('没有命令' + controlName);
								continue;
							}
							//自定义HTML式菜单，如下拉式的字体选择
							if(control.element && control.element.length) {
								li.append(control.element);
							}else {
								//普通按钮式菜单
								var	menuButton = $('<div class="wind_icon"><span class="'+ controlName +'" title="'+ control.tooltip +'"></span></div>');
								//把按钮与control对象绑定
								control.element = menuButton;
								control.element.attr('data-control',controlName);
								li.append(menuButton);
							}
							
							//自定义的事件
							control.bindEvent && control.bindEvent.call(_self,control);
						} else {
							//否则是“|”，那么是一个换行的标识
							li.append('<div class="wind_clear"></div>');
						}
					}
				}
				
			_self.loadPlugins(function() {
				_self.setDisabled('undo','redo');//加载时把undo、redo禁用,因为没有历史记录
				setTimeout(function() {
					$(_self.editorDoc.body).append('<p><br/></p>');
					_self.addToUndoStack();//加载时添加一个历史记录，undo功能用
				},200)
			});
			toolbar.append(ul);

			//防止toolbar选中，保证文本在ie下的的选中状态
			setTimeout(function() {
				toolbar.find('.wind_icon span,.wind_icon em').attr('unselectable','on');
			},10);

			//点击工具栏图标触发编辑器命令
			toolbar.on('click','.wind_icon',function(e) {
				e.preventDefault();
				var control = _self.controls[$(this).data('control')];
				if(control) {
					_self.triggerControl(control);
				}
			});

			//固定工具栏
			if(_self.options.fixedToolbar && !ie6) {
				_self.fixedToolbar();
			}
			var right_container = $('<div class="right_container"></div>').appendTo(toolbar);	
			var triggerMode = $('<span class="wind_codeMode">代码</span>').appendTo(right_container);
			triggerMode.on('click',function(e) {
				e.preventDefault();
				if(_self.viewMode === 'default') {
					_self.switchMode('html');
					triggerMode[0].className = 'wind_onCodeMode';
				}else {
					_self.switchMode('default');
					triggerMode[0].className = 'wind_codeMode';
				}
			});
			//console.timeEnd('initToolbar');
		},
		
		/**
		* 禁用工具栏控件
		*/
		setDisabled:function() {
			var controls = this.controls;
			for(var i = 0;i < arguments.length;i++) {
				var control = controls[ arguments[i] ];
				if( control && control.element) {
					control.element.addClass('disabled');
				}
			}
		},
		
		/**
		* 启用工具栏控件
		*/
		setEnabled:function() {
			if(this.viewMode !== 'default') {
				return;
			}
			var controls = this.controls;
			for(var i = 0;i < arguments.length;i++) {
				var control = controls[ arguments[i] ];
				if( control ) {
					control.element.removeClass('disabled');
				}
			}
		},
		
		/**
		* 隐藏工具栏控件
		*/
		setHide:function() {
			var controls = this.controls;
			for(var i = 0;i < arguments.length;i++) {
				var control = controls[ arguments[i] ];
				if( control ) {
					control.element.hide();
				}
			}
		},

		/**
		* 加载底部状态栏
		*/
    	fixedToolbar:function() {
    		var _self = this;
    		var toolbar = _self.toolbar,
    			toolbarWidth = toolbar.width(),
    			toolbarTop = toolbar.offset().top;
    		$(window).on('scroll resize',function() {
    			setTimeout(function() {
    				var scrollTop = $(document).scrollTop();
    				if(scrollTop > toolbarTop){
    					toolbar.css({position:'fixed',top:'0',width:toolbarWidth,zIndex:2});
    				}else{
    					toolbar.css({position:'',top:'0'});
    				}
    			},100);
    		})
    	},
    	
		/**
		* 加载底部状态栏
		*/
		initStatusbar:function() {
			//alert('加载底部状态条');
			var _self = this,
				statusbar = _self.statusbar,
				checkWords = $('<span>字数检查</span>').appendTo(statusbar),
				offset = checkWords.offset();
			checkWords.on('click',function(e) {
				var length = $(_self.editorDoc.body).text().length,
					words_span = statusbar.find('span.J_words_length');
				if(!words_span.length) {
					words_span = $('<span class="J_words_length">已写'+ length +'字</span>').appendTo(statusbar);
				}
				words_span.css({
					position:'relative',
					left:-words_span.outerWidth(),
					top:-words_span.outerHeight(),
					background:'#fff',
					border:'1px solid #ccc',
					padding:'3px'
				}).html('已写'+ length +'字').show().delay(2000).fadeOut();
			});
		},
    	
    	/**
    	* 启动工具栏
    	*/
    	enableToolbar:function() {
    		var _self = this;
    		_self.toolbar.find('.wind_select,.wind_icon').each(function() {
    			if($(this).data('control') !== 'undo' && $(this).data('control') !== 'redo') {
    				$(this).removeClass('disabled');
    			}
    		});
    		this.toolbar.removeClass('disabled');
    	},

    	/**
    	* 禁用工具栏
    	*/
    	disableToolbar:function() {
    		var _self = this;
    		_self.toolbar.find('.wind_select,.wind_icon').each(function() {
    			$(this).addClass('disabled');
    		});
    		this.toolbar.addClass('disabled');
    		this.setEnabled('html');
    	},

    	/**
		* 切换模式
		*/
		switchMode:function(viewMode) {
			var _self = this;
			var currentMode = _self.viewMode;
			if(currentMode === viewMode) { return; }
			$(_self).trigger('beforeModeChange',[viewMode]);
			if(viewMode === 'default') {
				_self.setContent(this.codeContainer.val());
				_self.iframe.show();
				_self.codeContainer.hide();
				//this.focus();
				_self.enableToolbar();
				_self.viewMode = 'default';
				//切换到可见即所得之后，光标定位在最后面
				setTimeout(function() {
					_self.setFocus($(_self.editorDoc.body));
				},10);
			} else if(viewMode === 'html') {
				//先过滤word特有的脏代码
				try{
					//this.editorDoc.body.innerHTML = this.cleanWordHTML(this.editorDoc.body.innerHTML);
				}catch(e) {
					//$.error('word格式转换出错:' + e);
				}
				//格式化为XHTML
				try{
					//this.editorDoc.body.innerHTML = this.formatXHTML(this.editorDoc.body.innerHTML);
				}catch(e) {
					//$.error('转为标准XHTML出错:' + e);
				}
				this.setValue(this.getContent());
				this.iframe.hide();
				this.codeContainer.show();
				this.disableToolbar();
				this.viewMode = 'html';
			}
			$(this).trigger('afterModeChange',[viewMode]);
		},
		
		/**
		* 获取Selection对象
		*/
		getSelection : function () {
			// firefox: document.getSelection is deprecated
			var win = this.iframe[0].contentWindow,
				doc = this.editorDoc;
			return doc.selection || win.getSelection();
		},
		
		/**
		* 获取选区中的range对象
		*/
		getRange : function () {
			var selection = this.getSelection();
			if (!selection) {
				return null;
			} 
			if (selection.rangeCount) { // w3c
				return selection.getRangeAt(0);
			} else if (selection.createRange) { // ie
				return selection.createRange();
			}
			return null;
		},

		/**
		* 获取选中的文本
		*/
		getRangeText : function () {
			var r = this.getRange();
			if(!r) {return null;}
			if(ie) {
				return r.htmlText;
			}else {
				var d = $('<div/>');
				d.html(r.cloneContents())
				return d.text();
			}
			
			//return r;
		},
		/**
		* 获取选中的HTML
		*/
		getRangeHTML: function() {
            var r = this.getRange();
			if(!r) {return null;}
			if(ie) {
				return r.htmlText;
			}else {
				var d = $('<div/>');
				d.html(r.cloneContents())
				return d.html();
			}
        },
		
		/**
		* 如果是可见即所得模式，则同步可见即所得内容到代码容器(textarea)
		*/
		saveContent:function() {
			var _self = this;
			if(this.viewMode === 'default') {
				var content = this.getContent();
				//content = content.replace(/<br\/?>$/, "");
				_self.setValue(content);
			}
			return this;
		},
		
		/**
		* 获取HTML
		*/
		getContent:function() {
			var _self = this;
			//var old_content = _self.editorDoc.body.innerHTML;
			//创建一个临时的，隐藏的div来提高体验和性能，因为取iframe中内容时，有一些自定义ubb需要转换，可能比较耗时
			//var old_iframe = _self.iframe;
			//var temp_iframe = old_iframe.clone();
			//temp_iframe.insertAfter(_self.iframe).appendTo(_self.body);
			//_self.iframe.hide();
			$(this).trigger('beforeGetContent');
			var content = _self.editorDoc.body.innerHTML;
			//_self.editorDoc.body.innerHTML = old_content;
			//temp_iframe.hide();
			//_self.iframe.show();
			return content;
		},
		
		/**
		* 粘贴管理 for not ie
		*/
		pasteCache:function(e) {
			//e.preventDefault();
			var editorDoc = this.editorDoc,
				enableKeyDown = false,
				_self = this;
			//create the temporary html editor
			var divTemp = editorDoc.createElement("div");
			divTemp.id = 'windeditor_tempdiv';
			divTemp.innerHTML = '\uFEFF';
			divTemp.style.left = "-10000px";    //hide the div
			divTemp.style.height = "1px";
			divTemp.style.width = "1px";
			divTemp.style.position = "absolute";
			divTemp.style.overflow = "hidden";
			editorDoc.body.appendChild(divTemp);
			//disable keyup,keypress, mousedown and keydown
            //$(editorDoc.body).bind('mousedown.paste', function(e) {e.preventDefault();});
            //$(editorDoc.body).bind('keydown.paste', function(e) {e.preventDefault();});
            enableKeyDown = false;
            //get current selection;
            var range = _self.getSelection().getRangeAt(0),//记住之前的光标位置
				//把光标移动新产生的临时div
				docBody = divTemp.firstChild,
				rng = editorDoc.createRange();
			rng.setStart(docBody, 0);
			rng.setEnd(docBody, 1);
			var sel = _self.getSelection();
			sel.removeAllRanges();
			sel.addRange(rng);

            var originText = editorDoc.textContent;
            if (originText === '\uFEFF'){
            	originText = "";
            }

            //setTimeout(function() {
				var newData = '';
            	//get and filter the data after onpaste is done
				if (divTemp.innerHTML === '\uFEFF') {//webkit返回
					newData = "";
					editorDoc.body.removeChild(divTemp);
					return;
				}
				newData = divTemp.innerHTML;
				if (range) {
					var sel = _self.getSelection();
					sel.removeAllRanges();
					sel.addRange(range);
				}
				//过滤word
				newData = _self.cleanWordHTML(newData);
				divTemp.innerHTML = newData;
				//paste the new data to the editor
				_self.insertHTML( newData );
				editorDoc.body.removeChild(divTemp);
			//}, 16);
            //enable keydown,keyup,keypress, mousedown;
            enableKeyDown = true;
            $(editorDoc.body).unbind('.paste');
			return this;
		},
		
		/**
		* 粘贴管理 for ie
		*/
		pasteCache4IE:function(e) {
			e.preventDefault();
			var _self = this;
			var ifmTemp = document.getElementById("ifmTemp");
			if (!ifmTemp) {
				ifmTemp = document.createElement("IFRAME");
				ifmTemp.id = "ifmTemp";
				ifmTemp.style.width = "1px";
				ifmTemp.style.height = "1px";
				ifmTemp.style.position = "absolute";
				ifmTemp.style.border = "none";
				ifmTemp.style.left = "-10000px";
				//ifmTemp.src="iframeblankpage.html";
				document.body.appendChild(ifmTemp);
				ifmTemp.contentWindow.document.designMode = "On";
				ifmTemp.contentWindow.document.open();
				ifmTemp.contentWindow.document.write("<body></body>");
				ifmTemp.contentWindow.document.close();
			}else {
				ifmTemp.contentWindow.document.body.innerHTML = "";
			}
			ifmTemp.contentWindow.focus();
			ifmTemp.contentWindow.document.execCommand("Paste",false,null);
		
			var newData = ifmTemp.contentWindow.document.body.innerHTML;
			//filter the pasted data
			newData = _self.cleanWordHTML(newData);
			ifmTemp.contentWindow.document.body.innerHTML = newData;
			_self.insertHTML(newData);
		},
		
		/**
		* 设置HTML
		*/
		setContent:function(html) {
			html = this.formatXHTML(html);
			try {
				$(this).trigger('beforeSetContent',[html]);
			}catch(e) {
				//自定义事件中的事件有可能出错导致JS停止执行
			}
			this.editorDoc.body.innerHTML = html;
			//必须有一个after之前的事件，因为有可能被ubb影响，ubb转换要在其它任何事件之前
			//!TODO 有待优化重构
			$(this).trigger('setContenting',[html]);
			//afterSetContent，有助于自定义的一些UBB和标签转换
			$(this).trigger('afterSetContent',[html]);
			return this;
		},
		
		/**
		* 获取代码模式下的显示值，有可能是textarea，有可能不是
		*/
    	getValue:function() {
    		$(this).trigger('beforGetValue');
    		this.saveContent();
    		return this.codeContainer.val();//默认取隐藏的textarea值
    	},

    	/**
		* 设置代码模式下的显示值，有可能是textarea，有可能不是
		*/
    	setValue:function( val ) {
    		$(this).trigger('beforeSetValue',[val]);
    		val = this.formatXHTML(val);
    		this.codeContainer.val( val );//默认设置隐藏的textarea值
    		$(this).trigger('afterSetValue',[val]);
    	},
    	
		/**
		* 过滤html
		*/
		filterHTML:function(html) {
			var filterTag = this.options.filterTag;
		},
		
		/**
		* 插入HTML
		*/
		insertHTML:function(html) {
			var img, range, _self = this;
			if (!html || html.length === 0) {
				return _self;
			}
			if(_self.is_empty()) {
				//_self.editorDoc.body.innerHTML = '<p><br/></p>';
			}
			var sel = _self.getSelection(), range = _self.getRange();


			html += '<'+ (ie ? 'img src="#"' : 'span') +' id="_windeditor_mark" width="0" height="0" />';
			if($(html).is('div,table')) {
				//某些情况下鼠标出不来
				html += '<p/>';
			}
			if(!range) {
				_self.setFocus(_self.editorDoc.body);
				range = _self.getRange();
			}
			if(ie) {
				_self.focus();
				var doc = _self.editorDoc;
				doc.execCommand("insertImage", false, "#windeditor#");
				img = $(doc.body).find("img[src='#windeditor#']");
				if (img.length) {
					img.replaceWith(html);
					img.remove();
				}
			}else {
				if(range.insertNode){
					range.deleteContents();
					range.insertNode(range.createContextualFragment(html));
				}else {
					_self.editorDoc.execCommand("insertHTML", false, html);
				}
			}
			var bmark = _self.editorDoc.getElementById('_windeditor_mark');
			if(bmark) {
				_self.setFocus($(bmark));
				$(bmark).remove();
			}
			_self.addToUndoStack();
			if(_self.editorDoc.body.lastChild.tagName !=='BR') {
				$(_self.editorDoc.body).append('<br/>');
			}
			return _self;
		},
		
		/**
		* 在光标处插入一个Node(jquery对象)
		*/
		insertNode:function(node) {
			var img, _self = this;
			if (!node || node.length === 0) {
				return _self;
			}
			if (ie) {
				var doc = _self.editorDoc;
				//doc.body.focus();
				doc.execCommand("insertImage", false, "windeditor");
				$img = $(doc).find("img[src$=windeditor]");
				if ($img.length) {
					$img.replaceWith(node);
				}
			} else {
				var range = this.getRange();
				range.deleteContents();
				range.insertNode(node[0]);
			}
			return _self;
		},
		
		/**
		* 把光标设置到某个node最后
		*/
		setFocus:function(node) {
			this.focus();
			var range,
				nativeNode = node[0].lastChild || node[0];
			var textNode = this.editorDoc.createElement("span");
			$(textNode).insertBefore(nativeNode)
			if(ie) {
				//range = this.getRange();
				//range.moveToElementText(textNode);
			}else {
				var range = this.editorDoc.createRange();
				range.setStart(textNode, 0);
				range.setEnd(textNode,0);
				var sel = this.getSelection();
				sel.removeAllRanges();
				sel.addRange(range);
			}
			$(textNode).remove();
		},

		/**
		* 选中一个节点
		*/
		selectNode:function(node) {
			var nativeNode = node[0];
			if(ie) {
				range = this.getRange();
				range.moveToElementText(nativeNode);
				//nativeNode.select();
			}else {
				var range = this.editorDoc.createRange();
				range.selectNodeContents(nativeNode);
				range.deleteContents ();
				range.selectNode(nativeNode);
				var sel = this.getSelection();
				sel.removeAllRanges();
				sel.addRange(range);
			}
		},

		/**
		* 内容是否为空
		*/
		is_empty:function() {
			var html = $(this.editorDoc.body).html();
			var emptyContentRegex = /^<([\w]+)[^>]*>(<br\/?>)?<\/\1>$/;
			if (emptyContentRegex.test(html)) {
				return true;
			}
			return false;
		},

		/**
		* 内容是否为空
		*/
		clear_local_data:function() {
			this.localStorage.remove('windeditor');
			return this;
		},

		/**
		* 获得焦点
		*/
		focus:function() {
			this.editorDoc.body.focus();
			return this;
		},
		
		/**
		* 简单与高级切换
		*/
		toggleToolbar: function() {
			return this;
		},
		
		/**
		* 添加到历史记录
		*/
		addToUndoStack:function() {
			var _self = this,
				undoLength = _self.options.undoLength,
				undoStack = _self.undoStack,
				undoIndex = _self.undoIndex;
			if( undoStack.length >= undoLength) {
				_self.undoStack.shift();
			}
			var stack = {
				html: _self.editorDoc.body.innerHTML,
				range: _self.getRange()
			};
			var prevHtml = undoIndex > 0 ? undoStack[undoIndex-1].html : '';
			if(stack.html !== prevHtml) {
				_self.undoStack.push(stack);
				_self.undoIndex ++;
			}
		},
		
		/**
		* 触发Toolbar中的按钮
		*/
		triggerControl:function(control) {
			var _self = this;
			if(typeof control === 'string') {
				control = _self.controls[control];
			}
			
			if(!control || control.element.hasClass('disabled')) {
				return;
			}
			
			//如果定义了exec方法，那么执行自定义代码
			_self.hideDialog();
			if (control.exec) {
				control.exec.call(_self,control);  
			} else {
				//执行浏览器自带命令
				_self.execCommand(control.command);
			}
			//除undo,redo外，其它点击时都要增加历史记录
			if(control !== _self.controls['undo'] && control !== _self.controls['redo']) {
				_self.addToUndoStack();
			}
			setTimeout(function() {
				_self.updateToolbar();
			},16);
			
			setTimeout(function(){
				if(_self.editorDoc.body.lastChild.tagName !=='BR') {
					$(_self.editorDoc.body).append('<br/>');
				}
			},100);
		},
		
		/**
		* 加载插件
		*/
		loadPlugins:function(callback) {
			var _self = this,
				plugins = _self.options.plugins,
				j = 0;
			if(!plugins.length) {
				$(_self).trigger('ready');
				callback && callback();
			}
			for(var i = 0,len = plugins.length; i < len; i++) {
				var	pluginCatalog = _self.options.editor_path + 'plugins/' + plugins[i];
				$.getScript(pluginCatalog + '/plugin.js',function() {
					j++;
					if(j === plugins.length) {
						setTimeout(function() {
							$(_self).trigger('ready');
							callback && callback();
						},0);
					}
				});
			}
		},
		
		/**
		 * localStorage 本地存储
		 */
		localStorage : (function() {
			var localStorageName = 'localStorage',
				storage; 
			function serialize(value) {
				//TODO:存储序列化或加密
				return value;
		    }
		    
		    function deserialize(value) {
		        return value;
		    }
		    
			if (localStorageName in window) {//chrome firefox opera
				storage = window[localStorageName];
		        return {
					set :function(key, val) { 
						storage.setItem(key, serialize(val)); 
					},
					get : function(key) { 
						return deserialize(storage.getItem(key)); 
					},
			        remove : function(key) { 
			            storage.removeItem(key);
			        },
			        clear : function() { 
			            storage.clear();
			        }
		        };
		    }else if(document.documentElement.addBehavior) {//ie
				var el = document.documentElement;
		        el.addBehavior('#default#userData');
				el.load(localStorageName);
				return {
					set : function(key, val) {
			            el.setAttribute(key, serialize(val));
			            el.save(localStorageName);
					},
			        get : function(key) {
			             return deserialize(el.getAttribute(key));
			        },
			        remove : function(key) {
			            el.removeAttribute(key);
			            el.save(localStorageName);
			        },
			        clear : function() {
			            var attributes = el.XMLDocument.documentElement.attributes;
			            el.load(localStorageName);
			            for (var i = 0, attr; attr = attributes[i]; i++) {
			                el.removeAttribute(attr.name);
			            }
			            el.save(localStorageName);
			        }
			        
				};
			}
		})(),
		
		/**
		* 拖拽功能
		*/
		dragdorp:function(element,handle) {
			if(!element.length) {
				return;
			}
			var lastMouseX,lastMouseY;
			var elemHeight = element.outerHeight(),elemWidth = element.outerWidth();
			var winWidth,winHeight;
			//当前窗口内捕获鼠标操作
		    function capture(elem) {
		        elem.setCapture ? elem.setCapture() : window.captureEvents(Event.MOUSEMOVE | Event.MOUSEUP);
		    }
		    function release(elem) {
		        elem.releaseCapture ? elem.releaseCapture() : window.releaseEvents(Event.MOUSEMOVE | Event.MOUSEUP);
		    }
		    function getMousePosition(e) {
				var posx = 0;
				var posy = 0;
		
				if (!e) { var e = window.event; }
		
				if (e.pageX || e.pageY) {
					posx = e.pageX;
					posy = e.pageY;
				}
				else if (e.clientX || e.clientY) {
					posx = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
					posy = e.clientY + document.body.scrollTop  + document.documentElement.scrollTop;
				}
		
				return { 'x': posx, 'y': posy };
			}
			handle = handle || element.find('.edit_menu_top');
			handle.css({cursor:'move'});
	    	var el = handle[0].setCapture ? handle : $(document);
	        handle.on('mousedown',function(e) {
	        	if(handle.setCapture) {
			        //设置鼠标捕获
			        handle.setCapture();
			    }else{
			        //阻止默认动作
			        e.preventDefault();
			    };
	        	capture(this);
	        	e.preventDefault();
	        	lastMouseX = e.pageX;
			    lastMouseY = e.pageY;
			    winWidth = $(window).width();
			    winHeight = $(window).height();
			    el.on('mousemove',function(e) {
			    	e.preventDefault();
			    	e.stopPropagation();
		    		var mousePostion = getMousePosition(e),
		            	mouseY = mousePostion.y, 
		            	mouseX = mousePostion.x,
			            top = parseInt(element.css('top')),
			            left = parseInt(element.css('left')),
			            currentLeft = left + mouseX - lastMouseX,
			            currentTop = top + (mouseY - lastMouseY);
			            maxLeft = winWidth - elemWidth;
			            maxTop = winHeight - elemHeight;
			        if(currentLeft < 0 || currentTop < 0 || currentLeft > maxLeft || currentTop > maxTop) { 
			        	return;
			        }
			        element.css({ left : currentLeft + "px", top : currentTop + "px" });
			        lastMouseX = mouseX;
			        lastMouseY = mouseY;
	       		}).on('mouseup',function (e) {
		            release(this);
		            $(el).unbind('mousemove').unbind('mouseup');
				});
	        });
		},
		
		/**
		* 格式化XHTML
		*/
		formatXHTML:function(html, htmlTags, urlType, wellFormatted, indentChar) {
			//thanks kindEditor 
			//@url http://www.kindsoft.net/
			function _toMap(str) {
				var obj = {}, items = str.split(",");
				for ( var i = 0; i < items.length; i++ )obj[ items[i] ] = true;
				return obj;
			}
			
			function _getAttrList(tag) {
				var list = {},
					reg = /\s+(?:([\w\-:]+)|(?:([\w\-:]+)=([^\s"'<>]+))|(?:([\w\-:"]+)="([^"]*)")|(?:([\w\-:"]+)='([^']*)'))(?=(?:\s|\/|>)+)/g,
					match;
				while ((match = reg.exec(tag))) {
					var key = (match[1] || match[2] || match[4] || match[6]).toLowerCase(),
						val = (match[2] ? match[3] : (match[4] ? match[5] : match[7])) || '';
					list[key] = val;
				}
				return list;
			}
			
			function _getCssList(css) {
				var list = {},
					reg = /\s*([\w\-]+)\s*:([^;]*)(;|$)/g,
					match;
				while ((match = reg.exec(css))) {
					var key = $.trim(match[1].toLowerCase()),
						val = $.trim(match[2]);
					list[key] = val;
				}
				return list;
			}
			var _INLINE_TAG_MAP = _toMap('a,abbr,acronym,b,basefont,bdo,big,br,button,cite,code,del,dfn,em,font,i,img,input,ins,kbd,label,map,q,s,samp,select,small,span,strike,strong,sub,sup,textarea,tt,u,var'),
				_BLOCK_TAG_MAP = _toMap('address,applet,blockquote,body,center,dd,dir,div,dl,dt,fieldset,form,frameset,h1,h2,h3,h4,h5,h6,head,hr,html,iframe,ins,isindex,li,map,menu,meta,noframes,noscript,object,ol,p,pre,script,style,table,tbody,td,tfoot,th,thead,title,tr,ul'),
				_SINGLE_TAG_MAP = _toMap('area,base,basefont,br,col,frame,hr,img,input,isindex,link,meta,param,embed'),
				_STYLE_TAG_MAP = _toMap('b,basefont,big,del,em,font,i,s,small,span,strike,strong,sub,sup,u'),
				_CONTROL_TAG_MAP = _toMap('img,table,input,textarea,button'),
				_PRE_TAG_MAP = _toMap('pre,style,script'),
				_NOSPLIT_TAG_MAP = _toMap('html,head,body,td,tr,table,ol,ul,li'),
				_AUTOCLOSE_TAG_MAP = _toMap('colgroup,dd,dt,li,options,p,td,tfoot,th,thead,tr'),
				_FILL_ATTR_MAP = _toMap('checked,compact,declare,defer,disabled,ismap,multiple,nohref,noresize,noshade,nowrap,readonly,selected'),
				_VALUE_TAG_MAP = _toMap('input,button,textarea,select');
				
			urlType = urlType || '';
			wellFormatted = wellFormatted || false;
			indentChar = indentChar || '\t';
			var fontSizeList = 'xx-small,x-small,small,medium,large,x-large,xx-large'.split(',');
			html = html.replace(/(<(?:pre|pre\s[^>]*)>)([\s\S]*?)(<\/pre>)/ig, function($0, $1, $2, $3) {
				return $1 + $2.replace(/<(?:br|br\s[^>]*)>/ig, '\n') + $3;
			});
			html = html.replace(/<(?:br|br\s[^>]*)\s*\/?>\s*<\/p>/ig, '</p>');
			html = html.replace(/(<(?:p|p\s[^>]*)>)\s*(<\/p>)/ig, '$1<br />$2');
			html = html.replace(/\u200B/g, '');
			var htmlTagMap = {};
			if (htmlTags) {
				$.each(htmlTags, function(key, val) {
					var arr = key.split(',');
					for (var i = 0, len = arr.length; i < len; i++) {
						htmlTagMap[arr[i]] = _toMap(val);
					}
				});
				if (!htmlTagMap.script) {
					html = html.replace(/(<(?:script|script\s[^>]*)>)([\s\S]*?)(<\/script>)/ig, '');
				}
				if (!htmlTagMap.style) {
					html = html.replace(/(<(?:style|style\s[^>]*)>)([\s\S]*?)(<\/style>)/ig, '');
				}
			}
			var re = /(\s*)<(\/)?([\w\-:]+)((?:\s+|(?:\s+[\w\-:]+)|(?:\s+[\w\-:]+=[^\s"'<>]+)|(?:\s+[\w\-:"]+="[^"]*")|(?:\s+[\w\-:"]+='[^']*'))*)(\/)?>(\s*)/g;
			var tagStack = [];
			html = html.replace(re, function($0, $1, $2, $3, $4, $5, $6) {
				var full = $0,
					startNewline = $1 || '',
					startSlash = $2 || '',
					tagName = $3.toLowerCase(),
					attr = $4 || '',
					endSlash = $5 ? ' ' + $5 : '',
					endNewline = $6 || '';
				if (htmlTags && !htmlTagMap[tagName]) {
					return '';
				}
				if (endSlash === '' && _SINGLE_TAG_MAP[tagName]) {
					endSlash = ' /';
				}
				if (_INLINE_TAG_MAP[tagName]) {
					if (startNewline) {
						startNewline = ' ';
					}
					if (endNewline) {
						endNewline = ' ';
					}
				}
				if (_PRE_TAG_MAP[tagName]) {
					if (startSlash) {
						endNewline = '\n';
					} else {
						startNewline = '\n';
					}
				}
				if (wellFormatted && tagName == 'br') {
					endNewline = '\n';
				}
				if (_BLOCK_TAG_MAP[tagName] && !_PRE_TAG_MAP[tagName]) {
					if (wellFormatted) {
						if (startSlash && tagStack.length > 0 && tagStack[tagStack.length - 1] === tagName) {
							tagStack.pop();
						} else {
							tagStack.push(tagName);
						}
						startNewline = '\n';
						endNewline = '\n';
						for (var i = 0, len = startSlash ? tagStack.length : tagStack.length - 1; i < len; i++) {
							startNewline += indentChar;
							if (!startSlash) {
								endNewline += indentChar;
							}
						}
						if (endSlash) {
							tagStack.pop();
						} else if (!startSlash) {
							endNewline += indentChar;
						}
					} else {
						startNewline = endNewline = '';
					}
				}
				if (attr !== '') {
					var attrMap = _getAttrList(full);
					if (tagName === 'font') {
						var fontStyleMap = {}, fontStyle = '';
						$.each(attrMap, function(key, val) {
							if (key === 'color') {
								fontStyleMap.color = val;
								delete attrMap[key];
							}
							if (key === 'size') {
								fontStyleMap['font-size'] = fontSizeList[parseInt(val, 10) - 1] || '';
								delete attrMap[key];
							}
							if (key === 'face') {
								fontStyleMap['font-family'] = val;
								delete attrMap[key];
							}
							if (key === 'style') {
								fontStyle = val;
							}
						});
						if (fontStyle && !/;$/.test(fontStyle)) {
							fontStyle += ';';
						}
						$.each(fontStyleMap, function(key, val) {
							if (val === '') {
								return;
							}
							if (/\s/.test(val)) {
								val = "'" + val + "'";
							}
							fontStyle += key + ':' + val + ';';
						});
						attrMap.style = fontStyle;
					}
					$.each(attrMap, function(key, val) {
						if (_FILL_ATTR_MAP[key]) {
							attrMap[key] = key;
						}
						if ($.inArray(key, ['src', 'href']) >= 0) {
							//attrMap[key] = _formatUrl(val, urlType);
						}
						if (htmlTags && key !== 'style' && !htmlTagMap[tagName]['*'] && !htmlTagMap[tagName][key] ||
							tagName === 'body' && key === 'contenteditable' ||
							/^kindeditor_\d+$/.test(key)) {
							delete attrMap[key];
						}
						if (key === 'style' && val !== '') {
							var styleMap = _getCssList(val);
							$.each(styleMap, function(k, v) {
								if (htmlTags && !htmlTagMap[tagName].style && !htmlTagMap[tagName]['.' + k]) {
									delete styleMap[k];
								}
							});
							var style = '';
							$.each(styleMap, function(k, v) {
								style += k + ':' + v + ';';
							});
							attrMap.style = style;
						}
					});
					attr = '';
					$.each(attrMap, function(key, val) {
						if (key === 'style' && val === '') {
							return;
						}
						val = val.replace(/"/g, '&quot;');
						attr += ' ' + key + '="' + val + '"';
					});
				}
				if (tagName === 'font') {
					tagName = 'span';
				}
				return startNewline + '<' + startSlash + tagName + attr + endSlash + '>' + endNewline;
			});
			//html = html.replace(/\n\s*\n/g, '\n');
			return html;
		},
		
		/**
		* word过滤
		*/
		cleanWordHTML:function(html) {
			//thanks ueditor 
			//@url http://ueditor.baidu.com/
			function isWordDocument( strValue ) {
	            var re = new RegExp( /(class="?Mso|style="[^"]*\bmso\-|w:WordDocument|<v:)/ig );
	            return re.test( strValue );
	        }
	
	        function ensureUnits( v ) {
	            v = v.replace( /([\d.]+)([\w]+)?/g, function ( m, p1, p2 ) {
	                return (Math.round( parseFloat( p1 ) ) || 1) + (p2 || 'px');
	            } );
	            return v;
	        }
	
	        function filterPasteWord( str ) {
	            str = str.replace( /<!--\s*EndFragment\s*-->[\s\S]*$/, '' )
	                //remove link break
	                .replace( /^(\r\n|\n|\r)|(\r\n|\n|\r)$/ig, "" )
	                //remove &nbsp; entities at the start of contents
	                .replace( /^\s*(&nbsp;)+/ig, "" )
	                //remove &nbsp; entities at the end of contents
	                .replace( /(&nbsp;|<br[^>]*>)+\s*$/ig, "" )
	                // Word comments like conditional comments etc
	                .replace( /<!--[\s\S]*?-->/ig, "" )
	                //转换图片
	                .replace(/<v:shape [^>]*>[\s\S]*?.<\/v:shape>/gi,function(str){
	                    var width = str.match(/width:([ \d.]*p[tx])/i)[1],
	                        height = str.match(/height:([ \d.]*p[tx])/i)[1],
	                        src =  str.match(/src=\s*"([^"]*)"/i)[1];
	                    return '<img width="'+ptToPx(width)+'" height="'+ptToPx(height)+'" src="' + src + '" />'
	                })
	                //去掉多余的属性
	                .replace( /v:\w+=["']?[^'"]+["']?/g, '' )
	                // Remove comments, scripts (e.g., msoShowComment), XML tag, VML content, MS Office namespaced tags, and a few other tags
	                .replace( /<(!|script[^>]*>.*?<\/script(?=[>\s])|\/?(\?xml(:\w+)?|xml|meta|link|style|\w+:\w+)(?=[\s\/>]))[^>]*>/gi, "" )
	                //convert word headers to strong
	                .replace( /<p [^>]*class="?MsoHeading"?[^>]*>(.*?)<\/p>/gi, "<p><strong>$1</strong></p>" )
	                //remove lang attribute
	                .replace( /(lang)\s*=\s*([\'\"]?)[\w-]+\2/ig, "" )
	                //清除多余的font不能匹配&nbsp;有可能是空格
	                .replace( /<font[^>]*>\s*<\/font>/gi, '' )
	                //清除多余的class
	                .replace( /class\s*=\s*["']?(?:(?:MsoTableGrid)|(?:MsoNormal(Table)?))\s*["']?/gi, '' );
	
	            // Examine all styles: delete junk, transform some, and keep the rest
	            //修复了原有的问题, 比如style='fontsize:"宋体"'原来的匹配失效了
	            str = str.replace( /(<[a-z][^>]*)\sstyle=(["'])([^\2]*?)\2/gi, function( str, tag, tmp, style ) {
	
	                var n = [],
	                        i = 0,
	                        s = style.replace( /^\s+|\s+$/, '' ).replace( /&quot;/gi, "'" ).split( /;\s*/g );
	
	                // Examine each style definition within the tag's style attribute
	                for ( var i = 0; i < s.length; i++ ) {
	                    var v = s[i];
	                    var name, value,
	                        parts = v.split( ":" );
	
	                    if ( parts.length == 2 ) {
	                        name = parts[0].toLowerCase();
	                        value = parts[1].toLowerCase();
	                        // Translate certain MS Office styles into their CSS equivalents
	                        switch ( name ) {
	                            case "mso-padding-alt":
	                            case "mso-padding-top-alt":
	                            case "mso-padding-right-alt":
	                            case "mso-padding-bottom-alt":
	                            case "mso-padding-left-alt":
	                            case "mso-margin-alt":
	                            case "mso-margin-top-alt":
	                            case "mso-margin-right-alt":
	                            case "mso-margin-bottom-alt":
	                            case "mso-margin-left-alt":
	                            case "mso-table-layout-alt":
	                            case "mso-height":
	                            case "mso-width":
	                            case "mso-vertical-align-alt":
	                                //trace:1819 ff下会解析出padding在table上
	                                if(!/<table/.test(tag))
	                                    n[i] = name.replace( /^mso-|-alt$/g, "" ) + ":" + ensureUnits( value );
	                                continue;
	                            case "horiz-align":
	                                n[i] = "text-align:" + value;
	                                continue;
	
	                            case "vert-align":
	                                n[i] = "vertical-align:" + value;
	                                continue;
	
	                            case "font-color":
	                            case "mso-foreground":
	                                n[i] = "color:" + value;
	                                continue;
	
	                            case "mso-background":
	                            case "mso-highlight":
	                                n[i] = "background:" + value;
	                                continue;
	
	                            case "mso-default-height":
	                                n[i] = "min-height:" + ensureUnits( value );
	                                continue;
	
	                            case "mso-default-width":
	                                n[i] = "min-width:" + ensureUnits( value );
	                                continue;
	
	                            case "mso-padding-between-alt":
	                                n[i] = "border-collapse:separate;border-spacing:" + ensureUnits( value );
	                                continue;
	
	                            case "text-line-through":
	                                if ( (value == "single") || (value == "double") ) {
	                                    n[i] = "text-decoration:line-through";
	                                }
	                                continue;
	
	
	                            //trace:1870
	//                            //word里边的字体统一干掉
	//                            case 'font-family':
	//                                continue;
	                            case "mso-zero-height":
	                                if ( value == "yes" ) {
	                                    n[i] = "display:none";
	                                }
	                                continue;
	                            case 'margin':
	                                if ( !/[1-9]/.test( parts[1] ) ) {
	                                    continue;
	                                }
	                        }
	
	                        if ( /^(mso|column|font-emph|lang|layout|line-break|list-image|nav|panose|punct|row|ruby|sep|size|src|tab-|table-border|text-(?:decor|trans)|top-bar|version|vnd|word-break)/.test( name ) ) {
	                            if ( !/mso\-list/.test( name ) )
	                                continue;
	                        }
	                        n[i] = name + ":" + parts[1];        // Lower-case name, but keep value case
	                    }
	                }
	                // If style attribute contained any valid styles the re-write it; otherwise delete style attribute.
	                if ( i > 0 ) {
	                    return tag + ' style="' + n.join( ';' ) + '"';
	                } else {
	                    return tag;
	                }
	            } );
	            str = str.replace( /([ ]+)<\/span>/ig, function ( m, p ) {
	                return new Array( p.length + 1 ).join( '&nbsp;' ) + '</span>';
	            } );
	            return str;
	        }
	
            if ( isWordDocument( html ) ) {
                html = filterPasteWord( html );
            }
            return html.replace( />[ \t\r\n]*</g, '><' );
		}
    };
    
	//生成jQuery插件
    $.fn['windeditor'] = function ( options ) {
        return this.each(function () {
            if ( !$.data(this, 'windeditor') ) {
                $.data(this, 'windeditor', new WindEditor( $(this), options ));
            }
        });
    };
	
	//暴露一个全局变量，插件机制需要
	window['WindEditor'] = WindEditor;
	
	/**
	* 注册插件接口
	*/
	WindEditor.plugin = function(pluginName,pluginFunction) {
		var textarea = $('textarea.wind_editor_textarea');//class 为初始化编辑器时添加到textarea
		if(!textarea.length) {
			return;
		}
		//!TODO:性能待优化
		textarea.each(function() {
			var instance = $(this).data('windeditor');
			pluginFunction.call(instance,pluginName);
		});
	};
	
})( jQuery, window);

//TODO:无障碍考虑、弹出窗的焦点问题、回车换行问题、性能优化、不同模式下值同步、多语言、插件机制完善
