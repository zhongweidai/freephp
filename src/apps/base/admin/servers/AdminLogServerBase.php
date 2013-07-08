<?php

defined('IN_FREE') or exit('No permission resources.');

class AdminLogServerBase extends FreeProServer
{

	public $model;

    function __construct()
    {
		parent::__construct();
    }

	public function model()
	{
		$this->model = M('admin_log','admin');
	}
    
    public function toLog($username,$userid)
    {
        $logs = array();
        $logs['USERNAME'] = $username;
        $logs['USERID'] = $userid;
        $logs['OP_TIME'] = SYS_TIME;
        $logs['URL'] = $this->getRequest()->getRequestUri();
        $logs['IP'] = $this->getRequest()->getClientIp();
        $sqls = $this->getComponent('log_container')->get('db_sql');
        $logs['EX_SQL'] = serialize($sqls);
        $logs['DATA_GET'] = serialize($this->getRequest()->getGet());
        $logs['DATA_POST'] = serialize($this->getRequest()->getPost());
        if($sqls)
        {
            return $this->model->insert($logs);
        }
        return true;   
    }

}

