<?php
defined('IN_FREE') or exit('No permission resources.');

class AdminServerBase extends FreeProServer
{

	public $model;

    function __construct()
    {
		parent::__construct();
    }

	public function model()
	{
		$this->model = M('admin','admin');
		//array(field,rule,message,condition,function,type,where)
		$this->model->setValidate(array(
			//array('PASSWORD','','{%password_can_not_be_empty}',1,'confirm',FreeModel::MODEL_INSERT),
			array('PASSWORD','6,20','{%passworderror}',1,'length',FreeModel::MODEL_INSERT),
			array('PASSWORD','6,20','{%passworderror}',0,'length',FreeModel::MODEL_UPDATE),
			array('USERNAME','isUserName','{%username_illegal}',1,'function',FreeModel::MODEL_BOTH,array(4,20)),
			array('USERNAME','admin','{%admin_already_exists}',1,'nequal',FreeModel::MODEL_BOTH),
			array('USERNAME','','{%admin_already_exists}',1,'unique',FreeModel::MODEL_BOTH),
			array('EMAIL','email','{%email_illegal}',1,'',FreeModel::MODEL_BOTH),
			array('EMAIL','','{%email_already_exists}',1,'unique',FreeModel::MODEL_BOTH),
		));
		$this->model->setDeal(array(
			array('PASSWORD','md5','function',FreeModel::MODEL_BOTH),
		));
	}
	/**
	*	用户登录
	**/
    public function login($username,$password)
    {
		$password = md5($password);
		$founder = C('founder');
		if(array_key_exists($username,$founder))
		{
			if($password === $founder[$username])
			{
				return array('ID'=>0,'USERNAME'=>$username,'ROLEID'=>0);
			}
		}
		$status = $this->model->getOne(array('USERNAME'=>$username,'PASSWORD'=>$password));
		return $status;
    }
	/**
	*	设置用户登录
	**/
	public function setLoginInfo($re)
	{
		$_SESSION['admin']['userid'] = $re['ID'];
		$_SESSION['admin']['roleid'] = $re['ROLEID'];
		$_SESSION['admin']['username'] = $re['USERNAME'];
		//$role_info = $this->role_db->get_one_role($r['ROLEID']);
		//$_SESSION['admin']['rolename'] = $role_info['NAME'];
		//$_SESSION['admin']['siteids'] = explode(',', $role_info['SITEID']);
		//$_SESSION['admin']['siteid'] = $_SESSION['userinfo']['siteids'][0];
		//$_SESSION['pc_hash'] = random(6,'abcdefghigklmnopqrstuvwxwyABCDEFGHIGKLMNOPQRSTUVWXWY0123456789');
		//$default_siteid = $_SESSION['admin']['siteid'];
	}
    
    public function getCurrentInfo($key = '')
     {
        if(empty($key))
        {
            return $_SESSION['admin'];
        }else{
            return $_SESSION['admin'][$key];
        }
     }
	
	/**
	*	判断用户是否登录
	**/
	public function isLogin()
	{
		return isset($_SESSION['admin']['userid']);
	}
	
	public function loginout()
	{
		unset($_SESSION['admin']);
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

