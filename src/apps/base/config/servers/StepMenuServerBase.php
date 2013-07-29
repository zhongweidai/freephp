<?php

defined('IN_FREE') or exit('No permission resources.');

class StepMenuServerBase extends FreeProServer
{	
	
    
    public function __construct()
    {
        parent::__construct();
        $this->namespace = 'stepselect';
    }
    
    public function model()
    {
        $this->model = M('step_menu','config');
    }
    /**
     * 删除某个联动菜单选项
     **/
     public function del($id)
     {
        if($this->model->getOne(array('FATHERID'=>$id)))
        {
            $this->error = '有子选项的节点不能删除';
            return false;
        }else{
            if($this->model->delete(array('ID'=>$id)))
            {
                return true;
            }else{
                $this->error = $this->model->getError();
                return false;
            }
        }
     }
    /**
     * 更新某个key的缓存 key为空 则更新全部
     * @param $step  联动菜单缓存key
     **/
    public function cache($step='')
    {
        if($step === '')
        {
            $config_server = S('Config','config');
            $steps = $config_server->get($this->namespace);
            foreach($steps as $key => $r)
            {
                $item = $this->model->select(array('STEP'=>$key));
                $this->getComponent('cache')->set($this->namespace . '-' . $key,$item,'config');
            }  
        }else{
            $item = $this->model->select(array('STEP'=>$step));
            $this->getComponent('cache')->set($this->namespace . '-' . $step,$item,'config');
        }
        return true;
    }
    
    public function getMenuItem($menu_name,$fid=0)
    {
        if($menu_name == 'site_list')
        {
            $list = S('Site','admin')->getCache();

            $ret = array();
            foreach($list as $key => $r)
            {
                $ret[$key] = $r;
                $ret[$key]['ID'] = $r['CODE'];
            }
            return $ret;
        }else{
            return $this->getComponent('cache')->get($this->namespace . '-' . $menu_name,'config');
        }
    }
}
?>