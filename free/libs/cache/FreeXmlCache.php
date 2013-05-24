<?php
Free::loadClass('AbstractFreeCache',PC_PATH . 'libs/cache',0);
class FreeXmlCache extends AbstractFreeCache{
	
	/*缓存默认配置*/
	protected $_setting = array(
								'suf' => '.cache.xml',	/*缓存文件后缀*/
								'type' => 'serialize',		/*缓存格式：array数组，serialize序列化，null字符串*/
							);
	
	/*缓存路径*/
	protected $filepath = '';

	/**
	 * 构造函数
	 * @param	array	$setting	缓存配置
	 * @return  void
	 */
	public function __construct($setting = '') {
		$this->get_setting($setting);
	}
	
	/**
	 * 写入缓存
	 * @param	string	$name		缓存名称
	 * @param	mixed	$data		缓存数据
	 * @param	string	$module		所属模型
	 * @return  mixed				缓存路径/false
	 */

	public function set($name, $data, $module) {
		if(empty($type)) $type = 'data';
		if(empty($module)) 
		{
			throw new FreeException(' 2th parameter ','110');
		}	
		$filepath = CACHE_PATH.'caches_'.$module.'/caches_'.$type.'/';
		$filename = $name.$this->_setting['suf'];
	    if(!is_dir($filepath)) {
			mkdir($filepath, 0777, true);
	    }
	     
        
	    if($this->_setting['type'] == 'array') {
	    	$data = "<?xml version=\"1.0\" encoding=\"utf-8\"?>".var_export($data, true)."";
	    } elseif($this->_setting['type'] == 'serialize') {
	    	Free::loadClass('xml');
	     $xml = new xml(); 
	    	$data = $xml->xml_serialize($data);
	    }
	    if ($module == 'commons' && substr($name, 0, 16) != 'category_content') {
		    $db = Free::loadModel('cache_model');
		    $datas = new_addslashes($data);
		    if ($db->get_one(array('filename'=>$filename, 'path'=>'caches_'.$module.'/caches_'.$type.'/'), '`filename`')) {
		    	$db->update(array('data'=>$datas), array('filename'=>$filename, 'path'=>'caches_'.$module.'/caches_'.$type.'/'));
		    } else {
		    	$db->insert(array('filename'=>$filename, 'path'=>'caches_'.$module.'/caches_'.$type.'/', 'data'=>$datas));
		    }
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
		if(empty($type)) $type = 'data';
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
		       $data = file_get_contents($filepath.$filename);
		    	Free::loadClass('xml');
	            $xml = new xml(); 
	    	    $data = $xml->xml_unserialize($data);
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
	public function delete($name,  $module) {
		if(empty($type)) $type = 'data';
		if(empty($module)) 
		{
			throw new FreeException(' 2th parameter ','110');
		}	
		$filepath = CACHE_PATH.'caches_'.$module.'/caches_'.$type.'/';
		$filename = $name.$this->_setting['suf'];
		if(file_exists($filepath.$filename)) {
			if ($module == 'commons' && substr($name, 0, 16) != 'category_content') {
				$db = Free::loadModel('cache_model');
		    	$db->delete(array('filename'=>$filename, 'path'=>'caches_'.$module.'/caches_'.$type.'/'));
			}
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
	public function get_setting($setting = '') {
		if($setting) {
			$this->_setting = array_merge($this->_setting, $setting);
		}
	}

	public function cacheinfo($name, $module) {
		if(empty($type)) $type = 'data';
		if(empty($module)) 
		{
			throw new FreeException(' 2th parameter ','110');
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