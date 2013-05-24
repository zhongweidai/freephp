<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
	<head>
		<meta charset="utf-8" />
		<title><?php echo L('admin_site_title');?></title>
		<link href="<?php echo CSS_PATH;?>admin_login.css?v={@G:c.version}" rel="stylesheet" />
		<script>
			if (window.parent !== window.self) {
					document.write = '';
					window.parent.location.href = window.self.location.href;
					setTimeout(function () {
							document.body.innerHTML = '';
					}, 0);
			}
		</script>
	</head>
<body>
	<div class="wrap">
		<h1><a href="admin.php"><?php echo L('admin_login_title');?></a></h1>
		<form method="post" name="login" action="<?php echo U('admin/index/login');?>" autoComplete="off">
			<div class="login">
				<ul>
					<li>
						<input class="input" id="admin_name" name="username" type="text" tabindex="1" placeholder="帐号名" />
					</li>
					<li>
						<input class="input" id="admin_pwd" type="password" name="password" tabindex="2" placeholder="密码" />
					</li>
					<!--li>
						<img src="" width="240" height="60">
					</li>
					<li>
						<input class="input" id="admin_pwd" type="password" name="password" tabindex="2" placeholder="请输入验证码" />
					</li-->
				</ul>
				<button type="submit" name="submit" class="btn">登录</button>
			</div>
		<input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form>
	</div>
<?php include $this->_view->templateResolve("admin","footer",$__style__,$__app__); ?>
</body>
</html>