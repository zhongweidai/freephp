<?php

define('IN_FREE', true);

//框架路径
define('PC_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR);

/**
 * 框架装载基础类
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package base
 */
class Free {
	private static $configs = array();
	private static $funcs = array();
	private static $classes = array();
	private static $runtime_path = '';
	/**
	 * 初始化应用程序
	 */
	public static function init() {
		!defined('FREE_DEBUG') && define('FREE_DEBUG', false);
		!defined('FREE_RUNTIME') && define('FREE_RUNTIME',true);
		if(FREE_DEBUG)
		{
			Free::loadClass('FreeDebug',PC_PATH . 'libs/log',0);
		}
		if(FREE_RUNTIME)
		{
			self::$runtime_path = FREE_PATH . 'caches/~runtime.php';
			if(!file_exists(self::$runtime_path))
			{
				self::cacheRun();
			}
			include self::$runtime_path;
		}else{
			self::loadSysFunc('global');
			self::autoLoadClass(PC_PATH . 'base');
			self::autoLoadClass(PC_PATH . 'model');
			self::autoLoadClass(PC_PATH . 'libs/utility');
		}
		new FreeInit();
	}
	
	/**
	 * 初始化应用程序
	 */
	public static function createApp($app = 'Web')
	{
		define('APP', $app);
		//产品版本
		$app_config = self::loadConfig('application',APP);
		!defined('PLATFORM_VERSION') && define('PLATFORM_VERSION' , isset($app_config['version'])  ? $app_config['version']  : 'base');
		
		//js 路径
		define('JS_PATH', $app_config['js_path']);
		//css 路径
		define('CSS_PATH', $app_config['css_path']);
		//img 路径
        define('IMG_PATH', $app_config['img_path']);
		
		self::loadSysClass('FreeApplication')->run();
	}
	
	public static function getApp()
	{
		return self::loadSysClass('FreeApplication')->getController();
	}
	
	/**
	 * 加载系统类方法
	 * @param string $classname 类名
	 * @param string $path 扩展地址
	 * @param intger $initialize 是否初始化
	 */
	public static function loadSysClass($classname, $initialize = 1) 
	{
		$classname = ucfirst($classname);
		$path = PC_PATH . 'base' . DIRECTORY_SEPARATOR;
		$key = md5($path.$classname);
		if (isset(self::$classes[$key])) {
			if (!empty(self::$classes[$key])) {
				return self::$classes[$key];
			} else {
				return true;
			}
		}
		$name = $classname;
		if ($initialize) {
			self::$classes[$key] = new $name;
		} else {
			self::$classes[$key] = true;
		}
		return self::$classes[$key];
		//return self::loadClass($classname, $path, $initialize);
	}
	
	/**
	 * 加载应用类方法
	 * @param string $classname 类名
	 * @param string $m 模块
	 * @param intger $initialize 是否初始化
	 */
	public static function loadAppClass($classname, $m, $initialize = 1) 
	{
		if (empty($m)) return false;
		//平台版本
		$classname = ucfirst($classname);
		$base_filepath = FREE_PATH .'src'.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.'base'.DIRECTORY_SEPARATOR . $m . DIRECTORY_SEPARATOR . 'servers';
		$base_classname = $classname . 'Base';
		if (file_exists($base_filepath  . DIRECTORY_SEPARATOR . $base_classname . '.php')) {
			
			if(PLATFORM_VERSION !== 'base')
			{
				$filepath = FREE_PATH.'src'.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.PLATFORM_VERSION.DIRECTORY_SEPARATOR.$m.DIRECTORY_SEPARATOR . 'servers';
				if(file_exists($filepath  . DIRECTORY_SEPARATOR . $classname . '.php'))
				{
					self::loadClass($base_classname, $base_filepath,0);
					return self::loadClass($classname, $filepath,$initialize);
				}
			}
			return self::loadClass($base_classname, $base_filepath,$initialize);
		}
		return false;
	}
	
	/**
	 * 加载数据模型
	 * @param string $classname 类名
	 */
	public static function loadModel($classname,$m) 
	{
		if (empty($m)) return false;
		$array = explode('_',$classname); 
		$OriClassName = $classname; 
		$classname = '';
		foreach($array as $cn)
		{
			$classname .= ucfirst($cn);
		}
		//平台版本
		$base_filepath = FREE_PATH .'src'.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.'base'.DIRECTORY_SEPARATOR . $m . DIRECTORY_SEPARATOR . 'model';
		$base_classname = $classname . 'BaseModel';
		if (file_exists($base_filepath  . DIRECTORY_SEPARATOR . $base_classname . '.php')) {
			
			if(PLATFORM_VERSION !== 'base')
			{
				$filepath = FREE_PATH.'src'.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.PLATFORM_VERSION.DIRECTORY_SEPARATOR.$m.DIRECTORY_SEPARATOR . 'model';
				if(file_exists($filepath  . DIRECTORY_SEPARATOR . $classname . 'Model.php'))
				{
					self::loadClass($base_classname, $base_filepath,0);
					return self::loadClass($classname . 'Model', $filepath);
				}
			}
			return self::loadClass($base_classname, $base_filepath);
		}
		return false;
	}
	/**
	 * 加载类文件函数
	 * @param string $classname 类名
	 * @param string $path 扩展地址
	 * @param intger $initialize 是否初始化
	 */
	public static function loadClass($classname, $path = '', $initialize = 1) 
	{
		$key = md5($path.$classname);	
		if (isset(self::$classes[$key])) {
			if (!empty(self::$classes[$key])) {
				return self::$classes[$key];
			} else {
				return true;
			}
		}
		if (file_exists($path.DIRECTORY_SEPARATOR.$classname.'.php')) {
			include $path.DIRECTORY_SEPARATOR.$classname.'.php';
			$name = $classname;
			if ($initialize) {
				self::$classes[$key] = new $name;
			} else {
				self::$classes[$key] = true;
			}
			return self::$classes[$key];
		} else {
			return false;
		}
	}
	/**
	 * 加载系统的工具库
	 * @param string $path 路径
	 */
	public static function autoLoadClass($path = '') {
		if (empty($path)) return;
		$path .= DIRECTORY_SEPARATOR.'*.php';
		$auto_class = glob($path);
		if(!empty($auto_class) && is_array($auto_class)) 
		{
			foreach($auto_class as $class_path) 
			{
				include $class_path;
			}
		}
	}
	/**
	 * 加载系统的函数库
	 * @param string $func 函数库名
	 */
	public static function loadSysFunc($func) {
		return self::loadFunc($func);
	}
	/**
	 * 加载函数库
	 * @param string $func 函数库名
	 * @param string $path 地址
	 */
	public static function loadFunc($func, $path = '') {
		if (empty($path)) $path = PC_PATH . 'func';
		$path .= DIRECTORY_SEPARATOR.$func.'.php';
		$key = md5($path);
		if (isset(self::$funcs[$key])) return true;
		if (file_exists($path)) {
			include $path;
		} else {
			self::$funcs[$key] = false;
			return false;
		}
		self::$funcs[$key] = true;
		return true;
	}
	/**
	 * 加载配置文件
	 * @param string $file 配置文件
	 * @param string $key  要获取的配置荐
	 * @param string $default  默认配置。当获取配置项目失败时该值发生作用。
	 * @param boolean $reload 强制重新加载。
	 */
	public static function loadConfig($file, $key = '', $default = '', $reload = false) {
		if (!$reload && isset(self::$configs[$file])) {
			if (empty($key)) {
				return self::$configs[$file];
			} elseif (isset(self::$configs[$file][$key])) {
				return self::$configs[$file][$key];
			} else {
				return $default;
			}
		}
		$path = FREE_PATH.'configs'.DIRECTORY_SEPARATOR.$file.'.php';
		if (file_exists($path)) {
			self::$configs[$file] = include $path;
		}
		if (empty($key)) {
			return self::$configs[$file];
		} elseif (isset(self::$configs[$file][$key])) {
			return self::$configs[$file][$key];
		} else {
			return $default;
		}
	}
	public static function setConfig($configs)
	{
		self::$configs = $configs;
	}
	
	public static function cacheRun()
	{
		$cache_array = array('base','model','func','libs/utility');
		$content = '';
		foreach($cache_array as $v)
		{
			$path = PC_PATH . $v . DIRECTORY_SEPARATOR.'*.php';
			$cache_path = glob($path);
			if(!empty($cache_path) && is_array($cache_path))
			{
				foreach($cache_path as $path)
				{
					$content .= free_compile($path);
				}
			}
		}
		$configs = array();
		$cache_array = array('application','cache','component','database','system');
		foreach($cache_array as $v)
		{
			$path = FREE_PATH.'configs'.DIRECTORY_SEPARATOR.$v.'.php';
			if (file_exists($path)) {
				$configs[$v] = include $path;
			}
		}
		$content .= 'Free::setConfig('  . var_export($configs, true) . ');';
		return file_put_contents(self::$runtime_path,free_strip_whitespace('<?php '.$content));
	}
	
}

Free::init();

/**
 * 框架基类
* @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
* @copyright ©2006-2103
* @version $$Id$$
* @package base
*/
class FreeBase {

	public $_filter = '';

	/**
	 *	获取组件
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $name 组件名
	 * @param string $option 参数
	 * @param int $type 是否重新初始化
	 +----------------------------------------------------------
	 * @return class
	 +----------------------------------------------------------
	 */
	final public function getComponent($name,$option = array(),$is_new = 0)
	{
		$component = Free::loadSysClass('FreeComponent')->get($name,$option,$is_new);
		if($component === false)
		{
			throw new FreeException($name . ' that is a Component ','100');
		}else{
			return $component;
		}
	}
	/**
	 *	注册组件
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $path 组件配置
	 +----------------------------------------------------------
	 * @return class
	 +----------------------------------------------------------
	 */
	final public function registerComponent($path = array())
	{
		$component = Free::loadSysClass('FreeComponent')->register($path);
	}
	/**
	 *	获取过滤器
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @return class
	 +----------------------------------------------------------
	 */
	final public function getFilter()
	{
		empty($this->_filter) && $this->_filter = Free::loadSysClass('FreeFilter');
		return $this->_filter;
	}
	/**
	 *	注册过滤器
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @return class
	 +----------------------------------------------------------
	 */
	final public function registerFilter($filter)
	{
		$this->getFilter()->register($filter);
	}
	/**
	 *	 移除过滤器
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @return class
	 +----------------------------------------------------------
	 */
	final public function removeFilter($filter_name)
	{
		$this->getFilter()->remove($filter_name);
	}
	/**
	 *	获取request请求类
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @return FreeHttpRequest
	 +----------------------------------------------------------
	 */
	public function getRequest()
	{
		return $this->getComponent('request');
	}
	/**
	 *	获取response响应
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @return FreeHttpResponse
	 +----------------------------------------------------------
	 */
	public function getResponse()
	{
		return $this->getComponent('response');
	}

}



// 去除代码中的空白和注释
function free_strip_whitespace($content) {
	$stripStr = '';
	//分析php源码
	$tokens = token_get_all($content);
	$last_space = false;
	for ($i = 0, $j = count($tokens); $i < $j; $i++) {
		if (is_string($tokens[$i])) {
			$last_space = false;
			$stripStr .= $tokens[$i];
		} else {
			switch ($tokens[$i][0]) {
				//过滤各种PHP注释
				case T_COMMENT:
				case T_DOC_COMMENT:
					break;
					//过滤空格
				case T_WHITESPACE:
					if (!$last_space) {
						$stripStr .= ' ';
						$last_space = true;
					}
					break;
				case T_START_HEREDOC:
					$stripStr .= "<<<FREE\n";
					break;
				case T_END_HEREDOC:
					$stripStr .= "FREE;\n";
					for($k = $i+1; $k < $j; $k++) {
						if(is_string($tokens[$k]) && $tokens[$k] == ';') {
							$i = $k;
							break;
						} else if($tokens[$k][0] == T_CLOSE_TAG) {
							break;
						}
					}
					break;
				default:
					$last_space = false;
					$stripStr .= $tokens[$i][1];
			}
		}
	}
	return $stripStr;
}

// 循环创建目录
function free_mkdir($dir, $mode = 0777) {
	if (is_dir($dir) || @mkdir($dir, $mode))
		return true;
	if (!free_mkdir(dirname($dir), $mode))
		return false;
	return @mkdir($dir, $mode);
}

//[RUNTIME]
// 编译文件
function free_compile($filename) {
	$content = file_get_contents($filename);
	// 替换预编译指令
	$content = preg_replace('/\/\/\[RUNTIME\](.*?)\/\/\[\/RUNTIME\]/s', '', $content);
	$content = substr(trim($content), 5);
	if ('?>' == substr($content, -2))
		$content = substr($content, 0, -2);
	return $content;
}

// 根据数组生成常量定义
function free_array_define($array,$check=true) {
	$content = "\n";
	foreach ($array as $key => $val) {
		$key = strtoupper($key);
		if($check)   $content .= 'defined(\'' . $key . '\') or ';
		if (is_int($val) || is_float($val)) {
			$content .= "define('" . $key . "'," . $val . ');';
		} elseif (is_bool($val)) {
			$val = ($val) ? 'true' : 'false';
			$content .= "define('" . $key . "'," . $val . ');';
		} elseif (is_string($val)) {
			$content .= "define('" . $key . "','" . addslashes($val) . "');";
		}
		$content    .= "\n";
	}
	return $content;
}


