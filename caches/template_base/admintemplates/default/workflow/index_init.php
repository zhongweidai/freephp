<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head>
<?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body>
<div class="wrap J_check_wrap">
<div class="nav">
	<ul class="cc">
		<li class="current"><a href="<?php echo U('workflow/index/init');?>">工作流管理</a></li>
	</ul>
</div>

<div class="mb10">
	<a title="添加工作流" class="btn" href="<?php echo U('workflow/index/add');?>"><span class="add"></span>添加工作流</a>
</div>
<form method="post" action="<?php echo U('workflow/index/opera',array('_json'=>1));?>" data-role="list" class="J_ajaxForm">
	<div class="table_list">
		<table width="100%">
			<colgroup>
				
				<col width="150">
				<col width="100">
				<col width="100">
				<col>
                <col width="80">
                <col>
                <col>
			</colgroup>
			<thead>
				<tr>
					
					<td>名称</td>
					<td>起始节点</td>
					<td>结束节点</td>
					<td class="tac">添加时间</td>
                    <td>描述</td>
                    <td class="tac">状态</td>
                    <td>操作</td>
				</tr>
			</thead>
            <?php $n=1; if(is_array($list['WORKFLOW'])) foreach($list['WORKFLOW'] AS $k => $v) { ?>
			<tbody id="J_tr">
			<tr>
				
				<td><?php echo $v['NAME'];?></td>
				<td><?php echo $list['start_slot'][$k]['NAME'];?></td>
                <td><?php echo $list['end_slot'][$k]['NAME'];?></td>
				<td class="tac"><?php echo FreeDate::format('',intval($v['ADDTIME']));?></td>
				<td><?php echo $v['INTRO'];?></td>
                <td class="tac"><?php if($v['STATUS']) { ?><?php echo L('icon_unlock');?><?php } else { ?><?php echo L('icon_locked');?><?php } ?></td>
                <td>
                
                <a href="<?php echo U('workflow/index/edit',array('id'=>$v['ID']));?>" class="mr10" title="<?php echo L('edit');?>">[<?php echo L('edit');?>]</a>
				<a href="<?php echo U('workflow/index/delete',array('id'=>$v['ID']));?>" class="mr10 J_ajax_del">[<?php echo L('delete');?>]</a>
				<a href="<?php echo U('workflow/index/edit',array('step'=>1,'id'=>$v['ID']));?>" class="mr10">[节点]</a>
                </td>
			</tr>
			</tbody>
            <?php $n++;}unset($n); ?>
		</table>
	</div>
    <div class="pages"><?php echo $pages;?></div>

	

<input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form>
</div>
<?php include $this->_view->templateResolve("admin","footer",$__style__,$__app__); ?>
</body>
</html>