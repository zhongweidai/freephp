<?php

/**
 * 组件装载
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package base
 */
 
class FreeComponent {
	protected $_paths = array();
	protected $_classes = array();
	
	public function __construct()
	{
		$this->_init();
	}
	/**
	*	获取组件
	**/
	public function get($name,$option = array(),$is_new = 0)
	{
		if (array_key_exists($name,$this->_paths))
		{
			$path = FREE_PATH . $this->_paths[$name] . '.php';
			$key = md5($path);
			
			if(!isset($this->_classes[$key]) || $is_new === 1)
			{
				if(file_exists($path))
				{
					include $path;
					$class_name = substr(strrchr($this->_paths[$name],'/'),1);
					$this->_classes[$key] = new $class_name($option);
				}else{
					return false;
				}
			}
			return $this->_classes[$key];
		}else{
			return false;
			
		}
	
	}
	/**
	*	注册组件
	**/
	public function register($path)
	{
		is_array($path) && $this->_paths = array_merge($this->_paths,$path);
	}
	
	private function _init()
	{
		$paths = array(
			'route'              =>  'free/libs/route/FreeDefaultRoute',
			'template'           =>  'free/libs/view/FreeTemplate',
			'cache'              =>  'free/libs/cache/FreeFileCache',
			'db'                 =>  'free/libs/db/FreeOracleDb',
			'request'            =>  'free/libs/http/FreeHttpRequest',
			'response'           =>  'free/libs/http/FreeHttpResponse',
			'token'              =>  'free/libs/token/FreeSecurityToken',
			'page'               =>  'free/libs/view/FreePage',
			'mongo_db'           =>  'free/libs/db/FreeMongoDb',
            'log_container'      =>  'free/libs/log/FreeLogContainer',
			//'debug'				 =>	 'free/libs/log/FreeDebug',
		);
		$upaths = Free::loadConfig('component');
		$this->_paths = is_array($upaths) ? array_merge($paths,$upaths) : $paths;
	}
	
}
?>