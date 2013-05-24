<?php
/**
 *  模板解析缓存抽象类
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package base
 */
abstract class AbstractFreeTemplate extends FreeBase{
	protected $_tpl_suffix;
	protected $_complie_suffix;

	public function getTplSuffix()
	{
		return $this->_tpl_suffix;
	}
	
	public function getComplieSuffix()
	{
		return $this->_complie_suffix;
	}
    /**
     * 编译模板
     *
     * @param $module	模块名称
     * @param $template	模板文件名
     * @param $istag	是否为标签模板
     * @return unknown
     */
   abstract public function compile($filename,$path,$complie_path);

}

?>