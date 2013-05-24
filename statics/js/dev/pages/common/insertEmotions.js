/**
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-表情插入
 * @Author	: linhao87@gmail.com
 * @Depend	: core.js、jquery.js(1.7 or later), global.js, jquery.insertContent
 * $Id$
 */

var emotions_temp = '<div id="J_emotions_pop" style="z-index:11;position:absolute;" class="core_menu pop_show_mini">\
						<div class="core_arrow_top" style="left:0;"><em></em><span></span></div><a href="#" id="J_emotions_close" class="pop_close">关闭</a>\
						<div id="J_emotions_menu"></div>\
						<div class="ct" id="J_emotions_pl"><div class="pop_loading"></div></div>\
					</div>',
		page_size = 32;		//单页表情数

function insertEmotions(elem, input, wrap) {
	var emotions_pop = $('#J_emotions_pop');
	
	if(emotions_pop.length) {
		emotions_pop.show();

		//定位
		emotionsPos(elem, emotions_pop, wrap);
	}else{
		
		if(wrap) {
			wrap.append(emotions_temp);
		}else{
			$('body').append(emotions_temp);
		}
		
		var emotions_pop = $('#J_emotions_pop'),
				emotions_menu = $('#J_emotions_menu'),
				emotions_pl = $('#J_emotions_pl');

		//定位
		emotionsPos(elem, emotions_pop, wrap);

		$.getJSON(GV.URL.EMOTIONS, function(data){
			try{
				if(data.state == 'success') {
					var nav_arr = [],
							index = 0;
					

					emotions_pl.html('');

					//循环读取菜单和表情
					$.each(data.data,function(i, o){
						index++;
						nav_arr.push('<li class="'+ (index === 1 ? 'current' : '') +'"><a href="">'+ o.category +'</a></li>');

						var emotion_arr = [],
								page_count = Math.ceil(o['emotion'].length/page_size);

						$.each(o.emotion, function(i, o){
							emotion_arr.push('<li><a href="#" class="J_emotions_item" data-sign="'+ o.sign +'"><img '+ (index === 1 ? 'src=\"'+o.url+'\"' : 'data-src=\"'+o.url+'\"') +'></a></li>');
						});


						
						
						//翻页写入
						if(page_count > 1) {
							emotions_pl.append('<div><ul class="cc">'+ showPage( 1, o.emotion) +'</ul></div>');

							var page = [];
							for(var i = 1; i <= page_count; i++) {
								page.push('<a href="" class="'+ ( i===1 ? 'current':'' ) +' J_emotions_page">'+ i +'</a>');
							}

							//点击页码
							emotions_pl.on('click', 'a.J_emotions_page', function(e){
								e.preventDefault();
								var $this = $(this);
								$this.parent().prev('ul').html(showPage( parseInt(this.innerHTML), o.emotion));
								$this.addClass('current').siblings().removeClass('current');
							});

							emotions_pl.children('div:first').append('<div class="show_page">'+ page.join('') +'</div>');

						}else{
							//表情写入
							emotions_pl.append('<div><ul class="cc" style="'+ (index === 1 ? '' : 'display:none') +'">'+ emotion_arr.join('') +'</ul></div>');
						}

					});

					//菜单写入
					emotions_menu.prepend('<div class="hd"><ul class="cc">'+ nav_arr.join('') +'</ul></div>');

					//点击菜单
					emotions_menu.on('click', 'a', function(e){
						e.preventDefault();
						var container = emotions_pl.children(':eq('+ $(this).parent().index() +')');

						$(this).parent().addClass('current').siblings().removeClass('current');
						container.show().siblings().hide();

						showImg(container);
					
					});

					

					//关闭
					$('#J_emotions_close').on('click', function(e){
						e.preventDefault();
						emotions_pop.hide();
					});

				}else if(data.state == 'fail'){
					resultTips({
						error : true,
						msg : data.message[0]
					});
				}

			}catch(e) {
				$.error(e);
			}	

		});

	}


	//点击表情
	$('#J_emotions_pl').off('click').on('click', 'a.J_emotions_item', function(e){
		e.preventDefault();

		//jquery.insertContent
		input.insertContent($(this).data('sign'));

		$('#J_emotions_pop').hide();
	});
	
}

//显示当前页
function showPage(index, data){
	var len = (index*page_size > data.length ? data.length : index*page_size),
			arr = [];

	for(var i = (index-1)*page_size; i <= len - 1; i++) {
		arr.push('<li><a href="#" class="J_emotions_item" data-sign="'+ data[i].sign +'"><img src="'+data[i].url+'"></a></li>');
	}

	return arr.join('');
}

//图片src写入
function showImg(wrap){
	var imgs = wrap.find('img');
	if(imgs.data('src')) {
		imgs.attr('src', function () {
			return $(this).data('src');
			}).data('src', '');
	}
}

//表情弹窗定位
function emotionsPos(elem, pop, wrap){
	if(wrap) {
		//容器内计算边距
		pop.css({
			left : elem.offset().left - wrap.offset().left - 30,
			top : elem.offset().top - wrap.offset().top + elem.height() + 5
		});
	}else{
		pop.css({
			left : elem.offset().left - 25,
			top : elem.offset().top + elem.outerHeight() + 15
		});
	}
	
}


/**
 * Created by 愚人码头 .
 * User: 愚人码头
 * Date: 11-5-19
 * Time: 上午10:24
 * 更多查看http://www.css88.com/archives/3627
 */
//在光标位置插入内容
(function($){
	$.fn.extend({
		insertContent: function(myValue,t){
			var $t=$(this)[0];

			if (document.selection) {//ie
				this.focus();
				var sel = document.selection.createRange();
				sel.text = myValue;
				this.focus();
                sel.moveStart ('character', -l);
		        var wee = sel.text.length;
                if(arguments.length == 2){
                    var l = $t.value.length;
                    sel.moveEnd("character", wee+t );
                    t<=0?sel.moveStart("character",wee-2*t-myValue.length):sel.moveStart("character",wee-t-myValue.length);

                    sel.select();
                }
			} else if ($t.selectionStart || $t.selectionStart == '0') {
                var startPos = $t.selectionStart;
                var endPos = $t.selectionEnd;
                var scrollTop = $t.scrollTop;
                $t.value = $t.value.substring(0, startPos) + myValue + $t.value.substring(endPos, $t.value.length);
                this.focus();
                $t.selectionStart = startPos + myValue.length;
                $t.selectionEnd = startPos + myValue.length;
                $t.scrollTop = scrollTop;
                if(arguments.length == 2){
                    $t.setSelectionRange(startPos-t,$t.selectionEnd+t);
                    this.focus();
                }
            }
            else {
                this.value += myValue;
                this.focus();
            }
		}
	})
})(jQuery);
