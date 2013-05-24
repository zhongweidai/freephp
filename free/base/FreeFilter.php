<?php
/**
 * 框架基类
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package base
 */
class FreeFilter extends FreeBase{
	private $_filters = array();
    private $_is_handled = 0;
	
	public function __construct()
	{
		self::_init();
	}
	/**
	 * 拦截链处理
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
	 * @param string $app 当前app
	+----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	public function handle(& $app)
	{
		if (count($this->_filters) < 1) 
		{
			return false;
		}
        $this->_is_handled++;
		$forward = $this->getHandler();
		//$this->_filter = array();
		return $forward;
	}

	/**
	 * 遍历拦截链处理
     +----------------------------------------------------------
     * @access public
	+----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	public function getHandler() {
		//注册当前的过滤器
		$this->registerComponent($this->_filters);
		foreach($this->_filters as $key => $val)
		{//遍历当前过滤器并进行处理
			$forward = $this->getComponent($key)->handle();
            unset($this->_filters[$key]);
			if($forward !== false)
			{
				return $forward;
			}
			
		}
		return false;
	}
	
	/**
	* 注册一个拦截器到拦截链中
	* @access public
     +----------------------------------------------------------
	* @param string $filter 注册过滤器
	+----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	public function register($filter=array())
	{
        is_array($filter) && $this->_filters += $filter; 
	}
	/**
	* 移除一个拦截器
	* @access public
     +----------------------------------------------------------
	* @param string $filter_name 过滤器名
	+----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function remove($filter_name)
    {
        if(isset($this->_filters[$filter_name]))
        {
            unset($this->_filters[$filter_name]);
        }
    }
	
	/**
	* 初始过滤器
	+----------------------------------------------------------
	* @access public
	+----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	private function _init()
	{
		$_filter = array(
			'default_filter'=>'free/libs/filter/FreeDefaultFilter',
		);
		$app_config = Free::loadConfig('application',APP);
		$this->_filters = isset($app_config['filter'])&&is_array($app_config['filter']) ? array_merge($_filter,$app_config['filter']) : $_filter;
	}
}
?>