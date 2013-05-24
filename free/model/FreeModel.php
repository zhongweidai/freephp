<?php
/**
 * 数据模型基类
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package base
 */
class FreeModel extends AbstractFreeModel{
	//array(field,rule,message,condition,function,type,where)
	protected $_validate = array();//

	public function __construct($table_name='') {
		$this->db_tablepre = Free::loadConfig('system','tablepre');
		!empty($table_name) && $this->table_name = $table_name;
		$this->table_name = $this->db_tablepre .strtoupper($this->table_name);
		$this->db = $this->getComponent('db');
	}
		
	/**
	 * 执行sql查询
	 * @param $where 		查询条件[例`name`='$name']
	 * @param $data 		需要查询的字段值[例`name`,`gender`,`birthday`]
	 * @param $limit 		返回结果范围[例：10或10,10 默认为空]
	 * @param $order 		排序方式	[默认按数据库默认方式排序]
	 * @param $group 		分组方式	[默认为空]
	 * @param $key          返回数组按键名排序
	 * @return array		查询结果集数组
	 */
	final public function select($where = array(), $data = array(), $limit = '', $order = '', $group = '', $key='') {
		return $this->db->select($data, $this->table_name, $where, $limit, $order, $group, $key);
	}
	/**
	 * 查询多条数据并分页
	 * @param $where
	 * @param $order
	 * @param $page
	 * @param $pagesize
	 * @return unknown_type
	 */
	final public function listInfo($where = array(), $data=array(), $order = '', $page = 1, $pagesize = 0, $key='',$page_style='default', $setpages = 10,$urlrule = '') {
		$page_class = $this->getComponent('page');
		$this->number = $this->count($where);
		$page = max(intval($page), 1);

		$this->pages = $page_class->show($this->number, $page, $pagesize, $page_style,$setpages,$urlrule);
		$pagesize = $page_class->getPageRow();
		$offset = $pagesize*($page-1);
		$array = array();
		if ($this->number > 0) {
			return $this->select($where, $data, "$offset, $pagesize", $order, '', $key);
		} else {
			return array();
		}
	}

	/**
	 * 获取单条记录查询
	 * @param $where 		查询条件
	 * @param $data 		需要查询的字段值[例`name`,`gender`,`birthday`]
	 * @param $order 		排序方式	[默认按数据库默认方式排序]
	 * @param $group 		分组方式	[默认为空]
	 * @return array/null	数据查询结果集,如果不存在，则返回空
	 */
	final public function getOne($where = array(), $data = array(), $order = '', $group = '') {
		return $this->db->getOne($data, $this->table_name, $where, $order, $group);
	}
	/**
	 * 直接执行sql查询
	 * @param $sql							查询sql语句
	 * @return	boolean/query resource		如果为查询语句，返回资源句柄，否则返回true/false
	 */
	final public function query($sql) {
		//$sql = str_replace('phpcms_', $this->db_tablepre, $sql);
		return $this->db->query($sql);
	}
	
	/**
	 * 执行添加记录操作
	 * @param $data 		要增加的数据，参数为数组。数组key为字段值，数组值为数据取值
	 * @param $return_insert_id 是否返回新建ID号
	 * @param $replace 是否采用 replace into的方式添加数据
	 * @return boolean
	 */
	final public function insert($data, $return_insert_id = false, $replace = false) {
		if($this->validation($data,1))
		{
			$this->deal($data,1);
			return $this->db->insert($data, $this->table_name, $return_insert_id, $replace);
		}else{
			return false;
		}
	}
	
	/**
	 * 获取最后一次添加记录的主键号
	 * @return int 
	 */
	final public function insertId() {
		return $this->db->insertId();
	}
	
	/**
	 * 执行更新记录操作
	 * @param $data 		要更新的数据内容，参数可以为数组也可以为字符串，建议数组。
	 * 						为数组时数组key为字段值，数组值为数据取值
	 * 						为字符串时[例：`name`='phpcms',`hits`=`hits`+1]。
	 *						为数组时[例: array('name'=>'phpcms','password'=>'123456')]
	 *						数组的另一种使用array('name'=>'+=1', 'base'=>'-=1');程序会自动解析为`name` = `name` + 1, `base` = `base` - 1
	 * @param $where 		更新数据时的条件,可为数组或字符串
	 * @return boolean
	 */
	final public function update($data, $where = array()) {
		$this->where = $where;
		if($this->validation($data,2))
		{
			$this->deal($data,2);
			return $this->db->update($data, $this->table_name, $where);
		}else{
			return false;
		}
	}

	
	/**
	 * 执行删除记录操作
	 * @param $where 		删除数据条件,不充许为空。
	 * @return boolean
	 */
	final public function delete($where=array()) {
		return $this->db->delete($this->table_name, $where);
	}
	
	/**
	 * 计算记录数
	 * @param string/array $where 查询条件
	 */
	public function count($where = array()) {
		$r = $this->getOne($where, "COUNT(*) AS TNUM");
		return $r['TNUM'];
	}
	
	/**
	 * 获取最后数据库操作影响到的条数
	 * @return int
	 */
	final public function affectedRows() {
		return $this->db->affectedRows();
	}
	
	/**
	 * 获取数据表主键
	 * @return array
	 */
	public function getPrimary() {
		return $this->db->getPrimary($this->table_name);
	}
	
	/**
	 * 获取表字段
	 * @param string $table_name    表名
	 * @return array
	 */
	final public function getFields($table_name = '') {
		if (empty($table_name)) {
			$table_name = $this->table_name;
		} else {
			$table_name = $this->db_tablepre.$table_name;
		}
		return $this->db->getFields($table_name);
	}
	
	/**
	 * 检查表是否存在
	 * @param $table 表名
	 * @return boolean
	 */
	final public function tableExists($table){
		return $this->db->tableExists($this->db_tablepre.$table);
	}
	
	/**
	 * 检查字段是否存在
	 * @param $field 字段名
	 * @return boolean
	 */
	public function fieldExists($field) {
		//$fields = $this->db->get_fields($this->table_name);
		//return array_key_exists($field, $fields);
		return $this->db->fieldExists($this->table_name, $field);
	}
	
	final public function listTables() {
		return $this->db->listTables();
	}
	/**
	 * 返回数据结果集
	 * @param $query （mysql_query返回值）
	 * @return array
	 */
	final public function fetchArray() {
		$data = array();
		while($r = $this->db->fetchNext()) {
			$data[] = $r;		
		}
		return $data;
	}
	
	/**
	 * 返回数据库版本号
	 */
	final public function version() {
		return $this->db->version();
	}
	/**
	 * 执行更新记录操作
	 * 对clob字段更新操作
	 *
	 * @see update
	 * @return boolean
	 */
	final public function updateA($column,$val,$table, $where = '') 
	{
		return $this->db->updateA($column,$val,$table, $where);
	}
}
?>