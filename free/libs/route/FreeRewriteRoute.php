<?php
Free::loadClass('AbstractFreeRoute',PC_PATH . 'libs/route',0);
/**
 * 路由组件类
 * @author 2012-11-21
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package base
 */
class FreeRewriteRoute extends AbstractFreeRoute{
    public $is_rewrite = 1;

	//路由配置
	private $route_config = '';
	
	public function __construct()
    {
        $this->getRewriteStatus();
        $this->setRewrite();
	}

	/**
	 * 获取模型
	 */
	public function route_m()
    {
		$m = isset($_GET['m']) && !empty($_GET['m']) ? $_GET['m'] : (isset($_POST['m']) && !empty($_POST['m']) ? $_POST['m'] : '');
		if (empty($m))
        {
			return $this->route_config['m'];
		}
        else
        {
			return $m;
		}
	}

	/**
	 * 获取控制器
	 */
	public function route_c()
    {
		$c = isset($_GET['c']) && !empty($_GET['c']) ? $_GET['c'] : (isset($_POST['c']) && !empty($_POST['c']) ? $_POST['c'] : '');
		if (empty($c))
        {
			return $this->route_config['c'];
		}
        else
        {
			return $c;
		}
	}

	/**
	 * 获取事件
	 */
	public function route_a()
    {
		$a = isset($_GET['a']) && !empty($_GET['a']) ? $_GET['a'] : (isset($_POST['a']) && !empty($_POST['a']) ? $_POST['a'] : '');
		if (empty($a))
        {
			return $this->route_config['a'];
		}
        else
        {
			return $a;
		}
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindRouter::assemble()
	 */
	public function assemble($action, $args = '',$script='')
    {
		if(!empty($action))
		{
			$r = explode('/',$action);
		}
		$route = array();
		isset($r[0]) && !empty($r[0]) && $route['m'] = $r[0] ;
		isset($r[1]) && !empty($r[1]) && $route['c'] = $r[1] ;
		isset($r[2]) && !empty($r[2]) && $route['a'] = $r[2] ;
        $app_list = array('web', 'wap', 'admin');
        if ($this->is_rewrite)
        {
            $app = $script && in_array(strtolower($script), $app_list) ? strtolower($script) : strtolower(APP);
            $script = dirname($this->getRequest()->getServer('SCRIPT_NAME'));
            $new_args = array();
            foreach($args as $k => $v)
            {
                $new_args[] = $k;
                $new_args[] = $v;
            }
            return implode('/', array_merge(array($script, $app), $route, $new_args));
        }
        else
        {
            $script == '' && $script = $this->getRequest()->getScript();
            $script == 'web' && $script = 'index.php';
            in_array(strtolower($script), $app_list) && $script .= '.php';
		    return $script . '?' .  (is_array($args) ? self::argsToUrl(array_merge($route, $args)) : (self::argsToUrl($route).$args));
        }
    }
	
	public static function argsToUrl($args, $encode = true, $separator = '&=')
    {
		if (strlen($separator) !== 2) return;
		$_tmp = '';
		foreach ((array) $args as $key => $value)
        {
			$value = $encode ? rawurlencode($value) : $value;
			if (is_int($key))
            {
				$value && $_tmp .= $value . $separator[0];
				continue;
			}
			$key = ($encode ? rawurlencode($key) : $key);
			$_tmp .= $key . $separator[1] . $value . $separator[0];
		}
		return trim($_tmp, $separator[0]);
	}

    /**
     * 是否开启重写
     * @return void
     */
    public function getRewriteStatus()
    {
        $config = Free::loadConfig('system');
        isset($config['is_rewrite']) && $this->is_rewrite = $config['is_rewrite'];
    }

    /**
     * 路由重写
     * @return bool
     */
    public function setRewrite()
    {
        $path_info = preg_replace('/^' . preg_quote(dirname($this->getRequest()->getServer('SCRIPT_NAME')), '/') . '/i', '', $this->getRequest()->getRequestUri());
        $base_script = $this->getRequest()->getScript();
        if (strstr($base_script, '.php'))
		{
			$path_info = str_replace($base_script . '/', '', $path_info);
		}
        if (($char_position = stripos($path_info, '?')) !== false)
        {
            $path_info = substr($path_info, 0, $char_position);
        }
        if (($char_position = stripos($path_info, '&')) !== false)
        {
            $path_info = substr($path_info, 0, $char_position);
        }
        
        if (!empty($path_info))
        {
            $args = array();
            $path_info = trim($path_info, '/');
            $args = explode('/', $path_info);
            $args[0] && array_shift($args);
            $args[0] && $_GET['m'] = array_shift($args);
            $args[0] && $_GET['c'] = array_shift($args);
            $args[0] && $_GET['a'] = array_shift($args);
            if (count($args) > 1)
            {
                for ($i = 0; $i < count($args); $i++)
                {
                    if(!isset($args[$i+1])) continue;
                    $k = $args[$i];
                    $v = (string)$args[++$i];
                    if (! strstr($v, '%'))
                    {
                        $_GET[$k] = $v;
                    }
                    else
                    {
                        $_GET[$k] = urldecode($v);
                    }
                }
            }
        }

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

        foreach ($_GET AS $key => $val)
        {
            if (strstr($key, '/'))
            {
                 unset($_GET[$key]);
            }
        }

        return true;
    }
}
?>