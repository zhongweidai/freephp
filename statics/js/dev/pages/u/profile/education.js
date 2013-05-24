/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-设置-教育经历
 * @Author	: linhao87@gmail.com
 * @Depend	: jquery.js(1.7 or later), dialog, jquery.form, tabs, jquery.draggable, URL_WORK_ADD和URL_WORK_EDIT 由profile_layout.htm定义
 * $Id$
 */
 
;(function(){

	var edu_list = $('#J_edu_list'),
			edu_op_wrap = $('#J_edu_op_wrap'),				//添加编辑栏
			edu_form = $('#J_edu_form'),							//表单
			edu_select = $('#J_edu_select'),
			edu_input = edu_form.find('input.J_plugin_school'),				//单位
			edit_id = $('#J_edit_id'),										//编辑id
			startyear = $('#J_startyear');
	
	//添加
	$('#J_edu_add').on('click', function(e){
		e.preventDefault();
		edu_op_wrap.insertAfter($('#J_edu_list > li:eq(0)')).show().siblings(':hidden').show();
		edu_form.resetForm();
		edu_form.attr('action', URL_EDU_ADD).data('role', 'add');			//修改提交地址

		edu_input.data({
			typeid : getTypeid(edu_select.val()),
			pid : '',
			cid : '',
			did : '',
			sid : ''
		}).val('');
		/*edit_id.val('');
		work_add.hide();
		work_none.hide();*/
	});

	//学历
	edu_select.on('change', function(){
		var v = $(this).val();

		edu_input.data({
			typeid : getTypeid(v),
			pid : '',
			cid : '',
			did : '',
			sid : ''
		}).val('');

		$('#J_region_pop').hide();
	});
	
	//添加工作_空
	/*$('#J_work_none > a').on('click', function(e){
		e.preventDefault();
		work_none.hide();
		work_add.trigger('click');
	});*/
	
	//学校编辑
	edu_list.on('click', 'a.J_school_edit', function(e){
		e.preventDefault();
		var $this = $(this),
				parent = $this.parents('li');
		
		parent.hide();
		parent.siblings(':hidden').show();
		//work_add.show();
		edu_op_wrap.insertAfter(parent).show();
		edu_form.attr('action', this.href).data('role', 'edit');						//修改提交地址
		edu_select.val($this.data('degreeid'));						//学历
		edu_input.val($this.data('school')).data({
			'pid' : $this.data('pid'),
			'cid' : $this.data('cid'),
			'did' : $this.data('did'),
			'sid' : $this.data('schoolid'),
			'typeid' : getTypeid($this.data('degreeid'))
		});
		
		edit_id.val($this.data('schoolid'));							//学校id
		$('#J_startyear').val($this.data('startyear'));		//入学年份

	});
	
	//提交
	edu_form.ajaxForm({
		dataType : 'json',
		success : function(data){
			if(data.state === 'success') {
				var role = edu_form.data('role');
				edu_op_wrap.hide();

				if(role == 'add') {
					edu_op_wrap.before('<li> <span class="fr">\
						<a class="mr20 J_school_edit" data-startyear="'+ startyear.val() +'" data-schoolid="'+ edu_input.data('sid') +'" data-school="'+ edu_input.val() +'" data-degreeid="'+ edu_input.data('degreeid') +'" data-did="'+ edu_input.data('did') +'" data-cid="'+ edu_input.data('cid') +'" data-pid="'+ edu_input.data('pid') +'" href="#">编辑</a>\
						<a class="J_edu_del" href="#">删除</a></span>\
						<span class="edu">'+ edu_select.children(':selected').text() +'</span> <span class="unit">'+ edu_input.val() +'</span> <span class="time">'+ startyear.val() +'年</span>\
					</li>');

					window.location.reload();
				}else{
					var hidden = edu_op_wrap.siblings(':hidden');
					hidden.show().find('span:eq(1)').text(edu_select.children(':selected').text());
					hidden.find('span:eq(2)').text(edu_input.val());
					hidden.find('span:eq(3)').text(startyear.val() +'年');
					hidden.find('a.J_school_edit').data({
						'startyear' : startyear.val(),
						'schoolid' : edu_input.data('schoolid'),
						'school' : edu_input.val(),
						'pid' : edu_input.data('pid'),
						'cid' : edu_input.data('cid'),
						'did' : edu_input.data('did'),
						'degreeid' : edu_select.val()
					});
				}
			}else if(data.state === 'fail'){
				resultTip({
					error : true,
					msg : data.message[0]
				});
			}
		}
	});
	
	//删除工作 global.js
	edu_list.on('click', 'a.J_edu_del', function(e){
		e.preventDefault();
		var $this = $(this);
		ajaxConfirm({
			href : this.href,
			elem : $this,
			callback : function(){
				$this.parents('li').slideUp('fast', function(){
					$(this).remove();
				});
			}
		});
	});
	
	//取消
	$('#J_edu_cancl').on('click', function(e){
		e.preventDefault();
		edu_op_wrap.siblings(':hidden').show();
		edu_op_wrap.hide();
		/*work_add.show();
		work_none.show();*/
	});


	function getTypeid(v){
		var typeid,
				v = parseInt(v);

		if(v === 1) {
			typeid = 1;
		}else if(v === 2 || v === 3){
			typeid = 2;
		}else{
			typeid = 3;
		}

		return typeid;
	}

})();