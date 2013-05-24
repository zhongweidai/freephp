<?php
/**
 * ttserver缓存操作策略实现类
 *
 * <i>FreeTtCache使用:</i><br/>
 * 1、像使用普通类库一样使用该组件:
 * <code>
 *  Free::loadClass('FreeTtCache', PC_PATH . 'libs/cache',0);
 * 	$cache = new FreeTtCache();
 *  $cache->set('test1','hello world');
 *  return $cache->get('test1');
 * </code>
 * 2、采用组件配置的方式，通过组件机制调用
 * 在应用配置的component组件配置块中,配置FreeTtCache(<i>该名字将决定调用的时候使用的组件名字</i>):
 * <pre>
 *   'ttserver' => 'free/libs/cache/FreeTtCache',
 * </pre>
 * 在应用中可以通过如下方式获得FreeTtCache对象:
 * <code>
 *  $cache = $this->getComponent('ttserver');	//ttserver的名字来自于组件配置中的名字
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
 * @version $Id: FreeTtCache.php 1 2012-07-13 11:00:00Z $ 
 * @package cache
 */
Free::loadClass('AbstractFreeCache',PC_PATH . 'libs/cache',0);
require_once dirname(__FILE__).'/ttserver/Tyrant.php';	
require_once dirname(__FILE__).'/ttserver/Tyrant/Query.php';
class FreeTtCache extends AbstractFreeCache{
	public $_query = object;
	public $_linktime;

	/**
	 * 构造函数-创建TTSERVER连接对象
	 */
    public function __construct($config='') {
		
   	 	if (!$config) {
			$config = Free::loadConfig('cache','ttserver');
			$config = $config['hash'];
			$this->_config = $config;
		}
		$num = count($config);
		$keys = Free::loadConfig('double');
		$ttserver= $keys['ttserver'];
		if(!$this->_conn = Tyrant::connect($config[$ttserver]['hostname'] , $config[$ttserver]['port']))
		{
			for($i = 1;$i < $num;$i++)
			{
				$n = $i + $ttserver;
				$key = $n >= $num ? $n - $num : $n;
				$this->_conn = Tyrant::connect($config[$key]['hostname'] , $config[$key]['port']);
				if($this->_conn != false)
				{
					$keys['ttserver'] = $key ;
					$data = "<?php\nreturn ".var_export($keys, true).";\n?>";
					file_put_contents(CACHE_PATH.'configs/double.php', $data, LOCK_EX);
					break;
				}
			}
		}
		
		$this->_query = new Tyrant_Query;
	}

	/**
	 * 设置缓存
	 * @param	string			$name		缓存名称
	 * @param   array|string 	$data		缓存数据
	 * @param	string			$module		所属模块
	 */
	public function set($name, $data, $module,$expire) {
        if(empty($module)) 
		{
			throw new FreeException(' 2th parameter ','110');
		}
		$newName = $module.'_'.$name;
        is_array($data) && $data = json_encode($data);
		return $this->_conn->put($newName , $data);

	}
	
	/**
	 * 获取缓存
	 * @param	string	$name		缓存名称
	 * @param	string	$module		所属模块
	 */
	public function get($name, $module) {
	   if(empty($module)) 
		{
			throw new FreeException(' 2th parameter ','110');
		}
		$newName = $module.'_'.$name;
		$value = $this->_conn->get($newName);
        $value = json_decode($value,true);
		return $value;
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
		return $this->_conn->out($newName);
	}
	
	/**
	 * 删除所有记录
	 */
	public function removeAll() {
		return $this->_conn->vanish();
	}

	/**
	 * 使用条件：TC HASH数据库
	 * 写入整形记录。若key存在，则更新记录，否则插入一条记录。
	 */
	public function add($key , $increment) {
		return $this->_conn->addInt($key , $increment);
	}

	/**
	 * 使用条件：TC TABLE数据库
	 * 获取一个连接对象遍历数据库中的所有键/值。
	 */
	public function getIterator() {
		return $this->_conn;
	}

	/**
	 * 获取记录数
	 */
	public function rnum() {
		return $this->_conn->rnum();
	}
	
	/**
	 * 使用条件：TC TABLE数据库
	 * 检索记录集，返回key
	 */
	public function search() {
		return $this->_conn->search($this->_query);
	}
	
	/**
	 * 使用条件：TC TABLE数据库
	 * 删除检索匹配的记录集
	 */
	public function searchOut() {
		return $this->_conn->searchOut($this->_query);
	}
	
	/**
	 * 使用条件：TC TABLE数据库
	 * 检索匹配的记录集，返回记录数组
	 */
	public function searchGet($names = null) {
		return $this->_conn->searchGet($this->_query, $names);
	}
	
	/**
	 * 使用条件：TC TABLE数据库
	 * 统计检索匹配的记录集个数
	 */
	public function searchCount() {
		return $this->_conn->searchCount($this->_query);
	}
}