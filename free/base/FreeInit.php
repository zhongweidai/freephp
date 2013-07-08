<?php
/**
 * 框架初始化
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package base
 */
class FreeInit extends FreeBase{
	
	public function __construct()
	{
		$this->systemInit();
		$this->resourceInit();
		$this->gzipInit();
		$this->sessionInit();
	}
	/**
	* 系统变量预定义
	+----------------------------------------------------------
	* @access public
	+----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	private function systemInit()
	{

		//缓存文件夹地址
		define('CACHE_PATH', FREE_PATH.'caches'.DIRECTORY_SEPARATOR);
		//主机协议
		define('SITE_PROTOCOL', isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://');
		//当前访问的主机名
		define('SITE_URL', (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''));//系统开始时间
		define('SYS_START_TIME', microtime());
		Free::loadConfig('system','errorlog') ? set_error_handler('myErrorHandler') : error_reporting(E_ERROR | E_WARNING | E_PARSE);
		//设置本地时差
		function_exists('date_default_timezone_set') && date_default_timezone_set(Free::loadConfig('system','timezone'));
		//输出页面字符集
		header('Content-type: text/html; charset='.Free::loadConfig('system','charset'));

		define('SYS_TIME', time());
		define('DEFAULT_PROVINCE', Free::loadConfig('system','default_province'));
		define('DEFAULT_CITY', Free::loadConfig('system','default_city'));
		//第三方插件目录
		define('EXTENTION_PATH', FREE_PATH.'extention'.DIRECTORY_SEPARATOR);
	}
	/**
	* 资源预定义
	+----------------------------------------------------------
	* @access public
	+----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	public static function resourceInit()
	{
		//定义网站根路径
		define('WEB_PATH',Free::loadConfig('system','web_path'));
		//图片上传路径
		define('UPLOAD_PATH',Free::loadConfig('system','upload_url'));
		define('UPLOAD_PATH_',Free::loadConfig('system','upload_path'));
	}
	/**
	* session初始化
	+----------------------------------------------------------
	* @access public
	+----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	private function sessionInit(){
		$this->getComponent('session');
	}
	/**
	* gzip配置
	+----------------------------------------------------------
	* @access public
	+----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	private function gzipInit()
	{
		if(Free::loadConfig('system','gzip') && function_exists('ob_gzhandler')) {
			ob_start('ob_gzhandler');
		} else {
			ob_start();
		}
	}
	

}

function myErrorHandler($errno, $errstr, $errfile, $errline)
{
	if ($errno == 8)
	{
		return '';
	}
	$errfile = str_replace(FREE_PATH, '', $errfile);
	if (Free::loadConfig('system', 'errorlog')) 
	{
		error_log(date('m-d H:i:s', SYS_TIME) . ' | ' . $errno . ' | ' . str_pad($errstr, 30) . ' | ' . $errfile . ' | ' . $errline . "\r\n", 3, CACHE_PATH . 'error_log.php');
	} else {
		$str = '<div style="font-size:12px;text-align:left; border-bottom:1px solid #9cc9e0; border-right:1px solid #9cc9e0;padding:1px 4px;color:#000000;font-family:Arial, Helvetica,sans-serif;"><span>errorno:' . $errno . ',str:' . $errstr . ',file:<font color="blue">' . $errfile . '</font>,line' . $errline . '<br /><a href="http://faq.phpcms.cn/?type=file&errno=' . $errno . '&errstr=' . urlencode($errstr) . '&errfile=' . urlencode($errfile) . '&errline=' . $errline . '" target="_blank" style="color:red">Need Help?</a></span></div>';
		echo $str;
	}
}

?>