<?php
Free::loadClass('AbstractFreeFilter',PC_PATH . 'libs/filter',0);
/**
 * 默认过滤器
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package base
 */
class FreeDefaultFilter extends AbstractFreeFilter
{
	
	/**
	 * 当前过滤器处理
	 */
	public function handle() 
	{
		if(!get_magic_quotes_gpc()) {
			$_POST = $this->newAddslashes($_POST);
			$_GET = $this->newAddslashes($_GET);
			$_REQUEST = $this->newAddslashes($_REQUEST);
			$_COOKIE = $this->newAddslashes($_COOKIE);
		}
		return false;
	}
	
	private function newAddslashes($string)
	{
		if(!is_array($string)) return addslashes($string);
		foreach($string as $key => $val) $string[$key] = $this->newAddslashes($val);
		return $string;
	}
}
?>