<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head>
<?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body>
<div class="wrap">

<!--用户后台权限: 列表  -->
<div class="nav">
	<ul class="cc">
		<li class="current"><a href="<?php echo U('admin/auth/');?>">后台用户</a></li>
		<li><a href="<?php echo U('admin/role/init');?>">管理角色</a></li>
	</ul>
</div>
<div class="h_a">提示信息</div>
<div class="mb10 prompt_text">
	<ol>
		<li>您可以添加后台管理账号</li>
		<li>进入后台的操作权限，由所赋予的角色决定，一个用户可以被赋予多个角色</li>
	</ol>
</div>
<div class="cc mb10"><a href="<?php echo U('admin/auth/add');?>" title="添加用户" class="btn J_dialog"><span class="add"></span>添加后台用户</a></div>
<div class="table_list">
	<table width="100%">
		<thead>
			<tr>
				<td width="140">用户名</td>
				<td width="200">真实姓名</td>
				<td width="140">上次登录IP</td>
				<td width="200">上次登录时间</td>
				<td width="140">邮箱地址</td>
				<td>操作</td>
			</tr>
		</thead>
<?php $n=1;if(is_array($admins)) foreach($admins AS $var) { ?>
		<tr>
			<td><?php echo $var['USERNAME'];?></td>
			<td><?php if($var['REALNAME']) { ?> <?php echo $var['REALNAME'];?> <?php } else { ?> 暂无<?php } ?></td>
			<td><?php if($var['LASTLOGINIP']) { ?> <?php echo $var['LASTLOGINIP'];?> <?php } else { ?>暂无<?php } ?></td>
			<td><?php if($var['LASTLOGINTIME']) { ?> <?php echo date('Y-m-d h:m:s', $var['LASTLOGINTIME']);?> <?php } else { ?>暂无<?php } ?></td>
			<td><?php echo $var['EMAIL'];?></td>
			<td>
				<a href="<?php echo U('admin/auth/edit', array('id'=>$var['ID']));?>" title="编辑用户" class="mr10 J_dialog">[编辑]</a>
				<a href="<?php echo U('admin/auth/delete', array('id'=>$var['ID']));?>" class="mr10 J_ajax_del">[删除]</a>
			</td>
		</tr>
<?php $n++;}unset($n); ?>
	</table>
</div>
<div class="pages"><?php echo $pages;?></div>
	
</div>
<?php include $this->_view->templateResolve("admin","footer",$__style__,$__app__); ?>
</body>
</html>