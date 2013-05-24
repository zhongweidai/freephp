<?php
defined('IN_FREE') or exit('No permission resources.');

class IndexBaseController extends FreeController
{

	function __construct()
	{
		parent::__construct();
	}
	
	public function initAction()
	{
		$this->template();
	}
}