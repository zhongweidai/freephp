<?php

defined('IN_FREE') or exit('No permission resources.');

class MenuBaseController extends AdminFrontController
{

    function __construct()
    {
		parent::__construct();
    }

    //后台菜单列表
	public function initAction()
	{
		$id = intval($this->getRequest()->getGet('id'));
		$this->assign('id',$id);
		$admin_menu_server = S('AdminMenu',$this->_module);
		$menu = $admin_menu_server->getMenuTree($id );
		//var_dump($menu);
		$this->assign('menu',$menu);
		$f_menu = $admin_menu_server->getFatherMenu($id);
		$this->assign('f_menu',array_reverse($f_menu));
		$this->template();
    }
	/**
	*	获取所有的父菜单
	**/
	public function deleteAction()
	{
		$id = intval($this->getRequest()->getGet('id'));
		$id = isset($id) ? intval($id) : $this->showMessage(L('param_error'));
		$admin_menu_server = S('AdminMenu',$this->_module);
		if($admin_menu_server->delete($id))
		{
			$this->showMessage(L('menu_delete_success'));
		}else{
			$this->showMessage(L('menu_delete_fail'),'',0);
		}
	}
	/**
	*	树形菜单操作
	**/
	public function operaAction()
	{
	//var_dump($_POST);
		$id = intval($this->getRequest()->getGet('id'));
		$admin_menu_server = S('AdminMenu',$this->_module);
		$data = $this->getRequest()->getPost('data') ;
		if(isset($data) && is_array($data))
		{
			foreach($this->getRequest()->getPost('data') as $key => $val)
			{
				$id = $val['ID'];
				unset($val['ID']);
                $val['STATUS'] = isset($val['STATUS']) ? 1 : 0;
				$admin_menu_server->edit($val,array('ID'=>$id ));
			}
		}
		$newdata = $this->getRequest()->getPost('newdata');
		if(isset($newdata) && is_array($newdata))
		{
			foreach($newdata as $key => $val)
			{
				if(stripos($key,'root') !== false)
				{
					if(isset($val['tempid'])) 
					{
						unset($val['tempid']);
					}
					$val['FATHERID'] = isset($val['FATHERID']) ? $val['FATHERID'] : $r_id;
					$val['IS_LOG'] = $val['IS_LOG'] == 'on' ? 1 : 0;
					$re_ids['temp_' . $key] = $admin_menu_server->add($val);
				}
			}
			foreach($newdata as $key => $val)
			{
				if(stripos($key,'child') !== false)
				{
					if(isset($val['tempid'])) 
					{
						unset($val['tempid']);
					}
					$val['FATHERID'] = isset($val['FATHERID']) && stripos($val['FATHERID'],'temp')!== false ? $re_ids[$val['FATHERID']] : $val['FATHERID'];
					$val['IS_LOG'] = $val['IS_LOG'] == 'on' ? 1 : 0;
					$re_ids[$key] = $admin_menu_server->add($val);
				}
			}
		}
		
		$this->showMessage(L('menu_opera_success'));
	}
	/**
	*	单独编辑
	**/
	public function editAction()
	{
		$admin_menu_server = S('AdminMenu',$this->_module);
		$id = intval($this->getRequest()->getGet('id'));
		$id = isset($id) ? intval($id) : $this->showMessage(L('param_error'));
		if($this->getRequest()->isPost())
		{
			if($admin_menu_server->edit($this->getRequest()->getPost('info'),array('ID'=>$id)))
			{
				$this->showMessage(L('menu_edit_success'));
			}else{
				$this->showMessage(L('menu_edit_fail'),'',0);
			}
			
		}else{
            $this->assign('id',$id);
			$menu = $admin_menu_server->get($id);
			$this->assign('menu',$menu);
			$this->assign('select_menu',$admin_menu_server->selectMenu(0,$id));
			$this->template();
		}
	}
	
}

