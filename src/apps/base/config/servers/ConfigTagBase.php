<?php
defined('IN_FREE') or exit('No permission resources.');

class SnsTagBase extends FreeProTag
{

    function __construct()
    {

    }
    
    public function count()
    {
        
    }
    
     
     public function siteList($data)
     {
        $psiteid = isset($data['psiteid']) ? $data['psiteid'] : DEFAULT_PROVINCE;
        if(isset($data['is_all']) && ($data['is_all'] == 1))
        {
            $site_list = S('Site','admin')->getCache();
        }else{
            $site_list = S('Site','admin')->getCache($data['psite']); 
        }
        
        return $site_list;
        
        
     }
}
?>