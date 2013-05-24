<?php
Free::loadClass('AbstractFreeUpload',PC_PATH . 'libs/upload',0);
/**
 * ftp远程文件上传
 *
 */
class FreeFtpUpload extends AbstractFreeUpload {

	private $config = array();

	private $ftp = null;

	/**
	 * 构造函数设置远程ftp链接信息
	 *
	 * @param array $config
	 */
	public function __construct($config) {
		$this->setConfig($config);
	}

	/* (non-PHPdoc)
	 * @see AbstractFreeUpload::postUpload()
	 */
	protected function postUpload($tmp_name, $filename) {
		$ftp = $this->getFtpConnection();
		if (!($size = $ftp->upload($tmp_name, $filename))) return false;
		@unlink($tmp_name);
		return $size;
	}

	/**
	 * 设置ftp链接配置文件
	 * 
	 * @param array $config ftp链接信息
	 * @return bool
	 */
	public function setConfig($config) {
		if (!is_array($config)) return false;
		$this->config = $config;
		return true;
	}

	/**
	 * 获得ftp链接对象
	 * 
	 * @return AbstractFreeFtp
	 */
	private function getFtpConnection() {
		if (is_object($this->ftp)) return $this->ftp;
		if (function_exists('ftp_connect')) {
			Free::loadClass('FreeFtp',PC_PATH . 'libs/ftp',0);
			$this->ftp = new FreeFtp($this->config);
			return $this->ftp;
		}
		Free::loadClass('FreeSocketFtp',PC_PATH . 'libs/ftp',0);
		$this->ftp = new FreeSocketFtp($this->config);
		return $this->ftp;
	}
}