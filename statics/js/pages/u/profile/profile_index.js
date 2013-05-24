/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-设置-资料
 * @Author	: linhao87@gmail.com
 * @Depend	: jquery.js(1.7 or later), dialog, jquery.form, tabs, jquery.draggable, URL_WORK_ADD和URL_WORK_EDIT 由profile_layout.htm定义
 * $Id$
 */
 
;(function(){
	//聚焦时默认提示
	var focus_tips = {
		homepage : '请输入有效的URL地址，以http://开头',
		profile : '您最多可以输入250字',
		bbs_sign : '您最多可以输入500字'
	};
	
	$("form.J_profile_form").validate({
		errorPlacement: function(error, element) {
			//错误提示容器
			console.log(error)
			$('#J_profile_tip_'+ element[0].name).html(error);
		},
		errorElement: 'span',
		focusInvalid : false,
		//invalidHandler : false, //未验证通过 回调
		//ignore : '.ignore' 忽略验证
		//onkeyup : true,
		errorClass : 'tips_error',
		validClass		: 'tips_success',
		onkeyup : false,
		rules: {
			homepage: {
				url	: true
			},
			profile : {
				maxlength : 250
			},
			bbs_sign : {
				maxlength : 500
			},
			mobile : {
				number : true
			},
			telphone : {
				telphone : true
			},
			zipcode : {
				zipcode : true
			}
		},
		highlight	: false,
		unhighlight	: function(element, errorClass, validClass) {
			var tip_elem = $('#J_profile_tip_'+ element.name);
			tip_elem.html('');
		},
		onfocusin	: function(element){
			var id = element.name;
			$('#J_profile_tip_'+ id).html(focus_tips[id]);
		},
		onfocusout : function(element){
			$('#J_profile_tip_'+ element.name).html('');
		},
		messages: {
			homepage : {
				url : '请输入有效的URL地址'
			},
			profile : {
				maxlength : '最多只能输入250字'
			},
			bbs_sign : {
				maxlength : '最多只能输入500字'
			},
			mobile : {
				number : '格式错误，仅支持数字'
			},
			telphone : {
				number : '格式错误，仅支持数字'
			}
		},
		submitHandler:function(form) {
			//提交
			$(form).ajaxSubmit({
				dataType : 'json',
				success : function(data){
					if(data.state === 'success') {
						resultTip({
							msg : data.message[0],
							callback : function(){
								//window.location.reload();
							}
						});
						//window.location.reload();
					}else if(data.state === 'fail'){
						resultTip({
							error : true,
							msg : data.message[0]
						});
					}
				}
			});
		}
	});
	
	//邮箱后缀匹配 jquery.emailAutoMatch
	$('#J_profile_email').emailAutoMatch();
	
	//工作经历
	var work_op_wrap = $('#J_work_op_wrap'),				//添加编辑栏
		work_form = $('#J_work_form'),							//表单
		work_company = $('#J_work_company'),				//单位
		edit_id = $('#J_edit_id'),										//编辑id
		work_add = $('#J_work_add'),
		work_none = $('#J_work_none');
	
	
	//添加工作
	work_add.on('click', function(e){
		e.preventDefault();
		work_op_wrap.insertAfter($('#J_work_list > li:eq(0)')).show().siblings(':hidden').show();
		work_form.resetForm();
		work_form.attr('action', URL_WORK_ADD);			//修改提交地址
		work_company.focus();
		edit_id.val('');
		work_add.hide();
		work_none.hide();
	});
	
	//添加工作_空
	$('#J_work_none > a').on('click', function(e){
		e.preventDefault();
		work_none.hide();
		work_add.trigger('click');
	});
	
	//工作编辑
	$('a.J_work_edit').on('click', function(e){
		e.preventDefault();
		var $this = $(this),
			parent = $this.parents('li');
		
		parent.hide();
		parent.siblings(':hidden').show();
		work_add.show();
		work_op_wrap.insertAfter(parent).show();
		work_form.attr('action', URL_WORK_EDIT);		//修改提交地址
		work_company.val($this.data('company'));		//写入公司名
		edit_id.val($this.data('id'));								//编辑id
		
		//写入年月
		$('#J_starty').val($this.data('starty'));
		$('#J_startm').val($this.data('startm'));
		$('#J_endy').val($this.data('endy'));
		$('#J_endm').val($this.data('endm'));
		
		work_company.focus();
	});
	
	//工作提交
	work_form.ajaxForm({
		dataType : 'json',
		success : function(data){
			if(data.state === 'success') {
				work_op_wrap.hide();
				window.location.reload();
			}else if(data.state === 'fail'){
				resultTip({
					error : true,
					msg : data.message[0]
				});
			}
		}
	});
	
	//删除工作 global.js
	$('a.J_work_del').on('click', function(e){
		e.preventDefault();
		ajaxConfirm({
			href : $(this).attr('href'),
			elem : $(this)
		});
	});
	
	//取消
	$('#J_work_cancl').on('click', function(e){
		e.preventDefault();
		work_op_wrap.siblings(':hidden').show();
		work_op_wrap.hide();
		work_add.show();
		work_none.show();
	});
})();