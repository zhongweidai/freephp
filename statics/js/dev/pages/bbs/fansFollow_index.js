/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-关注粉丝
 * @Author	: linhao87@gmail.com
 * @Depend	: jquery.js(1.7 or later), global.js, URL_UNFOLLOW, URL_FOLLOW
 * $Id$
 */

;(function(){
	var friends_items = $('.J_friends_items');
/*
 * 显示隐藏取消关注
*/
	var unfollow_btn = $('a.J_unfollow_btn');
	friends_items.hover(function(){
		$(this).find('a.J_unfollow_btn').fadeIn('fast');
	}, function(){
		$(this).find('a.J_unfollow_btn').fadeOut('fast');
	});


/*
 * 我的粉丝 关注&取消关注，切换显示
*/
	friends_items.on('click', 'a.J_fans_follow', function(e){
		e.preventDefault();
		var $this = $(this),
				role = $this.data('role'),
				uid = $this.data('uid'),
				url = (role == 'follow' ? URL_FOLLOW : URL_UNFOLLOW);			//提交地址

		//global.js
		ajaxMaskShow();

		$.post(url, {uid : uid} ,function(data){
			if(data.state == 'success') {
				//global.js
				ajaxMaskRemove();

				var parent = $this.parent();
				if(role == 'follow') {
					parent.html('<span title="互相关注" class="mnfollow">互相关注</span><a class="core_unfollow J_unfollow_btn J_fans_follow" data-role="unfollow" data-uid="'+ uid +'" href="#">取消关注</a>');
				}else{
					parent.html('<a class="core_follow J_fans_follow" data-role="follow" data-uid="'+ uid +'" href="#">加关注</a>');
				}
				
			}else if(data.state == 'fail'){
				//global.js
				resultTip({
					error : true,
					msg : data.message[0],
					follow : $this
				});
			}
		}, 'json');
	});


/*
 * 找人&脚印等 关注&取消关注，切换显示
*/

//J_friends_btn

})();