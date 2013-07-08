<?php

defined('IN_FREE') or exit('No permission resources.');

class SiteBaseController extends AdminFrontController
{
	protected $server;
    function __construct()
    {
		parent::__construct();
		$this->server = S('Site',$this->_module);
    }

    //首页
	public function initAction()
	{
		$sites = $this->server->getSiteTree();
		//var_dump($sites);
		$this->assign('sites',$sites);
		$this->template();
    }
	
	/**
	* 编辑站点
	**/
	public function editAction()
	{
		if($this->getRequest()->isPost())
		{
			$id = $this->getRequest()->getPost('id');
			$id = !empty($id) ? intval($id) : $this->showMessage(L('param_error'),'',0);
			$sites = WhtyString::trim($this->getRequest()->getPost('info'));
			$attachment = S('Attachment','Attachment');
			if(!empty($_FILES['icon']['name']))
			{
				if(!($site_image = $attachment->image($_FILES['icon'],'sites')))
				{
					$this->showMessage($attachment->getError(),'',0);
				}
				$sites['IMAGE'] = $site_image ;
			}
			if($this->server->edit($sites,array('ID'=>$id)))
			{
				$this->showMessage(L('site_opera_success'));
			}else{
				$this->showMessage($this->server->getError(),'',0);
			}
		}else{
			$id = $this->getRequest()->getGet('id');
			$id = !empty($id) ? intval($id) : $this->showMessage(L('param_error'),'',0);
			$sites = $this->server->getAll();

			$this->assign('sites',$sites);
			$this->assign('site',$this->server->getModel()->getOne(array('ID'=>$id)));
			//$f_sites = $this->getModel()->select(array('FATHERID'=>0));
			//$this->assign('f_sites',$f_sites);
			
			$this->template();
		}
	}
	
	/**
	*	树形站点操作
	**/
	public function operaAction()
	{
	//var_dump($_POST);
		$r_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$this->server = S('Site',$this->_module);
		$this->server->getModel()->closeHash();
		if(isset($_POST['data'] ) && is_array($_POST['data'] ))
		{
			foreach($_POST['data'] as $key => $val)
			{
				$id = $val['ID'];
				unset($val['ID']);
				$val['STATUS'] = $val['STATUS'] == '1' ? 1 : 0;
				if(!$this->server->edit($val,array('ID'=>$id )))
				{
					$this->showMessage('数据已提交，<span class="red">' . $this->server->getError() . '</span>','',1);
					return ;
				}
			}
		}
		if(isset($_POST['newdata'] ) && is_array($_POST['newdata']))
		{
			foreach($_POST['newdata'] as $key => $val)
			{
				if(stripos($key,'root') !== false)
				{
					if(isset($val['tempid'])) 
					{
						unset($val['tempid']);
					}
					$val['FATHERID'] = isset($val['FATHERID']) ? $val['FATHERID'] : $r_id;
					$val['STATUS'] = $val['STATUS'] == '1' ? 1 : 0;
					$re_ids['temp_' . $key] = $val['CODE'];
					if(!($this->server->add($val)))
					{
						$this->showMessage('数据已提交，<span class="red">' . $this->server->getModel()->getError() . '</span>','',0);
						return ;
					}
				}
			}
			//var_dump($re_ids);
			foreach($_POST['newdata'] as $key => $val)
			{
				if(stripos($key,'child') !== false)
				{
					if(isset($val['tempid'])) 
					{
						unset($val['tempid']);
					}
					$val['FATHERID'] = isset($val['FATHERID']) && (stripos($val['FATHERID'],'temp')!==false)? $re_ids[$val['FATHERID']] : $val['FATHERID'];
					$val['STATUS'] = $val['STATUS'] == 'on' ? 1 : 0;
					//var_dump($val);
					$re_ids[$key] = $val['CODE'];
					if(!($this->server->add($val)))
					{
						$this->showMessage('数据已提交，<span class="red">' .$this->server->getModel()->getError() . '</span>','',0);
						return ;
					}
				}
			}
		}
		$this->checkCsrf();
		$this->showMessage(L('site_opera_success'));
	}
	/**
	 * 删除
	 */
	public function deleteAction()
	{
		$id = $this->getRequest()->getGet('id');
		$id = !empty($id) ? intval($id) : $this->showMessage(L('param_error'),'',0);
		if($this->server->getModel()->delete(array('ID'=>$id)))
		{
			$this->showMessage(L('site_delete_success'));
		}else{
			$this->showMessage($this->server->getModel()->getError(),'',0);
		}
	}
	
	public function cacheAction()
	{
		if($this->server->cache())
		{
			$this->showMessage(L('site_cache_success'));
		}else{
			$this->showMessage(L('site_cache_fail'),'',0);
		}
		
	}
}

