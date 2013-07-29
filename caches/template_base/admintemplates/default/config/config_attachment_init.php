<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head><?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body>
<div class="wrap">
  <div class="nav">
    <ul class="cc">
      <li class="current"><a href="<?php echo U('config/index/init',array('ns'=>'attachment'));?>">附件设置</a></li>
      <li><a href="<?php echo U('config/index/init',array('ns'=>'ftp'));?>">远程附件</a></li>
      <li><a href="<?php echo U('config/index/init',array('ns'=>'thumb'));?>">附件缩略</a></li>
    </ul>
  </div>
  <form method="POST" class="J_ajaxForm" action="<?php echo U('config/index/edit',array('ns'=>'attachment'));?>" data-role="list">
    <div class="h_a">基本设置</div>
    <div class="table_full">
      <table width="100%">
        <col class="th" />
        <col width="400" />
        <col />
        <tr>
          <th>附件路径控制</th>
          <td><input name="PATHSIZE" type="text" class="input length_5 mr5" value="<?php echo $config['PATHSIZE'];?>">
            KB</td>
          <td><div class="fun_tips">当附件超过设定值时，系统将直接读取附件真实路径。此功能可帮助减少系统php进程消耗，亦可帮助转移附件流量并减轻附件服务器负担。请根据站点附件实际情况进行设置。0或留空表示不限制。 </div></td>
        </tr>
        <tr>
          <th>单次附件上传个数限制</th>
          <td><input name="ATTACHNUM" type="text" class="input length_5 mr5" value="<?php echo $config['ATTACHNUM'];?>"></td>
          <td><div class="fun_tips"></div></td>
        </tr>
        <tr>
          <th>附件类型和尺寸控制</th>
          <td><div class="cross">
              <ul id="J_ul_list_attachment" class="J_ul_list_public">
                <li> <span class="span_3">后缀名(小写)</span> <span class="span_3">最大值(KB)</span> </li>
                <?php if($config['EXTSIZE']) { ?>
                <?php $n=1; if(is_array($config['EXTSIZE'])) foreach($config['EXTSIZE'] AS $k => $v) { ?>
                <li> <span class="span_3">
                  <input name="EXTSIZE[<?php echo $k;?>][EXT]" type="text" class="input length_2" value="<?php echo $v['EXT'];?>">
                  </span> <span class="span_4">
                  <input name="EXTSIZE[<?php echo $k;?>][SIZE]" type="text" class="input mr15 length_2"  value="<?php echo $v['SIZE'];?>">
                  <a href="#" class="J_ul_list_remove">[删除]</a></span> </li>
                <?php $n++;}unset($n); ?>
                <?php } else { ?>
                <li> <span class="span_3">
                  <input name="EXTSIZE[NEW_0][EXT]" type="text" class="input length_2" value="">
                  </span> <span class="span_4">
                  <input name="EXTSIZE[NEW_0][SIZE]" type="text" class="input mr15 length_2"  value="">
                  <a href="#" class="J_ul_list_remove">[删除]</a></span> </li>
                <?php } ?>
              </ul>
            </div>
            <a href="" class="link_add J_ul_list_add" data-related="attachment" data-count="<?php echo count($config['EXTSIZE'])-1;?>">添加附件类型</a> </td>
          <td><div class="fun_tips">系统限制上传单个附件的最大值：<span class="red"></span></div></td>
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
<script type="text/javascript">
var _li_html = '<li>\
					<span class="span_3"><input type="text" value="" class="input length_2" name="EXTSIZE[NEW_][EXT]"></span>\
					<span class="span_4">\
						<input type="text" value="" class="input length_2 mr15" name="EXTSIZE[NEW_][SIZE]"><a class="J_ul_list_remove" href="#">[删除]</a>\
					</span>\
				</li>';
</script>
</body>
</html>