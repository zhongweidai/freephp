<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head><?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body>
<div class="wrap">
  <div class="nav">
    <ul class="cc">
      <li><a href="<?php echo U('config/index/init',array('ns'=>'attachment'));?>">附件设置</a></li>
      <li><a href="<?php echo U('config/index/init',array('ns'=>'ftp'));?>">远程附件</a></li>
      <li class="current"><a href="<?php echo U('config/index/init',array('ns'=>'thumb'));?>">附件缩略</a></li>
    </ul>
  </div>
  <form method="POST" class="J_ajaxForm" action="<?php echo U('config/index/edit',array('ns'=>'thumb'));?>" data-role="list">
    <div class="h_a">缩略图设置</div>
    <div class="table_full">
      <table width="100%">
        <col class="th" />
        <col width="400" />
        <col />
        <tr>
          <th>帖子图片缩略设置</th>
          <td><ul class="single_list cc">
              <li>
                <label>
                <input type="radio" checked="" value="0" name="THUMB" />
                不缩略</label>
              </li>
              <li>
                <label>
                <input type="radio" value="1" name="THUMB" />
                等比缩略</label>
              </li>
              <li>
                <label>
                <input type="radio" value="2" name="THUMB" />
                居中截取</label>
              </li>
            </ul></td>
          <td><div class="fun_tips"></div></td>
        </tr>
        <tr>
          <th>缩略图大小设置</th>
          <td><input type="text" name="THUMBSIZE_WIDTH" value="<?php echo $config['THUMBSIZE_WIDTH'];?>" class="input mr5" />
            <span class="mr5">*</span>
            <input type="text" name="THUMBSIZE_HEIGHT" value="<?php echo $config['THUMBSIZE_HEIGHT'];?>" class="input" /></td>
          <td><div class="fun_tips">超过此尺寸的图才缩略</div></td>
        </tr>
        <tr>
          <th>缩略图质量</th>
          <td><input name="QUALITY" type="text" class="input length_5" value="<?php echo $config['QUALITY'];?>" /></td>
          <td><div class="fun_tips">控制缩略图的生成质量，数字越大越清晰</div></td>
        </tr>
        <tr>
          <th>缩略图预览</th>
          <td><input type="button" id="J_img_preview" value="图片缩略预览" class="btn" /></td>
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
  <!-- end -->
</div>
<?php include $this->_view->templateResolve("admin","footer",$__style__,$__app__); ?>
<script>
$(function(){

	//引入组件
	Wind.use('dialog', 'jquery.form', function(){
		//缩略图预览
		$('#J_img_preview').click(function(e){
			e.preventDefault();
			var $this = $(this);

			$('form.J_ajaxForm').ajaxSubmit({
				url         : '<?php echo U("config/index/viewImg",array("ns"=>"thumb"));?>',
				dataType	: 'json',
				success		: function(data, statusText, xhr, $form) {
					if(data.state === "success") {
						Wind.dialog.html('<div style="padding:15px;"><img style="display:block;" src='+ data.data.img +' alt="缩略图预览"/></div><div class="pop_bottom"><button class="btn J_close" type="button">关闭</button></div>', {
							onClose : function(){
								$this.focus();
							}
						});
					}else{
						Wind.dialog.alert(data.message);
					}
				}
			});
		});
	});
});
</script>
</body>
</html>