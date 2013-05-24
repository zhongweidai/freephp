<?php
/**
 * redis缓存操作策略实现类
 *
 * <i>FreeRedisCache使用:</i><br/>
 * 1、像使用普通类库一样使用该组件:
 * <code>
 *  Free::loadClass('FreeRedisCache', PC_PATH . 'libs/cache',0);
 * 	$cache = new FreeRedisCache();
 *  $cache->set('test1','hello world');
 *  return $cache->get('test1');
 * </code>
 * 2、采用组件配置的方式，通过组件机制调用
 * 在应用配置的component组件配置块中,配置FreeRedisCache(<i>该名字将决定调用的时候使用的组件名字</i>):
 * <pre>
 *   'redis' => 'free/libs/cache/FreeRedisCache',
 * </pre>
 * 在应用中可以通过如下方式获得FreeRedisCache对象:
 * <code>
 *  $cache = $this->getComponent('redis');	//redis的名字来自于组件配置中的名字
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
 * @version $Id: FreeRedisCache.php 1 2012-07-13 11:00:00Z $ 
 * @package cache
 */
Free::loadClass('AbstractFreeCache',PC_PATH . 'libs/cache',0);
class FreeRedisCache extends AbstractFreeCache{

    private $redis = object;

	/**
	 * 构造函数-创建Redis连接对象
	 */
    public function __construct($config='') 
	{
		if (!is_object('redis')) 
		{
			if (!$config) {
				$config = Free::loadConfig('cache','redis');
				$config = $config['servers'];
				$this->_config = $config;
			}
			$this->redis = new Redis();
			$keys = Free::loadConfig('double');
			$_sid = $keys['redis'];
			try 
			{
				$this->_conn = $this->redis->connect($config[$_sid]['hostname'], $config[$_sid]['port'], $config[$_sid]['timeout']);
			} 
			catch (Exception $e) 
			{
				$this->change_server($config, $_sid);
			}
		}
	}
	
	/**
	 * 切换redis配置
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
				$this->_conn = $this->redis->connect($config[$key]['hostname'], $config[$key]['port'], $config[$key]['timeout']);
				if($this->_conn != false)
				{
					$keys['redis'] = $key ;
					$data = "<?php\nreturn ".var_export($keys, true).";\n?>";
					file_put_contents(FREE_PATH.'configs/double.php', $data);
					break;
				}
			}
			catch (Exception $e) 
			{
			
			}
		}

		if (!$this->_conn) 
		{
			die( 'cs Connection refused');
		}	
	}

	/**
	 * 设置缓存
	 * @param	string			$name		缓存名称
	 * @param   array|string 	$data		缓存数据
	 * @param	string			$module		所属模块
     * @param   int             $expire     存活时间 
	 */
	public function set($name, $data, $module,$expire=0) 
	{
		$newName = $module.'_'.$name;
        is_array($data) && $data = json_encode($data);
        if($expire == 0)
        {
            return $this->redis->set($newName, $data);
        }else{
            return $this->redis->setex($newName,$expire,$data);
        }

	}
	
	/**
	 * 获取缓存
	 * @param	string	$name		缓存名称
	 * @param	string	$module		所属模块
	 */
	public function get($name, $module) 
	{
		/**if(is_array($name)) 
		{
			return $this->redis->mget($name);
		} 
        **/
		$newName = $module.'_'.$name;
		if ($this->redis->exists($newName)) 
		{
            $data = $this->redis->get($newName);
            $data = json_decode($data,true);
			return $data;
		} 
		else 
		{
			return false;
		}
	}

	/**
	 * 删除缓存
	 * @param	string			$name		缓存名称
	 * @param	string			$module		所属模块
	 */
	public function delete($name, $module) 
	{
		$newName = $module.'_'.$name;
		return $this->redis->delete($newName);
	}
	
	/**
	 * List类型-链表进栈(插入)操作
	 * @param	$key	    string    链表名
	 * @param	$value	    string    插入的值
	 * @param	$direction	string	  头/尾插入
	 * @param	$overlay	boolean	  key重名是否覆盖值， true 覆盖 false 不覆盖
	 * @retrun  integer     返回插入的数据位置
	 */
	public function listPush($key, $value, $direction = 'r', $overlay = true)
	{
		if (empty($key)) return;
		$func = $direction == 'r' ? 'rPush' : 'lPush';
		$func = $overlay ? $func : $func.'x';
		return $this->redis->$func($key, $value);
	}
	
	/**
	 * List类型-链表出栈(删除)操作
	 * @param	$key	    string    链表名
	 * @param	$direction	string	  头/尾开始删除
	 * @param	$overlay	boolean	  key重名是否覆盖值， true 覆盖 false 不覆盖
	 * @retrun  integer   返回插入的数据位置
	 */
	public function listPop($key, $direction = 'r')
	{
		if (empty($key)) return;
		$func = $direction == 'r' ? 'rPop' : 'lPop';
		return $this->redis->$func($key, $value);
	}

	/**
	 * List类型-返回链表的元素个数
	 * @param	$key	    string    链表名
	 * @retrun  integer   
	 */
	public function listSize($key)
	{
		if (empty($key)) return;
		return $this->redis->lSize($key);
	}
	
	/**
	 * List类型-返回链表中index位置的元素
	 * @param	$key      string    链表名
	 * @param	$index	  integer   索引号
	 * @retrun  string   
	 */
	public function listGet($key, $index)
	{
		if (empty($key) || !is_numeric($index)) return;
		return $this->redis->lGet($key, $index);
	}
	
	/**
	 * List类型-给链表中index位置的元素赋值
	 * @param	$key	  string    链表名
	 * @param	$index	  integer   索引号
	 * @param	$value	  string    赋值
	 * @retrun  string   
	 */
	public function listSet($key, $index, $value)
	{
		if (empty($key) || !is_numeric($index)) return;
		return $this->redis->lSet($key, $index, $value);
	}
	
	/**
	 * List类型-返回链表start至end之间的元素
	 * @param	$key	  string    链表名
	 * @param	$start	  integer   开始索引号 默认从0即第一条记录开始
	 * @param	$end	  integer   结束索引号(-1返回所有, 负值表示从后面开始计算)
	 * @retrun  array   
	 */
	public function listRange($key, $start = 0, $end = -1)
	{
		if (empty($key)) return;
		return $this->redis->lRange($key, $start, $end);
	}
	
	/**
	 * List类型-删除链表指定的索引
	 * @param	$key	    string    链表名
	 * @param	$index	  integer   索引号
	 * @retrun  integer   返回删除的元素数量
	 */
	public function listDeleteIndex($key, $index, $length = 69)
	{
		$value = '';
		for (; $length > 0; --$length) 
		{
			$value .= chr(rand(32, 126));
		}

		$this->redis->lSet($key, $index, $value);
		return $this->redis->lRem($key, $value, 1);
	}
	
	/**
	 * 获取所有匹配的key列表，默认匹配所有即*
	 * @param	$pattern	  string   模糊匹配
	 * @retrun  array
	 */
	public function getKeys($pattern = '*') 
	{
		
		$keys = $this->redis->keys($pattern);
		return $keys;
	}
	
	/**
	 * 清空当前数据库-慎用
	 */
	public function flushDb()
	{
		return $this->redis->flushDb();
	}
	
	/**
	 * 清空所有数据库-慎用
	 */
	public function flushAll()
	{
		return $this->redis->flushAll();
	}
	
	/**
	 * Redis通道操作-一次执行多个操作
	 */
	public function pipe()
	{
		$pipe = $this->redis->multi(Redis::PIPELINE);
		/** demo
		for ($i = 0; $i < 10; $i++)
		{  
			$pipe->set($key, $value);  
			$pipe->get($key);
		}
		$replies = $pipe->exec();
		**/
		return $pipe;
	}
}