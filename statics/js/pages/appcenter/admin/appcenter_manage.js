/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 后台-应用中心
 * @Author	: linhao87@gmail.com
 * @Depend	: jquery.js(1.7 or later), dialog
 * $Id$
 */
 
 ;(function(){

	//ajax请求刷新操作
	$('a.J_ajax_refresh').on('click', function(e){
		e.preventDefault();
		var $this = $(this);
		$.getJSON($this.attr('href'), function(data){
			if(data.state == 'success') {
				if(data.referer) {
					window.location.href = data.referer;
				}else{
					window.location.reload();
				}
			}else if(data.state == 'fail'){
				Wind.dialog.alert(data.message[0]);
			}
		});
	});
 })();