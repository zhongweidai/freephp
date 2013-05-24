<?php defined('IN_FREE') or exit('No permission resources.'); ?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<meta charset="utf-8" />
		<title><?php echo L('admin_site_title');?></title>
		<link href="<?php echo CSS_PATH;?>admin_layout.css" rel="stylesheet" />
		<style>
			.fullScreen .content th{display:none;width:0;}
			.fullScreen .head,.fullScreen .tab{height:0;display:none;}
			.fullScreen #default{*left:0;*top:-90px;}
			.fullScreen div.options{top:0;}
		</style>
		<script>
			if (window.top !== window.self) {
			    document.write = '';
			    window.top.location.href = window.self.location.href;
			    setTimeout(function () {
			        document.body.innerHTML = '';
			    }, 0);
			}
		</script>
	</head>
	<body>
		<div class="wrap">
			<noscript><h1 class="noscript">您已禁用脚本，这样会导致页面不可用，请启用脚本后刷新页面</h1></noscript>
			<table width="100%" height="100%" style="table-layout:fixed;">
				<tr class="head">
					<th><a href="<?php echo U('admin/index/init');?>" class="logo">管理中心</a></th>
					<td>
					<div class="nav">
						<!-- 菜单异步获取，采用json格式，由js处理菜单展示结构 -->
						<ul id="J_B_main_block">

						</ul>
					</div>
					<div class="login_info">
                        <select name="current_showmodule" class="J_ajaxChange" class="select_1" href="<?php echo U('admin/index/changeSiteid');?>">
                            <?php $n=1;if(is_array($sites)) foreach($sites AS $v) { ?>
                            <option value="<?php echo $v['CODE'];?>" <?php if($v['CODE'] == $siteid) { ?>selected="selected"<?php } ?>><?php echo $v['NAME'];?></option>
                                <?php $n=1;if(is_array($v['items'])) foreach($v['items'] AS $c) { ?>
                                <option value="<?php echo $c['CODE'];?>" <?php if($c['CODE'] == $siteid) { ?>selected="selected"<?php } ?>>├<?php echo $c['NAME'];?></option>
                                <?php $n++;}unset($n); ?>
                            <?php $n++;}unset($n); ?>
                        </select>
                        <select name="current_showmodule" class="J_ajaxChange" class="select_1" href="<?php echo U('admin/index/changeShowmodule');?>">
                            <option value="1" <?php if(FreeCookie::get('free_admin_showmodule') == 1) { ?>selected="selected"<?php } ?>>WEB</option>
                            <option value="2" <?php if(FreeCookie::get('free_admin_showmodule') == 2) { ?>selected="selected"<?php } ?>>WAP</option>
                        </select>
                        <?php if(FreeCookie::get('free_admin_showmodule') == '2') { ?>
						<a href="<?php echo U('', array(), '', '', 'wap');?>" class="home" target="_blank" id="web_frontend">前台首页</a>
                        <?php } else { ?>
                        <a href="<?php echo U('', array(), '', '', 'web');?>" class="home" target="_blank" id="wap_frontend">前台首页</a>
                        <?php } ?>
                        <span class="mr10">管理员：  <?php echo $adminuser['username'];?> </span><a href="<?php echo U('admin/index/loginout');?>" class="mr10">[注销]</a>
					</div></td>
				</tr>
				<tr class="tab">
					<th>
					<div class="search">
						<input id="keyword" size="15" placeholder="Hi，freePro！" type="text">
						<button type="button" name="keyword" id="J_search" value="">搜</button>
					</div></th>
					<td>
					<div id="B_tabA" class="tabA">
						<a href="" tabindex="-1" class="tabA_pre" id="J_prev" title="上一页">上一页</a>
						<a href="" tabindex="-1" class="tabA_next" id="J_next" title="下一页">下一页</a>
						<div style="margin:0 25px;min-height:1px;">
							<div style="position:relative;height:30px;width:100%;overflow:hidden;">
								<ul id="B_history" style="white-space:nowrap;position:absolute;left:0;top:0;">
									<li class="current" data-id="default" tabindex="0"><span><a>后台首页</a></span></li>
								</ul>
							</div>
						</div>
					</div></td>
				</tr>
				<tr class="content">
					<th  style="overflow:hidden;">
						<div id="B_menunav">
							<div class="menubar">
								<dl id="B_menubar">
									<dt class="disabled"></dt>
								</dl>
							</div>
							<div id="menu_next" class="menuNext" style="display:none;">
								<a href="" class="pre" title="顶部超出，点击向下滚动">向下滚动</a>
								<a href="" class="next" title="高度超出，点击向上滚动">向上滚动</a>
							</div>
						</div>
					</th>
					<td id="B_frame">
						<div id="breadCrumb" style="display:none;">
							首页<em>&gt;</em>功能<em>&gt;</em>功能
						</div>
						<div class="options">
							<a href="" class="refresh" id="J_refresh" title="刷新">刷新</a>
							<a href="" id="J_fullScreen" class="full_screen" title="全屏">全屏</a>
						</div>
						<div class="loading" id="loading">加载中...</div>
						<iframe id="iframe_default" src="<?php echo U('admin/home/init');?>" style="height: 100%; width: 100%;display:none;" data-id="default" frameborder="0" scrolling="auto"></iframe>
					</td>
				</tr>
			</table>
		</div>
<?php include $this->_view->templateResolve("admin","footer",$__style__,$__app__); ?>
<script>
//iframe 加载事件
var iframe_default = document.getElementById('iframe_default');
$(iframe_default.contentWindow.document).ready(function() {
	$('#loading').hide();
	$(iframe_default).show();
});

var USUALL = [], /*常用的功能模块*/
	TEMP = [],
	SUALL = USUALL.concat('-', [ {
	name : '最近操作',
	disabled : true
} ], TEMP),
	SUBMENU_CONFIG = <?php echo $menus;?>,/*主菜单区*/
	imgpath = '',
	times = 0,
	getdescurl = '',
	searchurl = '',
	token = "";
//一级菜单展示
$(function(){
	var html = [];
	//console.log(SUBMENU_CONFIG);
	$.each(SUBMENU_CONFIG,function(i,o) {
		html.push('<li><a href="" title="'+ o.tip +'" data-id="'+ o.id +'">'+ o.name +'</a></li>');
	});
	$('#J_B_main_block').html(html.join(''));
});

function checkMenuNext() {
	var B_menunav = $('#B_menunav');
	var menu_next = $('#menu_next');
	if(B_menunav.offset().top + B_menunav.height() >= $(window).height() || B_menunav.offset().top < B_menunav.parent().offset().top) {
		menu_next.show();
	}else {
		menu_next.hide();
	}
}

$(window).on('resize',function() {
	setTimeout(function() {
		checkMenuNext();
	},100);
});

//上一页下一页的点击
(function(){
	var menu_next = $('#menu_next');
	var B_menunav = $('#B_menunav');
	menu_next.on('click','a',function(e) {
		e.preventDefault();
		if(e.target.className === 'pre') {
			if(B_menunav.offset().top < B_menunav.parent().offset().top) {
				B_menunav.animate({'marginTop':'+=28px'},100);
			}
		}else if(e.target.className === 'next'){
			if(B_menunav.offset().top + B_menunav.height() >= $(window).height()) {
				B_menunav.animate({'marginTop':'-=28px'},100);
			}
		}
	});
})();
//一级导航点击
$('#J_B_main_block').on('click','a',function(e) {
	e.preventDefault();
	e.stopPropagation();
	$(this).parent().addClass('current').siblings().removeClass('current');
	var data_id = $(this).attr('data-id'), data_list = SUBMENU_CONFIG[data_id], html = [], child_html = [], child_index=0, B_menubar = $('#B_menubar');

	if(B_menubar.attr('data-id') == data_id){
		return false;
	};
	show_left_menu(data_list['items']);
	B_menubar.html(html.join('')).attr('data-id', data_id);

	//检查是否应该出现上一页、下一页
	checkMenuNext();
	
	//显示左侧菜单
	function show_left_menu(data){
		
		for(var attr in data){
			if(data[attr] && typeof (data[attr]) === 'object') {
			//循环子对象
			
				if(!data[attr].url && attr === 'items'){
					//子菜单添加识别属性
					$.each(data[attr], function(i,o){
						child_index++;
						o.isChild = true;
						o.child_index = child_index;
					});
				}
				show_left_menu(data[attr]); //继续执行循环(筛选子菜单)
			}else{
				if(attr === 'name'){
					data.url = data.url ? data.url : '#';
					if(!(data['isChild'])) {
					//一级菜单
						html.push('<dt><a href="'+ data.url +'" data-id="'+ data.id +'">'+ data.name +'</a></dt>');
					}else{
					//二级菜单
						child_html.push('<li><a href="'+ data.url +'" data-id="'+ data.id +'">'+ data.name +'</a></li>');
						
						//二级菜单全部push完毕
						if(data.child_index == child_index){
							html.push('<dd style="display:none;"><ul>'+child_html.join('')+'</ul></dd>');
							child_html = [];
						}
					}
				}
			}
		}
	};
	
});

//左边菜单点击
$('#B_menubar').on('click','a',function(e) {
	e.preventDefault();
	e.stopPropagation();
	
	
	var $this = $(this), _dt = $this.parent(), _dd = _dt.next('dd');
	
	//当前菜单状态
	_dt.addClass('current').siblings('dt.current').removeClass('current');
	
	//子菜单显示&隐藏
	if(_dd.length){
		_dt.toggleClass('current');
		_dd.toggle();
		//检查上下分页
		checkMenuNext();
		return false;
	};
	
	$('#loading').show().focus();//显示loading
	$('#B_history li').removeClass('current');
	var data_id = $(this).attr('data-id'),li = $('#B_history li[data-id='+ data_id +']');
	var href = this.href;
	if(li.length > 0) {
		//如果是已经存在的iframe，则显示并让选项卡高亮,并不显示loading
		var iframe = $('#iframe_'+ data_id);
		$('#loading').hide();
		li.addClass('current');
		if( iframe[0].contentWindow && iframe[0].contentWindow.location.href !== href ) {
			iframe[0].contentWindow.location.href = href;
		}
		$('#B_frame iframe').hide();
		$('#iframe_'+ data_id).show();
		showTab(li);//计算此tab的位置，如果不在屏幕内，则移动导航位置
	} else {
		//否则动态创建一个并加以标识
		var	iframeAttr = {
				src			: href,
				id			: 'iframe_' + data_id,
				frameborder	: '0',
				scrolling	: 'auto',
				height		: '100%',
				width		: '100%'
			};
		var iframe = $('<iframe/>').prop(iframeAttr).appendTo('#B_frame');
		$(iframe[0].contentWindow.document).ready(function() {
			$('#B_frame iframe').hide();
			$('#loading').hide();
			var li = $('<li tabindex="0"><span><a>'+ $this.html() +'</a><a class="del" title="关闭此页">关闭</a></span></li>').attr('data-id',data_id).addClass('current');
			li.siblings().removeClass('current');
			li.appendTo('#B_history');
			showTab(li);//计算此tab的位置，如果不在屏幕内，则移动导航位置
			//$(this).show().unbind('load');
			
			//计算提交按钮是否固定底部
			/*var vis_height = $(window).height() - 76,	//窗口高度减去顶部固定高度
				iframe_body = $('#iframe_' + data_id)[0].contentWindow.document.body;
			if(vis_height > $('#iframe_' + data_id)[0].contentWindow.document.body.scrollHeight) {
				$(iframe_body).find('div.btn_wrap').removeClass('btn_wrap');
			}*/
			
		});
	}
});

//顶部点击一个tab页
$('#B_history').on('click focus','li',function(e) {
	e.preventDefault();
	e.stopPropagation();
	var data_id = $(this).data('id');
	$(this).addClass('current').siblings('li').removeClass('current');
	$('#iframe_'+ data_id).show().siblings('iframe').hide();//隐藏其它iframe
});

//顶部关闭一个tab页
$('#B_history').on('click','a.del',function(e) {
	e.stopPropagation();
	e.preventDefault();
	var li = $(this).parent().parent(),
		prev_li = li.prev('li'),
		data_id = li.attr('data-id');
	li.hide(60,function() {
		$(this).remove();//移除选项卡
		$('#iframe_'+ data_id).remove();//移除iframe页面
		var current_li = $('#B_history li.current');
		//找到关闭后当前应该显示的选项卡
		current_li = current_li.length ? current_li : prev_li;
		current_li.addClass('current');
		cur_data_id = current_li.attr('data-id');
		$('#iframe_'+ cur_data_id).show();
	});
});

//刷新
$('#J_refresh').click(function(e) {
	e.preventDefault();
	e.stopPropagation();
	var id = $('#B_history .current').attr('data-id'),iframe = $('#iframe_'+ id);
	if(iframe[0].contentWindow) {
		iframe[0].contentWindow.location.reload();
	}
});

//全屏/非全屏
$('#J_fullScreen').toggle(function(e) {
	e.preventDefault();
	e.stopPropagation();
	$(document.body).addClass('fullScreen');
},function(){
	$(document.body).removeClass('fullScreen');
});

//下一个选项卡
$('#J_next').click(function(e) {
	e.preventDefault();
	e.stopPropagation();
	var ul = $('#B_history'),
		current = ul.find('.current'),
		li = current.next('li');
		showTab(li);
});

//上一个选项卡
$('#J_prev').click(function(e) {
	e.preventDefault();
	e.stopPropagation();
	var ul = $('#B_history'),
		current = ul.find('.current'),
		li = current.prev('li');
		showTab(li);
});

//显示顶部导航时作位置判断，点击左边菜单、上一tab、下一tab时公用
function showTab(li) {
	if(li.length) {
		var ul = $('#B_history'),
			li_offset = li.offset(),
			li_width = li.outerWidth(),
			next_left = $('#J_next').offset().left,//右边按钮的界限位置
			prev_right = $('#J_prev').offset().left + $('#J_prev').outerWidth();//左边按钮的界限位置
		if(li_offset.left + li_width > next_left) {//如果将要移动的元素在不可见的右边，则需要移动
			var distance = li_offset.left + li_width - next_left;//计算当前父元素的右边距离，算出右移多少像素
			ul.animate({left:'-='+distance},200,'swing');
		}else if(li_offset.left < prev_right) {//如果将要移动的元素在不可见的左边，则需要移动
			var distance = prev_right - li_offset.left;//计算当前父元素的左边距离，算出左移多少像素
			ul.animate({ left:'+='+distance },200,'swing');
		}
		li.trigger('click');
	}
}

//增强体验，如果支持全屏，则使用更完美的全屏方案
/*Wind.use('jquery.requestFullScreen',function () {
	if(fullScreenApi.supportsFullScreen) {
		$('#J_fullScreen').unbind('click').one('click',function(e) {
			e.preventDefault();
			$('body').requestFullScreen();
		});
	}
})*/

//选择展现方式
$('.J_ajaxChange').change(function(e) {
	e.preventDefault();
	e.stopPropagation();
	var choose_value = $(this).val();
    $.getJSON($(this).attr('href'), {choose_value: choose_value}).done(function(data) {
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
</script>
	</body>
</html>
