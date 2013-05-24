<?php
/**
 *  Sybase数据库操作策略实现类
 *
 * <i>Sybase数据库的使用:</i><br/>
 * 1、像使用普通类库一样使用该组件:
 * <code>
 *  Free::loadClass('SybaseDb', PC_PATH . 'libs/db',0);
 * 	$db = new SybaseDb();
 *  return $db->query($sql);
 * </code>
 * 2、采用组件配置的方式，通过组件机制调用
 * 在应用配置的component组件配置块中,配置mysqlDb(<i>该名字将决定调用的时候使用的组件名字</i>):
 * <pre>
 *   'SybaseDb' => 'free/libs/db/SybaseDb',
 * </pre>
 * 在应用中可以通过如下方式获得db对象:
 * <code>
 * $db = $this->getComponent('SybaseDb');	//dbCache的名字来自于组件配置中的名字
 * return $db->query($sql);
 * </code>
 *
 * @author
 * @copyright
 * @license
 * @version $Id: SybaseDb.php 1 2012-07-13 11:00:00Z $ 
 * @package cache
 */
Free::loadClass('AbstractFreeDb', PC_PATH . 'libs/db', 0);

final class FreeSybaseDb extends AbstractFreeDb {

	/**
	 * 数据库配置信息
	 */
	private $config = null;
	
	/**
	 * 数据库连接句柄
	 */
	public static $link = null;
	
	/**
	 * 最近一次查询资源
	 */
	public $lastQueryId = null;
	
	/**
	 * 数据库操作模式
	 */
	protected $_executeMode = null;
	
	/**
	 * 数据库操作所影响的行数
	 */
	public static $_affectedRows = null;
	
	/**
	 * 统计数据库查询次数
	 */
	public $queryCount = 0;
	
	public function __construct() {
		$config = Free::loadConfig('database','sybase');
		$this->open($config);
	}
	
	/**
	 * open--设置数据库连接配置
	 *
	 * @param unknown_type $config
	 */
	public function open($config) {
		$this->config = $config;
		if($config['autoconnect']) $this->connect();
	}

	/**
	 * connect--数据库连接操作
	 *
	 * @return unknown
	 */
	public function connect() {
		$func = $this->config['pconnect'] == 1 ? 'sybase_pconnect' : 'sybase_connect';//是否为持久连接
		self::$link = $func($this->config['hostname'],$this->config['username'],$this->config['password'],$this->config['charset']) or die('Can not connect to Sybase server');//连接服务器
		sybase_select_db($this->config['database'],self::$link) or die('database do not exist');//选择数据库
		return self::$link;
	}
	
	/**
	 * execute--执行数据库sql操作
	 *
	 * @param unknown_type $sql
	 * @return unknown
	 */
	private function execute($sql) {
		if(!$sql) return false;
		if(!is_resource(self::$link)) $this->connect();//如果连接句柄为空，则执行连接操作
		if($this->lastQueryId) $this->free();//如果操作句柄不为空，则释放
        if(is_resource(self::$link)) $this->lastQueryId = sybase_query($sql,self::$link);//执行数据库操作
        if (!$this->lastQueryId) return false;//如果操作资源为空,则返回false
        $this->queryCount++;//操作成功，则增加数据库查询次数
        self::$_affectedRows = sybase_affected_rows();//获取最后一次数据库操作(仅支持insert,update,delete)所影响的行数
		return $this->lastQueryId;//返回操作资源
	}
	
	/**
	 * 
	 * select--数据库查询
	 * 
	 *
	 * @param unknown_type $data
	 * @param unknown_type $table
	 * @param unknown_type $where
	 * @param unknown_type $limit
	 * @param unknown_type $order
	 * @param unknown_type $group
	 * @param unknown_type $key
	 * @return unknown
	 */
	
	//CREATE PROCEDURE SplitPageByLine
	//(
	//--创建一个分页读取过程
	//@SqlStr         varchar(8000),    --SQL语句
	//@FirstRec       int,        --页起始行
	//@LastRec        int        --页结束行
	//)
	//AS
	//DECLARE @dt varchar(10)    --生成临时表的随机数
	//BEGIN
	//SELECT @dt= substring(convert(varchar, rand()), 3, 10)    --一个字符型的随机数
	//--将搜索结果放入临时表中，表名随机生成，在' FROM '前插入'INTO '+随机临时表名
	//SELECT @SqlStr = stuff(@SqlStr, charindex(' FROM ', upper(@SqlStr)), 6 ,' INTO tempdb..Lining' + @dt + ' FROM ')
	//EXECUTE (@SqlStr)
	//--为临时表增加id号
	//SELECT @SqlStr = 'ALTER TABLE tempdb..Lining' + @dt + ' ADD TEMPDB_ID numeric(10) IDENTITY PRIMARY KEY'
	//EXECUTE (@SqlStr)
	//--计算临时表中的记录数
	//--SELECT @SqlStr = 'SELECT Count(*) From tempdb..Lining' + @dt
	//--EXECUTE (@SqlStr)
	//--选取记录号在起始行和结束行中间的记录
	//SELECT @SqlStr = 'SELECT * FROM tempdb..Lining'+@dt+' WHERE TEMPDB_ID > ' + convert(varchar, @FirstRec) + ' and TEMPDB_ID <= ' + convert(varchar, @LastRec)
	//EXECUTE (@SqlStr)
	//--删除临时表
	//SELECT @SqlStr = 'DROP TABLE tempdb..Lining'+@dt
	//EXECUTE (@SqlStr) 
	//END
	
	public function select($data,$table,$where = ' 1=1 ',$limit = '',$order = '',$group = '',$key = '') {
		if(!$data || !$table) return false;
		if($order) $_order = ' ORDER BY '.$order;//排序
		if($group) $_group = ' GROUP BY '.$group;//分组
		//查询条件处理
		if($where){
			if(is_array($where)) foreach ($where as $k => $v) $_where .= $k.' = \''.$v.'\' AND ';//如果查询条件是数组
			else $_where = $where;//如果是字符串
			$_where = ' WHERE '.trim($this->sqlIn($_where),' AND ');
		}
		//拼接sql语句
		$sql = 'SELECT '.trim($this->sqlIn($data),',').' FROM '.$this->sqlIn($table).' '.$_where.$_group.$_order;
		//分页处理
		if($limit){
			if(strpos($limit, ',')){//如果是 limit 10,10 格式
				$limit = explode(',',$limit);
				$start = $limit[0];
				$pagesize = $limit[1];
			}
			else {//如果是 limit 10 格式
				$start = 0;
				$pagesize = $limit;
			}
			$sql = 'EXEC SplitPageByLine "'.$sql.'",'.$start.','.$pagesize;//执行分页存储过程
		}
		$this->execute($sql);//执行sql查询
		if(!is_resource($this->lastQueryId)) return $this->lastQueryId;
		//结果集处理--提取需要的字段
		$datalist = array();
		while($rs = $this->fetchNext()) {
			if($key) $datalist[$rs[$key]] = $rs;//提取所需字段
			else $datalist[] = $rs;
		}
		$this->freeResult();//释放查询资源
		return $datalist;//返回结果
	}
	
	/**
	 * selectA--select的重命名
	 *
	 * @param unknown_type $data
	 * @param unknown_type $table
	 * @param unknown_type $where
	 * @param unknown_type $limit
	 * @param unknown_type $order
	 * @param unknown_type $group
	 * @param unknown_type $key
	 */
	public function selectA($data, $table, $where = '', $limit = '', $order = '', $group = '', $key = '') {
		return $this->select($data,$table,$where,$limit,$order,$group,$key);//直接调用select
	}
	
	/**
	 * getOne--获取单条查询记录
	 *
	 * @param unknown_type $data
	 * @param unknown_type $table
	 * @param unknown_type $where
	 * @param unknown_type $order
	 * @param unknown_type $group
	 */
	public function getOne($data, $table, $where = '', $order = '', $group = '') {
		return $this->select($data,$table,$where,'1',$order,$group);//直接调用select
	}
	
	/**
	 * getOneA--getOne的重命名
	 *
	 * @param unknown_type $data
	 * @param unknown_type $table
	 * @param unknown_type $where
	 * @param unknown_type $order
	 * @param unknown_type $group
	 */
	public function getOneA($data, $table, $where = '', $order = '', $group = '') {
		return $this->getOne($data,$table,$where,$order,$group);//直接调用getOne
	}	

	/**
	 * fetchNext--获取数据库操作返回值
	 *
	 * @param unknown_type $type
	 * @return unknown
	 */
	public function fetchNext($type = '') {
		$fetch = sybase_fetch_array($this->lastQueryId);
		if(!$fetch) $this->freeResult();
		return $fetch;
	}
	
	/**
	 * freeResult--释放数据库操作资源
	 *
	 */
	public function freeResult() {
		if(is_resource($this->lastQueryId)) {
			sybase_free_result($this->lastQueryId);//释放操作资源
			$this->lastQueryId = null;
		}
	}
	
	/**
	 * query--数据库执行sql语句
	 *
	 * @param unknown_type $sql
	 * @return unknown
	 */
	public function query($sql) {
		if(!$sql) return false;
        $this -> execute($sql);//执行sql查询操作
        if(!is_resource($this->lastQueryId)) return $this->lastQueryId;//查询资源判断
		$datalist = array();
		while($rs = $this->fetchNext()) $datalist[] = $rs;//数据处理
		$this->freeResult();
		return $datalist;
	}
	
	/**
	 * insert--数据库插入操作
	 *
	 * @param unknown_type $data 必须为数组array('key'=>'value');
	 * @param unknown_type $table
	 * @param unknown_type $return_insertId
	 * @param unknown_type $relpace
	 * @return unknown
	 */
	public function insert($data, $table, $return_insertId = false, $relpace = false) {
		if(!is_array($data) || $table == '' || count($data) == 0) return false;
		$field = $value ='';
		//字段和值处理
		foreach ($data as $k => $v) {
			$field .= $k.',';
			$value .= '\''.$this->sqlIn($v).'\',';
		}
		$sql = 'INSERT INTO '.$table.' ('.trim($field,',').') VALUES ('.trim($value,',').')';//拼接sql语句
		$return = $this->execute($sql);//执行sql语句
		return $return_insertId?$this->insertId($table):$return;
	}
	
	/**
	 * insertId--返回插入操作时的id
	 *
	 * @param unknown_type $table
	 * @return unknown
	 */
	public function insertId($table) {
        return 0;
	}
	
	/**
	 * update--数据库更新操作
	 *
	 * @param unknown_type $data
	 * @param unknown_type $table
	 * @param unknown_type $where--不能是数组,目前只支持字符串
	 * @return unknown
	 */
	public function update($data, $table, $where = ' 1=1 ') {
		if(!$data || !$table || !$where) return false;
		$field = '';
		if(is_array($data)){//如果更新数据为数组
			foreach ($data as $k => $v){
				switch (substr($v, 0, 2)){
					case '+='://a = a + 1;
						$v = substr($v,2);
						if(is_numeric($v)) $field .= $k.' = '.$k.' + '.$v.',';
						break;
					case '-='://a = a -1;
						$v = substr($v,2);
						if(is_numeric($v)) $field .= $k.' = '.$k.' - '.$v.',';
						break;
					default:
						$field .= $k.' = \''.$this->sqlIn($v).'\',';
				}
			}
		}
		else $field = $this->sqlIn($data);//如果更新数据为字符串
		$sql = 'UPDATE '.$table.' SET '.trim($field,',').' WHERE '.$this->sqlIn($where);
		return $this->execute($sql);
	}

	/**
	 * updateA--对某一个字段进行更新
	 *
	 * @param unknown_type $column
	 * @param unknown_type $val
	 * @param unknown_type $table
	 * @param unknown_type $where
	 * @return unknown
	 */
	public function updateA($column,$val,$table, $where = ' 1=1 ') {
		return $this->update(array($column=>$val),$table,$where);//直接调用updata方法
	}
	
	/**
	 * delete--数据库删除操作
	 *
	 * @param unknown_type $table
	 * @param unknown_type $where
	 * @return unknown
	 */
	public function delete($table, $where) {
		if (!$table || !$where) return false;
		$sql = 'DELETE FROM '.$table.' WHERE '.$this->sqlIn($where);
		return $this->execute($sql);
	}
	
	/**
	 * affectedRows--获取上一数据库操作所影响的行数
	 *
	 * @return unknown
	 */
	public function affectedRows() {
		if(self::$_affectedRows) return self::$_affectedRows;
		else return 0;
	}
	
	/**
	 * getPrimary--获取表的主键--只会增加数据库负担,完全可以写在model层中
	 *
	 * @param unknown_type $table
	 * @return unknown
	 */
	public function getPrimary($table) {
		$sql = 'SELECT name AS PK FROM sysindexes WHERE id = object_id(\''.$table.'\') AND indid >=1 AND status > 2048';//获取表的主键
		$this->execute($sql);
		$result = array();
		while($r = $this->fetchNext()) $result[] = $r['PK'];
		return $result;
	}

	/**
	 * getFields--获取表的字段--只会增加数据库负担,完全可以写在model层中
	 *
	 * @param unknown_type $table
	 * @return unknown
	 */
	public function getFields($table) {
		if(!$table) return false;
		$sql = 'SELECT a.name AS COLUMN_NAME FROM syscolumns a INNER JOIN sysobjects d ON a.id=d.id AND d.type=\'U\' AND d.name<>\'dtproperties\' WHERE d.name=\''.$table.'\'';//获取表的字段
		$fields = array();
		$this->execute($sql);
		while($r = $this->fetchNext()) $fields[] = $r['COLUMN_NAME'];
		return $fields;
	}

	/**
	 * checkFields--判断字段是否在表中存在--只会增加数据库负担,完全可以写在model层中
	 *
	 * @param unknown_type $table
	 * @param unknown_type $array
	 * @return unknown
	 */
	public function checkFields($table, $array) {
		if(!$table || !$array) return false;
		$fields = $this->getFields($table);
		$nofields = array();
		foreach($array as $v) if(!array_key_exists($v, $fields)) $nofields[] = $v;
		return $nofields;
	}
	
	/**
	 * tableExists--判断表是否存在--只会增加数据库负担,完全可以写在model层中
	 *
	 * @param unknown_type $table
	 * @return unknown
	 */
	public function tableExists($table) {
		if($table) return false;
		$tables = $this->listTables();
		return in_array($table, $tables)?1:0;
	}
	
	/**
	 * listTables--获取数据库中用户表--只会增加数据库负担,完全可以写在model层中
	 *
	 * @return unknown
	 */
	public function listTables() {
		$tables = array();
		$sql = 'SELECT name AS TABLE_NAME FROM sysobjects WHERE type =\'U\'';//获取数据库中的用户表
		$this->execute($sql);
		while($r = $this->fetchNext()) $tables[] = $r['TABLE_NAME'];
		return $tables;
	}

	/**
	 * fieldExists--判断该字段是否存在--只会增加数据库负担,完全可以写在model层中
	 *
	 * @param unknown_type $table
	 * @param unknown_type $field
	 * @return unknown
	 */
	public function fieldExists($table, $field) {
		if(!$table || !$field) return false;
		$fields = $this->getFields($table);
		return array_key_exists($field, $fields);
	}
	
	/**
	 * numRows--返回结果的行数
	 *
	 * @param unknown_type $sql
	 * @return unknown
	 */
	public function numRows($sql) {
		$this->lastQueryId = $this->execute($sql);//执行sql查询
		return sybase_num_rows($this->lastQueryId);//returns the number of rows in a result set
	}
	
    /**
     * numFields--返回结果的列数
     *
     * @param unknown_type $sql
     * @return unknown
     */
	public function numFields($sql) {
		$this->lastQueryId = $this->execute($sql);//执行sql查询
		return sybase_num_fields($this->lastQueryId);//returns the number of fields in a result set
	}
	
	/**
	 * result--返回一行中的某一列
	 *
	 * @param unknown_type $sql
	 * @param unknown_type $row
	 * @return unknown
	 */
	public function result($sql,$row) {
		$this->lastQueryId = $this->execute($sql);//执行sql查询
		$back = array();
		for ($i = 0; $i < sybase_num_rows($this->lastQueryId); $i++) $back[] = sybase_result($this->lastQueryId,$i,$row);//数据处理
		return $back;
	}
	
	/**
	 * error--错误信息--sybase没有对应的方法
	 *
	 * @return unknown
	 */
	public function error() {
		return '';
	}
	
	/**
	 * errno--错误编码--sybase没有对应的方法
	 *
	 * @return unknown
	 */
	public function errno() {
		return 0;
	}
	
	/**
	 * version--获取数据库版本信息
	 *
	 * @return unknown
	 */
	public function version() {
		$sql = 'SELECT @@version';
		return $this->query($sql);
	}
    
	/**
	 * close--关闭连接
	 *
	 */
	public function close() {
		if (is_resource(self::$link)) sybase_close(self::$link);//关闭数据库连接
	}
	
	/**
	 * halt--展示报错信息
	 *
	 * @param unknown_type $message
	 * @param unknown_type $sql
	 */
	public function halt($message = '', $sql = '') {
		if(is_array($message)) $message = $message['message'];
		$this->errormsg = "<b>Oracle Query : </b> $sql <br /><b> Oracle Error : </b>".$this->error()." <br /> <b>Oracle Errno : </b>".$this->errno()." <br /><b> Message : </b> $message <br />";
		$msg = $this->errormsg;
		echo '<div style="font-size:12px;text-align:left; border:1px solid #9cc9e0; padding:1px 4px;color:#000000;font-family:Arial, Helvetica,sans-serif;"><span>'.$msg.'</span></div>';
		exit;
	}

	/**
	 * addSpecialChar--字段处理
	 *
	 * @param unknown_type $value
	 * @return unknown
	 */
	public function addSpecialChar(&$value) {
		/*
		这种写法让人很纠结哇～～～～
		if('*' == $value || false !== strpos($value, '(') || false !== strpos($value, '.') || false !== strpos ( $value, '`')) {} 
		else $value = trim($value);
		*/
		if($value != '*' && strpos($value, '(') && strpos($value, '.') && strpos( $value, '`')) $value = trim($value);
		return $value;
	}
	
	/**
	 * escapeString--对字段值两边加引号，以保证数据库安全
	 *
	 * @param unknown_type $value
	 * @param unknown_type $key
	 * @param unknown_type $quotation
	 * @return unknown
	 */
	public function escapeString(&$value, $key='', $quotation = 1) {
		if ($quotation && $value !== 'SYSDATE' && substr($value, 0,7) !== 'TO_DATE' && $value !== 'sysdate' && strpos($value,'.nextval')== false) $q = '\'';
		else $q = '';
		$value = $q.$value.$q;
		return $value;
	}

	/**
	 * _getExecuteMode--获取操作模式--这个sybase用不上
	 *
	 * @return unknown
	 */
	public function _getExecuteMode()
    {
        return $this->_executeMode;
    }
	
    /**
     * _setExecuteMode--设置操作模式--这个sybase用不上
     *
     * @param unknown_type $mode
     */
	private function _setExecuteMode($mode)
    {
    	$this->_executeMode = $mode;
    }
	
    /**
     * free--释放操作资源
     *
     */
    public function free() 
    {
        sybase_free_result($this->lastQueryId);
        $this->lastQueryId = null;
    }
    
	/**
	 * getAutoId--获取下一个插入id
	 *
	 * @param unknown_type $table
	 * @return unknown
	 */
    public function getAutoId($table) 
    {
        $sql = "SELECT @@identity AS ID";
		$this->execute($sql);
		$res = $this->fetchNext();
		$this->freeResult();
		return $res['ID'] + 1;
    }
    
    /**
     * sqlIn--剔除sql关键字
     *
     * @param unknown_type $data
     * @return unknown
     */
    protected function sqlIn($data)
	{
		$low = strtolower($data);
		if(strstr($low,' INSERT ') || strstr($low,' SELECT ') || strstr($low,' UPDATE ') || strstr($low,' DELETE ') ) die('sqlIn:sql error');
		return $data;
	}
}