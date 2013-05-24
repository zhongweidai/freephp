/**
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前后台-地区组件
 * @Author	: linhao87@gmail.com
 * @Depend	: core.js、jquery.js(1.7 or later), jquery.form, REGION_JSON页面定义，GV.REGION_CONFIG head全局变量
 * $Id$
 */

var region_temp = $('<div class="core_pop_wrap" id="J_region_pop">\
	<div class="core_pop">\
		<div style="width:600px;">\
			<div class="pop_top">\
				<a href="#" class="pop_close J_region_close">关闭</a>\
				<strong>选择地区</strong>\
			</div>\
			<div class="pop_cont">\
				<div id="J_region_pl" class="pop_loading"></div>\
			</div>\
			<div class="pop_bottom">\
				<button type="submit" class="btn btn_submit mr10 disabled J_region_pop_ok" disabled="disabled">确定</button><button type="button" class="btn J_region_close">关闭</button>\
			</div>\
		</div>\
	</div>\
</div>'),

region_pl = $('<div class="pop_region_list">\
					<ul id="J_region_pop_province" class="cc"></ul>\
					<div class="hr"></div>\
					<ul id="J_region_pop_city" class="cc">\
						<li><span>请选择</span></li>\
					</ul>\
					<div class="hr"></div>\
					<ul id="J_region_pop_district" class="cc">\
						<li><span>请选择</span></li>\
					</ul>\
					<div class="hr"></div>\
					<div id="J_school_wrap" style="display:none;"></div>\
				</div>');



$('a.J_region_change').on('click focus', function (e) {
	e.preventDefault();
	var $this = $(this),
			wrap = $this.parents('.J_region_set'),
			current_province = wrap.find('.J_province'),
			current_city = wrap.find('.J_city'),
			current_district = wrap.find('.J_district');

	regionInit(current_province.data('id'), current_city.data('id'), current_district.data('id'), $this.data('rank'), wrap, 'region');
});

function regionInit(pid, cid, did, rank, wrap, type){
	/*if(!GV.REGION_CONFIG) {
		return
	}*/

	var region_pop = $('#J_region_pop');

	if(region_pop.length) {
		//隐藏弹窗显示
		region_pop.show();

		hideRank(rank);
		getChild(region_pop, rank, type);

		if(pid) {
			$('#J_province_'+ pid).addClass('current').siblings().removeClass('current');					//弹窗选中当前省
				getCity(pid, cid, did);									//弹窗选中当前市
		}else{
			//重置
			$('#J_region_pop_province > li').removeClass('current');
			$('#J_region_pop_city, #J_region_pop_district').html('<li><span>请选择</span></li>');
			btnDisable();
		}

		//global.js
		popPos(region_pop);

		//确定
		subOk(wrap, region_pop);
	}else{
		//组装添加弹窗
		region_temp.appendTo('body');

		var region_pop = $('#J_region_pop');

		//global.js
		popPos(region_pop);

		//if(!GV.REGION_CONFIG) {
		//获取地区数据
		$.getJSON(GV.URL.REGION, function(data){
			if(data) {
				GV.REGION_CONFIG = data;

				$('#J_region_pl').replaceWith(region_pl);
				
				var region_pop_province = $('#J_region_pop_province'),
						region_pop_city = $('#J_region_pop_city');

				//写入省的html
				region_pop_province.html(showProvince());

				hideRank(rank);

				if(pid) {
					//有默认值
					$('#J_province_'+ pid).addClass('current').siblings().removeClass('current');
					getCity(pid, cid, did);
				}
					
				//显示
				region_pop.show(0, function(){
					//引入弹窗拖动组件
					Wind.use('jquery.draggable',function() {
						region_pop.draggable( { handle : '.pop_top'} );
					});
				});

				getChild(region_pop, rank, type);
				regionClose(region_pop);

				//确定
				subOk(wrap, region_pop);

				//global.js
				popPos(region_pop);

				//调用学校方法
				if(type == 'school') {
					GV.REGION_CONFIG.load = true;
					setSchool($('input.J_plugin_school:focus'));
				}
				

			}
		});
		//}

	}
				
}

function showProvince(){
	//显示省
	var province_arr = [];

	//循环省数据
	$.each(GV.REGION_CONFIG, function(i, o){
		province_arr.push('<li id="J_province_'+ i +'" data-id="'+ i +'" data-child="city" data-role="province"><a href="#" class="J_item">'+ o.name +'</a></li>');
	});
			
	return province_arr.join('');
}

function getCity(pid, cid, did){
	//获取城市
	var arr= [],
		data = GV.REGION_CONFIG[pid]['items'];
				
	$.each(data, function(i, o){
		arr.push('<li id="J_city_'+ i +'" data-id="'+ i +'" data-child="district" data-role="city"><a href="#" class="J_item">'+ o.name +'</a></li>');
	});
			
	//重置区县
	$('#J_region_pop_district').html('<li><span>请选择</span></li>');
			
	//写入城市
	$('#J_region_pop_city').html('<li class="current" data-id=""><a href="#" class="J_item">请选择</a></li>'+ arr.join(''));
			
	if(cid){
		//已设城市
		$('#J_city_'+ cid).addClass('current').siblings().removeClass('current');
		getDistrict(data[cid]['items'], did);
	}

}

//获取区县
function getDistrict(data, did){
	var arr= [];
	$.each(data, function(i, o){
		arr.push('<li id="J_district_'+ i +'" data-id="'+ i +'" data-child="" data-role="district"><a href="#" class="J_item" data-role="district">'+ o +'</a></li>');
	});
	$('#J_region_pop_district').html('<li class="current" data-id=""><a href="#" class="J_item">请选择</a></li>'+ arr.join(''));
			
	if(did){
		//高亮当前区县
		$('#J_district_'+ did).addClass('current').siblings().removeClass('current');
	}

}

//弹窗点击获取下级数据
function getChild(wrap, rank, type){
	
	wrap.on('click', 'a.J_item', function(e){
		e.preventDefault();
		var $this = $(this),
				li = $this.parent(),
				ul = li.parents('ul'),
				id = li.data('id'),
				child = li.data('child');
		
		li.addClass('current').siblings('li.current').removeClass('current');
				
		if($this.data('role') == 'district') {
			if(type !== 'school') {
				//学校 提交不可用
				btnRemoveDisable();
			}
			return;
		}

		if(rank == 'province') {
			//直到省
			//_this.btnRemoveDisable();
			return;
		}

		btnDisable();



		if(!id) {
			//点击“请选择”
			ul.nextAll('ul').html('<li><span>请选择</span></li>');
		}else{
			//点击省
			var data, arr = [];
					
			if(child == 'city') {
				getCity(id);
			}else if(child == 'district'){
				data = GV.REGION_CONFIG[$('#J_region_pop_province > li.current').data('id')]['items'][id]['items'];
				getDistrict(data);
			}

		}
		
	});
}

//确认不可点
function btnDisable(){
	$('button.J_region_pop_ok').addClass('disabled').attr('disabled', 'disabled');
}

//确认可点
function btnRemoveDisable(){
	$('button.J_region_pop_ok').removeClass('disabled').removeAttr('disabled');
}


//确认
function subOk(wrap, pop){
	if(wrap) {
			$('button.J_region_pop_ok').off('click').on('click', function(e){
				e.preventDefault();
				var current_lis = pop.find('ul > li.current');
				
				current_lis.each(function(i, o){
					wrap.find('.J_'+ $(this).data('role')).data('id', $(this).data('id')).text($(this).text());
				});

				wrap.find('input.J_areaid').val($('#J_region_pop_district > li.current').data('id'));
				pop.hide();
			});
	}
}

//关闭
function regionClose(wrap){
	wrap.on('click', '.J_region_close', function(e){
		e.preventDefault();
		wrap.hide();
	});
}

//隐藏省市级别
function hideRank(rank){
	var region_pop_city = $('#J_region_pop_city'),
			region_pop_district = $('#J_region_pop_district');

	if(rank == 'province') {
		region_pop_city.hide().next().hide();
		region_pop_district.hide().next().hide();
	}else{
		region_pop_city.show().next().show();
		region_pop_district.show().next().show();
	}
}