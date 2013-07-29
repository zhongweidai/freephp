<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head>
<?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body>
<div class="wrap">


<!--角色管理: 编辑角色  -->
<div class="nav">
	<div class="return"><a href="<?php echo U('admin/role/init');?>">返回上一级</a></div>
	<ul class="cc">
		<li><a href="<?php echo U('admin/auth/init');?>">后台用户</a></li>
		<li class="current"><a href="<?php echo U('admin/role/init');?>">管理角色</a></li>
	</ul>
</div>
<form class="J_ajaxForm" data-role="list" action="<?php echo U('admin/role/edit');?>" method="post">
<input type="hidden" value="<?php echo $role['ID'];?>" name="id" />
<div class="h_a">编辑角色</div>
<div class="table_full">
	<table width="100%" class="J_check_wrap">
		<col class="th" />
		<col width="400" />
		<col />
		<tr>
			<th>角色名称</th>
			<td><span class="must_red">*</span>
				<input name="rolename" readonly value="<?php echo $role['NAME'];?>" type="text" class="input input_hd length_5">
			</td>
			<td><div class="fun_tips"></div></td>
		</tr>
		<!-- 编辑时,当前的角色ID变量  <?php echo $role['id'];?>  -->
		<tr>
			<th>从已有角色复制权限</th>
			<td>
				<select id="J_role_select" name="roleid" class="select_5">
					<option>请选择角色</option>
					<?php $n=1;if(is_array($roles)) foreach($roles AS $val) { ?>
					<option value="<?php echo $val['ID'];?>"><?php echo $val['NAME'];?></option>
					<?php $n++;}unset($n); ?>
				</select>
			</td>
			<td><div class="fun_tips"></div></td>
		</tr>

		<?php $n=1;if(is_array($menus)) foreach($menus AS $var) { ?>
		<tr>
			<th><input name="menu" id="J_role_<?php echo $var['ID'];?>" data-direction="x" data-checklist="J_check_<?php echo $var['ID'];?>" type="checkbox" class="checkbox J_check_all" value="<?php echo $var['ID'];?>"><?php echo $var['NAME'];?></th>
			<td>
				<ul data-name="<?php echo $var['ID'];?>" id="J_check_<?php echo $var['ID'];?>" class="three_list cc J_ul_check">
				<?php $n=1;if(is_array($var['items'])) foreach($var['items'] AS $item) { ?>
					<li><label><input name="menus[]" data-xid="J_check_<?php echo $var['ID'];?>" class="J_check" type="checkbox" value="<?php echo $item['ID'];?>"><?php echo $item['NAME'];?></label></li>
				<?php $n++;}unset($n); ?>
				</ul>
			</td>
			<td><div class="fun_tips"></div></td>
		</tr>
		<?php $n++;}unset($n); ?>
		<tr>
		<th>状态</th>
			<td>
				<label><input name="status" type="checkbox" <?php if($role['STATUS']) { ?>checked<?php } ?>></label>选中代表开启当前角色
			</td>
			<td><div class="fun_tips"></div></td>
		</tr>
	</table>
</div>
<div class="btn_wrap">
	<div class="btn_wrap_pd">
		<button type="submit" class="btn btn_submit J_ajax_submit_btn">提交</button>
	</div>
</div>
<input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form>

<!-- 选中用列表变量 <?php echo $cAuths;?> -->

</div>
<?php include $this->_view->templateResolve("admin","footer",$__style__,$__app__); ?>
<script>
var ROLE_LIST_CONFIG = <?php echo $role_table;?>, //已有角色的权限集合
	ROLE_AUTH_CONFIG = <?php echo json_encode($admin_role);?>; //当前角色的已有权限集合
Wind.js(GV.JS_ROOT+ 'pages/admin/role_manage.js?v=' +GV.JS_VERSION);
</script>
</body>
</html>