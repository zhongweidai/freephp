<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head><?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body>
<div class="wrap">
  <div class="nav">
    <ul class="cc">
      <li><a href="<?php echo U('config/index/init',array('ns'=>'attachment'));?>">附件设置</a></li>
      <li class="current"><a href="<?php echo U('config/index/init',array('ns'=>'ftp'));?>">远程附件</a></li>
      <li><a href="<?php echo U('config/index/init',array('ns'=>'thumb'));?>">附件缩略</a></li>
    </ul>
  </div>
  <form method="POST" class="J_ajaxForm" action="<?php echo U('config/index/edit',array('ns'=>'ftp'));?>" data-role="list">
    <div class="h_a">远程附件设置</div>
    <div class="table_full">
      <table width="100%">
        <col class="th" />
        <col width="400" />
        <col />
        <tr>
          <th>远程附件功能</th>
          <td><ul class="switch_list cc">
              <li>
                <label>
                <input name="FTP_OPEN" value="1" type="radio"<?php if(($config['FTP_OPEN']==1)) { ?> checked<?php } ?> />
                <span>开启</span></label>
              </li>
              <li>
                <label>
                <input name="FTP_OPEN" value="0" type="radio"<?php if(($config['FTP_OPEN']==0)) { ?> checked<?php } ?> />
                <span>关闭</span></label>
              </li>
            </ul></td>
          <td><div class="fun_tips">可将附件上传到远程服务器。尤其针对镜像站点，可将附件传至主站点，便于附件的统一管理。</div></td>
        </tr>
        <tr>
          <th>远程附件地址</th>
          <td><input name="FTP_URL" type="text" class="input length_5" value="<?php echo $config['FTP_URL'];?>" /></td>
          <td><div class="fun_tips">请输入远程附件要存储的服务器目录，如:http://www.phpwind.net/attachment</div></td>
        </tr>
        <tr>
          <th>FTP服务器地址</th>
          <td><input name="FTP_IP" type="text" class="input length_5" value="<?php echo $config['FTP_IP'];?>" /></td>
          <td><div class="fun_tips"></div></td>
        </tr>
        <tr>
          <th>FTP服务器端口</th>
          <td><input name="FTP_PORT" type="text" class="input length_5" value="<?php echo $config['FTP_PORT'];?>" /></td>
          <td><div class="fun_tips">默认为21</div></td>
        </tr>
        <tr>
          <th>FTP上传目录</th>
          <td><input name="FTP_FOLDER" type="text" class="input length_5" value="<?php echo $config['FTP_FOLDER'];?>" /></td>
          <td><div class="fun_tips">请确保该目录对应的文件夹和远程附件地址对应的文件夹一致。</div></td>
        </tr>
        <tr>
          <th>FTP帐号</th>
          <td><input name="FTP_USERNAME" type="text" class="input length_5"  value="<?php echo $config['FTP_USERNAME'];?>" /></td>
          <td><div class="fun_tips"></div></td>
        </tr>
        <tr>
          <th>FTP密码</th>
          <td><input name="FTP_PASSWORDD" type="password" class="input length_5"  value="<?php echo $config['FTP_PASSWORDD'];?>" /></td>
          <td><div class="fun_tips">密码中请不要包含“*”</div></td>
        </tr>
        <tr>
          <th>FTP超时</th>
          <td><input name="FTP_EXPIRED" type="text" class="input length_5 mr5" value="<?php echo $config['FTP_EXPIRED'];?>" />
            秒</td>
          <td><div class="fun_tips">系统将在设定时间内等待服务器的响应。</div></td>
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
</body>
</html>