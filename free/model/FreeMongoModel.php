<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


class FreeMongoModel extends FreeModel{ //extends AbstractFreeModel {

   protected $_validate = array();//
	public function __construct($table_name='') {
		$this->db_tablepre = Free::loadConfig('system','tablepre');
		!empty($table_name) && $this->table_name = $table_name;
		$this->table_name = strtolower($this->db_tablepre .$this->table_name);
		$this->db = $this->getComponent('mongo_db');
	}

	/**
	 * 计算记录数
	 * @param string/array $where 查询条件
	 */
	public function count($where = array()) {
		$r = $this->db->count($this->table_name,$where);
		return $r;
	}
	
	/**
	 * 获取数据表主键
	 * @return array
	 */
	public function getPrimary() {
		return $this->_pk_id;
	}
	
}
?>
