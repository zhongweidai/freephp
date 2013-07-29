<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head><?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body>
<div class="wrap">
  <div class="nav">
    <ul class="cc">
      <li><a href="<?php echo U('config/index/init',array('ns'=>'site'));?>">站点信息</a></li>
      <li><a href="<?php echo U('config/index/init',array('ns'=>'register'));?>">用户注册</a></li>
      <li  class="current"><a href="<?php echo U('config/index/init',array('ns'=>'login'));?>">用户登录</a></li>
      <li><a href="<?php echo U('config/index/init',array('ns'=>'attachment'));?>">附件设置</a></li>
    </ul>
  </div>
  <div class="h_a">登录设置</div>
  <form method="POST" class="J_ajaxForm" action="<?php echo U('config/index/edit',array('ns'=>'login'));?>" data-role="list">
    <div class="table_full">
      <table width="100%">
        <col class="th" />
        <col width="400" />
        <col />
        <tr>
          <th>用户登录方式</th>
          <td><ul class="three_list cc">
              <li>
                <label>
                <input type="checkbox" value="1" name="LOGIN_TYPE[]" <?php if(in_array(1,$config['LOGIN_TYPE'])) { ?>checked<?php } ?> />
                UID</label>
              </li>
              <li>
                <label>
                <input type="checkbox" value="2" name="LOGIN_TYPE[]" <?php if(in_array(2, $config['LOGIN_TYPE'])) { ?>checked<?php } ?> />
                电子邮箱</label>
              </li>
              <li>
                <label>
                <input type="checkbox" value="3" name="LOGIN_TYPE[]" <?php if(in_array(3, $config['LOGIN_TYPE'])) { ?>checked<?php } ?> />
                用户名</label>
              </li>
              <li>
                <label>
                <input type="checkbox" value="4" name="LOGIN_TYPE[]" <?php if(in_array(4, $config['LOGIN_TYPE'])) { ?>checked<?php } ?> />
                手机号码</label>
              </li>
            </ul></td>
          <td><div class="fun_tips">请至少选定一种用户登录方式。</div></td>
        </tr>
        <tr>
          <th>密码尝试次数</th>
          <td><input type="number" class="input length_5" name="LOGIN_TRY_PASSWORD" value="<?php echo $config['LOGIN_TRY_PASSWORD'];?>" />
          </td>
          <td><div class="fun_tips">密码输入错误次数限制，超出限制次数后30分钟内不允许再登录。</div></td>
        </tr>
        <tr>
          <th>是否开启图形验证码</th>
          <td><ul class="switch_list cc">
              <li>
                <label>
                <input type="radio" name="LOGIN_VERIFY" value="1" <?php if($config['LOGIN_VERIFY']==1) { ?>checked<?php } ?> />
                <span>开启</span></label>
              </li>
              <li>
                <label>
                <input type="radio" name="LOGIN_VERIFY" value="0" <?php if($config['LOGIN_VERIFY']==0) { ?>checked<?php } ?> />
                <span>关闭</span></label>
              </li>
            </ul></td>
          <td><div class="fun_tips">选择“开启”，则用户在登录时是否开启验证码。</div></td>
        </tr>
      </table>
    </div>
    <div class="btn_wrap">
      <div class="btn_wrap_pd">
        <button class="btn btn_submit J_ajax_submit_btn" type="submit">提交</button>
      </div>
    </div>
  <input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form>
</div>
<?php include $this->_view->templateResolve("admin","footer",$__style__,$__app__); ?>
</body>
</html>