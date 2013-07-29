<?php

defined('IN_FREE') or exit('No permission resources.');

class IndexBaseController extends AdminFrontController
{

    function __construct()
    {
		parent::__construct();
    }

    //首页
	public function initAction()
	{
	    $this->assign('adminuser',$this->getCurrentAdminInfo());
		$admin_menu_server = S('AdminMenu',$this->_module);
		$re = $admin_menu_server->getMenuRoleTree(0,$this->getCurrentAdminInfo('roleid'));
		$this->assign('menus',json_encode($re));

        $sites = S('Site',$this->_module)->getSiteTree();
		$this->assign('sites',$sites);
        $this->assign('siteid', $this->getSiteId());
        
		$this->template();
    }
	
	//注册
	public function loginAction()
	{
		$admin_server = S('Admin',$this->_module);
		if($admin_server->isLogin())
		{
			$this->getResponse()->sendRedirect(U("$this->_module/$this->_controller/init"));
			exit;
		}
		if($this->getRequest()->isPost())
		{
			$username = $this->getRequest()->getPost('username');
			$password  = $this->getRequest()->getPost('password');
			$username = !empty($username) ? trim($username) : $this->showmessage(L('nameerror'),'',0);
			$password = !empty($password) ? trim($password) :  $this->showmessage(L('passworderror'),'',0);
		
			//$code = isset($_POST['code']) && trim($_POST['code']) ? trim($_POST['code']) : showmessage(L('input_code'), HTTP_REFERER);
			//$this->registerComponent(array('tree'=>WHTY_PATH . 'tree'));
			$re = $admin_server->login($username,$password);
			if(empty($re))
			{
				$this->showMessage(L('admin_login_failure'),'',0);
			}else{
				$admin_server->setLoginInfo($re);
				$this->getResponse()->sendRedirect(U("$this->_module/$this->_controller/init"));
				exit;
			}
		}
		$this->template();
	}
	
	public function loginoutAction()
	{
		$admin_server = S('Admin',$this->_module);
		$admin_server -> loginout();
		$this->getResponse()->sendRedirect(U("$this->_module/$this->_controller/init"));
	}

    //切换站点
    public function changeSiteIdAction()
    {
        if ($siteid = $this->getRequest()->getGet('choose_value'))
        {
            FreeCookie::set('whty_siteid', $siteid);
            $this->showMessage(L('operation_success'));
        }
        else
        {
            FreeCookie::set('whty_admin_showmodule', 1);
            $this->showMessage(L('operation_failure'),'',0);
        }
    }

    //切换WEB/WAP
    public function changeShowmoduleAction()
    {
        if ($showmodule = $this->getRequest()->getGet('choose_value'))
        {
            FreeCookie::set('whty_admin_showmodule', $showmodule);
            $this->showMessage(L('operation_success'));
        }
        else
        {
            FreeCookie::set('whty_admin_showmodule', 1);
            $this->showMessage(L('operation_failure'),'',0);
        }
    }
}

