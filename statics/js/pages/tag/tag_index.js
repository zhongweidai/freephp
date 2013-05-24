/*!
 * PHPWind PAGE JS
 * 后台-话题前台页
 * Author: linhao87@gmail.com
 */
 
 ;(function(){
	//hover右侧话题
	if($.browser.msie && $.browser.version < 7) {
		$('ul.J_side_tag_list > li').hover(function(e){
			$(this).addClass('current');
		}, function(e){
			$(this).removeClass('current');
		});
	}
	
	//删除右侧话题
	$('ul.J_side_tag_list').on('click', 'a.J_tag_del', function(e){
		e.preventDefault();
		var $this = $(this);
		$.getJSON($this.attr('href'), function(data){
			if(data.state === 'success') {
				$this.parent('li').slideUp('slow', function(){
					$(this).remove();
				});
				
				$('#J_tag_item_'+ $this.data('id')).slideUp('slow', function(){
					$(this).remove();
				});
			}else{
				resultTip({
					error : true,
					msg : data.message[0]
				});
			}
		});
	});

 
	//载入更多
	var tag_more = $('#J_tag_more');
	tag_more.on('click', function(e){
		e.preventDefault();
		var $this = $(this),
			li_arr = [];
		
		$.getJSON($this.attr('href'), function(data){
			$.each(data.tags, function(i, o) {
				li_arr.push('<li><a class="icon_del J_tag_del" data-id="'+ o.tag_id +'" href="#">删除</a><a class="title" href="#">'+ o.	
tag_name +'<em>('+ o.content_count +')</em></a></li>');
			});
			
			$(li_arr.join('')).hide().insertBefore(tag_more.parent('li')).slideDown('fast');
			
			//全部载完毕
			if(!data.step){
				$this.parent().remove();
				return false;
			}
		});
	});
	
 })();