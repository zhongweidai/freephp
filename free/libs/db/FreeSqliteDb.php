<?php
/**
 *  Sqlite数据库操作策略实现类
 *
 * <i>Sqlite数据库的使用:</i><br/>
 * 1、像使用普通类库一样使用该组件:
 * <code>
 *  Free::loadClass('SqliteDb', PC_PATH . 'libs/db',0);
 * 	$db = new SqliteDb();
 *  return $db->query($sql);
 * </code>
 * 2、采用组件配置的方式，通过组件机制调用
 * 在应用配置的component组件配置块中,配置mysqlDb(<i>该名字将决定调用的时候使用的组件名字</i>):
 * <pre>
 *   'SqliteDb' => 'free/libs/db/SqliteDb',
 * </pre>
 * 在应用中可以通过如下方式获得db对象:
 * <code>
 * $db = $this->getComponent('SqliteDb');	//dbCache的名字来自于组件配置中的名字
 * return $db->query($sql);
 * </code>
 *
 * @author
 * @copyright
 * @license
 * @version $Id: SqliteDb.php 1 2012-07-13 11:00:00Z $ 
 * @package cache
 */
Free::loadClass('AbstractFreeDb', PC_PATH . 'libs/db', 0);

final class FreeSqliteDb extends FreeBase  {

	/**
	 * 数据库配置信息
	 */
	private $config = null;
	
	/**
	 * 静态数据库对象
	 */
    public static $dbArr = array();
	
	/**
	 * 数据库连接资源句柄
	 */
    private $db = NULL;
	
	/**
	 * 数据库路径
	 */
    private $path = '';

		
	public function __construct() {
		$config = Free::loadConfig('database','sqlite');
		$this->open($config);
	}
	
	/**
	 * 打开数据库连接,有可能不真实连接数据库
	 * @param $config	数据库连接参数
	 * 			
	 * @return void
	 */
	public function open($config) {
		$this->config = $config;
		$this->path = $this->config['path'];
		if($config['autoconnect'] == 1) {
			$this->connect();
		}
	}

	/**
	 * 真正开启数据库连接
	 * 			
	 * @return void
	 */
    public function connect(){
		if(!array_key_exists($this->path, self::$dbArr)) {
            if(!file_exists($this->path)){
                $this->halt("Error: dbfile is not exists.");
            }
            $this->db = new PDO('sqlite:'.$this->path);
			if(is_null($this->db)){
				$this->halt('db connect failed');
			}
            self::$dbArr[$this->path] = $this->db;
        } else {
            $this->db = self::$dbArr[$this->path];
        }
    }

	/**
	 * 直接执行sql查询
	 * @param $sql							查询sql语句
	 * @return	boolean/query resource		如果为查询语句，返回资源句柄，否则返回true/false
	 */
    public function query($sql){
        $result = $this->db->query($sql);
        if(!$result)
            $this->halt("SQL Error", $sql);
        else
            return $result;
    }

	/**
	 * 获取最后一次添加记录的主键号
	 * @return int 
	 */
    public function insertId(){
        return $this->db->lastinsertid();
    }

	/**
	 * 返回结果的行数
	 *
	 * @param $query
	 */
    public function fetchArray($query){
        $rs = $query->fetch();
        if(is_array($rs) && count($rs)>0){
            foreach($rs as $key=>$val){
                if(is_int($key))
                    unset($rs[$key]);
            }
        }else{
            $rs = 0;
        }
		
        return $rs;
    }
	
	/**
	 * 获取单条记录查询
	 *
	 * @param $sql	查询sql语句
	 */
    public function fetchOne($sql){
        $query = $this->query($sql);
        return $this->fetchArray($query);
    }
	
	/**
	 * 返回所有记录
	 *
	 * @param $sql	查询sql语句
	 */
    public function fetchAll($sql){
        $query = $this->query($sql);
        $Rs = $query->fetchAll();
        if(is_array($Rs) && count($Rs)>0) {
            $tmpRs = array();
            foreach($Rs as $k => $r){
                if(is_array($r)){
                    foreach($r as $rk=>$rv){
                        if(is_int($rk)) unset($r[$rk]);
                    }
                }
                $tmpRs[$k]= $r;
            }
			$Rs = $tmpRs;
        }
		
        return $Rs;
    }

	/**
     * 关闭连接
     */
    public function close(){
        $this->db = null;
        $path = $this->path;
        if($path)
            unset(self::$dbArr[$path]);
    }

	/**
     * 字符串处理
     */
    public function encode($string){
        return str_replace("'", "''", $string);;
    }
	
    /**
     * 错误信息
     */
    public function error(){
        if ($this->db && $this->db->errorCode() != '00000') {
            $error = $this->db->errorInfo();
            return 'Error Info('.$error[1].'): '.$error[2];
        }
    }
	
	/** 
	 * 展示报错信息
	 *
	 * @param unknown_type $message
	 * @param unknown_type $sql
	 */
    public function halt($message='', $sql='') {
        exit($message. ": " .$sql.' <br/>'.$this->error());
    }
	
	public function beginTransaction() {
		$this->db->beginTransaction();
	}

	/**
     * 提交事务
     */
	public function commit() {
		$this->db->commit();
	}
}