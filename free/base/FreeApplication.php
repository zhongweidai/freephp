<?php
/**
 * 项目（应用）装载
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package base
 */
final class FreeApplication extends FreeBase {
	private $_module;
	private $_controller;
	private $_action;
	private $controller;
	private $action;
	private $_run_num = 1;
	/**
	 * 构造函数
	 */
	public function __construct() 
	{
		$param = $this->getComponent('route');
		$this->_module = $param->route_m();
		$this->_controller = $param->route_c();
		$this->_action = $param->route_a();
		//$this->init($this->_module,$this->_controller,$this->_action);
	}
		public function __destruct()
	{
		if(FREE_DEBUG)
		{
			echo FreeDebug::debug();
		}
	}

	
	/**
	 * 调用件事
	 */
	public function run($m='',$c='',$a='') 
	{
		$this->_run_num ++;
		if($this->_run_num > 10)
		{
			throw new FreeException('run extends the maximum','0');
		}
		!empty($m) &&  $this->_module = $m;
		!empty($c) && $this->_controller = $c;
		!empty($a) && $this->_action = $a;
		$this->controller = $this->loadController();
		$this->action = $this->_action. 'Action' ;
		if (method_exists($this->controller, $this->action)) 
		{
			if (preg_match('/^[_]/i', $this->action)) 
			{
				throw new FreeException('You are visiting the action is to protect the private action','102');
			} else {
				//call_user_func(array($this->controller, 'init'));
				//处理filter
				$this->doFilter();
				call_user_func(array($this->controller, 'doBefore'));
				call_user_func(array($this->controller, $this->action));
				call_user_func(array($this->controller, 'doAfter'));
			}
		} else {
			throw new FreeException($this->action.'	Action does not exist.','102');
		}
	}
	
	/**
     +----------------------------------------------------------
     * 获取当前app的m a c
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
	public function get()
	{
		return array($this->_module,$this->_controller,$this->_action);
	}
	
	/**
	 * 加载控制器
	 * @param string $filename
	 * @param string $m
	 * @return obj
	 */
	private function loadController() 
	{
		$filename = ucfirst($this->_controller);
		//平台版本
		$app_config = Free::loadConfig('application',APP);
		!defined('PLATFORM_VERSION') && define('PLATFORM_VERSION' , isset($app_config['version'])  ? $app_config['version']  : 'base');
		$classname = $filename . 'BaseController';
		$filepath = FREE_PATH.'src'.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.'base'.DIRECTORY_SEPARATOR.$this->_module.DIRECTORY_SEPARATOR. lcfirst(APP). DIRECTORY_SEPARATOR . $classname.'.php';

        if (file_exists($filepath)) {
			include_once $filepath;
			if(PLATFORM_VERSION !== 'base')
			{
				$filepath = FREE_PATH.'src'.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.PLATFORM_VERSION.DIRECTORY_SEPARATOR.$this->_module.DIRECTORY_SEPARATOR.$app_config['controller-path'] .DIRECTORY_SEPARATOR. $filename.'Controller.php';

				if(file_exists($filepath))
				{
					$classname = $filename . 'Controller';
					include_once $filepath;
				}
			}
			return new $classname();
		} else {
			throw new FreeException($filepath . ' that is a controller.','100');
		}
	}
	/**
     +----------------------------------------------------------
     * 过滤器处理
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
	public function doFilter()
	{
		$forward = $this->getFilter()->handle($this);
		if($forward !== false)
		{
			list($m,$c,$a) = explode('/',$forward );
			$this->controller->forward($m,$c,$a);
		}else{
			return true;
		}
	}
	/**
     +----------------------------------------------------------
     * 获取当前app
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
	public function getController()
	{
		return $this->controller ;
	}
	/**
     +----------------------------------------------------------
     * 设置当前app
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
	public function setController($controller)
	{
		$this->controller = $controller;
	}
	
}
?>