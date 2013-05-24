/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-投票
 * @Author	: linhao87@gmail.com
 * @Depend	: jquery.js(1.7 or later), 由页面定义
 * $Id: vote_index.js 6595 2012-03-21 13:50:55Z hao.lin $
 */
 
Wind.use('jquery.form', function(){
	var vote_list_ul = $('ul.J_vote_list_ul');
	
	//更多
	$('a.J_vote_down').on('click', function(e) {
		e.preventDefault();
		var $this = $(this),
			role = $this.data('role'),
			tid = $this.data('tid');

		$this.parent().hide().siblings('.J_vote_options').show();
		$('#J_vote_list_'+ tid).find('.J_dn').fadeIn();

	});
	
	//收起
	$('a.J_vote_up').on('click', function(e){
		e.preventDefault();
		var $this = $(this),
			tid = $this.data('tid');
		
		$('#J_vote_list_'+ tid).find('.J_dn').hide();
		$this.parent().hide().siblings('.J_vote_more').show();
	});
	
	//点击投票区
	vote_list_ul.on('click', function(e){
		var $this = $(this),
			elem_hide = $this.find('.J_dn:hidden');

		//显示隐藏元素
		elem_hide.fadeIn();
		
		//显示投票按钮，隐藏“更多”
		$this.parent().siblings('.J_vote_options:hidden').show().siblings('.J_vote_more').hide();
	});
	
	//列表提交
	$('button.J_vote_list_sub').on('click', function(e){
		e.preventDefault();

		$('#J_vote_form_'+ $(this).data('tid')).ajaxSubmit({
			dataType : 'json',
			success : function(data){
				if(data.state === 'success') {
					resultTip({
						msg : '投票成功',
						callback : function(){
							reloadPage(window);
						}
					});
				}else if(data.state === 'fail'){
					resultTip({
						error : true,
						msg : data.message[0]
					});
				}
			}
		});
	});
	
	//投票多选限制
	$.each(vote_list_ul, function(i, o){
		var $this = $(this),
			vote_checkbox = $this.find('input:checkbox'),			//投票框
			vote_max = parseInt($this.data('max'));					//多选数
			
		if(vote_max) {
		//存在最多项限制
			vote_checkbox.on('change', function(){
			
				//选中数是否等于多选数
				if(vote_checkbox.filter('input:checkbox:checked').length === vote_max) {
					$.each(vote_checkbox, function(){
						if(!$(this).prop('checked')) {
							//未选中项不可用
							$(this).prop('disabled', true);
						}
					});
				}else{
					vote_checkbox.filter(':disabled').prop('disabled', false);
				}
			});
		}
	});
	
	
});