<?php

defined('IN_WHTY') or exit('No permission resources.');
/**
 * @deprecated
 */
class WorkflowBaseController extends AdminFrontController
{
	protected $namespace;
	protected $server;
	function __construct()
	{
		parent::__construct();
		$namespace = $this->getRequest()->getGet('ns');
		$this->namespace = $namespace ? $namespace : 'admin_workflow';
		$this->server = S('Config',$this->_module);
		$this->steps = array('1'=>'一级审核','2'=>'二级审核','3'=>'三级审核','4'=>'四级审核');
	}
    
    public function initAction()
    {
        $site_configs = $this->server->get($this->namespace);
		$this->assign('config',$site_configs);
		$this->assign('steps',$this->steps);
		$this->template();
    }
    
/*    public function addAction()
    {
        $info = $this->getRequest()->getPost('info');

        $info['NAME'] = '111';
        $info['FLAWNAME'] = '222';
        $info['REMARK'] = '4444';
        
        $data = array();
        $data['admin_workflaw_' . WhtyString::genUuid()]= $info;
        
        $this->server->getModel()->closeAuto();
        //$this->server->edit($data,$this->namespace);
        $this->template('config','workflow_add');
    }*/
    
    //添加工作流
	public function addAction() 
	{
		if($this->getRequest()->isPost())
		{
			$info = WhtyString::trim($this->getRequest()->getPost('info'));//dump($info);exit;
			$setting[1] = WhtyString::trim($this->getRequest()->getPost('checkadmin1'));
			$setting[2] = WhtyString::trim($this->getRequest()->getPost('checkadmin2'));
			$setting[3] = WhtyString::trim($this->getRequest()->getPost('checkadmin3'));
			$setting[4] = WhtyString::trim($this->getRequest()->getPost('checkadmin4'));
			$setting['nocheck_users'] = WhtyString::trim($this->getRequest()->getPost('nocheck_users'));

			$info['SETTING'] = $setting;
			$data = array();
        	$data['admin_workflaw_' . WhtyString::genUuid()]= $info;        
        	$this->server->getModel()->closeAuto();       	
			if($this->server->edit($data,$this->namespace))
			{
				$this->showMessage(L('add_success'));
			}
		}
		else
		{
			$this->assign('steps',$this->steps);
			$this->model = M('admin','admin');
			$admins = $this->model->listInfo('','*','');
			$this->assign('admins',$admins);
			$this->template ();
		}
		

	}
	
    public function delAction()
    {
    	$uid = $this->getRequest()->getGet('uid');
        $this->server->getModel()->delete(array('NAMESPACE'=>$this->namespace,'NAME'=>$uid));
        $this->showMessage('删除成功');        
        
    }
    
	//批量删除
	public function delAllAction()
    {
       if ($this->getRequest()->isPost()) 
		{
            $uid = $this->getRequest()->getPost('uid');//dump($userNavid);exit;
            if(empty($uid))
            {
                $this->showMessage('请至少选定一条记录', '', 0);
            }
            else
            {
            	foreach ($uid as $id) 
				{
					//批量删除
					 $this->server->getModel()->delete(array('NAMESPACE'=>$this->namespace,'NAME'=>$id));
				}
           $this->showMessage('删除成功');  
              
            }
		}
    }
    
    //查看流程图
    public function viewAction()
    {
    	$uid = $this->getRequest()->getGet('uid');
		$site_configs = $this->server->get($this->namespace);
		foreach ($site_configs as $k=>$v)
		{
			if($k==$uid)
			{
				$uid = $k;
				$infos =$site_configs[$k];
			}            	
				
		}
		$checkadmin1 = $infos['SETTING']['1'];
		$checkadmin2 = $infos['SETTING']['2'];
		$checkadmin3 = $infos['SETTING']['3'];
		$checkadmin4 = $infos['SETTING']['4'];
		$nocheck_users = $infos['SETTING']['nocheck_users'];
		$this->assign('checkadmin1',$checkadmin1);
		$this->assign('checkadmin2',$checkadmin2);
		$this->assign('checkadmin3',$checkadmin3);
		$this->assign('checkadmin4',$checkadmin4);
		$this->assign('nocheck_users',$nocheck_users);
    	$this->template();    
    }
    
    public function editAction()
    {
    	if($this->getRequest()->isPost())
		{
			$uid = $this->getRequest()->getPost('uid');
			$info = WhtyString::trim($this->getRequest()->getPost('info'));
			$setting[1] = WhtyString::trim($this->getRequest()->getPost('checkadmin1'));
			$setting[2] = WhtyString::trim($this->getRequest()->getPost('checkadmin2'));
			$setting[3] = WhtyString::trim($this->getRequest()->getPost('checkadmin3'));
			$setting[4] = WhtyString::trim($this->getRequest()->getPost('checkadmin4'));
			$setting[nocheck_users] = WhtyString::trim($this->getRequest()->getPost('nocheck_users'));
			$info[SETTING] = $setting;
			
			$data = array();
        	$data[$uid]= $info;
        
        	$this->server->getModel()->closeAuto();
        	$this->server->edit($data,$this->namespace);
        	$this->showMessage(L('edit_success'));
			
		}
		else
		{
			$uid = $this->getRequest()->getGet('uid');
			$site_configs = $this->server->get($this->namespace);
			foreach ($site_configs as $k=>$v)
			{
				if($k==$uid)
				{
					$uid = $k;
					$infos =$site_configs[$k];
				}            	
				
			}
			$checkadmin1 = $infos['SETTING']['1'];
			$checkadmin2 = $infos['SETTING']['2'];
			$checkadmin3 = $infos['SETTING']['3'];
			$checkadmin4 = $infos['SETTING']['4'];
			$nocheck_users = $infos['SETTING']['nocheck_users'];
			$this->assign('checkadmin1',$checkadmin1);
			$this->assign('checkadmin2',$checkadmin2);
			$this->assign('checkadmin3',$checkadmin3);
			$this->assign('checkadmin4',$checkadmin4);
			$this->assign('nocheck_users',$nocheck_users);
			//dump($checkadmin1);
			$this->model = M('admin','admin');
			$admins = $this->model->listInfo('','*','');
			$this->assign('admins',$admins);
			$this->assign('infos',$infos);
			$this->assign('uid',$uid);
			$this->assign('steps',$this->steps);
			$this->template();
		}

    }
    

}
