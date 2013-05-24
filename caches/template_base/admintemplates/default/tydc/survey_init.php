<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head>
<?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body>
<div class="wrap">
    <div class="fr">
		<form class="J_custom_ajaxForm" method="post" action="">
			<button class="btn btn_submit J_ajax_submit_btn fr" data-url="<?php echo U('tydc/statis/cache');?>" data-form="J_custom_ajaxForm">刷新统计缓存</button>
		<input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form>
</div>

<div class="nav">
	<ul class="cc">
		<li class="current"><a href="<?php echo U('tydc/statis/'.$dc);?>">问卷调查</a></li>
	</ul>
</div>
<div class="cc mb10"> 
    <a href="<?php echo U('tydc/survey/add');?>" class="btn J_dialog" title="添加问卷调查" ><span class="add"></span>添加调查</a>  
</div>

<form method="post" action="<?php echo U('channel/content/linesEdit',array('id'=>$id,'nav_id'=>$nav_id));?>" data-role="list" class="J_ajaxForm" id="J_tag_form">
<div class="table_list J_check_wrap" id="J_tag_list">
		<table width="100%">
			<colgroup>
				<col width="20">
				<col width="160">
                <col width="50">
				<col width="50">
				<col width="50">
				<col width="100">
				<col width="250">
                <col>
			</colgroup>
			<thead>
				<tr>
					<td><label><input type="checkbox" data-checklist="J_check_y" data-direction="y" class="J_check_all" name="checkAll">全选</label></td>
					<td align="center"><a href="#" class="J_order" data-field="ORDER_NO" >标题</a></td>
                    <td align="center">录入者</td>
                    <td align="center">问卷状态</td>
					<td align="center">浏览次数</td>
					<td align="center">发布时间</td>
					<td align="center">管理操作</td>
				</tr>
			</thead>
			<?php if(is_array($data)) { ?>
			<tbody id="J_tr">
			<?php $n=1;if(is_array($data)) foreach($data AS $r) { ?>
            <tr data-id="<?php echo $r['ID'];?>">
                <td><input type="checkbox" value="<?php echo $r['ID'];?>" name="id[]" data-yid="J_check_y" data-xid="J_check_x" class="J_check"></td>
                <td align="center"><?php echo $r['TITLE'];?></td>
                <td align="center"><?php echo $r['USERNAME'];?></td>
                <td align="center"><?php if($r['PASSED']==1) { ?>通过<?php } else { ?>待审核<?php } ?></td>
                <td align="center"><?php echo $r['HITS'];?></td>
                <td align="center"><?php echo date('Y-m-d H:i:s', $r['ADDTIME']);?></td>
                <td align="center"> 
					<a href="<?php echo U('tydc/survey/init',array('id' => $r['ID']),'','','index.php');?>" class="mr10" target="_blank">前台预览</a>
					 <a title="修改" href="<?php echo U('tydc/survey/edit',array('id'=>$r['ID']));?>" class="mr10 J_dialog">修改</a>
					 <a title="问题列表" href="<?php echo U('tydc/survey/question',array('sid'=>$r['ID']));?>" class="mr10">问题列表</a>
					 <a title="信息列表" href="<?php echo U('tydc/survey/edit',array('id'=>$r['ID']));?>" class="mr10 ">信息列表</a>
					 <a title="查看统计" href="<?php echo U('tydc/survey/edit',array('id'=>$r['ID']));?>" class="mr10 J_dialog">查看统计</a>
					 <a title="删除" href="<?php echo U('tydc/survey/delete',array('id'=>$r['ID']));?>" class="J_ajax_del" >删除</a> 
				</td>
        	</tr>
			<?php $n++;}unset($n); ?>
			</tbody>
			<?php } ?>
		</table>
	</div>
	<div class="pages"><?php echo $pages;?></div>
		<div class="btn_wrap">
		<div class="btn_wrap_pd" id="J_sub_wrap">
			<label class="mr20"><input type="checkbox" data-checklist="J_check_x" data-direction="x" class="J_check_all" name="checkAll">全选</label>
            <button class="btn J_all" data-role="del" type="button">删除</button>
            <button class="btn J_all" data-role="pass" type="button">审核通过</button>
		</div>
	</div>
	<input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form>
</div>
<?php include $this->_view->templateResolve("admin","footer",$__style__,$__app__); ?>

</body>
</html>
<script>
(function($){
     /*$(".J_order").click(function(){
        var field = $(this).data('field');
        $("#orderfield").val(field);
        $("#J_navigation_search_form").submit();
    })；*/
    Wind.use('dialog', 'jquery.form', function(){
        var URL_DELETE = "<?php echo U('tydc/survey/delete');?>";
        var URL_PASS= "<?php echo U('tydc/survey/pass');?>";
		
        tag_form = $('#J_tag_form');
        //取消&删除热门
        $('button.J_all').on('click', function(e){
            e.preventDefault();
            var $this = $(this),
            role = $this.data('role'),
            url, _message_text;
            switch (role)
            {
                case 'del':
                    url = URL_DELETE;
                    _message_text = '确定要删除吗？';
                    break;
                case 'pass':
                    url = URL_PASS;
                    _message_text = '确定要批量通过码？';
                    break;
               
                default:
                    break;
            }
            if(role == "order")
			{
				tagManageSub(url, $this);
				return true;
			}
            if(getCheckedTr()) {
                Wind.dialog({
                    message : _message_text,
                    type : 'confirm',
                    onOk : function () {
                        tagManageSub(url, $this);
                    }
                });

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
                $('span',sub_wrap).empty();
                var el = sub_wrap.get(0);
                while ( $('.tips_success',el).size()+$('.tips_error',el).size() )
                {
                    el.removeChild(el.lastChild);
                }
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

})(jQuery);
</script>