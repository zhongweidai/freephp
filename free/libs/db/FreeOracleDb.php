<?php
/**
 *  Oracle数据库操作策略实现类
 *
 * <i>Oracle数据库的使用:</i><br/>
 * 1、像使用普通类库一样使用该组件:
 * <code>
 *  Free::loadClass('OracleDb', PC_PATH . 'libs/db',0);
 * 	$db = new OracleDb();
 *  return $db->query($sql);
 * </code>
 * 2、采用组件配置的方式，通过组件机制调用
 * 在应用配置的component组件配置块中,配置mysqlDb(<i>该名字将决定调用的时候使用的组件名字</i>):
 * <pre>
 *   'OracleDb' => 'free/libs/db/OracleDb',
 * </pre>
 * 在应用中可以通过如下方式获得db对象:
 * <code>
 * $db = $this->getComponent('OracleDb');	//dbCache的名字来自于组件配置中的名字
 * return $db->query($sql);
 * </code>
 *
 * @author
 * @copyright
 * @license
 * @version $Id: OracleDb.php 1 2012-07-13 11:00:00Z $ 
 * @package cache
 */
Free::loadClass('AbstractFreeDb', PC_PATH . 'libs/db', 0);

final class FreeOracleDb extends AbstractFreeDb  {
	
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
	
    protected $_fetchMode    = 'OCI_ASSOC';
	
	protected $_executeMode = null;
	
	public static  $_affectedRows = null;
	
	/**
	 *  统计数据库查询次数
	 */
	public $queryCount = 0;
		
    protected $comparison = array('eq'=>'=','neq'=>'<>','gt'=>'>','egt'=>'>=','lt'=>'<','elt'=>'<=','notlike'=>'NOT LIKE','like'=>'LIKE');
	
	public function __construct() {
		$config = Free::loadConfig('database','oracle');
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
		$func = $this->config['pconnect'] == 1 ? 'oci_pconnect' : 'oci_connect';
		$this -> _setExecuteMode(OCI_COMMIT_ON_SUCCESS);
		if(!$this->link = @$func($this->config['username'], $this->config['password'], '(DESCRIPTION=(CONNECT_DATA=(SERVICE_NAME=' . $this->config['servicename'] . '))(ADDRESS=(PROTOCOL=TCP)(HOST=' . $this->config['hostname'] . ')(PORT=1521)))', $this->config['charset'])) {
			$this->halt('Can not connect to Oracle server');
			return false;
		}
		return $this->link;
	}

	/**
	 * 数据库查询执行方法
	 * @param $sql 要执行的sql语句
	 * @return 查询资源句柄
	 */
	private function execute($sql) {
		if(!is_resource($this->link)) {
			$this->connect();
		}
		if($this->lastQueryId) {
        	$this->free();
        }
        if (is_resource($this->link)) {
        	$this->lastQueryId = oci_parse($this->link, $sql);
        }
        if (!$this->lastQueryId)
        {
        	return false;
        }
        $this->queryCount++;
        
		if (!$this->lastQueryId) {
			return false;
		}
		
        $retval = @oci_execute($this->lastQueryId, $this -> _getExecuteMode());		
        self::$_affectedRows = oci_num_rows($this->lastQueryId);
		if ($retval === false) {
            return false;
        }
		
		return $this->lastQueryId;
	}
	/**
	 * 执行sql查询-针对包含clob类型字段查询
	 * @param $data 		需要查询的字段值[例`name`,`gender`,`birthday`]
	 * @param $table 		数据表
	 * @param $where 		查询条件[例`name`='$name']
	 * @param $limit 		返回结果范围[例：10或10,10 默认为空]
	 * @param $order 		排序方式	[默认按数据库默认方式排序]
	 * @param $group 		分组方式	[默认为空]
	 * @param $key 			返回数组按键名排序
	 * @return array		查询结果集数组
	 */
	public function select($data, $table, $where = array(), $limit = '', $order = array(), $group = '', $key = '') {
		$where = $this->parseWhere($where);
		$where && $where = ' WHERE ' . $where . ' ';
		$order = $this->parseOrder($order);
		$order && $order = ' ORDER BY ' . $order;
		$group = $group == '' ? '' : ' GROUP BY '.$group;
		//$limit = $this->parseLimit($limit);		
		if(empty($data))
		{
			$data = '*';
		}elseif (is_array($data)){
			array_walk($data, array($this, 'addSpecialChar'));
			$data = implode(',', $data);
		}else {
			$field = explode(',', $data);
			array_walk($field, array($this, 'addSpecialChar'));
			$data = implode(',', $field);
		}

        $sql = 'SELECT '.$data.' FROM '.$table.$where.$group.$order;
		if($limit != '') {
			if(strpos($limit, ',')) {
				$arr = explode(',', $limit);				
				$sql = sprintf("SELECT * FROM (
				SELECT r.*, ROWNUM as row_number FROM ( %s ) r
				WHERE ROWNUM <= %d )
				WHERE %d <= row_number",
						$sql,
						$arr[0]+$arr[1],
						$arr[0]+1
						);
			} else {
				$sql = sprintf("SELECT Z1.*, ROWNUM RN 
                        FROM ( %s ) Z1 
                        WHERE ROWNUM <= %d",
						$sql,
						$limit
						);
			}
		}
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
	 * 取clob字段 luyerose
	 *
	 * @see getOne
	 */
	public function getOne($data, $table, $where = '', $order = '', $group = '') {
		$where = $this->parseWhere($where);
		$where && $where = ' WHERE ' . $where . ' ';
		$order = $this->parseOrder($order);
		$order && $order = ' ORDER BY ' . $order;
		$group = $group == '' ? '' : ' GROUP BY '.$group;
		if(empty($data))
		{
			$data = '*';
		}else{
			$field = explode(',', $data);
			array_walk($field, array($this, 'addSpecialChar'));
			$data = implode(',', $field);
		}

		$sql = 'SELECT '.$data.' FROM '.$table.$where.$group.$order;
		$this->execute($sql);
		$res = $this->fetchNext();
		$this->freeResult();
		
		return $res;
	}
	
	/**
	 * 遍历查询结果集
	 * @param $type		返回结果集类型	
	 * 					OCI_ASSOC，OCI_NUM 和 OCI_BOTH
	 * @return array
	 */
	public function fetchNext($type=OCI_ASSOC) {
		$type += OCI_RETURN_NULLS + OCI_RETURN_LOBS;
		$res = @oci_fetch_array($this->lastQueryId, $type);
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
			oci_free_statement($this->lastQueryId);
			$this->lastQueryId = null;
		}
	}
	
	/**
	 * 直接执行sql查询
	 * @param $sql							查询sql语句
	 * @return	boolean/query resource		如果为查询语句，返回资源句柄，否则返回true/false
	 */
	public function query($sql) {
        $re = $this -> execute($sql);
		if($re === false)
		{
			return false;
		}
        if(!is_resource($this->lastQueryId)) {
			return $this->lastQueryId;
		}
		return $re;/*
		$datalist = array();
		while(($rs = $this->fetchNext()) != false) {
			$datalist[] = $rs;
		}
		$this->freeResult();
		print_r($datalist);exit;
		return $datalist;*/
	}
	
	/**
	 * 执行添加记录操作
	 * @param $data 		要增加的数据，参数为数组。数组key为字段值，数组值为数据取值
	 * @param $table 		数据表
	 * @return boolean
	 */
	public function insert($data, $table, $return_insertId = false, $relpace = false) {
		if(!is_array( $data ) || $table == '' || count($data) == 0) {
			return false;
		}
		
		$fielddata = array_keys($data);
		$valuedata = array_values($data);
		array_walk($valuedata, array($this, 'escapeString'));
		foreach ($fielddata as $index=>$key)
		{
			$fielddata[$index] = $this->addSpecialChar($key);
		}		
		$field = implode (',', $fielddata);
		$value = implode (',', $valuedata);
		/**
		 * 自动插入自增长ID
		 */
		$id_key_str = '';
		$id_val_str = '';
		if( $return_insertId && empty($data['ID']) )
		{
			$id_key_str = '"ID",';
			$id_val_str = 'SEQ_'.$table.'.NEXTVAL,';
		}
		$cmd = 'INSERT INTO';
		$sql = $cmd.' '.$table.'('.$id_key_str.$field.') VALUES (' . $id_val_str .$value.')';
		//print_r($sql);
		$return = $this->execute($sql);
		//sql语句入日志
        $this->toLog($sql);
		return  $return_insertId ? $this->insertId($table) : $return;
	}
	
	/**
	 * 获取最后一次添加记录的主键号
	 * @return int 
	 */
	public function insertId($table) {
		$query = $this->query('select SEQ_' . $table . '.currval CURRVAL  from dual');
		$row  = $this->fetchNext(); 
		if( $row && $row['CURRVAL']){
			$this->freeResult();
			return $row['CURRVAL'];
		}else{
			return false;
		}
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
	public function update($data, $table, $where = array()) {
		$where = $this->parseWhere($where);
		if(empty($table) or empty($where) or empty($data)) {
			return false;
		}
		
		$where = ' WHERE '.$where;
		$field = $this->parseSet($data);

		$sql = 'UPDATE '.$table.' SET '.$field.$where;
        //sql语句入日志
        $this->toLog($sql);
		//print_R($sql);exit;
		return $this->execute($sql);
	}

	/**
	 * 执行更新记录操作
	 * 对clob字段更新操作
	 *
	 * @see update
	 * @return boolean
	 */
	public function updateA($column,$val,$table, $where = '') {
		$where = $this->parseWhere($where);
		if($table == '' or $where == '') {
			return false;
		}
		if(!is_resource($this->link)) {
			$this->connect();
		}
		$sql = "UPDATE $table SET $column=EMPTY_CLOB() WHERE $where RETURNING $column INTO :blob";
        $this->toLog($sql);
		$stmt = OCIParse($this->link,$sql);
		$lob  = OCINewDescriptor($this->link,OCI_D_LOB);
		OCIBindByName($stmt,":blob", $lob, -1, OCI_B_CLOB);
		OCIExecute($stmt, OCI_DEFAULT);
		if($lob->save($val))
		{
			OCICommit($this->link);
			OCIFreeDesc($lob);  
			OCIFreeStatement($stmt);  
			OCILogoff($this->link);
			return true;
		}
		else
		{
			OCIFreeDesc($lob);  
			OCIFreeStatement($stmt);  
			OCILogoff($this->link);
			return false;
		}
	}
	
	/**
	 * 执行删除记录操作
	 *
	 * @param $table 		数据表
	 * @param $where 		删除数据条件,不充许为空。
	 * 						如果要清空表，使用empty方法
	 * @return boolean
	 */
	public function delete($table, $where) {
		$where = $this->parseWhere($where);
		$where && $where = ' WHERE ' . $where . ' ';
		if ($table == '' || $where == '') {
			return false;
		}
		$sql = 'DELETE FROM '.$table.$where;
        $this->toLog($sql);
		return $this->execute($sql);
	}
	
	/**
	 * 获取最后数据库操作影响到的条数
	 * @return int
	 */
	public function affectedRows() {
		if(self::$_affectedRows) {
			return self::$_affectedRows;
		} else {
			return 0;
		}		
	}
	
	/**
	 * 获取数据表主键
	 * @param $table 		数据表
	 * @return array
	 */
	public function getPrimary($table) {
		$sql = "SELECT col.column_name as pk
		        FROM user_constraints con,  user_cons_columns col 
                WHERE con.constraint_name = col.constraint_name 
                AND con.constraint_type='P' 
                AND col.table_name = '$table'";
		$this->execute($sql);
		$result = array();
		while($r = $this->fetchNext()) {
			$result[] = $r['PK'];
		}
		return $result;
	}
	
	/**
	 * 获取表字段
	 * @param $table 		数据表
	 * @return array
	 */
	public function getFields($table) {
        $sql = "SELECT COLUMN_NAME,DATA_TYPE,DATA_PRECISION,DATA_SCALE,NULLABLE,DATA_DEFAULT,CHAR_LENGTH
                FROM user_tab_columns 
                WHERE table_name ='$table'";

        $fields = array();
        $this->execute($sql);
        while ($r = $this->fetchNext())
        {
            $fields[$r['COLUMN_NAME']] = $r;
        }
        return $fields;
    }

	/**
	 * 检查不存在的字段
	 *
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
	 *
	 * @param $table 表名
	 * @return boolean
	 */
	public function tableExists($table) {
		$tables = $this->listTables();
		return in_array($table, $tables) ? 1 : 0;
	}
	
	public function listTables() {
		$tables = array();
		$sql = "SELECT * FROM user_tables";
		$this->execute($sql);
		while($r = $this->fetchNext()) {
			$tables[] = $r["TABLE_NAME"];
		}
		return $tables;
	}

	/**
	 * 检查字段是否存在
	 *
	 * @param $table 表名
	 * @return boolean
	 */
	public function fieldExists($table, $field) {
		$fields = $this->getFields($table);
		return array_key_exists($field, $fields);
	}

	/**
	 * 返回结果的行数
	 *
	 * @param unknown_type $sql
	 */
	public function numRows($sql) {
		$this->lastQueryId = $this->execute($sql);
		return oci_num_rows($this->lastQueryId);
	}
	
    /**
     * 返回结果的列数
	 *
     * @param unknown_type $sql
     */
	public function numFields($sql) {
		$this->lastQueryId = $this->execute($sql);
		return oci_num_fields($this->lastQueryId);
	}
	
    /**
     * 返回一行中的某列
	 *
     * @param unknown_type $sql
     * @param unknown_type $row
     */
	public function result($sql, $row) {
		$this->lastQueryId = $this->execute($sql);
		return @oci_result($this->lastQueryId, $row);
	}
	
    /**
     * 错误信息
     */
	public function error() {
		$error = @oci_error($this->link);
		return $error['message'];
	}
	
    /**
     * 错误编码
     */
	public function errno() {
		$error = @oci_error($this->link);
		return $error['code'];
	}
	
    /**
     * 版本信息
     */
	public function version() {
		if(!is_resource($this->link)) {
			$this->connect();
		}
		return oci_server_version($this->link);
	}
	
    /**
     * 关闭连接
     */
	public function close() {
		if (is_resource($this->link)) {
			@oci_close($this->link);
		}
	}
	
	/** 
	 * 展示报错信息
	 *
	 * @param unknown_type $message
	 * @param unknown_type $sql
	 */
	public function halt($message = '', $sql = '') {
		if(is_array($message)) {
			$message = $message['message'];
		}
		$this->errormsg = "<b>Oracle Query : </b> $sql <br /><b> Oracle Error : </b>".$this->error()." <br /> <b>Oracle Errno : </b>".$this->errno()." <br /><b> Message : </b> $message <br />";
		$msg = $this->errormsg;
			echo '<div style="font-size:12px;text-align:left; border:1px solid #9cc9e0; padding:1px 4px;color:#000000;font-family:Arial, Helvetica,sans-serif;"><span>'.$msg.'</span></div>';
			exit;
	}
	/**
	 * 对字段两边加反引号，以保证数据库安全
	 *
	 * @param $value 数组值
	 */
	public function addSpecialChar(&$value) {
		if('*' == $value || false !== strpos($value, '(') || false !== strpos($value, '.') || false !== strpos ( $value, '`')) {
			//不处理包含* 或者 使用了sql方法。
		} else {
			$value = trim($value);
		}
		return '"' . $value . '"';
	}
	
	/**
	 * 对字段值两边加引号，以保证数据库安全
	 *
	 * @param $value 数组值
	 * @param $key 数组key
	 * @param $quotation 
	 */
	public function escapeString(&$value, $key='', $quotation = 1) {
		if ($quotation && $value !== "SYSDATE" && substr($value, 0,7) !== "TO_DATE" && $value !== "sysdate" && strpos($value,'.nextval')== false) {
			$q = '\'';
		} else {
			$q = '';
		}
		$value = $q.$value.$q;
		return $value;
	}

    /**
	 * @return int
	 */
	public function _getExecuteMode()
    {
        return $this->_executeMode;
    }
	
    /**
	 * @param integer $mode
	 */
	private function _setExecuteMode($mode)
    {
        switch($mode)
        {
            case OCI_COMMIT_ON_SUCCESS:
            case OCI_DEFAULT:
            case OCI_DESCRIBE_ONLY:
                $this->_executeMode = $mode;
                break;
            default:
            	return false;
                break;
        }
    }
	
    /**
     * 释放查询结果
     */
    public function free() 
    {
        oci_free_statement($this->lastQueryId);
        $this->lastQueryId = null;
    }
	
    /**
     * 获取下一个插入id
     */
    public function getAutoId($table) 
    {
        $sql = "SELECT " . $table . ".NEXTVAL ID FROM dual";
		$this->execute($sql);
		$res = $this->fetchNext(OCI_ASSOC);
		$this->freeResult();
		return $res['ID'];
    }
    protected function parseOrder($order) {
    	if(is_array($order)) {
    		$array   =  array();
    		foreach ($order as $key=>$val){
    			if(is_numeric($key)) {
    				$array[] =  $val;
    			}else{
    				$array[] =  '"' . $key . '" '.$val;
    			}
    		}
    		$order   =  implode(',',$array);
    	}
    	return !empty($order)?  $order:'';
    }

}
?>