<?php
Free::loadClass('IFreeSecurityToken',PC_PATH . 'libs/token',0);
/**
 * token令牌安全类
 * 
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package base
 */
class FreeSecurityToken extends FreeBase implements IFreeSecurityToken {
	
	private $key = 'TokenContainer';
	/**
	 * url token
	 *
	 * @var string
	 */
	protected $token = null;

	public function saveToken($token_name = '') 
	{	
		if($this->isRegistered($token_name))
		{
			return $_SESSION[$this->key][$token_name];
		}

		//$token_name = $this->getTokenName($token_name);
		$_token = FreeSecurity::generateGUID();
		$_SESSION[$this->key][$token_name] = $_token;
		return $_token;
	}


	public function validateToken($token, $token_name = '') {
		//$token_name = $this->getTokenName($token_name);
		$_token = $this->getToken($token_name);
		return $_token && $_token === $token;
	}

	public function deleteToken($token_name) {
		$token_name = $this->getTokenName($token_name);
		if(isset($_SESSION[$this->key][$token_name]))
		{
			unset($_SESSION[$this->key][$token_name]);
			return true;
		}else{
			return false;
		}
	}

	public function getToken($token_name) {
		$token_name = $this->getTokenName($token_name);
		return isset($_SESSION[$this->key][$token_name]) ? $_SESSION[$this->key][$token_name] : false;
	}

	/**
	 * token名称处理
	 * 
	 * @param string $token_name
	 * @return string
	 */
	protected function getTokenName($token_name) {
		return substr(md5('_token' . $token_name . '_csrf'), -16);
	}
	
	protected function isRegistered(&$token_name)
	{
		$token_name = $this->getTokenName($token_name);
		return isset($_SESSION[$this->key][$token_name]) && !empty($_SESSION[$this->key][$token_name]);
	}
}

?>