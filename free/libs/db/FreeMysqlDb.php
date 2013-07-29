<?php
/**
 * Mysql数据库操作策略实现类
 *
 * <i>Mysql数据库的使用:</i><br/>
 * 1、像使用普通类库一样使用该组件:
 * <code>
 *  Free::loadClass('MysqlDb', PC_PATH . 'libs/db',0);
 * 	$db = new MysqlDb();
 *  return $db->query($sql);
 * </code>
 * 2、采用组件配置的方式，通过组件机制调用
 * 在应用配置的component组件配置块中,配置mysqlDb(<i>该名字将决定调用的时候使用的组件名字</i>):
 * <pre>
 *   'mysqlDb' => 'free/libs/db/MysqlDb',
 * </pre>
 * 在应用中可以通过如下方式获得db对象:
 * <code>
 * $db = $this->getComponent('mysqlDb');	//dbCache的名字来自于组件配置中的名字
 * return $db->query($sql);
 * </code>
 *
 * @author
 * @copyright
 * @license
 * @version $Id: MysqlDb.php 1 2012-07-13 11:00:00Z $ 
 * @package cache
 */

Free::loadClass('AbstractFreeDb', PC_PATH . 'libs/db', 0);

final class FreeMysqlDb extends AbstractFreeDb {
	
	/**
	 * 数据库配置信息
	 */
	private $config = null;
	
	/**
	 * 数据库连接资源句柄
	 */
	public $link = null;
	
	/**
	 * 最近一次查询资源句柄
	 */
	public $lastQueryId = null;
	
	/**
	 *  统计数据库查询次数
	 */
	public $queryCount = 0;
		
    protected $comparison = array('eq'=>'=','neq'=>'<>','gt'=>'>','egt'=>'>=','lt'=>'<','elt'=>'<=','notlike'=>'NOT LIKE','like'=>'LIKE');
	
	
	public function __construct() {
		$config = Free::loadConfig('database','mysql');
		$this->open($config);
	}
	
	/**
	 * 打开数据库连接,有可能不真实连接数据库
	 *
	 * @param $config	数据库连接参数
	 * 			
	 * @return void
	 */
	public function open($config) {
		$this->config = $config;
		if($config['autoconnect'] == 1) {
			$this->connect();
		}
	}

	/**
	 * 真正开启数据库连接
	 * 			
	 * @return void
	 */
	public function connect() {
		$func = $this->config['pconnect'] == 1 ? 'mysql_pconnect' : 'mysql_connect';
		if(!$this->link = @$func($this->config['hostname'], $this->config['username'], $this->config['password'], 1)) {
			$this->halt('Can not connect to MySQL server');
			return false;
		}

		if($this->version() > '4.1') {
			$charset = isset($this->config['charset']) ? $this->config['charset'] : '';
			$serverset = $charset ? "character_set_connection='$charset',character_set_results='$charset',character_set_client=binary" : '';
			$serverset .= $this->version() > '5.0.1' ? ((empty($serverset) ? '' : ',')." sql_mode='' ") : '';
			$serverset && mysql_query("SET $serverset", $this->link);		
		}

		if($this->config['database'] && !@mysql_select_db($this->config['database'], $this->link)) {
			$this->halt('Cannot use database '.$this->config['database']);
			return false;
		}
		$this->database = $this->config['database'];
		return $this->link;
	}

	/**
	 * 数据库查询执行方法
	 *
	 * @param $sql 要执行的sql语句
	 *
	 * @return 查询资源句柄
	 */
	private function execute($sql) 
    {//dump($sql);
		if(!is_resource($this->link)) {
			$this->connect();
		}
		
		$this->lastQueryId = mysql_query($sql, $this->link) or $this->halt(mysql_error(), $sql);

		$this->queryCount++;
		return $this->lastQueryId;
	}

	/**
	 * 执行sql查询
	 * @param $data 		需要查询的字段值[例`name`,`gender`,`birthday`]
	 * @param $table 		数据表
	 * @param $where 		查询条件[例`name`='$name']
	 * @param $limit 		返回结果范围[例：10或10,10 默认为空]
	 * @param $order 		排序方式	[默认按数据库默认方式排序]
	 * @param $group 		分组方式	[默认为空]
	 * @param $key 			返回数组按键名排序
	 * @return array		查询结果集数组
	 */
	public function select($data, $table, $where = '', $limit = '', $order = '', $group = '', $key = '') {

        $where = $this->parseWhere($where);
        
		$where && $where = ' WHERE ' . $where . ' ';
		$order = $this->parseOrder($order);
		$order && $order = ' ORDER BY ' . $order;
		$group = $group == '' ? '' : ' GROUP BY '.$group;
		if(empty($data))
		{
			$data = '*';
		}
		if (is_array($data))
		{
			array_walk($data, array($this, 'addSpecialChar'));
			$data = implode(',', $data);
		}
		else{
			$field = explode(',', $data);
			array_walk($field, array($this, 'addSpecialChar'));
			$data = implode(',', $field);
		}
        $limit && $limit = ' limit ' . $limit;
		$sql = 'SELECT '.$data.' FROM `'.$this->config['database'].'`.`'.$table.'`'.$where.$group.$order.$limit;
		$this->execute($sql);
		if(!is_resource($this->lastQueryId)) {
			return $this->lastQueryId;
		}

		$datalist = array();
		while(($rs = $this->fetchNext()) != false) {
			if($key) {
				$datalist[$rs[$key]] = $rs;
			} else {
				$datalist[] = $rs;
			}
		}
		$this->freeResult();
		return $datalist;
	}

	/**
	 * 获取单条记录查询
	 * @param $data 		需要查询的字段值[例`name`,`gender`,`birthday`]
	 * @param $table 		数据表
	 * @param $where 		查询条件
	 * @param $order 		排序方式	[默认按数据库默认方式排序]
	 * @param $group 		分组方式	[默认为空]
	 * @return array/null	数据查询结果集,如果不存在，则返回空
	 */
	public function getOne($data, $table, $where = '', $order = '', $group = '') {	
		$where = $this->parseWhere($where);
		$where && $where = ' WHERE ' . $where . ' ';
		$order = $this->parseOrder($order);
		$order && $order = ' ORDER BY ' . $order;
		$group = $group == '' ? '' : ' GROUP BY '.$group;
		$limit = ' LIMIT 1';
        
		if(empty($data))
		{
			$data = '*';
		}else{
			$field = explode(',', $data);
			array_walk($field, array($this, 'addSpecialChar'));
			$data = implode(',', $field);
		}

		$sql = 'SELECT '.$data.' FROM `'.$this->config['database'].'`.`'.$table.'`'.$where.$group.$order.$limit;
		$this->execute($sql);
		$res = $this->fetchNext();
		$this->freeResult();
		return $res;
	}
	
	/**
	 * 遍历查询结果集
	 * @param $type		返回结果集类型	
	 * 					MYSQL_ASSOC，MYSQL_NUM 和 MYSQL_BOTH
	 * @return array
	 */
	public function fetchNext($type=MYSQL_ASSOC) {
		$res = mysql_fetch_array($this->lastQueryId, $type);
		if(!$res) {
			$this->freeResult();
		}
		return $res;
	}
	
	/**
	 * 释放查询资源
	 * @return void
	 */
	public function freeResult() {
		if(is_resource($this->lastQueryId)) {
			mysql_free_result($this->lastQueryId);
			$this->lastQueryId = null;
		}
	}
	
	/**
	 * 直接执行sql查询
	 * @param $sql							查询sql语句
	 * @return	boolean/query resource		如果为查询语句，返回资源句柄，否则返回true/false
	 */
	public function query($sql) {
		return $this->execute($sql);
	}
	
	/**
	 * 执行添加记录操作
	 * @param $data 		要增加的数据，参数为数组。数组key为字段值，数组值为数据取值
	 * @param $table 		数据表
	 * @return boolean
	 */
	public function insert($data, $table, $return_insertId = false, $replace = false) {
		if(!is_array( $data ) || $table == '' || count($data) == 0) {
			return false;
		}
		
		$fielddata = array_keys($data);
		$valuedata = array_values($data);
		array_walk($fielddata, array($this, 'addSpecialChar'));
		array_walk($valuedata, array($this, 'escapeString'));
		
		$field = implode (',', $fielddata);
		$value = implode (',', $valuedata);

		$cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';
		$sql = $cmd.' `'.$this->config['database'].'`.`'.$table.'`('.$field.') VALUES ('.$value.')';
		$return = $this->execute($sql);
        $this->toLog($sql);
		return $return_insertId ? $this->insertId() : $return;
	}
	
	/**
	 * 获取最后一次添加记录的主键号
	 * @return int 
	 */
	public function insertId($table) {
		return mysql_insert_id($this->link);
	}
	
	/**
	 * 执行更新记录操作
	 * @param $data 		要更新的数据内容，参数可以为数组也可以为字符串，建议数组。
	 * 						为数组时数组key为字段值，数组值为数据取值
	 * 						为字符串时[例：`name`='phpcms',`hits`=`hits`+1]。
	 *						为数组时[例: array('name'=>'phpcms','password'=>'123456')]
	 *						数组可使用array('name'=>'+=1', 'base'=>'-=1');程序会自动解析为`name` = `name` + 1, `base` = `base` - 1
	 * @param $table 		数据表
	 * @param $where 		更新数据时的条件
	 * @return boolean
	 */
	public function update($data, $table, $where = '') {
	   /**
		if($table == '' or $where == '') {
			return false;
		}

		$where = ' WHERE '.$where;
		$field = '';
		if(is_string($data) && $data != '') {
			$field = $data;
		} elseif (is_array($data) && count($data) > 0) {
			$fields = array();
			foreach($data as $k=>$v) {
				switch (substr($v, 0, 2)) {
					case '+=':
						$v = substr($v,2);
						if (is_numeric($v)) {
							$fields[] = $this->addSpecialChar($k).'='.$this->addSpecialChar($k).'+'.$this->escapeString($v, '', false);
						} else {
							continue;
						}
						
						break;
					case '-=':
						$v = substr($v,2);
						if (is_numeric($v)) {
							$fields[] = $this->addSpecialChar($k).'='.$this->addSpecialChar($k).'-'.$this->escapeString($v, '', false);
						} else {
							continue;
						}
						break;
					default:
						$fields[] = $this->addSpecialChar($k).'='.$this->escapeString($v);
				}
			}
			$field = implode(',', $fields);
		} else {
			return false;
		}
        **/
        $where = $this->parseWhere($where);
		if(empty($table) or empty($where) or empty($data)) {
			return false;
		}
		
		$where = ' WHERE '.$where;
		$field = $this->parseSet($data);
		$sql = 'UPDATE `'.$this->config['database'].'`.`'.$table.'` SET '.$field.$where;
        $this->toLog($sql);
		return $this->execute($sql);
	}
	
	/**
	 * 执行删除记录操作
	 * @param $table 		数据表
	 * @param $where 		删除数据条件,不充许为空。
	 * 						如果要清空表，使用empty方法
	 * @return boolean
	 */
	public function delete($table, $where) {
        if ($table == '' || $where == '') {
			return false;
		}
        $where = $this->parseWhere($where);
        $where && $where = ' WHERE ' . $where . ' ';
		$sql = 'DELETE FROM `'.$this->config['database'].'`.`'.$table.'`'.$where;
		$this->toLog($sql);
		return $this->execute($sql);
	}
	
	/**
	 * 获取最后数据库操作影响到的条数
	 * @return int
	 */
	public function affectedRows() {
		return mysql_affected_rows($this->link);
	}
	
	/**
	 * 获取数据表主键
	 * @param $table 		数据表
	 * @return array
	 */
	public function getPrimary($table) {
		$this->execute("SHOW COLUMNS FROM $table");
		while($r = $this->fetchNext()) {
			if($r['Key'] == 'PRI') break;
		}
		return $r['Field'];
	}

	/**
	 * 获取表字段
	 * @param $table 		数据表
	 * @return array
	 */
	public function getFields($table) {
		$fields = array();
		$this->execute("SHOW FULL COLUMNS FROM $table");
		while($r = $this->fetchNext()) {
			$fields[$r['Field']] = $r;
		}
		return $fields;
	}

	/**
	 * 检查不存在的字段
	 * @param $table 表名
	 * @return array
	 */
	public function checkFields($table, $array) {
		$fields = $this->getFields($table);
		$nofields = array();
		foreach($array as $v) {
			if(!array_key_exists($v, $fields)) {
				$nofields[] = $v;
			}
		}
		return $nofields;
	}

	/**
	 * 检查表是否存在
	 * @param $table 表名
	 * @return boolean
	 */
	public function tableExists($table) {
		$tables = $this->listTables();
		return in_array($table, $tables) ? 1 : 0;
	}
	
	public function listTables() {
		$tables = array();
		$this->execute("SHOW TABLES");
		while($r = $this->fetchNext()) {
			$tables[] = $r['Tables_in_'.$this->config['database']];
		}
		return $tables;
	}

	/**
	 * 检查字段是否存在
	 * @param $table 表名
	 * @return boolean
	 */
	public function fieldExists($table, $field) {
		$fields = $this->getFields($table);
		return array_key_exists($field, $fields);
	}

	public function numRows($sql) {
		$this->lastQueryId = $this->execute($sql);
		return mysql_num_rows($this->lastQueryId);
	}

	public function numFields($sql) {
		$this->lastQueryId = $this->execute($sql);
		return mysql_num_fields($this->lastQueryId);
	}

	public function result($sql, $row) {
		$this->lastQueryId = $this->execute($sql);
		return @mysql_result($this->lastQueryId, $row);
	}

	public function error() {
		return @mysql_error($this->link);
	}

	public function errno() {
		return intval(@mysql_errno($this->link)) ;
	}

	public function version() {
		if(!is_resource($this->link)) {
			$this->connect();
		}
		return mysql_get_server_info($this->link);
	}

	public function close() {
		if (is_resource($this->link)) {
			@mysql_close($this->link);
		}
	}
	
	public function halt($message = '', $sql = '') {
		$this->errormsg = "<b>MySQL Query : </b> $sql <br /><b> MySQL Error : </b>".$this->error()." <br /> <b>MySQL Errno : </b>".$this->errno()." <br /><b> Message : </b> $message <br />";
		$msg = $this->errormsg;
			echo '<div style="font-size:12px;text-align:left; border:1px solid #9cc9e0; padding:1px 4px;color:#000000;font-family:Arial, Helvetica,sans-serif;"><span>'.$msg.'</span></div>';
			exit;
	}

	/**
	 * 对字段两边加反引号，以保证数据库安全
	 * @param $value 数组值
	 */
	public function addSpecialChar(&$value) {
		if('*' == $value || false !== strpos($value, '(') || false !== strpos($value, '.') || false !== strpos ( $value, '`')) {
			//不处理包含* 或者 使用了sql方法。
		} else {
			$value = '`'.trim($value).'`';
		}
		return $value;
	}
	
	/**
	 * 对字段值两边加引号，以保证数据库安全
	 * @param $value 数组值
	 * @param $key 数组key
	 * @param $quotation 
	 */
	public function escapeString(&$value, $key='', $quotation = 1) {
        if($this->_linkID) {
            $value = mysql_real_escape_string($value,$this->_linkID);
        }else{
            $value = mysql_real_escape_string($value);
        }
		if ($quotation) {
			$q = '\'';
		} else {
			$q = '';
		}
		$value = $q.$value.$q;
		return $value;
	}
	protected function parseOrder($order) {
		if(is_array($order)) {
			$array   =  array();
			foreach ($order as $key=>$val){
				if(is_numeric($key)) {
					$array[] =  $val;
				}else{
					$array[] =  '`' . $key . '` '.$val;
				}
			}
			$order   =  implode(',',$array);
		}
		return !empty($order)?  $order:'';
	}
}