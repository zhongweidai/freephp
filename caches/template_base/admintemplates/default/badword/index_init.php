<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head>
<?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body>
<div class="wrap">

<div class="h_a">提示信息</div>
<div class="fr"><form action="" class="J_custom_ajaxForm" method="post"><button class="btn btn_submit J_ajax_submit_btn fr" data-form="J_custom_ajaxForm" data-url="<?php echo U('badword/index/setCache', array('show_module' => 1));?>">更新缓存</button><input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form></div>
<div class="mb10 prompt_text">
	<ol>
		<li>添加敏感字词</li>
        <li>点击敏感字词，替换字词，状态进行直接编辑</li>
	</ol>
</div>
<div class="cc mb10">
<!-- <a href="<?php echo U('badword/Index/add');?>" title="添加敏感字词" class="btn J_dialog">    -->
</div>

<div class="table_full">
	<form action="#" method="get" id="J_navigation_search_form">
	    <input type="hidden" name="m" value="badword"/>
        <input type="hidden" name="c" value="index"/>
        <input type="hidden" name="a" value="init"/>
        <input type="hidden" name="field" id="orderfield" />
        <input type="hidden" name="order" value="<?php echo $order;?>"/>
        <table width="100%">
		<tbody><tr>
			<td>
				敏感字词&nbsp;&nbsp;<input type="text" name="badword" class="input length_3 mr10" id='badword' value="<?php echo $search['BADWORD'];?>">
                <button type="submit" class="btn btn_submit" id="J_navigation_search"><?php echo L('search');?></button>
            </td>
		</tr></tbody>
        </table>
	<input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form>
</div>

<form method="post" class="J_ajaxForm" action="#" id="J_tag_form">
<div class="table_list J_check_wrap" id="J_tag_list">
	<table width="100%" id="J_table_list">
        <colgroup>
			<col width="50">
			<col width="80">
			<col width="150">
			<col width="150">
			<col width="100">
            <col width="180">
            <col width="80">
            <col>
		</colgroup>
		<thead>
			<tr>
				<td><label><input type="checkbox" data-checklist="J_check_y" data-direction="y" class="J_check_all" name="checkAll">全选</label></td>
				<td align="center"><a href="#" class="J_order" data-field="LISTORDER" >排序</a></td>
				<td align="center">敏感字词</td>
				<td align="center">替换字词</td>
				<td align="center"><a href="#" class="J_order" data-field="BLEVEL" >等级</a></td>
				<td align="center"><a href="#" class="J_order" data-field="LASTUSETIME" >操作时间</a></td>
                <td align="center"><a href="#" class="J_order" data-field="IS_OPEN" >状态</a></td>
				<td align="center">管理操作</td>
			</tr>
		</thead>
<?php $n=1;if(is_array($list)) foreach($list AS $r) { ?>
		<tr data-id="<?php echo $r['ID'];?>" data-url="<?php echo U('badword/index/ajax');?>">
			<td><input type="checkbox" value="<?php echo $r['ID'];?>" name="id[]" data-yid="J_check_y" data-xid="J_check_x" class="J_check"></td>
            <td align="center"><input class="input length_0" type="text" name="order[<?php echo $r['ID'];?>]" value="<?php echo $r['LISTORDER'];?>"/></td>
			<td align="center"><span class="J_listTable" data-type="edit" data-field="BADWORD"><?php echo $r['BADWORD'];?></td>
			<td align="center"><span class="J_listTable" data-type="edit" data-field="REPLACEWORD"><?php echo $r['REPLACEWORD'];?></td>
			<td align="center"><?php if($r['BLEVEL']==1) { ?>一般<?php } else { ?>危险<?php } ?></td>
			<td align="center"><?php echo date('Y-m-d H:i:s', $r['LASTUSETIME']); ?></td>
            <td align="center"><?php if($r['IS_OPEN']) { ?><span class="green J_listTable"  data-type="toggle" data-field="IS_OPEN">√</span><?php } else { ?><span class="red J_listTable" data-type="toggle" data-field="IS_OPEN">×</span><?php } ?></td>
			<td align="center">
			<a href="<?php echo U('badword/Index/delete', array('id'=>$r['ID']));?>" class="mr10 J_ajax_del">删除</a>
			</td>
		</tr>
<?php $n++;}unset($n); ?>
	</table>
    <table width="100%">
    <tbody>
        <tr class="ct">
            <td style="padding-left:38px;" colspan="5">
                <a id="J_add_root" class="link_add" href="" data-html="tbody" data-type="nav_1">添加敏感字词</a>
            </td>
        </tr>
    </tbody>
    </table>
</div>
    <div class="pages"><?php echo $pages;?></div>
		<div class="btn_wrap">
		<div class="btn_wrap_pd" id="J_sub_wrap">
			<input type="hidden" id="subAct" name="subAct"/>
			<label class="mr20"><input type="checkbox" data-checklist="J_check_x" data-direction="x" class="J_check_all" name="checkAll">全选</label>
            <button class="btn J_tag_sub_btn" data-role="del" type="submit">删除</button>
            <button class="btn J_tag_sub_btn btn_success" data-role="audio" type="button">批量审核</button>
            <button class="btn btn_submit" id="J_submit_btn" type="button">提交</button>
		</div>
	</div>
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
/*
root_tr_html 为“添加导航”html
child_tr_html 为“添加二级导航”html
*/

var root_tr_html = '<tr>\
                            <td></td>\
                            <td align="center">\<input name="newdata[root_][LISTORDER]" type="text" value="" class="input length_0 mr10">\</td>\
                            <td align="center">\<input name="newdata[root_][BADWORD]" type="text" class="input length_2 mr5" value="">\</td>\
                            <td align="center">\<input name="newdata[root_][REPLACEWORD]" type="text" class="input length_2 mr5" value="">\</td>\
                            <td><select name="newdata[root_][BLEVEL]">\
                                        <option value="1">一般</option>\
                                        <option value="2">危险</option>\
                                </select>\</td>\
                            <td></td>\
                            <td class="tac"></td>\
                            <td align="center">\
                                <a href="" class="mr5 J_newRow_del">[删除]</a>\
                            </td>\
                  </tr>';

Wind.js(GV.JS_ROOT+ 'pages/admin/common/forumTree_table.js?v=' +GV.JS_VERSION);
</script>
<script>
Wind.use('dialog', 'jquery.form', function(){

    var URL_SUBMIT = "<?php echo U('badword/index/batchSubmit');?>";
    var URL_AUDIT = "<?php echo U('badword/index/batchAudit');?>";
    var URL_DELETE = "<?php echo U('badword/index/batchDelete');?>";
    
    //点击排序
    $('#J_submit_btn').on('click', function(e){
        e.preventDefault();
        var $this = $(this);
        Wind.dialog({
            message : '确定要提交吗？',
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
        }else if(role == 'audio'){
            url = URL_AUDIT;
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
            }else if(role == 'audio'){
                Wind.dialog({
                    message : '确定要批量审核吗？',
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