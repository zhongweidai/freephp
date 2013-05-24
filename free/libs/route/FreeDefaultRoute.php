<?php
Free::loadClass('AbstractFreeRoute',PC_PATH . 'libs/route',0);
/**
 * 路由组件类
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package base
 */
class FreeDefaultRoute extends AbstractFreeRoute{

	//路由配置
	private $route_config = '';
	
	public function __construct() {
		$config = Free::loadConfig('application', APP);
		if(isset($config['route']))
		{
			$route_config = explode('/',$config['route']); 
		}
		$this->route_config['m'] = isset($route_config[0]) ?  $route_config[0] : 'index';
		$this->route_config['c'] = isset($route_config[1]) ?  $route_config[1] : 'index';
		$this->route_config['a'] = isset($route_config[2]) ?  $route_config[2] : 'init';
		$page = $this->getRequest()->getGet('page');
		if(isset($page))
		{
			$_GET['page'] = max(intval($page),1);
		}
		return true;
	}

	/**
	 * 获取模型
	 */
	public function route_m() {
		$m = isset($_GET['m']) && !empty($_GET['m']) ? $_GET['m'] : (isset($_POST['m']) && !empty($_POST['m']) ? $_POST['m'] : '');
		if (empty($m)) {
			return $this->route_config['m'];
		} else {
			return $m;
		}
	}

	/**
	 * 获取控制器
	 */
	public function route_c() {
		$c = isset($_GET['c']) && !empty($_GET['c']) ? $_GET['c'] : (isset($_POST['c']) && !empty($_POST['c']) ? $_POST['c'] : '');
		if (empty($c)) {
			return $this->route_config['c'];
		} else {
			return $c;
		}
	}

	/**
	 * 获取事件
	 */
	public function route_a() {
		$a = isset($_GET['a']) && !empty($_GET['a']) ? $_GET['a'] : (isset($_POST['a']) && !empty($_POST['a']) ? $_POST['a'] : '');
		if (empty($a)) {
			return $this->route_config['a'];
		} else {
			return $a;
		}
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindRouter::assemble()
	 */
	public function assemble($action, $args = '',$script='') {
		if(!empty($action))
		{
			$r = explode('/',$action);
		}
		$route = array();
		isset($r[0]) && !empty($r[0]) && $route['m'] = $r[0] ;
		isset($r[1]) && !empty($r[1]) && $route['c'] = $r[1] ;
		isset($r[2]) && !empty($r[2]) && $route['a'] = $r[2] ;
		$script == '' && $script = $this->getRequest()->getScript();
		return $script . '?' .  (is_array($args) ? self::argsToUrl(array_merge($route, $args)) : (self::argsToUrl($route).$args));
	}
	
	public static function argsToUrl($args, $encode = true, $separator = '&=') {
		if (strlen($separator) !== 2) return;
		$_tmp = '';
		foreach ((array) $args as $key => $value) {
			$value = $encode ? rawurlencode($value) : $value;
			if (is_int($key)) {
				$value && $_tmp .= $value . $separator[0];
				continue;
			}
			$key = ($encode ? rawurlencode($key) : $key);
			$_tmp .= $key . $separator[1] . $value . $separator[0];
		}
		return trim($_tmp, $separator[0]);
	}
}
?>