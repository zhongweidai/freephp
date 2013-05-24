/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-设置-个性标签
 * @Author	: linhao87@gmail.com
 * @Depend	: jquery.js(1.7 or later), global.js
 * $Id$
 */
 
;(function(){
	var my_tags = $('#J_my_tags'),
			hot_tags = $('#J_hot_tags'),
			tag_form = $('#J_tag_form');

	//global.js
	buttonStatus(tag_form.find('input:text'), tag_form.find('button:submit'));

	//添加标签
	$('#J_tag_form').ajaxForm({
		beforeSubmit : function(arr, $form, options) {
		},
		success : function(data, statusText, xhr, $form) {

		}
	});

	//换一组
	$('a.J_change_tags').on('click', function(e){
		e.preventDefault();
		var rel = document.getElementById($(this).data('rel'));		//替换对象

		$.getJSON(function(data){
			
		});
	});
})();