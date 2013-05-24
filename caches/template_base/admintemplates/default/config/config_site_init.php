<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head>
<?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body>
<div class="wrap">
  <div class="nav">
    <ul class="cc">
      <li class="current"><a href="<?php echo U('config/index/init',array('ns'=>'site'));?>">站点信息</a></li>
      <li><a href="<?php echo U('config/index/init',array('ns'=>'register'));?>">用户注册</a></li>
      <li><a href="<?php echo U('config/index/init',array('ns'=>'login'));?>">用户登录</a></li>
      <li><a href="<?php echo U('config/index/init',array('ns'=>'attachment'));?>">附件设置</a></li>
	   <!-- li><a href="<?php echo U('config/workflow/init');?>">工作流</a></li -->
      <li><a href="<?php echo U('config/recycle/init');?>">回收站设置</a></li>
    </ul>
  </div>
  <form method="POST" class="J_ajaxForm" action="<?php echo U('config/index/edit',array('ns'=>'site'));?>" data-role="list">
    <div class="h_a">站点信息设置</div>
    <div class="table_full">
      <table width="100%">
        <col class="th" />
        <col width="400" />
        <col />
        <tr>
          <th>站点名称</th>
          <td><input name="SITE_NAME" type="text" class="input length_5" value="<?php echo $config['SITE_NAME'];?>" />
          </td>
          <td><div class="fun_tips">默认站点名称，如果各个应用没有填写站点名称，则显示这个名称</div></td>
        </tr>
        <tr>
          <th>站点地址</th>
          <td><input name="SITE_URL" type="text" class="input length_5" value="<?php echo $config['SITE_URL'];?>" />
          </td>
          <td><div class="fun_tips">填写您站点的完整域名。例如: http://www.free.com.cn，不要以斜杠 (“/”) 结尾</div></td>
        </tr>
        <tr>
          <th>管理员电子邮箱</th>
          <td><input name="SITE_EMAIL" type="text" class="input length_5" value="<?php echo $config['SITE_EMAIL'];?>" />
          </td>
          <td><div class="fun_tips">填写站点管理员的邮箱地址</div></td>
        </tr>
        <tr>
          <th>ICP 备案信息</th>
          <td><input name="SITE_ICP" type="text" class="input length_5" value="<?php echo $config['SITE_ICP'];?>" />
          </td>
          <td><div class="fun_tips">填写 ICP 备案的信息，例如: 浙ICP备xxxxxxxx号</div></td>
        </tr>
        <tr>
          <th>第三方统计代码</th>
          <td><textarea class="length_5" name="STATISTICS_CODE"><?php echo $config['STATISTICS_CODE'];?></textarea>
          </td>
          <td><div class="fun_tips">在第三方网站上注册并获得统计代码，并将统计代码粘贴在下面文本框中即可。</div></td>
        </tr>
      </table>
    </div>
    <div class="h_a">站点状态设置</div>
    <div class="table_full">
      <table width="100%">
        <col class="th" />
        <col width="400" />
        <col />
        <tr>
          <th>站点状态</th>
          <td><ul id="J_status_type" class="single_list cc">
              <li>
                <label>
                <input data-title="s1" data-type="" name="VISIT_STAT" type="radio" value="0"<?php if($config['VISIT_STAT']==0) { ?> checked<?php } ?> />
                完全开放</label>
              </li>
              <li>
                <label>
                <input data-title="s2" data-type="J_status_s1,J_status_s2" name="VISIT_STAT" type="radio" value="1"<?php if($config['VISIT_STAT']==1) { ?> checked<?php } ?> />
                内部开放</label>
              </li>
              <li>
                <label>
                <input data-title="s3" data-type="J_status_s2" name="VISIT_STAT" type="radio" value="2"<?php if($config['VISIT_STAT']==2) { ?> checked<?php } ?> />
                完全关闭</label>
              </li>
            </ul></td>
          <td><div id="J_status_tip" class="fun_tips">完全关闭:除站点创始人，其他人都不允许访问站点，一般用于站点关闭、系统维护等情况</div></td>
        </tr>
      </table>
    </div>
    <div class="table_full">
      <table width="100%" id="J_status_s1" class="J_status_tbody">
        <col class="th" />
        <col width="400" />
        <col />
        <tr>
          <th>允许访问的IP段</th>
          <td><textarea class="length_5" name="VISIT_IP"><?php echo $config['VISIT_IP'];?></textarea>
          </td>
          <td><div class="fun_tips">站点内部开放状态下，允许访问站点的特定IP段用户。<br>
              如：192.168.1.*，表示192.168.1下的所有IP都允许访问站点。<br>
              多个IP段之间请用英文半角逗号“,”分隔。留空则表示不使用此功能。<br>
              您当前登录IP：127.0.0.1</div></td>
        </tr>
      </table>
      <table width="100%" id="J_status_s2" class="J_status_tbody">
        <col class="th" />
        <col width="400" />
        <col />
        <tr>
          <th>限制访问提示信息</th>
          <td><textarea class="length_5" name="VISIT_MESSAGE"><?php echo $config['VISIT_MESSAGE'];?></textarea>
          </td>
          <td><div class="fun_tips">当站点处于内部开放状态时，登录界面显示的提示信息</div></td>
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
$(function(){
	//站点状态
	var status_title = {
		s1 : '允许任何人访问站点',
		s2 : '特定会员才能访问站点，通常用于站点内部测试、调试',
		s3 : '除创始人，其他用户不允许访问站点，一般用于站点关闭、系统维护等情况'
	};
	
	var checked = $('#J_status_type input:checked');
	
	statusAreaShow(checked.data('type'));
	statusTitle(checked.data('title'));

	$('#J_status_type input:radio').on('change', function(){
			statusAreaShow($(this).data('type'));
			statusTitle($(this).data('title'));
	});

	//切换显示版块
	function statusAreaShow(type) {
		var status_arr= new Array();
		
		status_arr = type.split(",");
		$('table.J_status_tbody').hide();
		
		$.each(status_arr, function(i, o){
			$('#'+ o).show();
		});
	}
	
	//切换提示文案
	function statusTitle(title){
		$('#J_status_tip').text(status_title[title]);
	}
});
</script>
</body>
</html>