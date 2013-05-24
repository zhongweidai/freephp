<?php
/**
 * 日志容器
 * 
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package base
 */
class FreeLogContainer extends FreeBase 
{
	private $key = 'LogContainer';
    
    private $container = array();
    
    public function __construct()
    {
        
    }
	
    public function put($log,$key='error')
    {
        $this->container[$key][] = $log;
        return true;
    }
	
    public function flush()
    {
        return true;
    }
    
    public function get($key='error')
    {
        return isset($this->container[$key]) ? $this->container[$key] : NULL;
    }
    /**
     * 统计
     */
    public function tj()
    {
        $od = $this->get('tjdata');
        $data = array();
        if(is_array($od))
        {
            foreach($od as $key => $r)
            {
                $data += $r;
            }
        }
    }
}

?>