<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head><?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body>
<div class="wrap">
	<div id="home_toptip"></div>
	<h2 class="h_a">系统信息</h2>
	<div class="home_info">
		<ul>
			<li>
				<em>软件版本</em>
				<span><?php echo $sysinfo['wind_version'];?></span>
			</li>
			<!--
			<li>
				<em>操作系统</em>
				<span>WINNT</span>
			</li>
			 -->
			<li>
				<em>PHP版本</em>
				<span><?php echo $sysinfo['php_version'];?></span>
			</li>
			<li>
				<em>MYSQL版本</em>
				<span><?php echo $sysinfo['mysql_version'];?></span>
			</li>
			<li>
				<em>服务器端信息</em>
				<span><?php echo $sysinfo['server_software'];?></span>
			</li>
			<li>
				<em>最大上传限制</em>
				<span><?php echo $sysinfo['max_upload'];?></span>
			</li>
			<li>
				<em>最大执行时间</em>
				<span><?php echo $sysinfo['max_excute_time'];?></span>
			</li>
			<li>
				<em>邮件支持模式</em>
				<span><?php echo $sysinfo['sys_mail'];?></span>
			</li>
		</ul>
	</div>
	<h2 class="h_a">开发团队</h2>
	<div class="home_info" id="home_devteam">
        <ul>
            <li><em>版权所有</em><span></span></li>
            <li><em>负责人</em><span>代中伟</span></li>
            <li><em>产品研发</em><span>张俊、熊志刚、朱辉、陈兵等</span></li>
            <li><em>UED</em><span></span></li>
            <li><em>市场运营</em><span></span></li>
        </ul>
	</div>
</div>
</body>
</html>