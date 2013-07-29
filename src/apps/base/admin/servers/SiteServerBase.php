<?php

defined('IN_FREE') or exit('No permission resources.');

class SiteServerBase extends FreeProServer
{
	function __construct()
    {
		parent::__construct();
    }

	public function model()
	{
		$this->model = M('site','admin');
		//array(field,rule,message,condition,function,type,where)
		$this->model->setValidate(array(
			//array('PASSWORD','REPASSWORD','{%email_illegal}',1,'confirm',FreeModel::MODEL_BOTH),
			//array('NAME','4,20','{%role_already_exists}',1,'unique',FreeModel::MODEL_BOTH),
			array('CODE','/^[A-Za-z0-9\-]+$/','站点ID不能为空或格式不正确',1,'regex',FreeModel::MODEL_BOTH),
			array('CODE','4,20',"{%site_code_already_exists}",1,'unique',FreeModel::MODEL_BOTH),
		));
	}
	
	/**
	*	获取所有站点
	**/
    public function getAll()
	{
		static $sites_info= '';
		if($sites_info === '')
		{
			$sites = $this->model->select();
			$sites_info = array();
			foreach($sites as $key => $m)
			{
				$sites_info[$m['CODE']] = $m;
			}
		}
		return $sites_info;
	}
	
	/**
	*	获取所有站点树(供站点添加)
	**/
	public function getSiteTree($p_id=0,$delid = 0)
	{
		$sites_info = $this->getAll();
		if(isset($sites_info[$delid]))
		{
			unset($sites_info[$delid]);
		}
		return $this->treeSite($p_id,$sites_info);
	}
	/**
	*	生成tree
	**/
	private function treeSite($p_id=0,$trees = array())
	{
		foreach($trees as $key => $val)
		{
			if($val['FATHERID'] == $p_id)
			{
				$tree[$val['CODE']] = $val;
				$tree[$val['CODE']]['items'] = $this->treeSite($val['CODE'],$trees);
			}
		}
		return $tree;
	}
	/**
	*	编辑站点信息
	*/
	public function edit($data,$where)
	{
		if($this->isHaveChild($where,$data['CODE']))
		{
			$this->error = '父站点不能被修改';
			return false;
		}
		if($this->model->update($data,$where))
		{
			return true;
		}else{
			$this->error = $this->model->getError();
			return false;
		}
	}
	/**
	*	添加站点信息
	*/
	public function add($data)
	{

		if($this->model->insert($data))
		{
			return true;
		}else{
			$this->error = $this->model->getError();
			return false;
		}
	}
	
	public function isHaveChild($where,$code)
	{
		$site = $this->model->getOne($where);
		if(isset($site['CODE']) && $code != $site['CODE'] )
		{
			if($this->model->select(array('FATHERID'=>$site['CODE'])))
			{
				return true;
			}
		}
		return false;
		
	}
	
	
	public function cache()
	{
		$sites = $this->getAll();
		return $this->getComponent('cache')->set('sitelist',$sites,'commons');
	}
	/**
	*	获取所有城市信息列表
	*	@param psiteid 为空时所有城市信息 不为空返回当前省id下的城市
	**/
	public function getCache($psiteid = '')
	{
		static $site_list = '';
		if($site_list === '')
		{
			$site_list = $this->getComponent('cache')->get('sitelist','commons');
		}
		if(empty($psiteid))
		{
			return $site_list;
		}else{
			$list = array();
			foreach($site_list as $key => $r)
			{
				if($r['FATHERID'] == $psiteid)
				{
					$list[$key] = $r;
				}
			}
			return $list;
		}
		
	}
}

