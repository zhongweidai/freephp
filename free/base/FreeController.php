<?php

/**
 * 前端控制器基类
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package base
 */
 
class FreeController extends FreeBase{
	public $_module;
	public $_controller;
	public $_action;
	private $_view;
	protected $_lang = array();
	private $_out_put = array();//模板输出变量
	public $_list_rows = 20;//控制每页显示
	protected $_is_csrf = 0;
	
	
	public function __construct()
	{
		list($this->_module, $this->_controller,$this->_action)= Free::loadSysClass('FreeApplication')->get();
		$this->loadLanguage('system,'.$this->_module);
		Free::loadSysClass('FreeApplication')->setController($this);
	}
	

	/**
	*模板变量分配
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
	 * @param string $name 变量名
     * @param string $value 值
	+----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	final public function assign($name,$value='')
	{
		if(is_array($name)) {
			$this->_out_put   =  array_merge($this->_out_put,$name);
		}elseif(is_object($name)){
			foreach($name as $key =>$val)
			$this->_out_put[$key] = $val;
		}else {
			$this->_out_put[$name] = $value;
		}
	}
	/**
	*	加载语言包
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
	 * @param string $modules 模块名  支持多模块同时加载，模块名用,隔开
	+----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	public function loadLanguage($modules)
	{
		static $LANG_MODULES = array();
		$LANG = array();
		static $lang = '';
		$lang = FreeCookie::get('free_lang');
		empty($lang) && $lang = Free::loadConfig('system', 'lang');
		if (!empty($modules)) 
		{
			$modules = explode(',', $modules);
			foreach ($modules AS $m) 
			{
				if (!isset($LANG_MODULES[$m])&&file_exists(FREE_PATH . 'src' . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . $m . '.lang.php'))
				{
					require FREE_PATH . 'src' . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . $m . '.lang.php';
				}
			}
		}
		is_array($LANG) && $this->_lang = array_merge($this->_lang,$LANG);
	}
	/**
	*	加载语言
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
	 * @param string $name 语言名
	+----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	public function lang($name)
	{
		return isset($this->_lang[$name]) ? $this->_lang[$name] : $this->_lang['no_language'] . "[$name]";
	}
	/**
	* 模板调用
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
	 * @param string $name 语言名
	+----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	public function template($__m__='',$__filename__='',$__style__='default',$__app__=APP)
	{
		if(!$this->_view)
		{
			$this->_view = Free::loadSysClass('FreeView');
		}
		//$this->assign('LANG',$this->_lang);
		//$this->assign('csrf_token',$this->getComponent('token')->saveToken('csrf_token'));
		if (!$__style__)
		{
			$__style__ = 'default';
		}
		empty($__m__) && $__m__ = $this->_module;
		empty($__filename__) && $__filename__ = $this->_controller . '_' . $this->_action;
		extract($this->_out_put, EXTR_SKIP);
		include $this->_view->templateResolve($__m__,$__filename__,$__style__,$__app__);
		
	}
	

	
	/**
	*	app前处理
     +----------------------------------------------------------
     * @access public
	+----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	final public function doBefore()
	{
		$base_filepath = FREE_PATH .'src'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'base';
		$base_classname = APP . 'BeforeBaseDo';
		if (file_exists($base_filepath  . DIRECTORY_SEPARATOR . $base_classname . '.php')) 
        {
			if(PLATFORM_VERSION !== 'base')
			{
				$filepath = FREE_PATH.'src'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.PLATFORM_VERSION.DIRECTORY_SEPARATOR;
				$classname = APP . 'BeforeDo';
				if(file_exists($filepath  . DIRECTORY_SEPARATOR . $classname . '.php'))
				{
					Free::loadClass($base_classname, $base_filepath,0);
					return Free::loadClass($classname, $filepath)->handle($this);
				}
			}
			return Free::loadClass($base_classname, $base_filepath)->handle($this);
		}else{
			return false;
		}
		
	}
		
	/**
	*	控制后处理，建议调用产品钩子
	+----------------------------------------------------------
     * @access public
	+----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	public function doAfter()
	{
        
	}
	
	/**
	*	控制转发 不建议重写
	+----------------------------------------------------------
     * @access public
	+----------------------------------------------------------
	* @param string $m 
	* @param string $c
	* @param string $a 
	+----------------------------------------------------------
     * @return viod
     +----------------------------------------------------------
     */
	final public function forward($m,$c,$a)
	{
		if($m !== $this->_module || $c !== $this->_controller || $a !== $this->_action)
		{
			$app_class = Free::loadSysClass('FreeApplication')->run($m,$c,$a);
			die;
		}
	}
	/**
	* 控制跳转
	**/
	
	public function rediect()
	{	
		
	}
	
	public function pageConfig()
	{
	}

	/**
	* 程序执行时间
	*
	* @return	int	单位ms
	*/
	function executeTime($start = '') {
		$stime = $start ? explode(' ', $start) : explode(' ', SYS_START_TIME);
		$etime = explode(' ', microtime());
		return number_format(($etime [1] + $etime [0] - $stime [1] - $stime [0]), 6);
	}
	/**
	* 提示信息
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
	 * @param string $msg 提示信息
	* @param string $url_forward 跳转
	* @param string $status 状态
	* @param string $data 返回数据
	+----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	public function showMessage($msg, $url_forward = '',  $status=1,$data ='', $ms = 1250)
	{
		echo $msg;
		if(!$status) exit;
	}
	/**
	* 验证表单 crsf(hash)
     +----------------------------------------------------------
     * @access public
	+----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	public function checkCsrf()
	{//csrf 验证
		if(Free::loadConfig('system','is_check_csrf') && $this->_is_csrf ===0)
		{
			if(!$this->getComponent('token')->validateToken($this->getRequest()->getPost('__hash__'),'csrf_token'))
			{
				return false;
			}
			$this->getComponent('token')->deleteToken('csrf_token');
			unset($_POST['__hash__']);
			$this->_is_csrf ++;
		}
		return true;
	}
}
?>