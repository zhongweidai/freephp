<?php
Free::loadClass('AbstractFreeUpload',PC_PATH . 'libs/upload',0);
/**
 * 表单文件上传
 *
 */
class FreeFormUpload extends AbstractFreeUpload {

	/**
	 * 初始化允许用户上传的类型
	 *
	 * @param array $allowType
	 */
	public function __construct($allowType = array()) {
		$this->setAllowType($allowType);
	}

	/*
	 * (non-PHPdoc)
	 * @see AbstractFreeUpload::postUpload()
	 */
	protected function postUpload($tmp_name, $filename) {
		if (strpos($filename, '..') !== false || strpos($filename, '.php.') !== false || preg_match('/\.php$/', 
			$filename)) {
			exit('illegal file type!');
		}
		FreeFolder::mkRecur(dirname($filename));
		if (function_exists("move_uploaded_file") && @move_uploaded_file($tmp_name, $filename)) {
			@unlink($tmp_name);
			@chmod($filename, 0777);
			return filesize($filename);
		} elseif (@copy($tmp_name, $filename)) {
			@unlink($tmp_name);
			@chmod($filename, 0777);
			return filesize($filename);
		} elseif (is_readable($tmp_name)) {
			Free::loadClass('FreeFile',PC_PATH . 'libs/utility',0);
			FreeFile::write($filename, FreeFile::read($tmp_name));
			@unlink($tmp_name);
			if (file_exists($filename)) {
				@chmod($filename, 0777);
				return filesize($filename);
			}
		}
		return false;
	}
}