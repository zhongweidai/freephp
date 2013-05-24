/*!
 * PHPWind PAGE JS
 * 后台-添加/编辑用户
 * Author: linhao87@gmail.com
 */
$(function(){
    Wind.use('dialog', 'jquery.form', function () {

        //添加角色
        $('#J_auth_role_add').click(function(e){
            e.preventDefault();
            var sel_val = $('#J_roles').val(),
            has_role = $('#J_user_roles > option[value = "' + sel_val + '"]');
            if (sel_val && !has_role.length) {
                $('#J_roles option:selected').clone().appendTo($('#J_user_roles'));
            }
        });
        
        //移除角色
        $('#J_auth_role_del').click(function(e){
            e.preventDefault();
            var user_sel_val = $('#J_user_roles').val();
            if (user_sel_val) {
                $('#J_user_roles > option[value = "' + user_sel_val + '"]').remove();
            }
        });
        
        //提交
		var submit_tips = $('#J_submit_tips');
        $('#J_auth_sub').click(function(e){
            //全选拥有角色的select
			$('#J_user_roles > option').prop('selected', true);
			
			$('#J_auth_form').ajaxForm({
				dataType	: 'json',
				success     : function(data){
					if (data.state === 'success') {
						submit_tips.attr('class', 'tips_success').text(data.message).slideDown('fast');
						setTimeout(function(){
							window.parent.location.href = window.parent.location.pathname + window.parent.location.search;
							window.parent.Wind.dialog.closeAll();
						}, 1500);
					}else{
						submit_tips.attr('class', 'tips_error').text(data.message).slideDown('fast');
					}
				}
			});
        });
		
    });
});