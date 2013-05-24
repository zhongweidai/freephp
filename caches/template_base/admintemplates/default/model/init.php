<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head>
<?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body>
<div class="wrap J_check_wrap">
<div class="nav">
	<ul class="cc">
		<li class="current"><a href="<?php echo U('model/index/init');?>">模型管理</a></li>
		<li><a class="J_dialog" href="<?php echo U('model/index/add');?>">添加模型</a></li>
	</ul>
</div>
<div class="mb10">
	<a title="添加模型" class="btn J_dialog" href="<?php echo U('model/index/add');?>"><span class="add"></span>添加模型</a>
	<!-- a title="更新缓存" class="btn btn_success" href="<?php echo U('model/index/flush');?>">更新缓存</a -->
</div>


<form method="post" action="<?php echo U('model/index/delete');?>" data-role="list" class="J_ajaxForm">
	<div class="table_list">
		<table width="100%">
			<colgroup>
				<col width="50">
				<col width="50">
				<col>
				<col width="100">
				<col width="120">
				<col width="120">
                <col width="150">               
			</colgroup>
			<thead>
				<tr>
					<td><label><input type="checkbox" data-checklist="J_check_y" data-direction="y" class="J_check_all" name="checkAll">全选</label></td>
					<td>ID</td>
					<td><?php echo L('model_name');?></td>
					<td><?php echo L('model_tbname');?></td>
					<td><?php echo L('model_desc');?></td>
					<td><?php echo L('model_status');?></td>
					<td><?php echo L('model_data_count');?></td>
                    <td>操作</td>
				</tr>
			</thead>
			<?php if(is_array($models)) { ?>
            <?php $n=1; if(is_array($models)) foreach($models AS $k => $v) { ?>
			<tbody id="J_tr">
			<tr data-id="<?php echo $v['ID'];?>" data-url="<?php echo U('model/index/ajax');?>">
				<td><input type="checkbox" value="<?php echo $v['ID'];?>" name="id[]" data-yid="J_check_y" data-xid="J_check_x" class="J_check newscheck"/></td>
				<td><?php echo $v['ID'];?></td>
				<td><?php echo $v['NAME'];?></td>
				<td><?php echo $v['TABLENAME'];?></td>
				<td><?php echo $v['DESCRIPTION'];?></td>                
				<td><?php if($v['STATUS']) { ?><span class="green J_listTable"  data-type="toggle" data-field="STATUS">√</span><?php } else { ?><span class="red J_listTable" data-type="toggle" data-field="STATUS">×</span><?php } ?></td>
				<td><?php echo $data_count[$v['ID']];?></td>
                <td><a class="mr10 J_dialog" href="<?php echo U('model/index/edit',array('id'=>$v[ID]));?>" title="<?php echo L('modify');?>"><?php echo L('modify');?></a>
				<a class="mr10 J_ajax_del" href="<?php echo U('model/index/delete',array('id'=>$v[ID]));?>"><?php echo L('delete');?></a>
				<a class="mr10" href="<?php echo U('model/field/init',array('id'=>$v[ID]));?>">字段</a>
				<a class="mr10" href="<?php echo U('model/index/view',array('id'=>$v[ID]));?>">预览表单</a>
				<!-- a class="mr10" href="<?php echo U('model/content/init',array('id'=>$v[ID]));?>">内容</a -->
				</td>
			</tr>
			</tbody>
            <?php $n++;}unset($n); ?>
			<?php } ?>
		</table>
	</div>
    <div class="pages"><?php echo $pages;?></div>
    <?php if($role_id==0) { ?>
	<div class="btn_wrap">
		<div class="btn_wrap_pd">
			<label class="mr20"><input type="checkbox" data-checklist="J_check_x" data-direction="x" class="J_check_all" name="checkAll">全选</label>
			<button class="btn btn_submit" id="enable_all" type="submit"><?php echo L('enable');?></button>
			<button class="btn btn_submit" id="disable_all" type="button"><?php echo L('disabled');?></button>	
			<!-- button class="btn btn_submit" id="J_del_all" type="button"><?php echo L('delete');?></button -->			

		</div>
	</div>
	<?php } ?>
<input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form>
</div>
<?php include $this->_view->templateResolve("admin","footer",$__style__,$__app__); ?>
<script type="text/javascript">
Wind.use('dialog',function() {
	$('#enable_all').on('click', function(e){
		e.preventDefault();
		if(!$('input.newscheck:checked').length) {
			Wind.dialog.alert('请至少选定一条');
			return;
		}
		Wind.dialog({
			message	: '确定启用选定的模型吗？',
			type	: 'confirm',
			onOk	: function() {
				$('form.J_ajaxForm').ajaxSubmit({
					dataType : 'json',
					url		 : "<?php echo U('model/index/active',array('_json'=>1));?>",
					success	 : function(data, statusText, xhr, $form) {
						if(data.state === 'success') {
							var location = window.location;
							location.href = location.pathname + location.search;
						}else{
							Wind.dialog.alert(data.message);
						}
					}
				});
			}
		});
	});

	$('#disable_all').on('click', function(e){
		e.preventDefault();
		if(!$('input.newscheck:checked').length) {
			Wind.dialog.alert('请至少选定一条');
			return;
		}
		Wind.dialog({
			message	: '确定禁用选定的模型吗？',
			type	: 'confirm',
			onOk	: function() {
				$('form.J_ajaxForm').ajaxSubmit({
					dataType : 'json',
					url		 : "<?php echo U('model/index/delete',array('_json'=>1));?>",
					success	 : function(data, statusText, xhr, $form) {
						if(data.state === 'success') {
							var location = window.location;
							location.href = location.pathname + location.search;
						}else{
							Wind.dialog.alert(data.message);
						}
					}
				});
			}
		});
	});

	$('#J_del_all').on('click', function(e){
		e.preventDefault();
		if(!$('input.newscheck:checked').length) {
			Wind.dialog.alert('请至少选定一条');
			return;
		}
		Wind.dialog({
			message	: '确定删除选定的模型吗？',
			type	: 'confirm',
			onOk	: function() {
				$('form.J_ajaxForm').ajaxSubmit({
					dataType : 'json',
					url		 : "<?php echo U('model/index/remove',array('_json'=>1));?>",
					success	 : function(data, statusText, xhr, $form) {
						if(data.state === 'success') {
							var location = window.location;
							location.href = location.pathname + location.search;
						}else{
							Wind.dialog.alert(data.message);
						}
					}
				});
			}
		});
	});
	
});
</script>
</body>
</html>