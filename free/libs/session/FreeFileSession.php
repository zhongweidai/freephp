<?php
/**
 *  文件session处理类
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package base
 */
class FreeFileSession {
    function __construct() {
		$path = CACHE_PATH.'sessions';
		ini_set('session.save_handler', 'files');
		session_save_path($path);
		session_start();
		
    }
}
?>