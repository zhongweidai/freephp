<?php
/**
 * 文件缓存操作策略实现类
 *
 * <i>FileCache使用:</i><br/>
 * 1、像使用普通类库一样使用该组件:
 * <code>
 *  Free::loadClass('FreeFileCache', PC_PATH . 'libs/cache',0);
 * 	$cache = new FreeFileCache();
 *  $cache->set('test1','hello world');
 *  return $cache->get('test1');
 * </code>
 * 2、采用组件配置的方式，通过组件机制调用
 * 在应用配置的component组件配置块中,配置FileCache(<i>该名字将决定调用的时候使用的组件名字</i>):
 * <pre>
 *   'file' => 'free/libs/cache/FreeFileCache',
 * </pre>
 * 在应用中可以通过如下方式获得FreeFileCache对象:
 * <code>
 *  $cache = $this->getComponent('file');	//file的名字来自于组件配置中的名字
 *  $cache->set('test1','hello world');
 *  return $cache->get('test1');
 *  
 * 上面方法设置获取当前模块的缓存，如需设置获取其他模块的缓存，操作如下
 *  $cache->set('test1','hello world','sns');  --其中第三参数为模块名称，方便获取
 *  return $cache->get('test1','sns'); 
 * </code>
 *
 * @author
 * @copyright
 * @license
 * @version $Id: MysqlDb.php 1 2012-07-13 11:00:00Z $ 
 * @package cache
 */
Free::loadClass('AbstractFreeCache',PC_PATH . 'libs/cache',0);
class FreeFileCache extends AbstractFreeCache{
	
	/*缓存默认配置*/
	protected $_setting = array(
								'suf' => '.cache.php',	/*缓存文件后缀*/
								'type' => 'array',		/*缓存格式：array数组，serialize序列化，null字符串*/
							);
	
	/*缓存路径*/
	protected $filepath = '';

	/**
	 * 构造函数
	 * @param	array	$setting	缓存配置
	 * @return  void
	 */
	public function __construct($setting = '') {
		$this->getSetting($setting);
		//$this->_config = $setting;
	}
	
	/**
	 * 写入缓存
	 * @param	string	$name		缓存名称
	 * @param	mixed	$data		缓存数据
	 * @param	string	$module		所属模型
	 */

	public function set($name, $data, $module,$expire) {
		if(!is_string($name))
		{
			$this->showErrorMessage('the name of FileCache must be string.','002');
		}
		$this->getSetting($setting);
		$type = 'data';
		if(empty($module)) 
		{
			throw new FreeException(' 3th parameter ','110');
		}
		$filepath = CACHE_PATH.'caches_'.$module.'/caches_'.$type.'/';
		$filename = $name.$this->_setting['suf'];
	    if(!is_dir($filepath)) {
			mkdir($filepath, 0777, true);
	    }
	    if($this->_setting['type'] == 'array') {
	    	$data = "<?php\nreturn ".var_export($data, true).";\n?>";
	    } elseif($this->_setting['type'] == 'serialize') {
	    	$data = serialize($data);
	    }
	    //是否开启互斥锁
		if(Free::loadConfig('system', 'lock_ex')) {
			$file_size = file_put_contents($filepath.$filename, $data, LOCK_EX);
		} else {
			$file_size = file_put_contents($filepath.$filename, $data);
		}
	    
	    return $file_size ? $file_size : 'false';
	}
	
	/**
	 * 获取缓存
	 * @param	string	$name		缓存名称
	 * @param	string	$module		所属模型
	 * @return  mixed	$data		缓存数据
	 */
	public function get($name, $module) {
		$this->getSetting($setting);
		$type = 'data';
		if(empty($module)) 
		{
			throw new FreeException(' 2th parameter ','110');
		}
		$filepath = CACHE_PATH.'caches_'.$module.'/caches_'.$type.'/';
		$filename = $name.$this->_setting['suf'];

		if (!file_exists($filepath.$filename)) {
			return false;
		} else {
		    if($this->_setting['type'] == 'array') {
		    	$data = @require($filepath.$filename);		
		    } elseif($this->_setting['type'] == 'serialize') {
		    	$data = unserialize(file_get_contents($filepath.$filename));
		    }
		    
		    return $data;
		}
	}
	
	/**
	 * 删除缓存
	 * @param	string	$name		缓存名称
	 * @param	string	$module		所属模型
	 * @return  bool
	 */
	public function delete($name, $module) {
		$this->getSetting($setting);
		if(empty($type)) $type = 'data';
		if(empty($module)) 
		{
			throw new FreeException(' 2th parameter ','110');
		}	
		$filepath = CACHE_PATH.'caches_'.$module.'/caches_'.$type.'/';
		$filename = $name.$this->_setting['suf'];
		if(file_exists($filepath.$filename)) {
			return @unlink($filepath.$filename) ? true : false;
		} else {
			return false;
		}
	}
	
	/**
	 * 和系统缓存配置对比获取自定义缓存配置
	 * @param	array	$setting	自定义缓存配置
	 * @return  array	$setting	缓存配置
	 */
	public function getSetting($setting = '') {
		if($setting) {
			$this->_setting = array_merge($this->_setting, $setting);
		}
	}

	public function cacheinfo($name, $setting = '', $type = 'data', $module='commons') {
		$this->getSetting($setting);
		if(empty($type)) $type = 'data';
		if(empty($module)) 
		{
			throw new FreeException(' 4th parameter ','110');
		}
		$filepath = CACHE_PATH.'caches_'.$module.'/caches_'.$type.'/';
		$filename = $filepath.$name.$this->_setting['suf'];
		
		if(file_exists($filename)) {
			$res['filename'] = $name.$this->_setting['suf'];
			$res['filepath'] = $filepath;
			$res['filectime'] = filectime($filename);
			$res['filemtime'] = filemtime($filename);
			$res['filesize'] = filesize($filename);
			return $res;
		} else {
			return false;
		}
	}

}

?>