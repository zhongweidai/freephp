<?php defined('IN_FREE') or exit('No permission resources.'); ?><script>
//全局变量，是Global Variables不是Gay Video喔
var GV = {
	JS_ROOT : "<?php echo JS_PATH;?>dev/",																									//js目录
	JS_VERSION : "{@G:c.version}",																										//js版本号
	TOKEN : '<?php echo $csrf_token;?>',	//token ajax全局
	REGION_CONFIG : {},
	SCHOOL_CONFIG : {},
	URL : {
		LOGIN : '<?php echo U("admin/index/login");?>',																													//后台登录地址
		//REGION : '{@url:bbs/webData/area|pw}',					//地区
		//SCHOOL : '{@url:bbs/webData/school|pw}'				//学校
	}
};
</script>
<script src="<?php echo JS_PATH;?>core.js"></script>
<script src="<?php echo JS_PATH;?>jquery.js"></script>
<script src="<?php echo JS_PATH;?>pages/admin/common/common.js"></script>
