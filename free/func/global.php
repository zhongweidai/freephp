<?php
/**
 * 字符截取 支持UTF8/GBK
 * @param $string
 * @param $length
 * @param $dot
 */
function str_cut($string, $length, $dot = true,$start=0,$charset='utf-8') {
	return FreeString::substr($string, $start, $length, $charset, $dot = false);
}

/**
 *  读取配置
 **/
function C($file, $key = '', $default = '', $reload = false)
{
    return Free::loadConfig($file, $key, $default, $reload);
}
/**
 * 模型加载
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package all
 */
function M($classname='',$m='')
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
/**
 * 产品server加载
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package all
 */
function S($classname, $m, $initialize = 1)
{	
	return Free::loadAppClass($classname . 'Server', $m , $initialize); 
}

/**
 * 产品语言加载
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package all
 */
function L($name)
{
	return Free::getApp()->lang($name);
}

/**
 * 链接处理
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package all
 */
function U($action, $args = array(), $anchor = '', $rediect = '',$script='')
{
	/* @var $router AbstractWindRouter */
	$router = Free::getApp()->getComponent('route');
	if(is_array($rediect))
	{
		foreach($rediect as $key => $r)
		{
			$args[$key] = FreeSecurity::encrypt($r);
		}
	}
	$url = $router->assemble($action, $args,$script);
	$url .= $anchor ? '#' . $anchor : '';
	return $url;
}
/**
 * 项目扩展加载
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package all
 */
function E($et)
{
	return Free::loadClass($et, FREE_PATH . 'extension' . DIRECTORY_SEPARATOR);
}

/**
 * 5.3 以上版本兼容 function lcfirst
*/
if ( false === function_exists('lcfirst') ){
    function lcfirst( $str ) 
    { return (string)(strtolower(substr($str,0,1)).substr($str,1));} 
}

// 浏览器友好的变量输出
function dump($var, $echo=true, $label=null, $strict=true) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = "<pre>" . $label . htmlspecialchars($output, ENT_QUOTES) . "</pre>";
        } else {
            $output = $label . " : " . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    }else
        return $output;
}

/**
 * 加载模板标签缓存
 * @param string $name 缓存名
 * @param integer $times 缓存时间
 */
function getTplCache($name, $times = 0) {
    $path = 'tpl_data';
    if(!($cache_data = Free::getApp()->getComponent('cache')->get($name, $path)))
    {
    	return false;
    }
    if (SYS_TIME - $cache_data['ctime'] >= $times) {
        return false;
    } else {
        return $cache_data['data'];
    }
}

function setTplCache($name,$data)
{
	$path = 'tpl_data';
	$cache_data['ctime'] = SYS_TIME;
	$cache_data['data'] = $data;
	return Free::getApp()->getComponent('cache')->set($name,$cache_data,$path,24*3600);
}
