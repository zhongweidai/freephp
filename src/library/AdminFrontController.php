<?php
/**
 * admin产品控制器基类
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package src
 */
class AdminFrontController extends FreeProFrontController
{
    public $is_log = 0;
	public function __construct()
	{
		parent::__construct();
        
		$this->setSite();
        $this->getShowmodule();
	}
    
    /**
     *  获取管理员信息
     **/
     public function getCurrentAdminInfo($key = '')
     {
        return S('Admin','admin')->getCurrentInfo($key);
     }
	/**
	public function template($m='',$filename='',$style='',$version=PLATFORM_VERSION)
	{
		$m = empty($m) ? $this->_module : $m;
		empty($filename) && $filename = $this->_controller . '_' . $this->_action;
		$app = strtolower(APP);
		if(file_exists(WHTY_PATH.'src'.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$version.DIRECTORY_SEPARATOR.$m.DIRECTORY_SEPARATOR . $app . DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$filename.'.tpl.php'))
		{
			return WHTY_PATH.'src'.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$version.DIRECTORY_SEPARATOR.$m.DIRECTORY_SEPARATOR . $app . DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$filename.'.tpl.php';
		}else{
			return WHTY_PATH.'src'.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.'base'.DIRECTORY_SEPARATOR.$m.DIRECTORY_SEPARATOR . $app . DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$filename.'.tpl.php';
		}
	}
	**/
	public function template($__m__='',$__filename__='',$__style__='default',$__app__=APP)
	{
		empty($__m__) && $__m__ = $this->_module;
		empty($__filename__) && $__filename__ = $this->_controller . '_' . $this->_action;
		parent::template($__m__,$__filename__,$__style__,$__app__);
		//return $this->template_complie_path;
	}
	
	/**
	* 后台提示信息
	* @param string $msg 信息
	* @param string $url_forward 跳转
	* @param string $msg 信息
	* @param $data  数据
	* @param int $ms  停留时间
	* @return void
	**/
	public function showMessage($msg, $url_forward = '',$status=1,$data ='', $ms = 1250)
	{
	
		//注意有上传的提交表单，系统判断是不是ajax传输，需要提交的url增加_json=1
		if($this->getRequest()->getGet('_json') == 1 || $this->getRequest()->getIsAjaxRequest())
		{
			$re = array(
				'__error'	=>	$status == 1 ? '' : $msg,
				'referer'	=>	$url_forward ? $url_forward : '',
				'refresh'	=>	$url_forward == '' ? false : true,
				'state'	=>	$status == 1 ? 'success' : 'fail',
				'data'		=>	$data,
				'message'	=>	$msg
			);
			echo json_encode($re);
		}else{
			$this->assign('message',$msg);
			$this->assign('referer',$url_forward == '' ? '' : $url_forward);
			$this->assign('ms',$ms);
			$this->assign('refresh',$url_forward == '' ? 0 : 1);
			//$this->assign('dialog',$dialog);
			//$this->assign('returnjs',$returnjs);
			$this->template('admin', 'showmessage');
		}
		if($status != 1)exit;
	}
	/**
	*	获取当前后台展现方式
	**/
	public function getShowmodule()
	{
		$showmodule = FreeCookie::get('whty_admin_showmodule');
		$this->showmodule = $showmodule  ? $showmodule  : 1;
        return $this->showmodule;
	}
	
	public function setSite()
	{
		$siteid = $this->getRequest()->getGet('siteid');
		if($siteid)
		{
			FreeCookie::set('free_siteid',$siteid);
		}
		$psiteid = $this->getRequest()->getGet('psiteid');
		if($psiteid)
		{
			FreeCookie::set('free_psiteid',$psiteid);
		}
		return true;
	}
    
    /**
     * * 后处理方式
    * */
   	public function doAfter()
	{
	   if(C('system','is_admin_log') && $this->is_log == 1)
       {//  写入后台日志
    		$username = $this->getCurrentAdminInfo('username');
            $userid = $this->getCurrentAdminInfo('userid');
            $admin_log_server = S('AdminLog','admin');
            $admin_log_server->toLog($username,$userid);
    		return true;
        }
	} 
}   
?>