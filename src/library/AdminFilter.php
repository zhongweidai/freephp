<?php
/**
 * Web产品filter类
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package base
 */
class AdminFilter extends AbstractFreeFilter{
	
	public function __construct() {
	}

	
	/**
	 * 处理
	 */
	public function handle() 
	{
		$re = $this->checkAdmin();
		if($re !== false)
		{
			return $re;
		}
		return false;
	}

	/**
	 * 判断用户是否已经登陆
	 */
	final public function checkAdmin() {
		if(FreePro::getApp()->_module =='admin' && FreePro::getApp()->_controller =='index' && in_array(FreePro::getApp()->_action, array('login'))) {
			return false;
		} 
		if(!S('admin','admin')->isLogin()) 
		{
			return 'admin/index/login';
		}
		return $this->checkPriv();
	}
	
	public function checkPriv()
	{
		$roleid = $_SESSION['admin']['roleid'];
		if(FreePro::getApp()->_module =='admin' && in_array(FreePro::getApp()->_controller,array('index','home'))) {
			return false;
		}
        //是否写日志
        FreePro::getApp()->_action != 'init' && FreePro::getApp()->is_log = 1;
        if($roleid === 0)
        {
            return false;
        } 
		$role = S('AdminMenu','admin')->getMenuRole(0,$roleid);
		$str = '';
		foreach($role as $key => $r)
		{
			$str .= empty($str) ? $r['QUERY'] : ',' . $r['QUERY'];
		}
        
		$priv = explode(',',$str);
		$url = FreePro::getApp()->_module . '/' . FreePro::getApp()->_controller . '/' .FreePro::getApp()->_action;
		$url_ = FreePro::getApp()->_module . '/' . FreePro::getApp()->_controller . '/*';
		if(in_array($url,$priv) || in_array($url_,$priv))
		{
            
			return false;
		}else{
			FreePro::getApp()->showMessage('No permission','',0);
		}
	}
}
?>