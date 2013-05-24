<?php
/**
 * Web产品filter类
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package base
 */
class FrontFilter extends AbstractFreeFilter{
	
	/**
	 * 处理
	 */
	public function handle() 
	{
		$_SERVER['HTTP_REFERER'] = $this->sacarXss($_SERVER['HTTP_REFERER']);
		$_GET = $this->escape($_GET);
		$_POST = $this->escape($_POST);
		$_COOKIE = $this->escape($_COOKIE);
		$_GET = $this->sacarXss($_GET);
		return false;
	}
	/**
	*	防Xss注入
	**/
	private function filterXss($string, $allowedtags = '', $disabledattributes = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload','iframe'))
	{
		if(is_array($string))
		{
			foreach($string as $key => $val) $string[$key] = $this->filterXss($val);
		}
		else
		{
			$string = preg_replace('/\s('.implode('|', $disabledattributes).').*?([\s\>])/', '\\2', preg_replace('/<(.*?)>/ie', "'<'.preg_replace(array('/javascript:[^\"\']*/i', '/(".implode('|', $disabledattributes).")[ \\t\\n]*=[ \\t\\n]*[\"\'][^\"\']*[\"\']/i', '/\s+/'), array('', '', ' '), stripslashes('\\1')) . '>'", strip_tags($string, $allowedtags)));
		}
		return $string;
	}

	private function safeReplace($string)
	{
		if(!is_array($string)) 
		{
			$string = str_replace('%27','',$string);
			$string = str_replace('*','',$string);
			$string = str_replace('"','&quot;',$string);
			$string = str_replace("'",'',$string);
			$string = str_replace("\"",'',$string);
			$string = str_replace('//','',$string);
			$string = str_replace(';','',$string);
			$string = str_replace('<','&lt;',$string);
			$string = str_replace('>','&gt;',$string);
			$string = str_replace('(','',$string);
			$string = str_replace(')','',$string);
			$string = str_replace("{",'',$string);
			$string = str_replace('}','',$string);
			return $string;
		}
		foreach($string as $key => $val) 
		{
			$string[$key] = $this->safeReplace($val);
		}
		return $string;
	}
	/**
	*	防sql注入
	**/
	private function escape($string)
	{
		$search = array('/union/i', '/load_file(\s*(\/\*.*\*\/)?\s*)+\(/i', '/into(\s*(\/\*.*\*\/)?\s*)+outfile/i');
		$replace = array('union &nbsp;', 'load_file &nbsp; (', 'into &nbsp; outfile');
		if(!is_array($string))
		{
			$string = preg_replace($search, $replace, $string);
			return str_replace(array('\n', '\r'), array(chr(10), chr(13)), $string);
		}
		foreach($string as $key=>$val) $string[$key] = $this->escape($val);
		return $string;
	}
	
	function sacarXss($val){
 
		$val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $val);
		$search = 'abcdefghijklmnopqrstuvwxyz';
		$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$search .= '1234567890!@#$%^&*()';
		$search .= '~`";:?+/={}[]-_|\'\\';
		for ($i = 0; $i < strlen($search); $i++) {
			$val = preg_replace('/(&#[x|X]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
			$val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
		}
		$ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
		$ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
		$ra = array_merge($ra1, $ra2);
		$found = true;
		while ($found == true) {
			$val_before = $val;
			for ($i = 0; $i < sizeof($ra); $i++) {
				$pattern = '/';
				for ($j = 0; $j < strlen($ra[$i]); $j++) {
					if ($j > 0) {
						$pattern .= '(';
						$pattern .= '(&#[x|X]0{0,8}([9][a][b]);?)?';
						$pattern .= '|(&#0{0,8}([9][10][13]);?)?';
						$pattern .= ')?';
					}
					$pattern .= $ra[$i][$j];
				}
				$pattern .= '/i';
				$replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2);
				$val = preg_replace($pattern, $replacement, $val);
				if ($val_before == $val) {
					$found = false;
				}
			}
		}
		return $val;
	}
	
	private function cc()
	{
		
	}
}
?>