<?php

defined('IN_WHTY') or exit('No permission resources.');

class RoleBaseController extends AdminFrontController
{
	protected $server;
    function __construct()
    {
		parent::__construct();
		$this->server = S('Role',$this->_module);
    }

    //首页
	public function initAction()
	{
		$roles = $this->server->getAll();
		$this->assign('roles',$roles);
		$this->template();
    }
	
	/**
	* 编辑权限
	**/
	public function editAction()
	{
		if($this->getRequest()->isPost())
		{
			$id = $this->getRequest()->getPost('id');
			$id = !empty($id) ? intval($id) : $this->showMessage(L('param_error'),'',0);
			$roles['PRIV'] = implode(',',WhtyString::trim($this->getRequest()->getPost('menus')));
            $roles['STATUS'] = $this->getRequest()->getPost('status') ? 1 : 0;
			if($this->server->edit($roles,array('ID'=>$id)))
			{
				$this->showMessage(L('role_edit_success'));
			}else{
				$this->showMessage($this->server->getError(),'',0);
			}
		}else{
			$id = $this->getRequest()->getGet('id');
			$id = !empty($id) ? intval($id) : $this->showMessage(L('param_error'),'',0);
			$roles = $this->server->getAll();

			$this->assign('roles',$roles);
			$this->assign('role',$roles[$id]);
			
			$admin_role = explode(',',$roles[$id]['PRIV']);
			$this->assign('admin_role',$admin_role);
			
			$menu_server = S('AdminMenu',$this->_module);
			$menus = $menu_server->getMenuTree();
			$this->assign('menus',$menus);
			
			foreach($roles as $key => $r)
			{
				$role_table[$r['NAME']] = explode(',',$r['PRIV']);
			}
			$this->assign('role_table',json_encode($role_table));
			$this->template();
		}
	}
	/**
	 * 添加
	 */
	public function addAction()
	{
		if($this->getRequest()->isPost())
		{
			$roles['PRIV'] = implode(',',WhtyString::trim($this->getRequest()->getPost('menus')));
			$roles['NAME'] = $this->getRequest()->getPost('rolename');
            $roles['STATUS'] = $this->getRequest()->getPost('status') ? 1 : 0;
			if($this->server->add($roles))
			{
				$this->showMessage(L('role_edit_success'));
			}else{
				$this->showMessage($this->server->getError(),'',0);
			}
		}else{
			$roles = $this->server->getAll();
			$this->assign('roles',$roles);
			
			$menu_server = S('AdminMenu',$this->_module);
			$menus = $menu_server->getMenuTree();
			$this->assign('menus',$menus);
			
			foreach($roles as $key => $r)
			{
				$role_table[$r['NAME']] = explode(',',$r['PRIV']);
			}
			$this->assign('role_table',json_encode($role_table));
			$this->template();
		}
	}
	/**
	 * 删除
	 */
	public function deleteAction()
	{
		$id = $this->getRequest()->getGet('id');
		$id = !empty($id) ? intval($id) : $this->showMessage(L('param_error'),'',0);
		if($this->server->getModel()->delete(array('ID'=>$id)))
		{
			$this->showMessage(L('role_delete_success'));
		}else{
			$this->showMessage($this->server->getModel()->getError(),'',0);
		}
	}
    /**
     * 编辑
     */
    public function statusAction()
    {
        $id = $this->getRequest()->getPost('id');
        $value =  $this->getRequest()->getPost('value');
        $field = $this->getRequest()->getPost('field');
        $value = intval($value) == 1 ? 0 : 1;
        $data[strtoupper($field)] = $value;
  
        if($this->server->getModel()->closeAuto()->update($data,array('ID'=>intval($id))))
        {
            $this->showMessage('success','', 1,$value);
        }else{
            $this->showMessage('fail','', 0,$value);
        }
        
    }
}

