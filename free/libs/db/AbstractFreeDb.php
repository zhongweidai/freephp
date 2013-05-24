<?php
/**
 * 数据库基础类
 * 
 * 该基类继承了框架的基类FreeBase,用以提供实现组件的一些特性.同时该类作为数据库策略的基类定义了通用的对方访问接口,及子类需要实现的抽象接口.
 *
 * @author
 * @copyright
 * @license
 * @version $Id: AbstractFreeDb.php 1 2012-07-13 11:00:00Z $ 
 * @package cache
 */

abstract class AbstractFreeDb extends FreeBase {
	
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
	


	/**
	 * 执行sql查询
	 *
	 * @param $data 		需要查询的字段值[例`name`,`gender`,`birthday`]
	 * @param $table 		数据表
	 * @param $where 		查询条件[例`name`='$name']
	 * @param $limit 		返回结果范围[例：10或10,10 默认为空]
	 * @param $order 		排序方式	[默认按数据库默认方式排序]
	 * @param $group 		分组方式	[默认为空]
	 * @param $key 			返回数组按键名排序
	 *
	 * @return array		查询结果集数组
	 */
	abstract public function select($data, $table, $where = '', $limit = '', $order = '', $group = '', $key = '');

	/**
	 * 获取单条记录查询
	 *
	 * @param $data 		需要查询的字段值[例`name`,`gender`,`birthday`]
	 * @param $table 		数据表
	 * @param $where 		查询条件
	 * @param $order 		排序方式	[默认按数据库默认方式排序]
	 * @param $group 		分组方式	[默认为空]
	 *
	 * @return array/null	数据查询结果集,如果不存在，则返回空
	 */
	abstract public function getOne($data, $table, $where = '', $order = '', $group = '');
	
	/**
	 * 遍历查询结果集
	 *
	 * @param $type		返回结果集类型	MYSQL_ASSOC，MYSQL_NUM 和 MYSQL_BOTH
	 *
	 * @return array
	 */
	abstract public function fetchNext($type='');
	
	/**
	 * 释放查询资源
	 *
	 * @return void
	 */
	abstract public function freeResult();
	
	/**
	 * 直接执行sql查询
	 *
	 * @param $sql							
	 * 查询sql语句
	 *
	 * @return	boolean/query resource		如果为查询语句，返回资源句柄，否则返回true/false
	 */
	abstract public function query($sql);
	
	/**
	 * 执行添加记录操作
	 *
	 * @param $data 		要增加的数据，参数为数组。数组key为字段值，数组值为数据取值
	 * @param $table 		数据表
	 *
	 * @return boolean
	 */
	abstract public function insert($data, $table, $return_insert_id = false, $replace = false);
	
	/**
	 * 获取最后一次添加记录的主键号
	 *
	 * @return int 
	 */
	abstract public function insertId($table_name);
	
	/**
	 * 执行更新记录操作
	 *
	 * @param $data 		要更新的数据内容，参数可以为数组也可以为字符串，建议数组。
	 * 						为数组时数组key为字段值，数组值为数据取值
	 * 						为字符串时[例：`name`='phpcms',`hits`=`hits`+1]。
	 *						为数组时[例: array('name'=>'phpcms','password'=>'123456')]
	 *						数组可使用array('name'=>'+=1', 'base'=>'-=1');程序会自动解析为`name` = `name` + 1, `base` = `base` - 1
	 * @param $table 		数据表
	 * @param $where 		更新数据时的条件
	 *
	 * @return boolean
	 */
	abstract public function update($data, $table, $where = '');
	
	/**
	 * 执行删除记录操作
	 *
	 * @param $table 		数据表
	 * @param $where 		删除数据条件,不充许为空。
	 * 						如果要清空表，使用empty方法
	 *
	 * @return boolean
	 */
	abstract public function delete($table, $where);
	
	/**
     * 关闭连接
     */
	abstract public function close();
	
	/**
     * 错误信息
     */
	abstract public function error();
	
    /**
     * 错误编号
     */
	abstract public function errno();

	/**
	 * 对字段两边加反引号，以保证数据库安全
	 * @param $value 数组值
	 */
	abstract public function addSpecialChar(&$value);
	
	/**
	 * 对字段值两边加引号，以保证数据库安全
	 *
	 * @param $value 数组值
	 * @param $key 数组key
	 * @param $quotation 
	 */
	abstract public function escapeString(&$value, $key='', $quotation = 1);
	
	/**
	 * 数据库连接错误展现
	 */
	public function halt($message = '', $sql = '') {
		$this->errormsg = "<b>SQL Query : </b> $sql <br /><b> SQL Error : </b>".$this->error()." <br /> <b>MySQL Errno : </b>".$this->errno()." <br /><b> Message : </b> $message <br />";
		$msg = $this->errormsg;
		$this->showErrorMessage('003',$msg);
	}
	/**
     +----------------------------------------------------------
     * 字段名分析
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $key
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseKey(&$key) {
        return $key;
    }
	/**
     +----------------------------------------------------------
     * value分析
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $value
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseValue($value) {
        if(is_string($value)) {
           $value = $this->escapeString($value);
        }elseif(isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp'){
            $value   =  $this->escapeString($value[1]);
        }elseif(is_array($value)) {
            $value   =  array_map(array($this, 'parseValue'),$value);
        }elseif(is_null($value)){
            $value   =  'null';
        }
        return $value;
    }
	/**
     +----------------------------------------------------------
     * update set分析
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $value
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
	protected function parseSet($data) 
	{
		$fields = '';
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
		return $field;
	}
	protected function parseOrder($order) {
        if(is_array($order)) {
            $array   =  array();
            foreach ($order as $key=>$val){
                if(is_numeric($key)) {
                    $array[] =  $val;
                }else{
                    $array[] =  $key .' '.$val;
                }
            }
            $order   =  implode(',',$array);
        }
        return !empty($order)?  $order:'';
    }
	/**
     +----------------------------------------------------------
     * where分析
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $where
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseWhere($where) {
        $whereStr = '';
        if(is_string($where)) {
            // 直接使用字符串条件
            $whereStr = $where;
        }else{ // 使用数组或者对象条件表达式
            if(isset($where['_logic'])) {
                // 定义逻辑运算规则 例如 OR XOR AND NOT
                $operate    =   ' '.strtoupper($where['_logic']).' ';
                unset($where['_logic']);
            }else{
                // 默认进行 AND 运算
                $operate    =   ' AND ';
            }
            foreach ($where as $key=>$val){
                $whereStr .= '( ';
                if(0===strpos($key,'_')) {
                    // 解析特殊条件表达式
                    $whereStr   .= $this->parseAdsWhere($key,$val);
                }else{
                    // 查询字段的安全过滤
                    if(!preg_match('/^[A-Z_\|\&\-.a-z0-9\(\)\,]+$/',trim($key))){
                         throw new FreeException('_EXPRESS_ERROR_:'.$key);
                    }
                    // 多条件支持
                    $multi = is_array($val) &&  isset($val['_multi']);
                    $key = trim($key);
                    if(strpos($key,'|')) { // 支持 name|title|nickname 方式定义查询字段
                        $array   =  explode('|',$key);
                        $str   = array();
                        foreach ($array as $m=>$k){
                            $v =  $multi?$val[$m]:$val;
                            $str[]   = '('.$this->parseWhereItem($this->parseKey($k),$v).')';
                        }
                        $whereStr .= implode(' OR ',$str);
                    }elseif(strpos($key,'&')){
                        $array   =  explode('&',$key);
                        $str   = array();
                        foreach ($array as $m=>$k){
                            $v =  $multi?$val[$m]:$val;
                            $str[]   = '('.$this->parseWhereItem($this->parseKey($k),$v).')';
                        }
                        $whereStr .= implode(' AND ',$str);
                    }else{
                        $whereStr   .= $this->parseWhereItem($this->parseKey($key),$val);
                    }
                }
                $whereStr .= ' )'.$operate;
            }
            $whereStr = substr($whereStr,0,-strlen($operate));
        }
        return $whereStr;
    }

    // where子单元分析
    protected function parseWhereItem($key,$val) {
        $whereStr = '';
        if(is_array($val)) {
            if(is_string($val[0])) {
                if(preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT|NOTLIKE|LIKE)$/i',$val[0])) { // 比较运算
                    $whereStr .= $key.' '.$this->comparison[strtolower($val[0])].' '.$this->parseValue($val[1]);
                }elseif('exp'==strtolower($val[0])){ // 使用表达式
                    $whereStr .= ' ('.$key.' '.$val[1].') ';
                }elseif(preg_match('/IN/i',$val[0])){ // IN 运算
                    if(isset($val[2]) && 'exp'==$val[2]) {
                        $whereStr .= $key.' '.strtoupper($val[0]).' '.$val[1];
                    }else{
                        if(is_string($val[1])) {
                             $val[1] =  explode(',',$val[1]);
                        }
                        $zone   =   implode(',',$this->parseValue($val[1]));
                        $whereStr .= $key.' '.strtoupper($val[0]).' ('.$zone.')';
                    }
                }elseif(preg_match('/BETWEEN/i',$val[0])){ // BETWEEN运算
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    $whereStr .=  ' ('.$key.' '.strtoupper($val[0]).' '.$this->parseValue($data[0]).' AND '.$this->parseValue($data[1]).' )';
                }else{
                    throw new FreeException('_EXPRESS_ERROR_:'.$val[0]);
                }
            }else {
                $count = count($val);
                if(in_array(strtoupper(trim($val[$count-1])),array('AND','OR','XOR'))) {
                    $rule = strtoupper(trim($val[$count-1]));
                    $count   =  $count -1;
                }else{
                    $rule = 'AND';
                }
                for($i=0;$i<$count;$i++) {
                    $data = is_array($val[$i])?$val[$i][1]:$val[$i];
                    if('exp'==strtolower($val[$i][0])) {
                        $whereStr .= '('.$key.' '.$data.') '.$rule.' ';
                    }else{
                        $op = is_array($val[$i])?$this->comparison[strtolower($val[$i][0])]:'=';
                        $whereStr .= '('.$key.' '.$op.' '.$this->parseValue($data).') '.$rule.' ';
                    }
                }
                $whereStr = substr($whereStr,0,-4);
            }
        }else {
                $whereStr .= $key.' = '.$this->parseValue($val);
        }
        return $whereStr;
    }

    /**
     +----------------------------------------------------------
     * 特殊条件分析
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $key
     * @param mixed $val
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseAdsWhere($key,$val) {
        $whereStr   = '';
        switch($key) {
            case '_string':
                // 字符串模式查询条件
                $whereStr = $val;
                break;
            case '_complex':
                // 复合查询条件
                $whereStr   = substr($this->parseWhere($val),6);
                break;
            case '_query':
                // 字符串模式查询条件
                parse_str($val,$where);
                if(isset($where['_logic'])) {
                    $op   =  ' '.strtoupper($where['_logic']).' ';
                    unset($where['_logic']);
                }else{
                    $op   =  ' AND ';
                }
                $array   =  array();
                foreach ($where as $field=>$data)
                    $array[] = $this->parseKey($field).' = '.$this->parseValue($data);
                $whereStr   = implode($op,$array);
                break;
        }
        return $whereStr;
    }
    
     /**
     +----------------------------------------------------------
     * sql执行记录
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $key
     * @param mixed $val
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
     public function toLog($sql)
     {
        if($sql)
        {
            return $this->getComponent('log_container')->put($sql,'db_sql'); 
        } 
     }
     
}