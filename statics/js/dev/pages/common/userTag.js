/**
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台用户名输入标签js（发私信、发帖提到某人）
 * @Author	: linhao87@gmail.com
 * @Depend	: core.js、jquery.js(1.7 or later)
 * $Id$
 */
userTag();
function userTag() {
	var user_tag_wrap = $('.J_user_tag_wrap');
	
	var CONFIG_USER = ['a', 'aa', 'aaa', 'b', 'bb', 'bbb'], _v;
	
	$.each(user_tag_wrap, function (i, o) {
		var $this = $(this),
			user_tag_ul = $this.find('ul.J_user_tag_ul'),
			user_tag_input = $this.find('input.J_user_tag_input'),
			timer;
		
		user_tag_input.val('');
		
		//点击区域输入聚焦
		user_tag_wrap.on('click', function (e) {
			if (e.target == $this[0]) {
				user_tag_input.focus();
			}
		});
		
		user_tag_input.on('keydown', function (e) {
			//键盘输入
			var $this = $(this),
			v = $.trim($this.val());
			
			if (e.keyCode === 32 || e.keyCode === 13) {
				//输入空格或回车
				e.preventDefault();
				
				//是否有当前项
				var current = $('#J_user_match_wrap li.current');
				if(current.length) {
					v = current.text();
					_v = '';
				}

				tagCreat(v, user_tag_ul, user_tag_input);
				
			}
			
		}).on('blur', function (e) {
			//失焦
			var v = $.trim($(this).val());
			
			if (!v) {
				return false; //空内容
			}
			
			timer = setTimeout(function(){
				tagCreat(v, user_tag_ul, user_tag_input);
				$('#J_user_match_wrap').hide().empty();
			}, 100);
			
		}).on('keyup', function (e) {
			//console.log(_v);
			var $this = $(this),
				v = $.trim($this.val()),
				user_match_wrap = $('#J_user_match_wrap');
			
			if (!v) {
				user_match_wrap.hide().empty();
				_v = ''; //清空
				return false; //空内容
			}

			var item_length = user_match_wrap.find('li').length; //匹配项的总数
				current_index = user_match_wrap.find('li.current').data('index'); //current项的index值
			
			if (e.keyCode === 38) {
				//按键向上
				
				if (!current_index || current_index <= 1) {
					//没有选中项
					current_index = item_length;
				} else {
					//有选中项
					current_index--;
				}
				
			} else if (e.keyCode === 40) {
				//按键向下
				
				if (!current_index || current_index >= item_length) {
					current_index = 1;
				} else {
					current_index++;
				}
				
			} else {
				
				//验证是否重复内容
				if(v === _v) {
					return false;
				}
				
				var li_arr = [], k = 0;
				
				//匹配
				$.each(CONFIG_USER, function (i, o) {
					if (RegExp(v).test(o)) {
						k++;
						li_arr.push('<li id="J_user_match_'+ k +'" data-index="'+ k +'"><a href="">' + o + '</a></li>');
					}
					
				});
				//console.log(li_arr);
				
				if (li_arr.length) {
					//匹配成立，判断列表是否已存在
					
					if (user_match_wrap.length) {
						user_match_wrap.html('<ul>' + li_arr.join('') + '</ul>').show(); //重写
					} else {
						$('body').append('<div id="J_user_match_wrap" class="user_select_down "><ul>' + li_arr.join('') + '</ul></div>');
					}
					
					var _wrap = $('#J_user_match_wrap');
					_wrap.css({
						left : $this.offset().left,
						top : $this.offset().top + $this.innerHeight()
					});
					
					//点击项目
					$('#J_user_match_wrap a').on('click', function(e){
						e.preventDefault();
						
						clearTimeout(timer); //防止blur冲突
						
						tagCreat($(this).text(), user_tag_ul, $this);
						_wrap.hide().empty();
					});
					
				} else {
					user_match_wrap.hide().empty();
				}
				
				_v = v; //写入
			}
			
			//上下键移动选中项
			if(current_index) {
				$('#J_user_match_' +current_index).addClass('current').siblings().removeClass('current');
			}
			
		});
		
	});
	
	//删除
	$('ul.J_user_tag_ul').on('click', 'del.J_user_tag_del', function (e) {
		e.preventDefault();
		$(this).parents('li').remove();
		_v = '';
	});
	
	//验证&创建用户tag
	function tagCreat(v, ul, input) {
		if(!v) {
			return false;
		}
		//验证用户名特殊字符
		var reg = /[&\\'\"\/*,<>#%?　]/g;
		
		if (reg.test(v)) {
			console.log('不能含有非法字符');
			return false;
		}
		
		//获取已生成的用户名
		var v_arr = [];
		$.each(ul.children('li'), function (i, o) {
			v_arr.push($(this).find('.J_tag_name').text());
		});
		
		//重复验证
		var repeat = false;
		$.each(v_arr, function (i, o) {
			if (o === v) {
				repeat = true;
			}
		});
		if (repeat) {
			return false;
		}
		
		//生成tag
		ul.append('<li><a href="javascript:;"><span class="J_tag_name">' + v + '</span><del title="' + v + '" class="J_user_tag_del">×</del><input type="hidden" value="' + v + '" name="'+ input.data('name') +'" /></a></li>');
		
		input.val('');
	}
	
};
