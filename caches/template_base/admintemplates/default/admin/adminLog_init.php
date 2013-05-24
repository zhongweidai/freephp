<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head>
<?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body>
<div class="wrap">

<div class="h_a">筛选条件</div>
<div class="mb10 prompt_text">
	<li>
		<form id="J_navigation_search_form" method="get" action="<?php echo U();?>">
			<input type="hidden" value="admin" name="m">
			<input type="hidden" value="adminLog" name="c">
			<input type="hidden" value="init" name="a">
			操作名：<input type="text" value="<?php echo $username;?>" class="input length_2 " name="username" > 
			时间：<input type="text" value="<?php echo date('Y-m-d',$startime ? $startime : time());?>" class="input length_2 date J_date" name="startime" id="startime">——<input type="text" value="<?php echo date('Y-m-d',$endtime ? $endtime : time());?>" class="input length_2 date J_date" name="endtime" id="endtime"> <INPUT TYPE="submit" class="btn btn_submit">
			&nbsp;&nbsp;&nbsp;&nbsp;
			<a class="mr10 J_ajax_del" href="<?php echo U('admin/adminLog/delete',array('date'=>1));?>">[删除今天之前的数据]</a>
			<a class="mr10 J_ajax_del" href="<?php echo U('admin/adminLog/delete',array('date'=>3));?>">[删除3天之前的数据]</a>
			<a class="mr10 J_ajax_del" href="<?php echo U('admin/adminLog/delete',array('date'=>7));?>">[删除7天之前的数据]</a>
		<input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form>	
	</li>
</div>
<div class="table_list">
	<table width="100%">
		<thead>
			<tr>
				<td>操作人</td>
				<td>访问IP</td>
				<td>操作时间</td>
				<td>操作地址</td>
				<td>管理操作</td>
			</tr>
		</thead>
<?php $n=1;if(is_array($logs)) foreach($logs AS $v) { ?>
		<tr>
			<td><?php echo $v['USERNAME'];?>[<?php echo $v['USERID'];?>]</td>
			<td><?php echo $v['IP'];?></td>
			<td><?php echo date('Y-m-d h:i:s',$v['OP_TIME']);?></td>
			<td><?php echo $v['URL'];?></td>
			<td>
				<a href="<?php echo U('admin/adminLog/show', array('id'=>$v['ID']));?>" title="日志详情" class="mr10 J_dialog">[日志详情]</a>
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