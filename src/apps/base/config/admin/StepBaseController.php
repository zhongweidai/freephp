<?php

defined('IN_WHTY') or exit('No permission resources.');

class StepBaseController extends AdminFrontController
{
	protected $namespace;
	protected $server;
	function __construct()
	{
		parent::__construct();
	//	$namespace = $this->getRequest()->getGet('ns');
		$this->namespace = 'stepselect';
		$this->server = S('Config','config');
        $this->item_server = S('StepMenu','config');
	}
    
    public function initAction()
    {
        $steps = $this->server->get($this->namespace);
		$this->assign('steps',$steps);
		$this->template();
    }
    /**
     * 添加关联菜单
     **/
    public function addAction()
    {
        if($this->getRequest()->isPost())
        {
            $info = $this->getRequest()->getPost('info');
            $data = array();
            if($this->server->getModel()->getOne(array('NAMESPACE'=>$this->namespace,'NAME'=>$info['KEY'])))
            {
                $this->showMessage('缓存key重复','',0);
            }else{
                $data[$info['KEY']] = array('name' => $info['NAME'],'is_system'=>0);
                if($this->server->edit($data,$this->namespace))
                {
                    $this->showMessage('添加成功','',1);
                }else{
                    $this->showMessage($this->server->getError(),'',0);
                } 
            }
        }else{
            $this->template();
        }
    }
    
    public function delAction()
    {
        $name = $this->getRequest()->getGet('name');
        if($this->server->getModel()->delete(array('NAMESPACE'=>$this->namespace,'NAME'=>$name)))
        {
            $this->showMessage('删除成功','',1);
        }else{
            $this->showMessage($this->server->getModel()->getError());
        }
    }
    /**
     * 单个编辑某个信息
     **/
    public function editAction()
    {
        $info = $this->getRequest()->getPost();
        $this->server->getModel()->closeAuto();
        $steps = $this->server->get($this->namespace);
        if(!isset($steps[$info['id']]))
        {
            $this->showMessage('参数错误','',1);
        }
        $data[$info['id']] = $steps[$info['id']];
        $data[$info['id']][$info['field']] = $info['value'];
        //dump($data);
        $this->server->edit($data,$this->namespace);
        $this->showMessage('修改成功','',1,$info['value']);
    }
    /**
     * 某个联动菜单展现管理
     **/
    public function showAction()
    {
        $steps = $this->server->get($this->namespace);
        $name = $this->getRequest()->getGet('name');
        
        $this->assign('name',$name);
        $step = $steps[$name];
        $this->assign('step',$step);
        
        $items = $this->item_server->getModel()->select(array('STEP'=>$name));
        $tree = E('tree');
        $tree_array = array();
        foreach((array)$items as $key => $r)
        {
            $r['del_url'] = U('config/step/delItem',array('id'=>$r['ID']));
            $tree_array[$r['ID']] = $r;
            
        }
        
       // dump($tree_array);exit;
        $tree->init ($tree_array);
        $select_str = '<select class="select_3" name="item[FATHERID]"><option value="0">一级选择</option>';
        
        $s_str = "<option value='\$ID'>\$spacer \$NAME</option>";
        $S_str2 = "<optgroup label='\$spacer \$NAME'></optgroup>";
        
        $select_str .= $tree->get_tree_category(0, $s_str, $S_str2);
        $select_str .= '</select>';
        
        $this->assign('select_str',$select_str);
        $tree->init ($tree_array);
        $str = "<tbody><tr>
            <td></td>
            <td ><input name='data[\$ID][ORDERNO]' type='text' class='input length_0 mr10' value='\$ORDERNO' />	
                \$spacer<input name='data[\$ID][NAME]' type='text' class='input length_3 mr5' value='\$NAME' />
                \$display_icon
            </td>
            <td>
				<input name='data[\$ID][VALUE]'  class='input length_2 mr5' value='\$VALUE' />
            </td>
            <td>
				<a href='\$del_url' class='mr10 J_ajax_del'>[删除]</a>
			</td>
		</tr></tbody>";
        $tree_step = $tree->get_tree ( 0, $str );
        $this->assign('tree_step',$tree_step);
        $this->template();  
    }
     /**
     * 批量添加联动菜单选项
     */
    public function addItemAction()
    {
        $name = $this->getRequest()->getGet('name');
        $item = $this->getRequest()->getPost('item');
        if(empty($item['NAME']))
        {
            $this->showMessage('选项名称不能为空','',0);
        }
		if(strpos($item['NAME'], ',')) {
            $arr = explode(',', $item['NAME']);
		}else{
            $arr = array($item['NAME']);
		}
                
        $item['STEP'] = $name;
        $item['VALUE'] = 0;
        foreach($arr as $key => $r)
        {
            $item['NAME'] = $r;
            if(!$this->item_server->getModel()->insert($item))
            {
                $this->showMessage($this->item_server->getModel()->getError(),'',0);
            }
        }        
        
        $this->showMessage('添加成功','',1);
    }
     /**
     * 删除联动菜单选项
     */
    public function delItemAction()
    {
        $id = $this->getRequest()->getGet('id');
        if($this->item_server->del($id))
        {
            $this->showMessage('删除成功','',1);
        }else{
            $this->showMessage($this->item_server->getError(),'',0);
        }
    }
    /**
     * 批量编辑联动菜单选项
     */
    public function editItemAction()
    {
        $items = $this->getRequest()->getPost('data');
        //dump($items);
        foreach($items as $key => $r)
        {
            if(!$this->item_server->getModel()->update($r,array('ID'=>$key)))
            {
                $this->showMessage($this->item_server->getModel()->getError(),'',0);
            }
        }
        $this->showMessage('操作成功','',1);
    }
    
    public  function cacheAction()
    {
        $step_name = $this->getRequest()->getGet('name');
        if(empty($step_name))
        {
            $this->showMessage('缓存key参数缺失','',0);
        }
        $this->item_server->cache($step_name);
        $this->showMessage('缓存已更新','',1);
    }
    
    public function cacheAllAction()
    {
        $this->item_server->cache();
        $this->showMessage('缓存已更新','',1);
    }
  
}
