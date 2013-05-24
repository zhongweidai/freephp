<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head>
<?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body>
<div class="wrap J_check_wrap">
<div class="nav">
	<ul class="cc">
		<li class="current"><a href="<?php echo U('collect/index/init');?>">采集管理</a></li>
	</ul>
</div>
<div class="cc mb10"> 
    <a href="<?php echo U('collect/index/add');?>" class="btn" title="添加采集点" ><span class="add"></span>添加采集点</a>  
</div>
<!-- div class="fr"><form action="" class="J_custom_ajaxForm" method="post"><button class="btn btn_submit J_ajax_submit_btn fr" data-form="J_custom_ajaxForm" data-url="<?php echo U('channel/index/setCache');?>">更新缓存</button><input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form></div>

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
</div -->

<form method="post" action="<?php echo U('collect/index/linesEdit');?>" data-role="list" class="J_ajaxForm" id="J_tag_form">
	<div class="table_list J_check_wrap" id="J_tag_list">
		<table width="100%">
			<colgroup>
				<col width="50">
				<col width="100">
                <col width="150">
				<col width="180">
				<col width="300">
                <col>
			</colgroup>
			<thead>
				<tr>
					<td><label><input type="checkbox" data-checklist="J_check_y" data-direction="y" class="J_check_all" name="checkAll">全选</label></td>
                    <td align="center">ID</td>
                    <td align="center">名称</td>
					<td align="center">最后采集时间</td>
					<td align="center">内容操作</td>
					<td align="center">操作</td>
				</tr>
			</thead>
			<?php if(is_array($infos)) { ?>
            <?php $n=1;if(is_array($infos)) foreach($infos AS $r) { ?>
			<tbody id="J_tr">
            <tr data-id="<?php echo $r['ID'];?>" data-url="<?php echo U('collect/index/ajax');?>">
                <td><input type="checkbox" value="<?php echo $r['ID'];?>" name="id[]" data-yid="J_check_y" data-xid="J_check_x" class="J_check"></td> 
                <td align="center"><?php echo $r['ID'];?></td>
                <td align="center"><?php echo $r['NAME'];?></td>
                <td align="center"><?php if(empty($r['LASTDATE'])) { ?>--<?php } else { ?><?php echo date('Y-m-d', $r['LASTDATE']);?><?php } ?></td>
                <td align="center">
                <a class="mr10" title="采集网址"  href="<?php echo U('collect/index/urlList',array('id'=>$r['ID']));?>">采集网址</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                <a class="mr10" title="采集内容" href="<?php echo U('collect/index/content',array('id'=>$r['ID']));?>" >采集内容</a>|&nbsp;&nbsp; 
                <a class="mr10" title="内容发布"  href="<?php echo U('collect/index/pubList',array('id'=>$r['ID']));?>">内容发布</a>
                </td>
            	<td align="center">
                <a class="mr10" title="测试"  href="<?php echo U('collect/index/publicTest',array('id'=>$r['ID']));?>">测试</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                <a class="mr10" title="编辑"  href="<?php echo U('collect/index/edit',array('id'=>$r['ID']));?>">编辑</a> |&nbsp;&nbsp; 
                <a class="mr10 J_dialog" title="复制采集节点" href="<?php echo U('collect/index/copy',array('id'=>$r['ID']));?>" >复制</a> |&nbsp;&nbsp; 
                <a class="mr10" title="导出" href="<?php echo U('collect/index/export',array('id'=>$r['ID']));?>" >导出</a>
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
            <button class="btn J_tag_sub_btn" data-role="del" type="button">删除</button>
            <a class="btn J_dialog" title="导入采集点" href="<?php echo U('collect/index/nodeImport',array('id'=>$r['ID']));?>" >导入采集点</a>
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
    var URL_DELETE = "<?php echo U('collect/index/batchDelete');?>";

    tag_form = $('#J_tag_form');
    //取消&删除热门
    $('button.J_tag_sub_btn').on('click', function(e){
        e.preventDefault();
        var $this = $(this),
        role = $this.data('role'),
        url;
        if(role == 'del'){
            url = URL_DELETE;
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