<?php
/**
 * 后台首页操作处理类
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package base
 */
class HomeBaseController extends AdminFrontController {
    function __construct()
    {
		parent::__construct();
    }
	/**
	 * 后台首页处理方法
	 */
	public function initAction() 
	{
        if (false != ($sendmail_path = ini_get('sendmail_path'))) {
			$sysMail = 'Unix Sendmail ( Path: '.$sendmail_path.')';
		} elseif (false != ($SMTP = ini_get('SMTP'))) {
			$sysMail = 'SMTP ( Server: '.$SMTP.')';
		} else {
			$sysMail = 'Disabled';
		}

		$sysinfo = array(
			'wind_version' => C('version', 'pc_version') .'-'. C('version', 'pc_release'),
			'php_version' => PHP_VERSION,
			'server_software' => str_replace('PHP/'. PHP_VERSION,'',$this->getRequest()->getServer('SERVER_SOFTWARE')),
			'mysql_version' => $this->getComponent('db')->version(),
			'max_upload' => ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'Disabled',
			'max_excute_time' => intval(ini_get('max_execution_time')).' seconds',
			'sys_mail' => $sysMail,
		);

		$this->assign('sysinfo', $sysinfo);
		$this->template();
	}
	
}

?>