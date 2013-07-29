<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head>
<?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
<style>
body{width:540px;}
</style>
</head>
<body class="body_none">
<div>
<!-- mod start -->
<form method="post" class="J_ajaxForm1" action="<?php echo U('admin/site/edit',array('_json'=>1));?>" data-role="list" encoding="multipart/form-data" enctype="multipart/form-data">
<div class="pop_cont pop_table">
	<table width="100%" style="table-layout:fixed;">
		<col width="80" />
		<col />
		<tr>
			<th>管理员[ID]</th>
			<td>
				<?php echo $admin['USERNAME'];?>[<?php echo $admin['USERID'];?>]
			</td>
		</tr>
		<tr>
			<th>操作IP</th>
			<td>
				<?php echo $admin['IP'];?>
			</td>
		</tr>
		<tr>
			<th>访问URL</th>
			<td>
				<?php echo $admin['URL'];?>
			</td>
		</tr>
		<tr>
			<td>操作时间</td>
			<td>
				<?php echo date('Y-m-d H:i:s',$admin['OP_TIME']);?>
			</td>
		</tr>
		<!--tr>
			<th>GET数据</th>
			<td>
				<?php echo $admin['DATA_GET'];?>
			</td>
		</tr>
		<tr>
			<td>POST数据</td>
			<td>
				
			</td>
		</tr-->
		<tr>
			<th>执行sql</th>
			<td>
				<?php $n=1;if(is_array(unserialize($admin['EX_SQL']))) foreach(unserialize($admin['EX_SQL']) AS $s) { ?>
				<?php echo htmlspecialchars($s);?></br>
				<?php $n++;}unset($n); ?>
			</td>
		</tr>
	</table>
</div>
			
		
<!-- mod end -->
<div class="pop_bottom">
		<button class="btn btn_submit J_ajax_submit_btn" type="submit">提交</button>
		<input name="id" type="hidden" value="<?php echo $site['ID'];?>">
</div>
<input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form>
<!-- end -->

</div>
<?php include $this->_view->templateResolve("admin","footer",$__style__,$__app__); ?>
</body>
</html>