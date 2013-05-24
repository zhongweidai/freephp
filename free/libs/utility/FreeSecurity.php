<?php
/**
 * 字符、路径过滤等安全处理
 *
 */
class FreeSecurity {
	const SERCRETKEY = 'DAIZHONGWEI';
	/**
	 * 转义输出字符串
	 * 
	 * @param string $str 被转义的字符串
	 * @return string
	 */
	public static function escapeHTML($str) {
		if (!is_string($str)) return $str;
		return htmlspecialchars($str, ENT_QUOTES);
	}

	/**
	 * 转义字符串
	 * 
	 * @param array $array 被转移的数组
	 * @return array
	 */
	public static function escapeArrayHTML($array) {
		if (!is_array($array) || count($array) > 100) return $array;
		$_tmp = array();
		foreach ($array as $key => $value) {
			is_string($key) && $key = self::escapeHTML($key);
			$_tmp[$key] = self::escapeHTML($value);
		}
		return $_tmp;
	}

	/**
	 * 字符串加密
	 * 
	 * @param string $str 需要加密的字符串
	 * @param string $key 密钥
	 * @return string 加密后的结果
	 */
	public static function encrypt($str, $key=self::SERCRETKEY, $iv = '') {
		return self::authcode($str,'ENCODE',$key,$iv);
	}

	/**
	 * 解密字符串 
	 *
	 * @param string $str 解密的字符串
	 * @param string $key 密钥
	 * @return string 解密后的结果
	 */
	public static function decrypt($str, $key=self::SERCRETKEY, $iv = '') {
		return self::authcode($str,'DECODE',$key,$iv);
	}

	/**
	 * 创建token令牌串
	 * 
	 * 创建token令牌串,用于避免表单重复提交等.
	 * 使用当前的sessionID以及当前时间戳,生成唯一一串令牌串,并返回.
	 * @deprecated
	 * @return string
	 */
	public static function createToken() {
		return self::generateGUID();
	}

	/**
	 * 获取唯一标识符串,标识符串的长度为16个字节,128位.
	 * 
	 * 根据当前时间与sessionID,混合生成一个唯一的串.
	 * @return string GUID串,16个字节
	 */
	public static function generateGUID() {
		return substr(md5(self::generateRandStr(8) . microtime()), -16);
	}
	/**
	 * 获得随机数字符串
	 * 
	 * @param int $length 随机数的长度
	 * @return string 随机获得的字串
	 */
	public static function generateRandStr($length) {
		$randstr = "";
		for ($i = 0; $i < (int) $length; $i++) {
			$randnum = mt_rand(0, 61);
			if ($randnum < 10) {
				$randstr .= chr($randnum + 48);
			} else if ($randnum < 36) {
				$randstr .= chr($randnum + 55);
			} else {
				$randstr .= chr($randnum + 61);
			}
		}
		return $randstr;
	}
	/**
	 * 路径检查转义
	 * 
	 * @param string $fileName 被检查的路径
	 * @param boolean $ifCheck 是否需要检查文件名，默认为false
	 * @return string
	 */
	public static function escapePath($filePath, $ifCheck = false) {
		$_tmp = array("'" => '', '#' => '', '=' => '', '`' => '', '$' => '', '%' => '', '&' => '', ';' => '');
		$_tmp['://'] = $_tmp["\0"] = '';
		$ifCheck && $_tmp['..'] = '';
		if (strtr($filePath, $_tmp) == $filePath) return preg_replace('/[\/\\\]{1,}/i', '/', $filePath);
		if (WIND_DEBUG & 2) {
			$WindLogger = Free::loadClass('WindLogger',PC_PATH . 'libs/log',1);
			$WindLogger->info(
				"[utility.FreeSecurity.escapePath] file path is illegal.\r\n\tFilePath:" . $filePath);
		}
		throw new FreeException('[utility.FreeSecurity.escapePath] file path is illegal');
	}
	
	public static function authcode($string, $operation = 'DECODE', $key = 'user_sercretkey', $expiry = 0) 
	{
    $ckey_length = 1; //note 随机密钥长度 取值 0-32;
    //note 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
    //note 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
    //note 当此值为 0 时，则不产生随机密钥

    $key = md5($key ? $key : 1234);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = 'a';
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}
}