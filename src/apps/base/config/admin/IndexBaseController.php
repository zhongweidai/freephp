<?php

defined('IN_FREE') or exit('No permission resources.');

class IndexBaseController extends AdminFrontController
{
	protected $namespace;
	protected $server;
	function __construct()
	{
		parent::__construct();
		$namespace = $this->getRequest()->getGet('ns');
		$this->namespace = $namespace ? $namespace : 'site';
		$this->server = S('Config',$this->_module);
	}

	//网站配置
	public function initAction()
	{
		$site_configs = $this->server->get($this->namespace);
		$this->assign('config',$site_configs);
		$this->template($this->_module,'config_' . $this->namespace . '_init');
	}

	//修改网站配置
	public function editAction()
	{
		if($this->getRequest()->isPost())
		{
			$info = $this->getRequest()->getPost();
			if($this->server->edit($info,$this->namespace))
			{
				$this->showMessage(L('config_opera_success'));
			}else{
				$this->showMessage($this->server->getError(),'',0);
			}
		}else{
			$this->showMessage(L('param_error'),'',0);
		}
	}

	public function viewImgAction()
	{
		echo json_encode(array("data"=>array("img"=>"http://www.baidu.com/img/baidu_sylogo1.gif"),"state"=>"success"));
	}

}
