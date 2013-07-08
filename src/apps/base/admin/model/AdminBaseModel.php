<?php
defined('IN_FREE') or exit('No permission resources.');

class AdminBaseModel extends FreeModel
{
	public $table_name = 'ADMIN';
	
	public function __construct()
    {
		parent::__construct();
    }    //首页

}

