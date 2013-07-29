<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head>
<?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body>
<div class="wrap">
	<!--角色管理: 列表-->
	<div class="nav">
		<ul class="cc">
			<li><a href="<?php echo U('admin/auth/init');?>">后台用户</a></li>
			<li class="current"><a href="<?php echo U('admin/role/init');?>">管理角色</a></li>
		</ul>
	</div>
	<div class="h_a">提示信息</div>
	<div class="mb10 prompt_text">
		<ol>
			<li>可以将某一类的后台管理权限归为一个角色，然后将角色赋予用户。</li>
			<li>如果角色权限改变，用户的后台权限也随之改变</li>
			<li>您可以复用之前添加的角色的权限</li>
		</ol>
	</div>
	<div class="cc mb10"><a href="<?php echo U('admin/role/add');?>" class="btn"><span class="add"></span>添加角色</a></div>
	<div class="table_list">
		<table width="100%">
			<thead>
				<tr>
					<td width="140">角色名称</td>
					<td width="140">状态</td>
					<td>操作</td>
				</tr>
			</thead>
	<?php $n=1;if(is_array($roles)) foreach($roles AS $role) { ?>
			<tr data-id="<?php echo $role['ID'];?>" data-url="<?php echo U('admin/role/status');?>">

				<td><?php echo $role['NAME'];?></td>
					<td><span class="red J_listTable" data-type="toggle" data-field="status"><?php if($role['STATUS']) { ?>√<?php } else { ?>×<?php } ?></span>
				</td>
				<td>
					<a href="<?php echo U('admin/role/edit',array('id'=>$role['ID']));?>" class="mr10">[编辑]</a>
					<a href="<?php echo U('admin/role/delete',array('id'=>$role['ID']));?>" class="mr10 J_ajax_del">[删除]</a>
				</td>
			</tr>
	<?php $n++;}unset($n); ?>
		</table>
	</div>
</div>
<?php include $this->_view->templateResolve("admin","footer",$__style__,$__app__); ?>
</body>
</html>