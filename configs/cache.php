<?php

return array (
	'file1' => array (
		'type' => 'file',
		'debug' => true,
		'pconnect' => 0,
		'autoconnect' => 0
		),
	'memcache' => array(
		'0' => array (
			'hostname' => '10.8.6.239',
			'port' => 11211,
			'timeout' => 1,
		),
	),
	'mongo_db' => array(
		'connect'=>array(
			0=> array(
				'type' => 'mongo',
				'hostname' => '127.0.0.1',
				'database' => 'FREE',
				'username' => '',
				'password' => '',
				'port'     => '27017',
				'autoconnect' => 0,
			),
		),
		'timeout' => 200,
	),
	
	'redis' => array(
		'type' => 'redis',
		'servers' => array(
			'0' => array (
				'hostname' => '127.0.0.1',
				'port' => '6379',
				'timeout' => '0',
			),
		),
	),
);

?>