<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head>
<?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body>
<div class="wrap J_check_wrap">


<div class="h_a">提示信息</div>
<div class="mb10 prompt_text">
	<ol>
		<li>禁止后，指定的IP将无法访问站点。</li>		
		<li>可用于禁止某个具体IP，如：192.0.0.1</li>
		<li>可用于禁止某个IP网段，如：192.0.0</li>
	</ol>
</div>

<div class="table_full">
	<form action="#" method="get" id="J_navigation_search_form">
	    <input type="hidden" name="m" value="ipban"/>
        <input type="hidden" name="c" value="index"/>
        <input type="hidden" name="a" value="init"/>
        <input type="hidden" name="field" id="orderfield" />
        <input type="hidden" name="order" value="<?php echo $order;?>"/>
        <table width="100%">
		<tbody>
		<tr>
			<td>
				IP地址&nbsp;&nbsp;<input type="text" name="BANINFO" class="input length_3 mr10" value="<?php echo $ip;?>">
			   状态
				<select name="STATUS" class="select_1" id="tpl_style">
					<option value="">不限</option>
                    <option value="1" <?php if($where['STATUS'] == '1') { ?>selected="selected"<?php } ?>>启用</option>
                    <option value="0" <?php if($where['STATUS'] == '0') { ?>selected="selected"<?php } ?>>禁用</option>
				</select>
                <button type="submit" class="btn btn_submit" id="J_navigation_search"><?php echo L('search');?></button>
            </td>
		</tr></tbody>
    </table>
	<input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form>
</div>

<form action="<?php echo U('ipban/index/add');?>" data-role="list" method="post" class="J_ajaxForm">
<div class="table_list">
	<table id="J_table_list" width="100%">
		<thead>
			<tr>
				<td width="5"><label><input type="checkbox" data-checklist="J_check_y" data-direction="y" class="J_check_all" name="checkAll">全选</label></td>
				<td align="center">IP</td>
				<td align="center"><a href="#" class="J_order" data-field="STATUS" title="点击排序">状态</a></td>
				<td align="center">操作</td>
			</tr>
		</thead>
<?php $n=1;if(is_array($baninfo)) foreach($baninfo AS $v) { ?>
		<tr data-id="<?php echo $v['ID'];?>" data-url="<?php echo U('ipban/index/ajax');?>">
			<td><input type="checkbox" value="<?php echo $v['ID'];?>" name="ipbanid[]" data-yid="J_check_y" data-xid="J_check_x" class="J_check"></td>
			<td align="center"><span class="J_listTable" data-type="edit" data-field="BANINFO" title="点击修改IP地址"><?php echo $v['BANINFO'];?></span></td>
			<td align="center"><?php if($v['STATUS']) { ?><span class="green J_listTable"  data-type="toggle" data-field="STATUS" title="点击修改状态">√</span><?php } else { ?><span class="red J_listTable" data-type="toggle" data-field="STATUS" title="点击修改状态">×</span><?php } ?></td>
			<td align="center">
			<!--<a href="<?php echo U('ipban/index/edit', array('id'=>$v['ID']));?>" title="编辑" class="mr10 J_dialog">[编辑]</a>-->
			<a href="<?php echo U('ipban/index/delete', array('id'=>$v['ID']));?>" class="mr10 J_ajax_del">[删除]</a>
			</td>
		</tr>
<?php $n++;}unset($n); ?>
	</table>
	<table width="100%">
		<tr class="ct">
			<td colspan="5" style="padding-left:38px;">
				<a data-type="nav_1" data-html="tbody" href="" id="J_add_root" class="link_add">添加IP地址</a>
			</td>
		</tr>
	</table>
	
</div>
	<div class="pages"><?php echo $pages;?></div>

<div class="btn_wrap">
		<div class="btn_wrap_pd">
			<label class="mr20"><input type="checkbox" data-checklist="J_check_x" data-direction="x" class="J_check_all" name="checkAll">全选</label>
			<input type="hidden" name="submittype" value="" id="submittype"/> 
            <button id="J_link_del_all"     class="btn" type="button" title="批量删除" onclick="$('#submittype').val('delete')">删除</button>
            <button id="J_link_status_all"  class="btn btn_success" type="button" title="批量启用状态" onclick="$('#submittype').val('status')">启用状态</button>		
            <button class="btn btn_submit J_ajax_submit_btn " type="submit" title="批量添加"><?php echo L('submit');?></button>	
		</div>
	</div> 
<input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form>
</div>
<?php include $this->_view->templateResolve("admin","footer",$__style__,$__app__); ?>
<script>
	//点击排序
    $(".J_order").click(function(){
        var field = $(this).data('field');
        $("#orderfield").val(field);
        $("#J_navigation_search_form").submit();
    })
</script>

<script>
/*
root_tr_html 为“添加栏目”html
*/
var root_tr_html = '<tr>\
    <td></td>\
    <td align="center">\<input name="newdata[root_][BANINFO]" type="text" value="" class="input length_2 mr10">\</td>\
    <td align="center">\<input name="newdata[root_][STATUS]" type="checkbox" value="1" >\</td>\
    <td align="center">\
        <a href="" class="mr5 J_newRow_del">[删除]</a>\
    </td>\
</tr>';
Wind.js(GV.JS_ROOT+ 'pages/admin/common/forumTree_table.js?v=' +GV.JS_VERSION);
 </script>

<script type="text/javascript">
Wind.use('dialog',function() {
	//批量删除
	$('#J_link_del_all,#J_link_status_all').on('click', function(e){
		e.preventDefault();
        var msg = $('#submittype').val();
        if(msg == 'delete') msg = '删除';
        if(msg == 'status') msg = '更新状态';
		if(!$('input.J_check:checked').length) {
			Wind.dialog.alert('请至少选定一条数据');
			return;
		}
		Wind.dialog({
			message	: '确定对选定菜单进行批量' + msg + '操作吗？', 
			type	: 'confirm',
			onOk	: function() {
				$('form.J_ajaxForm').ajaxSubmit({
					dataType : 'json',
					url		 : "<?php echo U('ipban/index/batch');?>",
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