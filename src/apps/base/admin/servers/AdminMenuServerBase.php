<?php

defined('IN_FREE') or exit('No permission resources.');

class AdminMenuServerBase extends FreeProServer
{
	
	protected $tree = array();
	
	function __construct()
    {
		parent::__construct();
    }

	public function model()
	{
		$this->model = M('admin_menu','admin');
	}

	/**
	*	获取所有的菜单
	**/
    public function getAll()
    {
		static $admin_menu = '';
		if($admin_menu === '')
		{
			$menus = $this->model->select( array(), '*', '', 'ORDERNO desc');
			$admin_menu = array();
			foreach($menus as $key => $m)
			{
				$admin_menu[$m['ID']] = $m;
			}
		}
		
		return $admin_menu;
    }
	
	/**
	*	获取所有后台菜单树(供菜单添加)
	**/
	public function getMenuTree($p_id=0,$delid = 0)
	{
		$admin_menu = $this->getAll();
		if(isset($admin_menu[$delid]))
		{
			unset($admin_menu[$delid]);
		}
		return $this->treeMenu($p_id,$admin_menu);
	}
	/**
	*	获取指定权限可访问菜单（供adminfile）
	**/
	public function getMenuRole($p_id=0,$roleid = 0)
	{
		$admin_menu = $this->getAll();
		if($roleid != 0)
		{
			$roles = S('Role','admin')->getAll();
			 if(strpos($roleid,','))
			 {
				$rs = implode(',',$roleid);
			 }else{
				$rs = array($roleid);
			 }
			 $priv = array();
			 foreach($rs as $key => $r)
			 {
				$priv = array_merge($priv, empty($roles[$r]['PRIV']) ? array() : explode(',',$roles[$r]['PRIV']));
			 }
		}	
		foreach($admin_menu as $key => $r)
		{
            if($r['STATUS'] == 0)
            {
                unset($admin_menu[$key]);
                continue;
            } 
            if($roleid != 0)
            {
                if($r['FATHERID'] != 0 && !in_array($r['ID'],$priv))
                {
                    unset($admin_menu[$key]);
                } 
            }
		}
		
		return $admin_menu;
	}
	/**
	*	获取指定权限后台菜单树
	**/
	public function getMenuRoleTree($p_id=0,$roleid = 0)
	{
		$admin_menu = $this->getMenuRole($p_id,$roleid);
		return $this->treeMenu($p_id,$admin_menu);
	}
	/**
     * 导航选择
     * @param string $p_id 父id
     * @param intval/array $id 别选中的ID，多选是可以是数组
     */
	public function selectMenu($p_id=0,$id = 0)
	{
		$admin_menu = $this->getAll();
        $array = array();
        foreach($admin_menu as $key => $val)
        {
            if($val['FATHERID'] != 0)
            {
                continue;
            }
            $array[$key] = $val;
            if($val['ID'] == $id)
            {
                $array[$key]['selected'] = 'selected';
            }
        }
		$tree = E('tree');
		$str = "<option value='\$ID' \$selected>\$spacer \$NAME</option>;";
		$str2 = "<optgroup label='\$spacer \$NAME'></optgroup>";
		$tree->init($array);
		$string = $tree->get_tree_category(0, $str, $str2);
		return $string;
		
	}
	
	/**
	*	生成tree
	**/
	private function treeMenu($p_id=0,$trees = array())
	{
		foreach($trees as $key => $val)
		{
			if($val['FATHERID'] == $p_id)
			{
				$tree[$val['ID']] = $val;
				$tree[$val['ID']]['id'] = $val['ID'];
				$tree[$val['ID']]['name'] = $val['NAME'];
				$tree[$val['ID']]['icon'] = '';
				$tree[$val['ID']]['tip'] = '';
				$tree[$val['ID']]['parent'] = $p_id ==0 ? 'root' : $p_id;
				$tree[$val['ID']]['url'] = $this->resolveMenuUrl($val['QUERY']);
				$tree[$val['ID']]['items'] = $this->treeMenu($val['ID'],$trees);
			}
		}
		return $tree;
	}
	/**
	*	获取所有的父菜单
	**/
	public function getFatherMenu($c_id=0)
	{
		$admin_menu = $this->getAll();
		if($c_id == 0)
		{
			return array(); 
		}
		foreach($admin_menu as $key => $val)
		{
			if($val['ID'] == $c_id)
			{
				$this->tree[] = $val;
				if($val['FATHERID'] != 0)
				{
					$this->getFatherMenu($val['FATHERID']);
				}
			}
		}
		return $this->tree;
	}
	
	public function get($id)
	{
		return $this->model->getOne(array('ID'=>$id));
	}
	
	public function edit($data,$where)
	{
		return $this->model->update($data,$where);
	}
	
	public function add($data)
	{
		return $this->model->insert($data,true);
	}
	/**
	*	删除指定菜单
	**/
	public function delete($ids)
	{
		if($this->model->getOne(array('FATHERID'=>$ids)))
		{
			return false;
		}
		$this->model->delete(array('ID'=>$ids));
		return true;
	}
	/**
	*	拼凑后台菜单url
	**/
	public function resolveMenuUrl($str)
	{
		if(stripos($str,',') !== false)
		{
			$urls = explode(',', $str);
			$str = $urls[0];
		}
		//var_dump(U(str_replace('*','',$str)));
		if(stripos($str,'?') !== false)
		{
			$us = explode('?', $str);
			$str = $us[0];
			return U(str_replace('*','',$str),array('showmodule'=>$us[1]));
		}else{
			return U(str_replace('*','',$str));
		}
	}
}

