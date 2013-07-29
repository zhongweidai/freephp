<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head><?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body style="width:460px;">
<div class="warp">
  <!--用户权限编辑添加: 用户编辑弹窗-->
  <form id="J_auth_form" action="<?php echo U('admin/auth/add');?>" method="post" class="J_ajaxForm"  data-role="edit">
    <div style="width:460px;background:#fff;">
      <div class="pop_cont pop_table">
        <div class="mb15">
          <table width="100%" style="table-layout:fixed;">
            <col width="80" />
            <col />
            <tr>
              <th><?php echo L('username');?></th>
              <td><span class="must_red">*</span>
                <input name="info[USERNAME]" value="" type="text" class="input input_hd"> 作为后台登录的唯一账号
              </td>
            </tr>
            <tr>
              <th><?php echo L('password');?></th>
              <td><span class="must_red">*</span>
                <input type="password" name="info[PASSWORD]" class="input" value=""> 密码请尽量设置复杂
              </td>
            </tr>
            <tr>
              <th><?php echo L('cofirmpwd');?></th>
              <td><span class="must_red">*</span>
                <input type="password" name="REPASSWORD" class="input" value=""> 请记住设置的密码
              </td>
            </tr>
			 <tr>
              <th>真实姓名</th>
              <td><span class="must_red">*</span>
                <input type="text" name="info[REALNAME]" class="input input_hd length_3" value=""> 
              </td>
            </tr>
            <tr>
              <th><?php echo L('email');?></th>
              <td><span class="must_red">*</span>
                <input type="text" name="info[EMAIL]" class="input input_hd length_3" value=""> 
              </td>
            </tr>
          </table>
        </div>
        <div class="cc shift">
          <div class="fl">
            <h4><?php echo L('userinrole');?></h4>
            <select id="J_roles" size="10" name="roles">
              <?php $n=1;if(is_array($roles)) foreach($roles AS $role) { ?><option value="<?php echo $role['ID'];?>"><?php echo $role['NAME'];?></option><?php $n++;}unset($n); ?>
			</select>
          </div>
          <div class="fl shift_operate">
            <p class="mb10"><a id="J_auth_role_add" href="" class="btn">添加 &gt;&gt;</a></p>
            <p><a id="J_auth_role_del" href="" class="btn">&lt;&lt; 移除</a></p>
          </div>
          <div class="fl">
            <h4>拥有角色</h4>
            <select id="J_user_roles" name="userRole[]" size="10" multiple="multiple">
            </select>
          </div>
        </div>
      </div>
      <div class="pop_bottom">
        <button id="J_auth_sub" class="btn btn_submit" type="submit">提交</button>
        <div id="J_submit_tips"></div>
      </div>
    </div>
  <input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form>
  <!--用户编辑弹窗结束-->
</div>
<?php include $this->_view->templateResolve("admin","footer",$__style__,$__app__); ?>
<script>
Wind.js('<?php echo JS_PATH;?>pages/admin/auth_manage.js');
</script>
</body>
</html>