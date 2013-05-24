<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head>
<?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body>
<div class="wrap J_check_wrap">
<div class="nav">
	<ul class="cc">
		<li class="current"><a href="<?php echo U('channel/index/init');?>">频道管理</a></li>
		<!-- li><a href="<?php echo U('channel/index/add');?>">添加频道</a></li -->
	</ul>
</div>
<div class="cc mb10"> 
    <a href="<?php echo U('channel/index/add');?>" class="btn" title="添加频道" ><span class="add"></span>添加频道</a>  
</div>
<div class="fr"><form action="" class="J_custom_ajaxForm" method="post"><button class="btn btn_submit J_ajax_submit_btn fr" data-form="J_custom_ajaxForm" data-url="<?php echo U('channel/index/setCache');?>">更新缓存</button><input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form></div>

<div class="table_full">
	<form action="#" method="get" id="J_navigation_search_form">
	    <input type="hidden" name="m" value="channel"/>
        <input type="hidden" name="c" value="index"/>
        <input type="hidden" name="a" value="init"/>
        <input type="hidden" name="field" id="orderfield" />
        <input type="hidden" name="order" value="<?php echo $order;?>"/>
        <table width="100%">
		<tbody><tr>
			<td>
				频道名称&nbsp;&nbsp;<input type="text" name="name" class="input length_3 mr10" id='name' value="<?php echo $search['CLNAME'];?>">
				频道标识&nbsp;&nbsp;<input type="text" name="identity" class="input length_3 mr10" id="identity" value="<?php echo $search['IDENTITY'];?>"/>
                <button type="submit" class="btn btn_submit" id="J_navigation_search">搜索</button>
            </td>
		</tr></tbody>
    </table>
	<input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form>
</div>

<form method="post" action="<?php echo U('content/index/linesEdit');?>" data-role="list" class="J_ajaxForm" id="J_tag_form">
	<div class="table_list J_check_wrap" id="J_tag_list">
		<table width="100%">
			<colgroup>
				<col width="50">
				<col width="60">
                <col width="50">
				<col width="160">
				<col width="80">
				<col width="180">
				<col width="100">
                <col width="50">
                <col width="50">
                <col width="50">
                <col>
			</colgroup>
			<thead>
				<tr>
					<td><label><input type="checkbox" data-checklist="J_check_y" data-direction="y" class="J_check_all" name="checkAll">全选</label></td>
					<td align="center"><a href="#" class="J_order" data-field="ORDER_NO" >排序</a></td>
                    <td align="center">ID</td>
                    <td align="center">频道名称</td>
					<td align="center"><a href="#" class="J_order" data-field="IDENTITY" >标识</a></td>
					<td align="center">频道描述</td>
					<td align="center"><a href="#" class="J_order" data-field="CREATE_DATE" >创建时间</a></td>
                    <td align="center"><a href="#" class="J_order" data-field="IS_SEARCH" >搜索</a></td>
                    <td align="center"><a href="#" class="J_order" data-field="IS_COMMENT" >评价</a></td>
                    <td align="center"><a href="#" class="J_order" data-field="STATUS" >状态</a></td>
					<td align="center">操作</td>
				</tr>
			</thead>
			<?php if(is_array($infos)) { ?>
            <?php $n=1;if(is_array($infos)) foreach($infos AS $r) { ?>
			<tbody id="J_tr">
            <tr data-id="<?php echo $r['ID'];?>" data-url="<?php echo U('channel/index/ajax');?>">
                <td><input type="checkbox" value="<?php echo $r['ID'];?>" name="id[]" data-yid="J_check_y" data-xid="J_check_x" class="J_check"></td> 
                <td align="center"><input class="input length_0" type="text" name="order[<?php echo $r['ID'];?>]" value="<?php echo $r['ORDER_NO'];?>"/></td>
                <td align="center"><?php echo $r['ID'];?></td>
                <td align="center"><span class="J_listTable" data-type="edit" data-field="CLNAME"><?php echo $r['CLNAME'];?></span></td>
                <td align="center"><?php echo $r['IDENTITY'];?></td>
                <td align="center"><span class="J_listTable" data-type="edit" data-field="REMARK"><?php echo $r['REMARK'];?></span></td>
                <td align="center"><?php echo date('Y-m-d', $r['CREATE_DATE']);?></td>
                <td align="center"><?php if($r['IS_SEARCH']) { ?><span class="green J_listTable"  data-type="toggle" data-field="IS_SEARCH">√</span><?php } else { ?><span class="red J_listTable" data-type="toggle" data-field="IS_SEARCH">×</span><?php } ?></td>
                <td align="center"><?php if($r['IS_COMMENT']) { ?><span class="green J_listTable"  data-type="toggle" data-field="IS_COMMENT">√</span><?php } else { ?><span class="red J_listTable" data-type="toggle" data-field="IS_COMMENT">×</span><?php } ?></td>
                <td align="center"><?php if($r['STATUS']) { ?><span class="green J_listTable"  data-type="toggle" data-field="STATUS">√</span><?php } else { ?><span class="red J_listTable" data-type="toggle" data-field="STATUS">×</span><?php } ?></td>
        		<td align="center">
                <a class="mr10" title="权限"  href="<?php echo U('channel/role/init',array('id'=>$r['ID']));?>">权限</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                <a class="mr10" title="栏目" href="<?php echo U('channel/navigation/init',array('cid'=>$r['ID']));?>" >栏目</a>|&nbsp;&nbsp; 
                <a class="mr10" title="编辑"  href="<?php echo U('channel/index/edit',array('id'=>$r['ID']));?>">编辑</a> |&nbsp;&nbsp; 
                <a class="mr10 J_ajax_del" href="<?php echo U('channel/index/delete',array('id'=>$r['ID']));?>" >删除</a> |&nbsp;&nbsp; 
                <a class="mr10" title="栏目" href="<?php echo U('channel/content/init',array('id'=>$r['ID']));?>" >内容</a>
            </td>
        	</tr>

			</tbody>
            <?php $n++;}unset($n); ?>
			<?php } ?>
		</table>
	</div>
    <div class="pages"><?php echo $pages;?></div>

	<div class="btn_wrap">
		<div class="btn_wrap_pd" id="J_sub_wrap">
			<label class="mr20"><input type="checkbox" data-checklist="J_check_x" data-direction="x" class="J_check_all" name="checkAll">全选</label>
            <button class="btn btn_submit" id="J_submit_btn" type="button">排序</button>
            <button class="btn J_tag_sub_btn" data-role="del" type="button">删除</button>
			<button class="btn btn_submit J_tag_sub_btn btn_success" id="J_del_all" type="button" data-role="audit">审核通过</button>
            <button class="btn btn_submit J_tag_sub_btn btn_success" id="J_del_all" type="button" data-role="search">开启搜索</button>
            <button class="btn btn_submit J_tag_sub_btn btn_success" id="J_del_all" type="button" data-role="comment">开启评价</button>
		</div>
	</div>
<input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form>
</div>
<?php include $this->_view->templateResolve("admin","footer",$__style__,$__app__); ?>
<script>
    $(".J_order").click(function(){
        var field = $(this).data('field');
        $("#orderfield").val(field);
        $("#J_navigation_search_form").submit();
    })
</script>
<script>
Wind.use('dialog', 'jquery.form', function(){
    var URL_SUBMIT = "<?php echo U('channel/index/batchOrder');?>";
    var URL_DELETE = "<?php echo U('channel/index/batchDelete');?>";
    var URL_AUDIT = "<?php echo U('channel/index/batchAudit', array('flag'=>'audit'));?>";
    var URL_SEARCH = "<?php echo U('channel/index/batchAudit', array('flag'=>'search'));?>";
    var URL_COMMENT = "<?php echo U('channel/index/batchAudit', array('flag'=>'comment'));?>";
    
    //点击提交
    $('#J_submit_btn').on('click', function(e){
        e.preventDefault();
        var $this = $(this);
        Wind.dialog({
            message : '确定要排序吗？',
            type : 'confirm',
            onOk : function () {
                tagManageSub(URL_SUBMIT, $this);
            }
        });
    });  

    tag_form = $('#J_tag_form');
    //取消&删除热门
    $('button.J_tag_sub_btn').on('click', function(e){
        e.preventDefault();
        var $this = $(this),
        role = $this.data('role'),
        url;
        if(role == 'del'){
            url = URL_DELETE;
        }else if(role == 'audit'){
            url = URL_AUDIT;
        }else if(role == 'search'){
            url = URL_SEARCH;
        }else if(role == 'comment'){
            url = URL_COMMENT;
        }

        if(getCheckedTr()) {
            if(role == 'del') {
                Wind.dialog({
                    message : '确定要删除吗？',
                    type : 'confirm',
                    onOk : function () {
                        tagManageSub(url, $this);
                    }
                });
            }else if(role == 'audit'){
                Wind.dialog({
                    message : '确定要批量审核吗？',
                    type : 'confirm',
                    onOk : function () {
                        tagManageSub(url, $this);
                    }
                });
            }else if(role == 'search'){
                Wind.dialog({
                    message : '确定要批量修改搜索状态吗？',
                    type : 'confirm',
                    onOk : function () {
                        tagManageSub(url, $this);
                    }
                });
            }else if(role == 'comment'){
                Wind.dialog({
                    message : '确定要批量修改评价状态吗？',
                    type : 'confirm',
                    onOk : function () {
                        tagManageSub(url, $this);
                    }
                });
            }else{
                tagManageSub(url, $this);
            }
        }else{
            Wind.dialog.alert('请至少选择一项');
        }
    });
     
    //提交方法
    function tagManageSub(url, btn){
        tag_form.ajaxSubmit({
        url : url,
        dataType : 'json',
        beforeSubmit: function(arr, $form, options) {
            btn.prop('disabled',true).addClass('disabled');
        },
        success : function(data, statusText, xhr, $form) {
            sub_wrap = $('#J_sub_wrap');
            if( data.state === 'success' ) {
                $('<span class="tips_success">' + data.message + '</span>' ).appendTo(sub_wrap).fadeIn('slow').delay( 1000 ).fadeOut(function() {
                    //common.js
                    reloadPage(window);
                });
            }else if( data.state === 'fail' ) {
                $( '<span class="tips_error">' + data.message + '</span>' ).appendTo(sub_wrap).fadeIn('fast' );
                btn.removeProp('disabled').removeClass('disabled');
            }
        }
        });
    }
     
    //选择统计
    function getCheckedTr(){
        if($('#J_tag_list input.J_check:checked').length >= 1) {
            return true;
        }else{
            return false;
        }
    }
     
});
</script>
</body>
</html>