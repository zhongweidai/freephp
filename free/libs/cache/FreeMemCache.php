<?php 
/**
 * memcache缓存操作策略实现类
 *
 * <i>FreeMemCache使用:</i><br/>
 * 1、像使用普通类库一样使用该组件:
 * <code>
 *  Free::loadClass('FreeMemCache', PC_PATH . 'libs/cache',0);
 * 	$cache = new FreeMemCache();
 *  $cache->set('test1','hello world');
 *  return $cache->get('test1');
 * </code>
 * 2、采用组件配置的方式，通过组件机制调用
 * 在应用配置的component组件配置块中,配置FreeMemCache(<i>该名字将决定调用的时候使用的组件名字</i>):
 * <pre>
 *   'memcache' => 'free/libs/cache/FreeMemCache',
 * </pre>
 * 在应用中可以通过如下方式获得FreeMemCache对象:
 * <code>
 *  $cache = $this->getComponent('memcache');	//memcache的名字来自于组件配置中的名字
 *  $cache->set('test1','hello world');
 *  return $cache->get('test1');
 *  
 *  上面方法设置获取当前模块的缓存，如需设置获取其他模块的缓存，操作如下
 *  $cache->set('test1','hello world','sns');  --其中第三参数为模块名称，方便获取
 *  return $cache->get('test1','sns'); 
 * </code>
 *
 * @author
 * @copyright
 * @license
 * @version $Id: FreeMemCache.php 1 2012-07-13 11:00:00Z $ 
 * @package cache
 */
Free::loadClass('AbstractFreeCache',PC_PATH . 'libs/cache',0);
class FreeMemCache extends AbstractFreeCache{

	private $memcache = object;

	/**
	 * 构造函数-创建Memcache连接对象
	 */
    public function __construct($config='') 
	{
		if (!is_object('memcache')) 
		{
			if (!$config) {
				$config = Free::loadConfig('cache','memcache');
				$this->_config = $config;
			}
			$this->memcache = new Memcache;
			$keys = Free::loadConfig('double');
			$_sid = $keys['memcache'];
			$this->_conn = $this->memcache->connect($config[$_sid]['hostname'], $config[$_sid]['port'], $config[$_sid]['timeout']);
			if(!$this->_conn){
				$this->change_server($config, $_sid);
			}
		}
	}
	
	/**
	 * 切换Memcache
	 */
	protected function change_server($config, $_sid)
	{
		$num = count($config);
		if ($num <= 1) die( 'cs Connection refused');
		$keys = Free::loadConfig('double');
		
		for($i = 1;$i < $num; $i++)
		{
			$n = $i + $_sid;
			$key = $n >= $num ? $n - $num : $n;
			try 
			{
				$this->_conn = $this->memcache->connect($config[$key]['hostname'], $config[$key]['port'], $config[$key]['timeout']);
				if($this->_conn != false)
				{
					$keys['memcache'] = $key ;
					$data = "<?php\nreturn ".var_export($keys, true).";\n?>";
					file_put_contents(FREE_PATH.'configs/double.php', $data);
					break;
				}
			}
			catch (Exception $e) 
			{
			
			}
		}

		if (!$this->_conn) die( 'cs Connection refused');
	}

	public function memcache() {
		$this->__construct();
	}
	/**
	 * 获取缓存
	 * @param	string			$name		缓存名称
	 * @param	string			$module		所属模块
	 */
	public function get($name, $module) {
		if(empty($module)) 
		{
			throw new FreeException(' 2th parameter ','110');
		}	
		$newName = $module.'_'.$name;
		$value = $this->memcache->get($newName);
        $value = json_decode($value,true);
		return $value;
	}
	/**
	 * 设置缓存
	 * @param	string			$name		缓存名称
	 * @param   array|string 	$data		缓存数据
	 * @param	string			$module		所属模块
	 */
	public function set($name, $data, $module, $expire=0) {
		if(empty($module)) 
		{
			throw new FreeException(' 3th parameter ','110');
		}	
		$newName = $module.'_'.$name;
        is_array($data) && $data = json_encode($data);
		return $this->memcache->set($newName, $data, false, $expire);
	}
	/**
	 * 删除缓存
	 * @param	string			$name		缓存名称
	 * @param	string			$module		所属模块
	 */
	public function delete($name, $module) {
		if(empty($module)) 
		{
			throw new FreeException(' 2th parameter ','110');
		}	
		$newName = $module.'_'.$name;
		return $this->memcache->delete($newName);
	}

	public function flush() {
		return $this->memcache->flush();
	}
}
?>