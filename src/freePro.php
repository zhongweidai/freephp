<?php
include FREE_PATH.'/free/free.php';
/**
 * 产品装载基础类
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package base
 */
class FreePro {
	/**
	 * 初始化应用程序
	 */
	public static function init($app) 
	{
	    Free::loadFunc('global',FREE_PATH . 'src' . DIRECTORY_SEPARATOR . 'func' . DIRECTORY_SEPARATOR);
		Free::loadClass('FreeProFrontController',FREE_PATH . 'src/library',0);
		Free::loadClass($app . 'FrontController',FREE_PATH . 'src/library',0);
		Free::loadClass('FreeProServer',FREE_PATH . 'src/library',0);
	}
	
	/**
	 * 初始化应用程序
	 */
	public static function run($app='') 
	{
		$app = empty($app) ? 'Web' : $app;
		self::init($app);
		Free::createApp($app);
	}
	
	public static function getApp()
	{
		return Free::getApp();
	}
    
    public static function load($classname, $path = '', $initialize = 1)
    {
        return Free::loadClass($classname, $path , $initialize);
    }
    
    /**
    public static function loadFunc($func, $path = '')
    {
        return Free::loadFunc($func,FREE_PATH . 'src' . DIRECTORY_SEPARATOR . 'func' . DIRECTORY_SEPARATOR);
    } 
    **/
	/**
	 * 加载模块server
	 * @param string $classname 类名
	 */
	 /**
	public static function loadService($classname, $m, $initialize = 1)
	{
		return Free::loadAppClass($classname . 'Server', $m , $initialize); 
	}
	**/
	
	
	/**
	 * 加载数据模型
	 * @param string $classname 类名
	 */
	 /**
	public static function loadModel($classname,$m) 
	{
		$model = Free::loadModel($classname,$m);
		if($model !== false)
		{
			return $model;
		}else{
			static $WXMODEL = array();
			if(!isset($WXMODEL[$m . '-' . $classname]))
			{
				$WXMODEL[$m . '-' . $classname] = new FreeModel($classname);
			}
			return $WXMODEL[$m . '-' . $classname];
		}
	}
	**/
	
	/**
	 * 加载配置文件
	 * @param string $file 配置文件
	 */
	public static function loadConfig($file, $key = '', $default = '', $reload = false) 
	{
		return Free::loadconfig($file, $key, $default, $reload);
	}
}
?>