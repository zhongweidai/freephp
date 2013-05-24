<?php
/**
 * 文件工具类
 *
 */
class FreeFile {
	/**
	 * 以读的方式打开文件，具有较强的平台移植性
	 * 
	 * @var string 
	 */
	const READ = 'rb';
	/**
	 * 以读写的方式打开文件，具有较强的平台移植性
	 * 
	 * @var string 
	 */
	const READWRITE = 'rb+';
	/**
	 * 以写的方式打开文件，具有较强的平台移植性
	 * 
	 * @var string 
	 */
	const WRITE = 'wb';
	/**
	 * 以读写的方式打开文件，具有较强的平台移植性
	 * 
	 * @var string 
	 */
	const WRITEREAD = 'wb+';
	/**
	 * 以追加写入方式打开文件，具有较强的平台移植性
	 * 
	 * @var string 
	 */
	const APPEND_WRITE = 'ab';
	/**
	 * 以追加读写入方式打开文件，具有较强的平台移植性
	 * 
	 * @var string 
	 */
	const APPEND_WRITEREAD = 'ab+';
	
	/**
	 * 删除文件
	 * 
	 * @param string $filename 文件名称
	 * @return boolean
	 */
	public static function del($filename) {
		return @unlink($filename);
	}

	/**
	 * 保存文件
	 * 
	 * @param string $fileName          保存的文件名
	 * @param mixed $data               保存的数据
	 * @param boolean $isBuildReturn    是否组装保存的数据是return $params的格式，如果没有则以变量声明的方式保存,默认为true则以return的方式保存
	 * @param string $method            打开文件方式，默认为rb+的形式
	 * @param boolean $ifLock           是否对文件加锁，默认为true即加锁
	 */
	public static function savePhpData($fileName, $data, $isBuildReturn = true, $method = self::READWRITE, $ifLock = true) {
		$temp = "<?php\r\n ";
		if (!$isBuildReturn && is_array($data)) {
			foreach ($data as $key => $value) {
				if (!preg_match('/^\w+$/', $key)) continue;
				$temp .= "\$" . $key . " = " . FreeString::varToString($value) . ";\r\n";
			}
			$temp .= "\r\n?>";
		} else {
			($isBuildReturn) && $temp .= " return ";
			$temp .= FreeString::varToString($data) . ";\r\n?>";
		}
		return self::write($fileName, $temp, $method, $ifLock);
	}

	/**
	 * 写文件
	 *
	 * @param string $fileName 文件绝对路径
	 * @param string $data 数据
	 * @param string $method 读写模式,默认模式为rb+
	 * @param bool $ifLock 是否锁文件，默认为true即加锁
	 * @param bool $ifCheckPath 是否检查文件名中的“..”，默认为true即检查
	 * @param bool $ifChmod 是否将文件属性改为可读写,默认为true
	 * @return int 返回写入的字节数
	 */
	public static function write($fileName, $data, $method = self::READWRITE, $ifLock = false, $ifCheckPath = true, $ifChmod = true) {
		touch($fileName);
		if (!$handle = fopen($fileName, $method)) return false;
		$ifLock && flock($handle, LOCK_EX);
		$writeCheck = fwrite($handle, $data);
		$method == self::READWRITE && ftruncate($handle, strlen($data));
		fclose($handle);
		$ifChmod && chmod($fileName, 0777);
		return $writeCheck;
	}

	/**
	 * 读取文件
	 *
	 * @param string $fileName 文件绝对路径
	 * @param string $method 读取模式默认模式为rb
	 * @return string 从文件中读取的数据
	 */
	public static function read($fileName, $method = self::READ) {
		$data = '';
		if (!$handle = fopen($fileName, $method)) return false;
		while (!feof($handle))
			$data .= fgets($handle, 4096);
		fclose($handle);
		return $data;
	}

	/**
	 * @param string $fileName
	 * @return boolean
	 */
	public static function isFile($fileName) {
		return $fileName ? is_file($fileName) : false;
	}

	/**
	 * 取得文件信息
	 * 
	 * @param string $fileName 文件名字
	 * @return array 文件信息
	 */
	public static function getInfo($fileName) {
		return self::isFile($fileName) ? stat($fileName) : array();
	}

	/**
	 * 取得文件后缀
	 * 
	 * @param string $filename 文件名称
	 * @return string
	 */
	public static function getSuffix($filename) {
		if (false === ($rpos = strrpos($filename, '.'))) return '';
		return substr($filename, $rpos + 1);
	}
	
	
	/**
	* 转化 \ 为 /
	* 
	* @param	string	$path	路径
	* @return	string	路径
	*/
	public static function dirPath($path) {
		$path = str_replace('\\', '/', $path);
		if(substr($path, -1) != '/') $path = $path.'/';
		return $path;
	}
	/**
	* 创建目录
	* 
	* @param	string	$path	路径
	* @param	string	$mode	属性
	* @return	string	如果已经存在则返回true，否则为flase
	*/
	public static function dirCreate($path, $mode = 0777) {
		if(is_dir($path)) return TRUE;
		$ftp_enable = 0;
		$path = self::dirPath($path);
		$temp = explode('/', $path);
		$cur_dir = '';
		$max = count($temp) - 1;
		for($i=0; $i<$max; $i++) {
			$cur_dir .= $temp[$i].'/';
			if (@is_dir($cur_dir)) continue;
			@mkdir($cur_dir, 0777,true);
			@chmod($cur_dir, 0777);
		}
		return is_dir($path);
	}
	/**
	* 拷贝目录及下面所有文件
	* 
	* @param	string	$fromdir	原路径
	* @param	string	$todir		目标路径
	* @return	string	如果目标路径不存在则返回false，否则为true
	*/
	public static function dirCopy($fromdir, $todir) {
		$fromdir = self::dirPath($fromdir);
		$todir = self::dirPath($todir);
		if (!is_dir($fromdir)) return FALSE;
		if (!is_dir($todir)) self::dirCreate($todir);
		$list = glob($fromdir.'*');
		if (!empty($list)) {
			foreach($list as $v) {
				$path = $todir.basename($v);
				if(is_dir($v)) {
					self::dirCopy($v, $path);
				} else {
					copy($v, $path);
					@chmod($path, 0777);
				}
			}
		}
	    return TRUE;
	}
	/**
	* 转换目录下面的所有文件编码格式
	* 
	* @param	string	$in_charset		原字符集
	* @param	string	$out_charset	目标字符集
	* @param	string	$dir			目录地址
	* @param	string	$fileexts		转换的文件格式
	* @return	string	如果原字符集和目标字符集相同则返回false，否则为true
	*/
	public static  function dirIconv($in_charset, $out_charset, $dir, $fileexts = 'php|html|htm|shtml|shtm|js|txt|xml') {
		if($in_charset == $out_charset) return false;
		$list = self::dirList($dir);
		foreach($list as $v) {
			if (preg_match("/\.($fileexts)/i", $v) && is_file($v)){
				file_put_contents($v, iconv($in_charset, $out_charset, file_get_contents($v)));
			}
		}
		return true;
	}
	/**
	* 列出目录下所有文件
	* 
	* @param	string	$path		路径
	* @param	string	$exts		扩展名
	* @param	array	$list		增加的文件列表
	* @return	array	所有满足条件的文件
	*/
	public static function dirList($path, $exts = '', $list= array()) {
		$path = self::dirPath($path);
		$files = glob($path.'*');
		foreach($files as $v) {
			$fileext = fileext($v);
			if (!$exts || preg_match("/\.($exts)/i", $v)) {
				$list[] = $v;
				if (is_dir($v)) {
					$list = self::dirList($v, $exts, $list);
				}
			}
		}
		return $list;
	}
	/**
	* 设置目录下面的所有文件的访问和修改时间
	* 
	* @param	string	$path		路径
	* @param	int		$mtime		修改时间
	* @param	int		$atime		访问时间
	* @return	array	不是目录时返回false，否则返回 true
	*/
	public static function dirTouch($path, $mtime = TIME, $atime = TIME) {
		if (!is_dir($path)) return false;
		$path = self::dirPath($path);
		if (!is_dir($path)) touch($path, $mtime, $atime);
		$files = glob($path.'*');
		foreach($files as $v) {
			is_dir($v) ? self::dirTouch($v, $mtime, $atime) : touch($v, $mtime, $atime);
		}
		return true;
	}
	/**
	* 目录列表
	* 
	* @param	string	$dir		路径
	* @param	int		$parentid	父id
	* @param	array	$dirs		传入的目录
	* @return	array	返回目录列表
	*/
	public static function dirTree($dir, $parentid = 0, $dirs = array()) {
		global $id;
		if ($parentid == 0) $id = 0;
		$list = glob($dir.'*');
		foreach($list as $v) {
			if (is_dir($v)) {
	            $id++;
				$dirs[$id] = array('id'=>$id,'parentid'=>$parentid, 'name'=>basename($v), 'dir'=>$v.'/');
				$dirs = self::dirTree($v.'/', $id, $dirs);
			}
		}
		return $dirs;
	}
	
	/**
	* 删除目录及目录下面的所有文件
	* 
	* @param	string	$dir		路径
	* @return	bool	如果成功则返回 TRUE，失败则返回 FALSE
	*/
	public static function dirDelete($dir) {
		$dir = self::dirPath($dir);
		if (!is_dir($dir)) return FALSE;
		$list = glob($dir.'*');
		foreach($list as $v) {
			is_dir($v) ? self::dirDelete($v) : @unlink($v);
		}
	    return @rmdir($dir);
	}
}