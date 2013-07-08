<?php

defined('IN_FREE') or exit('No permission resources.');

class AdminLogBaseController extends AdminFrontController
{

    function __construct()
    {
		parent::__construct();
        $this->server = S('AdminLog','admin');
    }

    /**
     * 首页
     */
	public function initAction()
	{
		$page = max(intval($this->getRequest()->getGet('page')),1);
        $startime = $this->getRequest()->getGet('startime');
        $endtime = $this->getRequest()->getGet('endtime');
        $username = $this->getRequest()->getGet('username');
        $where = array();
        if($username)
        {
            $where['USERNAME'] = array('LIKE',"%$username%"); 
            $this->assign('username',$username);
        }
        if($startime)
        {
            $where['OP_TIME'] = array('GT',strtotime($startime));
            $this->assign('startime',strtotime($startime));
        }
        if($endtime)
        {
            $where['OP_TIME'] = array('LT',strtotime($endtime) + 3600*24);
            $this->assign('endtime',strtotime($endtime));
        }
		$logs = $this->server->getModel()->listInfo($where,array(),'OP_TIME DESC',$page);
        $this->assign('logs',$logs);
		$this->assign('pages',$this->server->getModel()->pages);
		 $this->template();
    }
	
	/**
	* 编辑管理员
	**/
	public function showAction()
	{
    	$id = $this->getRequest()->getGet('id');
    	$id = !empty($id) ? intval($id) : $this->showMessage(L('param_error'),'',0);
    	$admin =  $this->server->getModel()->getOne(array('ID'=>$id));
    	$this->assign('admin',$admin);
    	$this->template();
	}
	
	/**
	 * 删除管理员
	 */
	public function deleteAction()
	{
         $day = $this->getRequest()->getGet('date');
         $day = !empty($day) ? intval($day) : $this->showMessage(L('param_error'),'',0);
         $where['OP_TIME'] = array('LT',strtotime(date('Y-m-d',SYS_TIME)) - ($day-1)*3600*24);
         if($this->server->getModel()->delete($where))
         {
            $this->showMessage(L('opera_success'),'',1);
         }else{
            $this->showMessage(L('param_error'),'',0);
         }
          
	}
}

