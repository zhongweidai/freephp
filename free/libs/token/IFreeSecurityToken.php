<?php
/**
 * token 组件安全类接口定义
 * 
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package base
 */
interface IFreeSecurityToken {

	/**
	 * 获取当前tokenName保存的值,如果获取的值为空则代表token不存在,或者已经失效
	 *
	 * @param string $tokenName
	 */
	public function getToken($tokenName);

	/**
	 * 根据TokenName删除token值
	 *
	 * @param string $tokenName
	 */
	public function deleteToken($tokenName);

	/**
	 * 保存token
	 * 
	 * @param string $tokenName token名称,默认名称为<i>_tokenAppName</i>
	 * @return string 返回token值
	 */
	public function saveToken($tokenName = '');

	/**
	 * 验证token的有效性
	 * 
	 * 验证token的有效性.<code>
	 * 当token有效时则返回true,同时删除token.
	 * 当coken无效时则返回false.
	 * <code>
	 * @param string $token
	 * @param string $tokenName token名称,默认名称为<i>_tokenAppName</i>
	 */
	public function validateToken($token, $tokenName = '');

}

?>