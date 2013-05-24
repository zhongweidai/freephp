<?php
return array (
	'Web' => array(
		'version'  => 'base',
		'template-path' =>  'view/base/webtemplates/',
		'controller-path' => 'web',
		'route' => 'index/index/init',
		'filter'=>array('front_filter'=>'src/library/FrontFilter'),
	),
);
?>