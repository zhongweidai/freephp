<?php
	define('FREE_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR);
	define('FREE_DEBUG',true);
	define('FREE_RUNTIME',false);//是否开启运行缓存 默认开启
	include FREE_PATH.'/src/freePro.php';
	FreePro::run('Admin');
