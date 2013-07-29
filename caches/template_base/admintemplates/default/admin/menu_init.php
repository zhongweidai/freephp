<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head>
<?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body>
<div class="wrap">
<!--div class="mb10">
	<a href="{@WindUrlHelper::createUrl('/nav/nav/add')}&type=<?php echo $navType;?>" class="btn J_dialog"><span class="add"></span>添加导航</a>
</div-->
<div class="nav">
	<ul class="cc">
		<li <?php if(empty($id)) { ?>class="current"<?php } ?>><a href="<?php echo U('admin/menu/init');?>"><?php echo L('menu_list');?></a></li>
		<?php $n=1;if(is_array($f_menu)) foreach($f_menu AS $value) { ?>
		<li   <?php if($id==$value['ID']) { ?>class="current"<?php } ?>> <a href="<?php echo U('admin/menu/init',array('id'=>$value['ID']));?>">>> <?php echo $value['NAME'];?></a></li>
		<?php $n++;}unset($n); ?>
			<!--li><a href="<?php echo U('admin/menu/add');?>"><?php echo L('add_menu');?></a></li-->
	</ul>
</div>
<form method="post" class="J_ajaxForm" action="<?php echo U('admin/menu/opera',array('id'=>$id));?>" data-role="list">

<div class="table_list">
	<table width="100%" id="J_table_list" style="table-layout:fixed;">
		<col width="30">
		<col width="380">
		<col width="260">
		<col width="100">
		<thead>
			<tr>
				<td></td>
				<td>[排序] <?php echo L('menu_name');?></td>
				<td>action</td>
				<td>状态</td>
				<td><?php echo L('menu_manage');?></td>
			</tr>
		</thead>
	<?php
		foreach ($menu as $value) {
			$checked=$value['STATUS']?'checked':'';
			$count=count($value['items']);
			$icon='zero_icon';
			if($count>0){
				$icon='J_start_icon away_icon';
			}
	?>
		<tbody>
		<tr class="bgA">
			<td><span class="<?php echo $icon;?>" data-id="<?php echo $value['id'];?>"></span></td>
			<td>
				<input name="data[<?php echo $value['id'];?>][ID]" type="hidden" value="<?php echo $value['ID'];?>" >
				<input name="data[<?php echo $value['id'];?>][ORDERNO]" type="text" class="input length_0 mr10" value="<?php echo $value['ORDERNO'];?>">
				<input name="data[<?php echo $value['id'];?>][NAME]" type="text" class="input length_3 mr5" value="<?php echo $value['NAME'];?>">
				<a style="display:none" href="<?php echo U('admin/menu/add',array('parentid'=>$value['ID']));?>" class="link_add J_addChild add_nav" data-id="<?php echo $value['ID'];?>" data-html="tbody" data-type="nav_2" data-leve="1" ><?php echo L('add_next_menu');?></a>
			</td>
			<td><input name="data[<?php echo $value['ID'];?>][QUERY]" type="text" class="input length_4" value="<?php echo $value['QUERY'];?>"></td>
			<td class="tac"><input name="data[<?php echo $value['ID'];?>][STATUS]" type="checkbox" value="1" <?php echo $checked;?>></td>
			<td>
				<a href="<?php echo U('admin/menu/edit',array('id'=>$value['ID']));?>" class="mr10 J_dialog" title="<?php echo L('edit');?>">[<?php echo L('edit');?>]</a>
				<a href="<?php echo U('admin/menu/delete',array('id'=>$value['ID']));?>" class="mr10 J_ajax_del">[<?php echo L('delete');?>]</a>
				<?php
					if($count>0){
				?>
				<!--a href="<?php echo U('admin/menu/init',array('id'=>$value['ID']));?>">[<?php echo L('next_menu');?>]</a-->
				<?php }?>
			</td>
		</tr>
		</tbody>
		<?php
			if($count>0){
		?>
			<tbody id="J_table_list_<?php echo $value['ID'];?>">
		<?php
			foreach ($value['items'] as  $childKey=>$childValue) {
			$checked=$childValue['STATUS']?'checked':'';
			$endicon=($childKey==$count-1)?'  plus_end_icon':'';
		?>
			<tr>
				<td>&nbsp;</td>
				<td><span class="plus_icon<?php echo $endicon;?> mr10"></span><input name="data[<?php echo $childValue['ID'];?>][ID]" type="hidden" value="<?php echo $childValue['ID'];?>" ><input name="data[<?php echo $childValue['ID'];?>][ORDERNO]" type="text" class="input length_0 mr10" value="<?php echo $childValue['ORDERNO'];?>" style="width:20px;"><input name="data[<?php echo $childValue['ID'];?>][NAME]" type="text" class="input length_3 mr5" value="<?php echo $childValue['NAME'];?>"><!--<a href="<?php echo $addUrl;?>&type=<?php echo $navType;?>&parentid=<?php echo $value['navid'];?>" style="display:none" class="s2 dialog">+添加导航</a>--><!--a style="display:none" href="<?php echo U('admin/menu/add',array('parentid'=>$value['ID']));?>" class="link_add J_addChild add_nav" data-id="<?php echo $childValue['ID'];?>" data-html="tbody" data-type="nav_3" data-leve="2" >添加二级导航</a-->
					</td>
				<td>
					<input name="data[<?php echo $childValue['ID'];?>][QUERY]" type="text" class="input length_4" value="<?php echo $childValue['QUERY'];?>">
				</td>
				<td class="tac"><input name="data[<?php echo $childValue['ID'];?>][STATUS]" type="checkbox" value="1" <?php echo $checked;?>></td-->
				<td>
					<a href="<?php echo U('admin/menu/edit',array('id'=>$childValue['ID']));?>" class="mr10 J_dialog" title="导航编辑">[编辑]</a><a href="<?php echo U('admin/menu/delete',array('id'=>$childValue['ID']));?>" class="mr10 J_ajax_del">[删除]</a>
				</td>
			</tr>
		<?php }?>
		</tbody>
		<?php }?>
		<?php }?>
	</table>
	<table width="100%">
		<tr class="ct"><td colspan="5" style="padding-left:38px;"><a data-type="nav_1" data-html="tbody" href="" id="J_add_root" class="link_add"><?php echo L('menu_add');?></a></td></tr>
	</table>
</div>
<div class="btn_wrap">
	<div class="btn_wrap_pd">
		<button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
		<input name="navtype" type="hidden" value="<?php echo $navType;?>" >
	</div>
</div>	
<input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form>


</div>
<?php include $this->_view->templateResolve("admin","footer",$__style__,$__app__); ?>
<script>
/*
root_tr_html 为“添加导航”html
child_tr_html 为“添加二级导航”html
*/
var root_tr_html = '<tr>\
                            <td><span class="zero_icon mr10"></span></td>\
                                        <td>\
                                            <input name="newdata[root_][ORDERNO]" type="text" value="" class="input length_0 mr10">\
                                            <input name="newdata[root_][NAME]" type="text" class="input length_3 mr5" value="">\
                                            <a style="display: none; " href="#" class="link_add J_addChild add_nav" data-html="tbody" data-id="temp_root_" data-type="nav_2">添加二级导航</a>\
                                            <input type="hidden" name="newdata[root_][tempid]" value="temp_root_"/>\
                                        </td>\
                                        <td>\
                                            <input name="newdata[root_][QUERY]" type="text" class="input length_4" value="">\
                                        </td>\
										<td>\
											<input name="newdata[root_][STATUS]" type="checkbox" value="1" >\
                                        </td>\
                                        <td>\
                                            <a href="" class="mr5 J_newRow_del">[删除]</a>\
                                        </td>\
                                    </tr>',
	child_tr_html = '<tr>\
						<td></td>\
						<td><span class="plus_icon"></span>\
							<input name="newdata[child_][ORDERNO]" type="text" value="" class="input length_0 mr10">\
                                            <input name="newdata[child_][NAME]" type="text" class="input length_3 mr5" value="">\
                                        </td>\
                                        <td>\
                                            <input name="newdata[child_][QUERY]" type="text" class="input length_4" value="">\
                                        </td>\
										<td>\
											<input name="newdata[child_][STATUS]" type="checkbox" value="1" >\
                                        </td>\
                                        <td>\
                                            <a href="" class="mr5 J_newRow_del">[删除]</a>\
                                            <input type="hidden" name="newdata[child_][FATHERID]" value="id_"/>\
                                        </td>\
                                    </tr>';

Wind.js(GV.JS_ROOT+ 'pages/admin/common/forumTree_table.js?v=' +GV.JS_VERSION);
</script>
<script type="text/javascript" src="<?php echo JS_PATH;?>util_libs/draggable.js"></script>
</body>
</html>