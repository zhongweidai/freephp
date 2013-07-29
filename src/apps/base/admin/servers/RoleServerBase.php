<?php

defined('IN_FREE') or exit('No permission resources.');

class RoleServerBase extends FreeProServer
{
	function __construct()
    {
		parent::__construct();
    }

	public function model()
	{
		$this->model = M('admin_role','admin');
		//array(field,rule,message,condition,function,type,where)
		$this->model->setValidate(array(//array('PASSWORD','REPASSWORD','{%email_illegal}',1,'confirm',FreeModel::MODEL_BOTH),
			array('NAME','4,20','{%role_already_exists}',1,'unique',FreeModel::MODEL_BOTH),
		));
	}
	
	/**
	*	获取所有角色
	**/
    public function getAll()
	{
		static $admin_roles = '';
		if($admin_roles === '')
		{
			$roles = $this->model->select();
			$admin_roles = array();
			foreach($roles as $key => $m)
			{
				$admin_roles[$m['ID']] = $m;
			}
		}
		return $admin_roles;
	}
	
	public function edit($data,$where)
	{
		if($this->model->update($data,$where))
		{
			return true;
		}else{
			$this->error = $this->model->getError();
			return false;
		}
	}
	
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

}

