<?php
/**
 * 产品service基类
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package src
 */
class FreeProServer extends AbstractFreeServer
{
	public function model()
	{
	
	}
	
	public function getModel()
	{
		return $this->model;	
	}
}

/**
 * 产品Tag基类
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package src
 */
class FreeProTag extends FreeBase implements IFreeTag
{
	public function count($data)
	{
		return 0;
	}
    
    public function getSiteId()
    {
        return FreePro::getApp()->getSiteId();
    }
}
?>