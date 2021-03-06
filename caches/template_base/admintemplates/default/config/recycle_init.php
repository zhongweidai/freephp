<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head><?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body>
<div class="wrap J_check_wrap">
	<div class="nav">
		<ul class="cc">
			<li><a href="<?php echo U('config/index/init',array('ns'=>'site'));?>">站点信息</a></li>
			<li><a href="<?php echo U('config/index/init',array('ns'=>'register'));?>">用户注册</a></li>
			<li><a href="<?php echo U('config/index/init',array('ns'=>'login'));?>">用户登录</a></li>
			<li><a href="<?php echo U('config/index/init',array('ns'=>'attachment'));?>">附件设置</a></li>
			<li  class="current"><a href="<?php echo U('config/recycle/init');?>">回收站设置</a></li>
		</ul>
	</div>
	
  <form method="POST" class="J_ajaxForm" action="<?php echo U('config/index/edit',array('ns'=>'recycle'));?>" data-role="list">
  	<input type="hidden" name="RECYCLE_ENABLE" value="0" />
    <div class="h_a">回收站设置</div>
    <div class="table_full">
      <table width="100%">
        <col class="th" />
        <col width="400" />
        <col />
        <tr>
          <th>启用内容回收站</th>
          <td align="left"><input name="RECYCLE_ENABLE" type="checkbox" class="input length_5" value="1" <?php if($config['RECYCLE_ENABLE']) { ?> checked<?php } ?> />
          </td>
          <td><div class="fun_tips">启用回收站后，被删除的内容会移至回收站</div></td>
        </tr>
        <tr>
          <th>数据库类型</th>
          <td>  
              <select name="RECYCLE_driver" class="select length_5" />
                <option value="Mysql"<?php if($config['RECYCLE_driver']=='Mysql') { ?> selected<?php } ?>>MySQL</option>
                <option value="Oracle"<?php if($config['RECYCLE_driver']=='Oracle') { ?> selected<?php } ?>>Oracle</option>
              </select>
          </td>
          <td><div class="fun_tips"></div></td>
        </tr>
        <tr>
          <th>数据库地址</th>
          <td><input name="RECYCLE_hostname" type="text" class="input length_5" value="<?php echo $config['RECYCLE_hostname'];?>" />
          </td>
          <td><div class="fun_tips"></div></td>
        </tr>
         <tr>
          <th>实例名</th>
          <td><input name="RECYCLE_servicename" type="text" class="input length_5" value="<?php echo $config['RECYCLE_servicename'];?>" />
          </td>
          <td><div class="fun_tips">使用Oracle时需要填写</div></td>
        </tr>
        <tr>
          <th>用户</th>
          <td><input name="RECYCLE_username" type="text" class="input length_5" value="<?php echo $config['RECYCLE_username'];?>" />
          </td>
          <td><div class="fun_tips"></div></td>
        </tr>
        <tr>
          <th>密码</th>
          <td><input name="RECYCLE_password" type="password" class="input length_5" value="<?php echo $config['RECYCLE_password'];?>" />
          </td>
          <td><div class="fun_tips"></div></td>
        </tr>
        <tr>
          <th>数据库名</th>
          <td><input class="input length_5" type="text" name="RECYCLE_database" value="<?php echo $config['RECYCLE_database'];?>" />
          </td>
          <td><div class="fun_tips"></div></td>
        </tr>
        <tr>
          <th>数据库表名前缀</th>
          <td><input class="input length_5" type="text" name="RECYCLE_tablepre" value="<?php echo $config['RECYCLE_tablepre'];?>" />
          </td>
          <td><div class="fun_tips"></div></td>
        </tr>
        <tr>
          <th>回收站表命名规则</th>
          <td>原表名<input class="input length_5" id="table_suff" type="text" name="RECYCLE_TABLE_SUFF" value="<?php echo $config['RECYCLE_TABLE_SUFF'];?>" />
          </td>
          <td><div class="fun_tips"></div></td>
        </tr>
      </table>
    </div>
    
    <div class="btn_wrap">
      <div class="btn_wrap_pd">
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
      </div>
    </div>
  <input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form>

</div>
<?php include $this->_view->templateResolve("admin","footer",$__style__,$__app__); ?>
<script>
(function($){
	$('#table_suff').bind('blur',function(){
		$(this).val( $(this).val().toUpperCase() );
	});
})(jQuery);
<script>
</body>
</html>