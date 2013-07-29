<?php

defined('IN_FREE') or exit('No permission resources.');

class AuthBaseController extends AdminFrontController
{

    function __construct()
    {
		parent::__construct();
    }

    /**
     * 首页
     */
	public function initAction()
	{
		$page = max(intval($this->getRequest()->getGet('page')),1);
		$admin_server = S('Admin',$this->_module);
		$admins = $admin_server->getModel()->listInfo('','*','',$page);
		$this->assign('admins',$admins);
		$this->assign('pages',$admin_server->getModel()->pages);
		 $this->template();
    }
	
	/**
	* 编辑管理员
	**/
	public function editAction()
	{
		$admin_server = S('Admin',$this->_module);
		if($this->getRequest()->isPost())
		{
			$id = $this->getRequest()->getPost('id');
			$id = !empty($id) ? intval($id) : $this->showMessage(L('param_error'),'',0);
			$admin = FreeString::trim($this->getRequest()->getPost('info'));
			if(!empty($admin['PASSWORD']) && $admin['PASSWORD'] != $this->getRequest()->getPost('REPASSWORD'))
			{
				$this->showMessage(L('passwords_not_match'),'',0);
			}
			if(empty($admin['PASSWORD']))
			{
				unset($admin['PASSWORD']);
			}
			$admin['ROLEID'] = implode(',',$_POST['userRole']);
			if($admin_server->edit($admin,array('ID'=>$id)))
			{
				$this->showMessage(L('admin_edit_success'));
			}else{
				$this->showMessage($admin_server->getError(),'',0);
			}
		}else{
			$id = $this->getRequest()->getGet('id');
			$id = !empty($id) ? intval($id) : $this->showMessage(L('param_error'),'',0);
			$admin = $admin_server->getModel()->getOne(array('ID'=>$id));
			$this->assign('admin',$admin);
			$admin_role = explode(',',$admin['ROLEID']);
			$this->assign('admin_role',$admin_role);
			$roles = S('Role',$this->_module)->getAll();
			$this->assign('roles',$roles);
			$this->template();
		}
	}
	/**
	 * 添加管理员
	 */
	public function addAction()
	{
		$admin_server = S('Admin',$this->_module);
		if($this->getRequest()->isPost())
		{
			$admin = FreeString::trim($this->getRequest()->getPost('info'));
			if($admin['PASSWORD'] != $this->getRequest()->getPost('REPASSWORD') && !empty($admin['PASSWORD']))
			{
				$this->showMessage(L('passwords_not_match'),'',0);
			}
			$admin['ROLEID'] = implode(',',$_POST['userRole']);
			if($admin_server->add($admin))
			{
				$this->showMessage(L('admin_add_success'));
			}else{
				$this->showMessage($admin_server->getError(),'',0);
			}
		}else{
			$admin_role = explode(',',$admin['ROLEID']);
			$this->assign('admin_role',$admin_role);
			$roles = S('Role',$this->_module)->getAll();
			$this->assign('roles',$roles);
			$this->template();
		}
	}
	/**
	 * 删除管理员
	 */
	public function deleteAction()
	{
		$admin_server = S('Admin',$this->_module);
		$id = $this->getRequest()->getGet('id');
		$id = !empty($id) ? intval($id) : $this->showMessage(L('param_error'),'',0);
		if($admin_server->getModel()->delete(array('ID'=>$id)))
		{
			$this->showMessage(L('admin_delet_success'));
		}else{
			$this->showMessage($admin_server->getModel()->getError(),'',0);
		}
	}
}

