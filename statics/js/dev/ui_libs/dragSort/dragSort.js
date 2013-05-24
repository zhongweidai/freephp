/**
 * PHPWind util Library
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 图片拖拽排序
 * @Author	: linhao87@gmail.com
 * @Depend	: jquery.js(1.7 or later), jquery.draggable
 * $Id: dragSort.js 6768 2012-03-23 09:34:54Z hao.lin $
 */
;(function ( $, window, document, undefined ) {
    var pluginName = 'dragSort';
    var defaults = {
			elem_click : undefined
    };

    function Plugin( element, options ) {
        this.element = element;
        this.options = $.extend( {}, defaults, options) ;
        this.init();
    }
   
    Plugin.prototype = {
		init : function (){
			var element = this.element,
				options = this.options,
				unit_length = options.unit_length,
				elem_click = options.elem_click,
				count = element.children('li').length,
				holder	= $(options.holder),		//占位元素
				timer;
				
				
				var jj = $('#JJ');
			//	
			
			element.on('click', elem_click, function(e){
				e.preventDefault();
			}).on('mousedown', elem_click, function(){
				//鼠标按下
				var element_padding = Number(element.css('paddingLeft').replace('px', '')),	//内边距
					element_left = element.offset().left + element_padding,								//容器left距离
					element_top = element.offset().top + element_padding,							//容器top距离
					item_width = element.children('li').innerWidth(),										//拖动元素宽
					item_height = element.children('li').outerHeight('includeMargin');				//拖动元素高
					//jj.text(element_top);
				var $this = $(this),				//
					index,
					_index,
					li = $this.parent('li');		//移动元素

				//重新定位，写入&移除id，移到最后
				$('#J_sort_move').removeAttr('id').css({
					position : 'relative',
					left : '0',
					top : '0',
					zIndex : '1'
				});
				
				li.css({
					position : 'absolute',
					left : li.offset().left - element.offset().left,
					top : li.offset().top - element.offset().top,
					zIndex : '2'
				}).attr('id', 'J_sort_move');
				
				
				var insert_pos = element.children('li:eq('+ li.index() +')');	//插入位置
				
				//写入占位元素
				if(!$('#J_sort_holder').length) {
					holder.insertBefore(insert_pos);
				}else{
					if(index !== _index) {
						$('#J_sort_holder').insertBefore(insert_pos);
					}
				}
				
				_index = $('#J_sort_holder').index();
				
				//拖拽
				$this.on('mousemove', function(e){
					var center_left = $this.offset().left - element_left + $this.outerWidth()/2,								//移动元素中心位置的左边距
						center_top = $this.offset().top - element_top + $this.outerHeight()/2;								//移动元素中心位置的上边距
	
					index = Math.floor(center_left/item_width) + (Math.floor(center_top/item_height) * unit_length);		//移动元素在容器中的的索引值，计算规则（x + y*z）
					
					if(index <= 0) {
						index = 0;
					}else if(index >= count-1){
						index = count-1;
					}
					
					//原始位置的索引值
					
					//console.log(typeof index);
					if(_index === index) {
						//没移出
						return false;
					}else{
						clearTimeout(timer);
				
						timer = setTimeout(function(){
							//var pos = element.children('li:eq('+ index +')');	//插入位置
							//先清除占位元素
						//	var _holder = $('#J_sort_holder');
							//_holder.remove();

							if(_index < index) {
								//向右移
								holder.remove().insertAfter(element.children('li:eq('+ parseInt(index) +')'));
							}else if(_index > index) {
								//向左
								//$('#J_temp').css({width : '0'}).insertBefore($('#J_medal_show_list li:eq('+ index +')')).animate({width : '170px'},'800');
		
								var v =0;
								jj.text(_index+' '+ index);
								if(index !== 0) {
									if(holder.index() <= li.index()) {
										//左侧
										v = index
									}else{
										//已向右移动，再向左
										v = parseInt(index+1);
									}
									
								}
								holder.remove().insertBefore(element.children('li:eq('+ v +')'));
							}
							_index = index;
						}, 10);
				

					}

				});
			}).on('mouseup', 'a.J_medal_order', function(){
				//鼠标抬起
				clearTimeout(timer);
				var $this = $(this),
					li = $this.parent();
					
					li.removeAttr('id').css({
							position : 'relative',
							zIndex : '1',
							left : 0,
							top : 0
						}).insertBefore('#J_sort_holder');
						$('#J_sort_holder').remove();
					//将移动元素插入新位置
					/* li.removeAttr('id').animate({left : '0',top : '0'}, function(){
						li.css({
							position : 'relative',
							zIndex : '1'
							
						}).insertBefore('#J_sort_holder');
						$('#J_sort_holder').remove();
					}); */
					
					//清楚空位
					

			});
			
		}
    };

    $.fn[pluginName] = Wind[pluginName]= function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName, new Plugin( $(this), options ));
            }
        });
    };

})( jQuery, window ,document );

