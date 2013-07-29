<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head>
<?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
<style>
body{width:440px;}
</style>
</head>
<body class="body_none">
<div>
<!-- mod start -->
<form method="post" class="J_ajaxForm" action="<?php echo U('admin/menu/edit',array('id'=>$id));?>" data-role="list">
<div class="pop_cont pop_table">
	<table width="100%" style="table-layout:fixed;">
		<col width="80" />
		<col />
		<tr>
			<th><?php echo L('menu_pre');?></th>
			<td>
				<span class="must_red">*</span>
				<select name="info[FATHERID]" class="select_5">
					<option value=0><?php echo L('menu_first');?></option>
					<?php echo $select_menu;?>
				</select>
			</td>
		</tr>
		<tr>
			<th><?php echo L('menu_name');?></th>
			<td>
				<span class="must_red">*</span>
				<input type="text" name="info[NAME]" class="input input_hd length_5" value="<?php echo $menu['NAME'];?>">
			</td>
		</tr>
		<tr>
			<th><?php echo L('menu_enname');?></th>
			<td>
				<span class="must_red">*</span>
				<input type="text" name="info[ENGNAME]" class="input input_hd length_5" value="<?php echo $menu['ENGNAME'];?>">
			</td>
		</tr>
		<tr>
			<th>动作</th>
			<td>
				<div class="mb5"><input type="text" name="info[QUERY]" class="input length_5" value="<?php echo $menu['QUERY'];?>"></div>
				<div class="gray">如：admin/menu/*</div>
			</td>
		</tr>
		<tr>
			<th>顺序</th>
			<td>
				<input type="text" name="info[ORDERNO]" class="input input length_0 mr10" value="<?php echo $menu['ORDERNO'];?>">

			<!--<?php echo L('is_menu_log');?> <input class="J_bold" type="checkbox" value="1" data-class="b" name="info[IS_LOG]" <?php if($menu['IS_LOG']) { ?>checked<?php } ?>>-->
			</td>
		</tr>
		<tr>
			<th>说明</th>
			<td>
				<div class="mb5">
					<input class="input length_5" type="text" value="<?php echo $menu['REMARK'];?>" name="info[REMARK]">
				</div>
				<div class="gray">鼠标悬浮于链接文字上时的说明内容</div>
			</td>
		</tr>
	</table>
</div>
<!-- mod end -->
<div class="pop_bottom">
		<button class="btn btn_submit J_ajax_submit_btn" type="submit">提交</button>
		<input name="id" type="hidden" value="<?php echo $menu['ID'];?>">
</div>
<input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form>
<!-- end -->

</div>
<?php include $this->_view->templateResolve("admin","footer",$__style__,$__app__); ?>
</body>
</html>