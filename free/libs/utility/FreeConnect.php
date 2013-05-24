<?php
/**
 * 接口通讯方法
 *
 */
class FreeConnect {
	const CONNECT_TIMEOUT = 15;
	const CONNECT_DEBUG_TIME = 1;
	/**
	*  post数据
	*  @param string $url		post的url
	*  @param int $limit		返回的数据的长度
	*  @param string $post		post数据，字符串形式username='dalarge'&password='123456'
	*  @param string $cookie	模拟 cookie，字符串形式username='dalarge'&password='123456'
	*  @param string $ip		ip地址
	*  @param int $timeout		连接超时时间
	*  @param bool $block		是否为阻塞模式
	*  @return string			返回字符串
	*/
	static public function ucFopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = '', $block = TRUE) {
		$start = microtime();
		empty($timeout) && $timeout = CONNECT_TIMEOUT;
		if ($post) {
			$return = '';
			$matches = parse_url($url);
			!isset($matches['host']) && $matches['host'] = '';
			!isset($matches['path']) && $matches['path'] = '';
			!isset($matches['query']) && $matches['query'] = '';
			!isset($matches['port']) && $matches['port'] = '';
			$host = $matches['host'];
			$path = $matches['path'] ? $matches['path'] . ($matches['query'] ? '?' . $matches['query'] : '') : '/';
			$port = !empty($matches['port']) ? $matches['port'] : 80;
			$out = "POST $path HTTP/1.0\r\n";
			$out .= "Accept: */*\r\n";
			 //$out .= "Referer: $boardurl\r\n";
			$out .= "Accept-Language: zh-cn\r\n";
			$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
			$out .= "Host: $host:$port\r\n";
			$out .= 'Content-Length: ' . strlen($post) . "\r\n";
			$out .= "Connection: Close\r\n";
			$out .= "Cache-Control: no-cache\r\n";
			$out .= "Cookie: $cookie\r\n\r\n";
			$out .= $post;
			$fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
			if (!$fp) {
			//写入日志
				//write_interface_logs('fsocket', $url, '', $post, $start);
				return ''; //note $errstr : $errno \r\n
			} else {
				//stream_set_blocking($fp, $block);
				//stream_set_timeout($fp, $timeout);
				@fwrite($fp, $out);
				//$status = stream_get_meta_data($fp);
				//print_r(fread($fp, '118192'));exit;
				if (!$status['timed_out']) {
					while (!feof($fp)) {
						if (($header = @fgets($fp)) && ($header == "\r\n" || $header == "\n")) {
							break;
						}
					}

					$stop = false;
					while (!feof($fp) && !$stop) {
						$data = fread($fp, ($limit == 0 || $limit > 118192 ? 118192 : $limit));
						$return .= $data;
						if ($limit) {
							$limit -= strlen($data);
							$stop = $limit <= 0;
						}
					}
				}
				@fclose($fp);
				//写入日志
				self::toLog($start,$url,$post);
				return $return;
			}
		}
		else
		{	
			$result =self::curlOpen($url, $limit, $post, $cookie, $bysocket, $ip, $timeout, $block);
			self::toLog($start,$url,$post);
			return $result; 
		}
	}

/**
 * curl
 *
 * @param unknown_type $url
 * @param unknown_type $limit
 * @param unknown_type $post
 * @param unknown_type $cookie
 * @param unknown_type $bysocket
 * @param unknown_type $ip
 * @param unknown_type $timeout
 * @param unknown_type $block
 */
	static public function curlOpen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 1, $block = TRUE) 
	{
		if (!$url)
			die('url is null');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url); //The URL to fetch.
		curl_setopt($ch, CURLOPT_HEADER, 0); //TRUE to include the header in the output.
		//post
		if ($post) {
			curl_setopt($ch, CURLOPT_POST, 1); //TRUE to do a regular HTTP POST
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post); //The full data to post in a HTTP "POST" operation.
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1); //TRUE to return the raw output
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); //The maximum number of seconds to allow CURL functions to execute.
		if ($cookie)
			curl_setopt($ch, CURLOPT_COOKIE, $cookie); //The contents of the "Set-Cookie: " header to be used in the HTTP request.
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	
	static public function toLog($start,$url,$param)
	{
		if( CONNECT_DEBUG_TIME != 0)
		{
			
		}
		return true;
	}
	/**
	* curl rpc
	* @param unknown_type $url
	* @param unknown_type $request
	* @param unknown_type $limit
	* @param unknown_type $timeout
	* @return unknown
	*/
	static public function rpcFopen($url, $request, $limit = 0, $timeout = '') 
	{
		if (!$url)
		{
			die('url is null');
		}
		$start = microtime();
		empty($timeout) && $timeout = CONNECT_TIMEOUT;
		$urlarr = parse_url($url);
		$host = $urlarr['host'];
		$path = $urlarr['path'];

		$len = strlen($request);
		$hander = "POST $path HTTP/1.1\r\n";
		$hander .= "Host: $host\r\n";
		$hander .= "Content-type: application/json\r\n";
		$hander .= "Connection: Close\r\n";
		$hander .= "Content-Length: $len\r\n";
		$hander .= "\r\n";
		$hander .= $request . "\r\n";
		$handerarr[0] = $hander;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url); //The URL to fetch.
		curl_setopt($ch, CURLOPT_HEADER, 0); //TRUE to include the header in the output.
		curl_setopt($ch, CURLOPT_HTTPHEADER, $handerarr); //An array of HTTP header fields to set.
		curl_setopt($ch, CURLOPT_POST, 1); //TRUE to do a regular HTTP POST
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1); //TRUE to return the raw output
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); //The maximum number of seconds to allow CURL functions to execute.
		$data = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($data, true);
		self::toLog($start,$url,$request);
		return $data;
	}
	
	/**
	 * 教育平台接口调用函数
	 *
	 * @param 接口地址 $interface
	 * @param 接口编号 $method
	 * @param 接口参数 $params
	 * @param unknown_type $is_encode
	 * @param unknown_type $is_resources
	 * @return unknown
	 */
	static public function eduPost($interface, $method, $params = array(), $is_encode = 1, $is_resources = 0){
		$request = array();
		$request['mac'] = md5(time());
		$request['head'] = array('serialNumber' => time(), 'method' => $method, 'version' => '1');
		if ($is_encode) {
			$authcode = array('password','newpassword');
			
			foreach ($params as $k => $val) {
				if( in_array(strtolower($k), $authcode) && !empty($val)){
						$params[$k] = md5($val);
				}
				if (in_array(strtolower($k), $authcode) && !empty($val)) {
					$params[$k] = authcode($params[$k], 'ENCODE');
				}
			}
		}
		$request['body'] = $params;
	
	    $request = json_encode($request);
	
	    $return = self::rpcFopen($interface, $request);

	    return $return;
	}
	
}