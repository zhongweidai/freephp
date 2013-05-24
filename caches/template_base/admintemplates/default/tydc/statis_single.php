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
		<li><a href="<?php echo U('tydc/statis/'.$dc);?>">全员统计报表</a></li>
		<li class="current"><a href="<?php echo U('tydc/statis/single', array('dc' => $dc));?>">单项统计报表</a></li>
        <li><a href="<?php echo U('tydc/statis/search', array('dc' => $dc));?>">自定义统计报表</a></li>
	</ul>
	
</div>
<!--
<div class="h_a">提示信息</div>
<div class="mb10 prompt_text">
	<li>提示</li>
</div>
-->
<div class="table_list">
    <table width="100%">
        <colgroup>
            <col width="60">
            <col width="400">
            <col>
        </colgroup>
        <thead>
            <tr>
                <td>编号</td>
                <td>调查内容</td>
                <td>统计报表</td>
            </tr>
        </thead>
	    <tbody id="J_tr">
        <?php $n=1;if(is_array($lists)) foreach($lists AS $list) { ?>
        <?php if(count($lists) > 1) { ?>
        <tr>
            <td colspan="3" style="background-color: #F9F9F9"><a name="nav-<?php echo $list['ID'];?>"></a> <?php echo $list['CN_NAME'];?> <?php echo $list['EN_NAME'];?></td>
        </tr>
        <?php } ?>
        <?php $n=1;if(is_array($list['items'])) foreach($list['items'] AS $v) { ?>
        <?php $k++; ?>
            <tr>
                <td><?php echo $k;?></td>
                <td><?php echo $v['TITLE'];?></td>
                <td><a href="<?php echo U('tydc/statis/singleChart', array('dc_id' => $v['ID'], 'dc_type' => $v['TYPE_ID'], 'dc' => $dc));?>" class="J_dialog btn" title="单项比例">单项比例</a> </td>
            </tr>
        <?php $n++;}unset($n); ?>
        <?php $n++;}unset($n); ?>
        </tbody>
    </table>
</div>
<?php if(count($lists) > 1) { ?>
<div class="btn_wrap">
	<div class="btn_wrap_pd">
        <?php $n=1;if(is_array($lists)) foreach($lists AS $v) { ?>
        <a class="btn mb10" href="#nav-<?php echo $v['ID'];?>"><?php echo $v['CN_NAME'];?></a>
        <?php $n++;}unset($n); ?>
    </div>
</div>
<?php } ?>
</div>
<?php include $this->_view->templateResolve("admin","footer",$__style__,$__app__); ?>
</body>
</html>