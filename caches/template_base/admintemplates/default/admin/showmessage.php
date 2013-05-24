<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head>
<?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
<?php if($referer && $refresh) { ?>
<meta http-equiv="refresh" content="<?php echo $ms;?>;url=<?php echo $referer;?>">
<?php } ?>
</head>
<body>
<div class="wrap">
	<div id="error_tips">
		<h2>信息提示</h2>
		<div class="error_cont">
			<ul>
				<li><?php echo $message;?></li>
			</ul>
			<?php if($referer) { ?>
			<div class="error_return"><a href="<?php echo $referer;?>" class="btn">返回</a></div>
			<?php } else { ?>
			<div class="error_return"><a href="javascript:window.history.go(-1);" class="btn">返回</a></div>
			<?php } ?>
		</div>
	</div>
</div>
<?php include $this->_view->templateResolve("admin","footer",$__style__,$__app__); ?>
</body>
</html>