<?php
/**
 * 缓存抽象类
 * 
 * 该基类继承了框架的基类FreeBase,用以提供实现组件的一些特性.同时该类作为数据库策略的基类定义了通用的对方访问接口,及子类需要实现的抽象接口.
 *
 * @author
 * @copyright
 * @license
 * @version $Id: AbstractFreeCache.php 1 2012-07-13 11:00:00Z $ 
 * @package cache
 */
abstract class AbstractFreeCache extends Freebase{
	/**
	 * 缓存链接
	 */
	public $_conn = null;
	/**
	 * 缓存配置信息
	 */
	public $_config = null;
	/**
	 * 获取缓存
	 * @param unknown_type $name	--缓存key名称
	 * @param unknown_type $module	--缓存模块名
	 */
	abstract public function get($name, $module);
	/**
	 * 设置缓存
	 * @param unknown_type $name	--缓存key名称
	 * @param unknown_type $data	--缓存的数据
	 * @param unknown_type $module	--缓存模块名
     * @param   int        $expire     存活时间 
	 */
	abstract public function set($name, $data, $module,$expire);
	/**
	 * 删除缓存
	 * @param unknown_type $name	--缓存key名称
	 * @param unknown_type $module	--缓存模块名
	 */
	abstract public function delete($name, $module);
}
?>