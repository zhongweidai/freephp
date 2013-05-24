<?php
/**
 * memcache session处理类
 */
class FreeMemcacheSession {
    private  $lifetime = 1800;
    private  $memcache;
    private  $config;
    private  $sessionname = "FREECITY2";

    /**
 * 构造函数
 * 
 */
	public function __construct() {
        $this->config = Free::loadConfig('cache','memcache');
		$this->connect($this->config);
		$this->lifetime = Free::loadConfig('system','session_ttl');
        session_name($this->sessionname);
    	session_set_save_handler(array(&$this,'open'), array(&$this,'close'), array(&$this,'read'), array(&$this,'write'), array(&$this,'destroy'), array(&$this,'gc'));
    	session_start();//dump(session_id());exit;
    }
/**
	 * 构造函数-创建Memcache连接对象
	 */
    protected function connect($config) 
	{
		if (!is_object('memcache')) 
		{
			if (!$config) return;
			$this->memcache = new Memcache;
			
			$keys = Free::loadConfig('double');
			$_sid = $keys['memcache'];
			$this->_conn = $this->memcache->connect($config[$_sid]['hostname'], $config[$_sid]['port'], $config[$_sid]['timeout']);
			if($this->_conn == false)
			{
				$this->change_server($config, $_sid);
			}
		}
	}
	
	/*切换Memcache*/
	protected function change_server($config, $_sid)
	{
		$num = count($config);
		if ($num <= 1) die( 'cs Connection refused');
		$keys = Free::loadConfig('double');
		
		for($i = 1;$i < $num; $i++)
		{
			$n = $i + $_sid;
			$key = $n >= $num ? $n - $num : $n;

			$this->_conn = $this->memcache->connect($config[$key]['hostname'], $config[$key]['port'], $config[$key]['timeout']);
			if($this->_conn != false)
			{
				$keys['memcache'] = $key ;
				$data = "<?php\nreturn ".var_export($keys, true).";\n?>";
				file_put_contents(FREE_PATH.'configs/double.php', $data, LOCK_EX);
				break;
			}
		}

		if (!$this->_conn) die( 'cs Connection refused');
	}
   
/**
 * session_set_save_handler  open方法
 * @param $save_path
 * @param $session_name
 * @return true
 */
    public function open($save_path, $session_name) {

	return true;
    }
/**
 * session_set_save_handler  close方法
 * @return bool
 */
    public function close() {
        return true;
    } 
/**
 * 读取session_id
 * session_set_save_handler  read方法
 * @return string 读取session_id
 */
    public function read($id) {
        $value = $this->memcache->get($id);
	return $value;
    } 
/**
 * 写入session_id 的值
 * 
 * @param $id session
 * @param $data 值
 * @return mixed query 执行结果
 */
    public function write($id, $data) {
    	return $this->memcache->set($id, $data, false, $this->lifetime);
    }
/** 
 * 删除指定的session_id
 * 
 * @param $id session
 * @return bool
 */
    public function destroy($id) {
        return $this->memcache->delete($id);
    }
/**
 * @return bool
 */
   public function gc($maxlifetime) {
	return true;
    }
}
?>