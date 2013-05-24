<?php

/**
 +-----------------------------------------------
 * Mongo数据库驱动类 需要配合MongoModel使用
 +-----------------------------------------------
 */
 Free::loadClass('AbstractFreeDb', PC_PATH . 'libs/db', 0);
class FreeMongoDb extends AbstractFreeDb{

    protected $_db = null; // MongoDb Object
    protected $_collection    = null; // MongoCollection Object
    protected $_db_name = ''; // dbName
    protected $_collection_name = ''; // collectionName
    protected $_cursor   =  null; // MongoCursor Object
    protected $comparison      = array('neq'=>'ne','ne'=>'ne','gt'=>'gt','egt'=>'gte','gte'=>'gte','lt'=>'lt','elt'=>'lte','lte'=>'lte','in'=>'in','not in'=>'nin','nin'=>'nin');
	protected $timeout = 200;//毫秒单位
	
 public function __construct() {
        if (!class_exists('Mongo')) {
            throw new FreeException("The MongoDB PECL extension has not been installed or enabled",'100');
        }
		$configs =Free::loadConfig("cache","mongo_db");
		$num = count($configs['connect']);
		$configs['timeout'] && $this->timeout = $configs['timeout'];
		$keys = Free::loadConfig('double');
		$this->key = intval($keys['mongo_db']);
		$this->config = $configs['connect'][$this->key];
		$status = $this->connect();
		if($status == false)
		{//如果连接失败，则链接下一个服务
			for($i = 1; $i < $num; $i++)
			{
				$n = $this->key + $i;
				$key = $n >= $num ? $n - $num : $n;
				$this->config = $configs['connect'][$key]; 
				$status = $this->connect();
				if($status!=false)
				{
					$keys['mongo_db'] = $key ;
					$this->key = $key;
					$data = "<?php\nreturn ".var_export($keys, true).";\n?>";
					file_put_contents(FREE_PATH.'configs/double.php', $data);
					break;
				}
			}
		}
		if($status==false)
		{
			throw new FreeException("mongoDB not connect",'502');
		}
    }
	
	function __destruct() {

		if($this->connection)
		{
			$this->connection->close();
		}
	}


    /**
     +----------------------------------------------------------
     * 连接数据库方法
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function connect($config='') {
        if ( !isset($this->link) ) {
            if(empty($config))  $config =   $this->config;
            $host = 'mongodb://'
				.($config['username']?"{$config['username']}":'')
				.($config['password']?":{$config['password']}@":'')
				.$config['hostname'].($config['port']?":{$config['port']}":'');
			$this->_db_name = $this->config['database'];
            try{
				$options = array('connect'=>true,'timeout'=>$this->timeout);
                $this->link = new mongo( $host,$options);
				$this->_db = $this->link->{$this->_db_name};
            }catch (MongoConnectionException $e){
                return false;
            }
            // 标记连接成功
            //$this->connected    =   true;
			//unset($this->config);
        }
        return $this->link;
    }

    /**
     +----------------------------------------------------------
     * 切换当前操作的Db
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $db  db
     * @param boolean $master 是否主服务器
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function switchDb($db=''){
        try{
			if(empty($db))
			{
				if(!$this->_mongo)
				{
					// 当前MongoDb对象
					$this->_db_name  = $this->config['database'];
					$this->_db = $this->link->{$this->_db_name};
				}
			}else{
				$this->_db_name =$db;
				$this->_db = $this->link->{$this->_db_name};
			}
			return $this->_db;
        }catch (MongoException $e){
            throw new FreeException("$e->getMessage()",'502');
		}
    }
	/**
	 * 执行sql查询 字段查询
	 * @param $data 		需要查询的字段值[例`name`,`gender`,`birthday`]
	 * @param $table 		数据表
	 * @param $where 		查询条件
	 * @param $limit 		返回结果范围[例：10或array(10,10) 默认为空]
	 * @param $order 		排序方式	[默认按数据库默认方式排序]
	 * @param $group 		分组方式	[默认为空]
	 * @param $key 			返回数组按键名排序
	 * @return array		查询结果集数组
	 */
	public function select($data, $table, $where = array(), $limit = '', $order = array(), $group = '', $key = '')
	{
		if (empty($table)) {
			throw new FreeException("No Mongo collection selected to insert into",'500');
        }
		$results = array();
		$where = $this->parseWhere($where);//var_dump($where);
		$data = $this->parseField($data);
		list($offset,$limit) = $this->parseLimit($limit);
		$order = $this->parseOrder($order);
		try{
			$documents = $this->_db->{$table}->find($where, $data)->limit((int) $limit)->skip((int) $offset)->sort($order);
		}catch(MongoCursorException $e){
			throw new FreeException("select of data into MongoDB failed: {$e->getMessage()}",'502');
		}
		$returns = array();
        foreach ($documents as $doc)
		{
			if($key) {
				$returns[$doc[$key]] = $doc;
			} else {
				$returns[] = $doc;
			}
        }
        return $returns;
	}
	/**
	 * 字段查询
	 * @param $data 		需要查询的字段值[例`name`,`gender`,`birthday`]
	 * @param $table 		数据表
	 * @param $where 		查询条件[例`name`='$name']
	 * @param $limit 		返回结果范围[例：10或10,10 默认为空]
	 * @param $order 		排序方式	[默认按数据库默认方式排序]
	 * @param $group 		分组方式	[默认为空]
	 * @param $key 			返回数组按键名排序
	 * @return array		查询结果集数组
	 */
	public function getOne($data, $table, $where = array(), $order = array(), $group = '') 
	{
		if (empty($table)) {
			throw new FreeException("No Mongo collection selected to insert into",'500');
        }
		$results = array();
		$where = $this->parseWhere($where);
		$data = $this->parseField($data);
		$order = $this->parseOrder($order);
		$results = array();
		try{
			$documents = $this->_db->{$table}->find($where, $data)->limit(1)->skip(0);
		}catch(MongoCursorException $e){
			throw new FreeException("getone of data into MongoDB failed: {$e->getMessage()}",'502');
		}
        $returns = array();
        foreach ($documents as $doc)
		{
			$returns[] = $doc;
        }
		return $returns[0];
	}
	
	public function update($data, $table, $where = array()) 
	{
		$where = $this->parseWhere($where);
		$data = $this->parseSet($data);
		if (empty($data)) {
            $this->error("No Mongo collection selected to update", 500);
        } if (count($data) == 0 || !is_array($data)) {
            $this->error("Nothing to update in Mongo collection or update is not an array", 500);
        } try {
            $this->_db->{$table}->update($where, $data, array('fsync' => TRUE, 'multiple' => FALSE));
            return(TRUE);
        } catch (MongoCursorException $e) {
            throw new FreeException("Update of data into MongoDB failed: {$e->getMessage()}", 500);
        }
	}
	/**
     +----------------------------------------------------------
     * 插入记录
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data 数据
     * @param array $options 参数表达式
     * @param boolean $replace 是否replace
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     */
	public function insert($data, $table, $return_insertId = false, $relpace = false)
	{
		if (empty($table)) {
			throw new FreeException("No Mongo collection selected to insert into",'500');
        } if (count($data) == 0 || !is_array($data)) {
            throw new FreeException("Nothing to insert into Mongo collection or insert is not an array",'500');
        } 
		try {
			/*$re = put_sqs('list_mongo_creaseidsqs_'.$collection,'1');
			if(is_numeric($re)){
				$re++;
				$data[$name] = intval($re);
            }else{
				$data[$name] = intval(time());
                //die('mongosqs error');
            }*/
			//生成自增ID
			$this->registerComponent(array('sqs_redis'=>'free/libs/cache/FreeRedisCache'));
			$data['ID'] =$this->getComponent('sqs_redis')->listPush('list_mongo_creaseidsqs_'.$table, '1');

			//$data['ID'] = intval(time());
            $this->_db->{$table}->insert($data, array('fsync' => TRUE)); 
            //$this->clear();
            return $data['ID'];
        } catch (MongoCursorException $e) {
            throw new FreeException("Insert of data into MongoDB failed: {$e->getMessage()}",'500');
        }
	}
	
	public function count($table,$where = array())
	{
		$where = $this->parseWhere($where);
		if (empty($table)) {
            throw new FreeException("In order to retreive a count of documents from MongoDB, a collection name must be passed", 500);
        } 
		$count = $this->_db->{$table}->find($where)->limit(99999999)->skip(0)->count();
        return($count);
	}

    /**
     +----------------------------------------------------------
     * 执行命令
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $command  指令
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function query($command) {
        $this->queryStr = 'command:'.json_encode($command);
        // 记录开始执行时间
        $result   = $this->_db->command($command);
        if(!$result['ok']) {
            throw new FreeException($result['errmsg']);
        }
        return $result;
    }

    /**
     +----------------------------------------------------------
     * 执行语句
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $code  sql指令
     * @param array $args  参数
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function execute($code,$args=array()) {
        $result   = $this->_db->execute($code,$args);
        if($result['ok']) {
            return $result['retval'];
        }else{
            throw new FreeException($result['errmsg'],500);
        }
    }

    /**
     +----------------------------------------------------------
     * 关闭数据库
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function close() {
        if($this->link) {
            $this->link->close();
            $this->_db = null;
            $this->_collection =  null;
            $this->_cursor = null;
        }
    }

    /**
     +----------------------------------------------------------
     * 数据库错误信息
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function error() {
        $this->error = $this->_db->lastError();
        return $this->error;
    }
    /**
     +----------------------------------------------------------
     * 生成下一条记录ID 用于自增非MongoId主键
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $pk 主键名
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     */
    public function mongoNextId($table,$pk) {
        try{
            $pk ='ID';
            $result   =  $this->_db->{$table}->find(array(),array($pk=>1))->sort(array($pk=>-1))->limit(1);
        } catch (MongoCursorException $e) {
             throw new FreeException($e->getMessage(),500);
        }
        $data = $result->getNext();
        return isset($data[$pk])?$data[$pk]+1:1;
    }


    /**
     +----------------------------------------------------------
     * 删除记录
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $table  表名 	 
     * @param array $where 
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     */
    public function delete($table, $where)
	{
		if (empty($table)) {
            throw new FreeException("No Mongo collection selected to delete from", 500);
        } try {
			$where = $this->parseWhere($where);
            $this->_db->{$table}->remove($where, array('fsync' => TRUE));
            return(TRUE);
        } catch (MongoCursorException $e) {
            throw new FreeException("Delete of data into MongoDB failed: {$e->getMessage()}", 502);
        }
    }
/**
	 * 遍历查询结果集
	 *
	 * @param $type		返回结果集类型	MYSQL_ASSOC，MYSQL_NUM 和 MYSQL_BOTH
	 *
	 * @return array
	 */
	public function fetchNext($type='')
	{
		throw new FreeException("MongoDb Does not support this method :{__METHOD__}", 502);
		return false;
	}
	/**
	 * 获取最后一次添加记录的主键号
	 *
	 * @return int 
	 */
	public function insertId($table_name)
	{
		throw new FreeException("MongoDb Does not support this method :{__METHOD__}", 502);
		return 0;
	}
	public function affectedRows() {
		throw new FreeException("MongoDb Does not support this method :{__METHOD__}", 502);
		return false;		
	}
	/**
	 * 释放查询资源
	 *
	 * @return void
	 */
	public function freeResult()
	{
		$this->_cursor = null;
	}
	
    /**
     * 错误编号
     */
	public function errno()
	{
		throw new FreeException("MongoDb Does not support this method :{__METHOD__}", 502);
		return false;
	}
	/**
     * 版本信息
     */
	public function version() {
		throw new FreeException("MongoDb Does not support this method :{__METHOD__}", 502);
		return '';
	}
	/**
	 * 对字段两边加反引号，以保证数据库安全
	 * @param $value 数组值
	 */
	public function addSpecialChar(&$value)
	{
		return $value;
	}
	
	/**
	 * 对字段值两边加引号，以保证数据库安全
	 *
	 * @param $value 数组值
	 * @param $key 数组key
	 * @param $quotation 
	 */
	public function escapeString(&$value, $key='', $quotation = 1)
	{
		return $value;
	}
	/**
	 * 获取数据表主键
	 * @param $table 		数据表
	 * @return array
	 */
	public function getPrimary($table) {
		return 'ID';
	}
   
/**
    public function group($keys,$initial,$reduce,$options=array()){
        $this->_collection->group($keys,$initial,$reduce,$options);
    }
**/
    /**
     +----------------------------------------------------------
     * 取得数据表的字段信息
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function getFields($table=''){
        if(empty($table)) {
            throw new FreeException("No Mongo collection selected to delete from", 500);
        }
        try{
            $result   =  $this->_db->{$table}->findOne();
        } catch (MongoCursorException $e) {
            throw new FreeException($e->getMessage(),500);
        }
        if($result) { // 存在数据则分析字段
            $info =  array();
            foreach ($result as $key=>$val){
                $info[$key] =  array(
                    'name'=>$key,
                    'type'=>getType($val),
                    );
            }
            return $info;
        }
        // 暂时没有数据 返回false
        return false;
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
	 * 检查表是否存在
	 *
	 * @param $table 表名
	 * @return boolean
	 */
	public function tableExists($table) {
		$tables = $this->listTables();
		return in_array($table, $tables) ? 1 : 0;
	}
    /**
     +----------------------------------------------------------
     * 取得当前数据库的collection信息
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function listTables(){
        $list   = $this->_db->listCollections();
        $info =  array();
        foreach ($list as $collection){
            $info[]   =  $collection->getName();
        }
        return $info;
    }

    /**
     +----------------------------------------------------------
     * set分析
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param array $data
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseSet($data) {
		$fields = array();
		foreach($data as $key =>$v) {
			$key = strtoupper($key);
			switch (substr($v, 0, 2)) {
				case '+=':
				case '-=':
					$v = substr($v,2);
					$fields['$inc'][$key]  =  (int)$v;
					break;
				default:
					$fields['$set'][$key] =  $v;
			}
		}
        return $fields;
    }

    /**
     +----------------------------------------------------------
     * order分析
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $order
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    protected function parseOrder($order) {
		if(empty($order))
		{
			return array();
		}
        if(is_string($order)) {
            $array   =  explode(',',$order);
            $order   =  array();
            foreach ($array as $key=>$val){
                $arr  =  explode(' ',trim($val));
                if(isset($arr[1])) {
                    $arr[1]  =  strtolower($arr[1])=='asc'?1:-1;
                }else{
                    $arr[1]  =  1;
                }
                $order[strtoupper($arr[0])]    = $arr[1];
            }
        }else{
			foreach($order as $key => $val)
			{
				$order[strtoupper($key)] = strtolower($val)=='asc'?1:-1;
			}
		}
        return $order;
    }

    /**
     +----------------------------------------------------------
     * limit分析
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $limit
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    protected function parseLimit($limit) {
        if(strpos($limit,',')) {
            $array  =  explode(',',$limit);
        }else{
            $array   =  array(0,$limit);
        }
        return $array;
    }

    /**
     +----------------------------------------------------------
     * field分析
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $fields
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function parseField($fields){
        if(empty($fields)) {
            $fields    = array();
        }
        if(is_string($fields)) {
            $fields    = explode(',',$fields);
        }
		$returns = array();
		foreach($fields as $key =>$val)
		{
			$returns[strtoupper($key)] = $val;
		}
        return $returns;
    }

    /**
     +----------------------------------------------------------
     * where分析
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $where
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function parseWhere($where){
        $query   = array();
		if(isset($where['_logic'])) {
              // 定义逻辑运算规则 例如 OR
			$operate    =   strtolower($where['_logic']);
			unset($where['_logic']);
		}
        foreach ($where as $key=>$val){
            if('_id' != $key && 0===strpos($key,'_')) {
                // 解析特殊条件表达式
                $query   = $this->parseThinkWhere($key,$val);
            }else{
                // 查询字段的安全过滤
                if(!preg_match('/^[A-Z_\|\&\-.a-z0-9]+$/',trim($key))){
                    throw new FreeException('wrong where','502');
                }
                $key = trim($key);
                if(strpos($key,'|')) {
                    $array   =  explode('|',$key);
                    $str   = array();
                    foreach ($array as $k){
                        $str[]   = $this->parseWhereItem($k,$val);
                    }
                    $query['$or'] =    $str;
                }elseif(strpos($key,'&')){
                    $array   =  explode('&',$key);
                    $str   = array();
                    foreach ($array as $k){
                        $str[]   = $this->parseWhereItem($k,$val);
                    }
                    $query   = array_merge($query,$str);
                }else{
                    $str   = $this->parseWhereItem($key,$val);
					if($operate == 'or')
					{
						$query[] =  $str;
					}else{
						$query   = array_merge($query,$str);
					}
				}
            }
        }
		if($operate == 'or')
		{
			$return['$or'] = $query;	
		}else{
			$return = $query;
		}
        return $return;
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
    protected function parseThinkWhere($key,$val) {
        $query   = array();
        switch($key) {
            case '_query': // 字符串模式查询条件
                parse_str($val,$query);
                if(isset($query['_logic']) && strtolower($query['_logic']) == 'or' ) {
                    unset($query['_logic']);
                    $query['$or']   =  $query;
                }
                break;
            case '_string':// MongoCode查询
                $query['$where']  = new MongoCode($val);
                break;
        }
        return $query;
    }

    /**
     +----------------------------------------------------------
     * where子单元分析
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $key
     * @param mixed $val
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    protected function parseWhereItem($key,$val) {
		$key = strtoupper($key);
        $query   = array();
        if(is_array($val)) {
            if(is_string($val[0])) {
                $con  =  strtolower($val[0]);
                if(in_array($con,array('neq','ne','gt','egt','gte','lt','lte','elt'))) { // 比较运算
                    $k = '$'.$this->comparison[$con];
                    $query[$key]  =  array($k=>$val[1]);
                }elseif('like'== $con){ // 模糊查询 采用正则方式
					$val[1] = str_replace('%','',$val[1]);
                    $query[$key]  =  new MongoRegex("/".$val[1]."/i");  
                }elseif('mod'==$con){ // mod 查询
                    $query[$key]   =  array('$mod'=>$val[1]);
                }elseif('regex'==$con){ // 正则查询
                    $query[$key]  =  new MongoRegex($val[1]);
                }elseif(in_array($con,array('in','nin','not in'))){ // IN NIN 运算
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    $k = '$'.$this->comparison[$con];
                    $query[$key]  =  array($k=>$data);
                }elseif('all'==$con){ // 满足所有指定条件
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    $query[$key]  =  array('$all'=>$data);
                }elseif('between'==$con){ // BETWEEN运算
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    $query[$key]  =  array('$gte'=>$data[0],'$lte'=>$data[1]);
                }elseif('not between'==$con){
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    $query[$key]  =  array('$lt'=>$data[0],'$gt'=>$data[1]);
                }elseif('exp'==$con){ // 表达式查询
                    $query['$where']  = new MongoCode($val[1]);
                }elseif('exists'==$con){ // 字段是否存在
                    $query[$key]  =array('$exists'=>(bool)$val[1]);
                }elseif('size'==$con){ // 限制属性大小
                    $query[$key]  =array('$size'=>intval($val[1]));
                }elseif('type'==$con){ // 限制字段类型 1 浮点型 2 字符型 3 对象或者MongoDBRef 5 MongoBinData 7 MongoId 8 布尔型 9 MongoDate 10 NULL 15 MongoCode 16 32位整型 17 MongoTimestamp 18 MongoInt64 如果是数组的话判断元素的类型
                    $query[$key]  =array('$type'=>intval($val[1]));
                }else{
                    $query[$key]  =  $val;
                }
                return $query;
            }else {
                $count = count($val);
				$rule = strtolower(trim($val[$count-1]));
                if(in_array($rule,array('or','xor'))) {
                    $rule = $rule;
                    $count   =  $count -1;
                }else{
					for($i=0;$i<$count;$i++) {
						$data = is_array($val[$i])?$val[$i][1]:$val[$i];
						$op = is_array($val[$i]) ? $this->comparison[strtolower($val[$i][0])] : $this->comparison['neq'];//在普通db此处默认eq
						$query[$key]['$' . $op] = $data;
					}
				}
				return $query;
            }
        }
        $query[$key]  =  $val;
        return $query;
    }
}