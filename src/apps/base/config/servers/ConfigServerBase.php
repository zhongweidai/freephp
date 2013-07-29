<?php

defined('IN_FREE') or exit('No permission resources.');

class ConfigServerBase extends FreeProServer
{	
	protected $namespace = array();
    
	
	function __construct()
    {
		parent::__construct();
		$this->initNamespace();
    }

	public function model()
	{
		$this->model = M('common_config','config');
		$this->model->setValidate(array(
			array('NAME','1,63','{config name extends 63}',1,'length',FreeModel::MODEL_INSERT),
			array('NAMESPACE','1,63','{config namespace extends 63}',1,'length',FreeModel::MODEL_INSERT),
		));
	}
	
	protected function initNamespace()
	{
		$this->namespace = array(
			'global'	    =>		'global',
			'site'		    =>		'site',
			'login'	 	    =>		'login',
			'register'	    =>		'register',
			'credit'	    =>		'credit',
			'attachment'    =>      'attachment',
			'ftp' 		    =>      'ftp',
			'thumb'         =>      'thumb',
            'admin_workflow'=>      'admin_workflow',
            'stepselect'    =>      'stepselect',
			'recycle'		=> 		'recycle',
		); 	
	}
    /**
     * 数据库方式获取指定命名的配置（仅供后台）
     * */
	public function get($namespace)
	{
		if(!isset($this->namespace[$namespace]))
		{
			$this->error = L('param_error');
			return false;
		}
		$namespace = $this->namespace[$namespace];
		$configs = $this->model->select(array('NAMESPACE'=>$namespace));
		$return = array();
		if(is_array($configs))
		{
			foreach($configs as $key => $r)
			{
				$return[$r['NAME']] = $r['VTYPE'] == 'array' ? unserialize($r['VALUE']) : $r['VALUE'];
			}
		}
		return $return;
	}
	
	/**
	*	更新配置
	**/
	public function edit($info,$namespace)
	{
        if(isset($info['__hash__']))
        {
            unset($info['__hash__']);
        }
		if(!isset($this->namespace[$namespace]))
		{
			$this->error = L('param_error');
			return false;
		}
		$namespace = $this->namespace[$namespace];
		if(is_array($info))
		{
			foreach($info as $key => $r)
			{
				$data = array();
				if(is_array($r))
				{
					$data['VTYPE'] = 'array';
					$data['VALUE'] = serialize($r);
				}else{
					$data['VTYPE'] = 'string';
					$data['VALUE'] = $r;
				}
				if($this->model->select(array('NAME'=>$key,'NAMESPACE'=>$namespace)))
				{
					$re = $this->model->update($data,array('NAME'=>$key,'NAMESPACE'=>$namespace));
				}else{
					$data['NAME'] = $key;
					$data['NAMESPACE'] = $namespace;
					$re = $this->model->insert($data);
				}
				if(!$re)
				{
					$this->error = $this->model->getError();
					return false;
				}

			}
		}
        $this->cache($namespace);
		return true;
	}
	
	/**
	*	设置缓存
	**/
	public function cache($namespace='')
	{
		if(!is_array($namespace))
		{
			$re = $this->get($namespace);
			return $this->getComponent('cache')->set('config_' . $namespace,$re,'commons');
		}else{
			foreach($this->namespace as $key => $v)
			{
				$this->cache($key);
			}
			return true;
		}
	}
	
	/**
	*	通过缓存获取配置
    *    缓存方式获取指定命名的配置（前台使用 当然后台也可使用）
	**/
	public function getCache($namespace,$name='')
	{
		static $site_configs = NULL;
        
		if(!isset($site_configs[$namespace]))
        {
            $site_configs[$namespace] = $this->getComponent('cache')->get('config_' . $namespace,'commons');
             
        }

		if($name === '')
		{
			return $site_configs[$namespace];
		}else{
			return $site_configs[$namespace][$name];
		}
	}
}

