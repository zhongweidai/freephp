<?php
/**
 * 基础产品预处理类
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package src
 */
abstract class AbstractFreeBeforeDo extends FreeBase
{
	/**
	* 所有的前置处理都要继承
	* @param $app 当前控制器类
	 */
	abstract public function handle(& $app);
	
}
?>