<?php
return array (
	'Web' => array(
		'version'  => 'base',
		'template-path' =>  'view/base/webtemplates/',
		'controller-path' => 'web',
		'route' => 'index/index/init',
		'filter'=>array('front_filter'=>'src/library/FrontFilter'),
	),
	'Admin' => array(
		'version'  => 'base',
		'template-path' =>  'view/base/admintemplates/',
		'controller-path' => 'admin',
		'route' => 'admin/index/init',
		'filter'=>array('admin_filter'=>'src/library/AdminFilter'),
		'js_path' => '/myfr/freephp/statics/admin/js/', 
		'css_path' => '/myfr/freephp/statics/admin/css/', 
		'img_path' => '/myfr/freephp/statics/admin/images/', 
		'is_admin_log' => 1, 
	),
);
?>