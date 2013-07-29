<?php
/**
 * 内容回收站配置
 * @author qiuhuijian
 *
 */
class RecycleBaseController extends AdminFrontController{
	protected $namespace;
	protected $server;
	function __construct()
	{
		parent::__construct();
		$namespace = $this->getRequest()->getGet('ns');
		$this->namespace = $namespace ? $namespace : 'recycle';
		$this->server = S('Config',$this->_module);
	}
	/**
	 * 初始页面
	 */
	public function initAction()
	{
		$site_configs = $this->server->get($this->namespace);
		$current_config = C('database','mysql');
		foreach ($current_config as $key=>$val)
		{
			$k ='RECYCLE_'.$key;
			if ( empty($site_configs[$k]))
			{
				$site_configs[$k] = $val;
			}
		}		
		if ( empty($site_configs['RECYCLE_TABLE_SUFF']))
		{
			$site_configs['RECYCLE_TABLE_SUFF'] = '_BAK';
		}
		if (empty($site_configs['RECYCLE_driver']))
		{
			$site_configs['RECYCLE_driver'] = 'Mysql';
		}
		$this->assign('config',$site_configs);
		$this->template();
	}
}