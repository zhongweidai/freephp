<?php
/**
 * 产品控制器基类
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package src
 */
class FreeProFrontController extends FreeController
{
	function __construct()
	{
		parent::__construct();
	}

	public function showMessage($msg, $url_forward = 'goback', $status=1,$data ='',$ms = 1250)
	{
	
		include $this->template('common', 'message');
		exit;
	}
	/**
	*	获取当前省ID
	**/	
	public function getPsite()
	{
		$psiteid = FreeCookie::get('whty_psiteid');
		return $psiteid ? $psiteid : DEFAULTPROVINCE;
	}

	/**
	*	获取当前城市ID
	**/
	public function getSiteId()
	{

		if (defined('SITEID')) {
			$siteid = SITEID;
		} else {
			$siteid = WhtyCookie::get('whty_siteid');
		}
		return $siteid ? $siteid : DEFAULT_CITY;
	}

	/**
	*	获取当前城市信息
	**/
	public function getSiteInfo($code= '')
	{
		static $siteinfo = '';
		if($siteinfo === '')
		{
			$siteid = $this->getSiteId();
			$siteinfos = $this->getComponent('cache')->get('citylist','commons');
			$siteinfo = isset($siteinfo[$siteid]) ?  $siteinfo[$siteid] : array();
		}
		if(!empty($code))
		{
			return isset($siteinfo[$code]) ? $siteinfo[$code] : '';
		}else{
			return $siteinfo;
		}
	}

}
?>