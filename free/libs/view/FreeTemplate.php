<?php
Free::loadClass('AbstractFreeTemplate', PC_PATH . 'libs/view', 0);
/**
 *  模板解析缓存
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package base
 */
class FreeTemplate extends AbstractFreeTemplate{
	protected $_tpl_suffix = '.html';
	protected $_complie_suffix = '.php';

    /**
     * 编译模板
     *
     * @param $module	模块名称
     * @param $template	模板文件名
     * @param $istag	是否为标签模板
     * @return unknown
     */
    public function compile($filename,$path,$complie_path) {
		$compiledtplfile =  $complie_path . $filename . $this->_complie_suffix;
		$tplfile = $path . $filename . $this->_tpl_suffix;
		if (file_exists($tplfile)) {
			if (!file_exists($compiledtplfile) || (@filemtime($tplfile) > @filemtime($compiledtplfile)))
			{
				$content = @file_get_contents($tplfile);
				if (!is_dir($complie_path)) 
				{
					mkdir($complie_path, 0777, true);
				}
				$content = $this->templateParse($content);
				$strlen = file_put_contents($compiledtplfile, $content);
				chmod($compiledtplfile, 0777);
			}
			return $compiledtplfile;
		} else {
			return false;
		}
	}
    /**
     * 更新模板缓存
     *
     * @param $tplfile	模板原文件路径
     * @param $compiledtplfile	编译完成后，写入文件名
     * @return $strlen 长度
     */
    public function templateRefresh($tplfile, $compiledtplfile) {
        $str = @file_get_contents($tplfile);
        $str = $this->templateParse($str);
        $strlen = file_put_contents($compiledtplfile, $str);
        chmod($compiledtplfile, 0777);
        return $strlen;
    }

    /**
     * 解析模板
     *
     * @param $str	模板内容
     * @return ture
     */
    public function templateParse($str) {
        $str = preg_replace("/\{template\s+(.+)\}/", "<?php include \$this->_view->templateResolve(\\1,\$__style__,\$__app__); ?>", $str);
        $str = preg_replace("/\{waptemplate\s+(.+)\}/", "<?php include \$this->_view->templateResolve(\\1,\$__style__,\$__app__); ?>", $str);
        $str = preg_replace("/\{include\s+(.+)\}/", "<?php include \\1; ?>", $str);
        $str = preg_replace("/\{php\s+(.+)\}/", "<?php \\1?>", $str);
        $str = preg_replace("/\{if\s+(.+?)\}/", "<?php if(\\1) { ?>", $str);
        $str = preg_replace("/\{else\}/", "<?php } else { ?>", $str);
        $str = preg_replace("/\{elseif\s+(.+?)\}/", "<?php } elseif (\\1) { ?>", $str);
        $str = preg_replace("/\{\/if\}/", "<?php } ?>", $str);
        //for 循环
        $str = preg_replace("/\{for\s+(.+?)\}/", "<?php for(\\1) { ?>", $str);
        $str = preg_replace("/\{\/for\}/", "<?php } ?>", $str);
        //++ --
        $str = preg_replace("/\{\+\+(.+?)\}/", "<?php ++\\1; ?>", $str);
        $str = preg_replace("/\{\-\-(.+?)\}/", "<?php ++\\1; ?>", $str);
        $str = preg_replace("/\{(.+?)\+\+\}/", "<?php \\1++; ?>", $str);
        $str = preg_replace("/\{(.+?)\-\-\}/", "<?php \\1--; ?>", $str);
        $str = preg_replace("/\{loop\s+(\S+)\s+(\S+)\}/", "<?php \$n=1;if(is_array(\\1)) foreach(\\1 AS \\2) { ?>", $str);
        $str = preg_replace("/\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}/", "<?php \$n=1; if(is_array(\\1)) foreach(\\1 AS \\2 => \\3) { ?>", $str);
        $str = preg_replace("/\{\/loop\}/", "<?php \$n++;}unset(\$n); ?>", $str);
        $str = preg_replace("/\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*\(([^{}]*)\))\}/", "<?php echo \\1;?>", $str);
        $str = preg_replace("/\{\\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*\(([^{}]*)\))\}/", "<?php echo \\1;?>", $str);
        $str = preg_replace("/\{(\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/", "<?php echo \\1;?>", $str);
        $str = preg_replace("/\{(\\$[a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]+)\}/es", "\$this->addquote('<?php echo \\1;?>')", $str);
        $str = preg_replace("/\{([A-Z_\x7f-\xff][A-Z0-9_\x7f-\xff]*)\}/s", "<?php echo \\1;?>", $str);
        $str = preg_replace("/\{pc:(\w+)\s+([^}]+)\}/ie", "self::pcTag('$1','$2', '$0')", $str);
        $str = preg_replace("/\{\/pc\}/ie", "self::endPcTag()", $str);
        $str = preg_replace("/\{form:(\w+)\s+([^}]+)\}/ie", "self::formTag('$1','$2')", $str);
		$str = preg_replace("/\<\/form\>/ie", "self::addCsrf()" , $str);
		
        $str = "<?php defined('IN_FREE') or exit('No permission resources.'); ?>" . $str;
        return $str;
    }
	
	public static function addCsrf()
	{
		return '<input type="hidden" name="__hash__" value="<?php' .
			//' $this->getComponent(\'token\')->deleteToken(\'csrf_token\');'. 
			' echo $this->getComponent(\'token\')->saveToken(\'csrf_token\');?>" /></form>';
	}
    /**
     * 转义 // 为 /
     *
     * @param $var	转义的字符
     * @return 转义后的字符
     */
    public function addquote($var) {
        return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var));
    }

    /**
     * 解析PC标签
     * @param string $op 操作方式
     * @param string $data 参数
     * @param string $html 匹配到的所有的HTML代码
     */
    public static function pcTag($op, $data, $html) {
        preg_match_all("/([a-z]+)\=[\"]?([^\"]+)[\"]?/i", stripslashes($data), $matches, PREG_SET_ORDER);
        $arr = array('action', 'num', 'cache', 'page', 'pagesize', 'urlrule', 'return', 'start','pagestyle');
        $tools = array('json', 'xml', 'block', 'get');
        $datas = array();
        $tag_id = md5(stripslashes($html));
        //可视化条件
        $str_datas = 'op=' . $op . '&tag_md5=' . $tag_id;
        //print_R($str_datas);
        foreach ($matches as $v) {
            $str_datas .= $str_datas ? "&$v[1]=" . ($op == 'block' && strpos($v[2], '$') === 0 ? $v[2] : urlencode($v[2])) : "$v[1]=" . (strpos($v[2], '$') === 0 ? $v[2] : urlencode($v[2]));
            if (in_array($v[1], $arr)) {
                $$v[1] = $v[2];
                continue;
            }
            $datas[$v[1]] = $v[2];
        }
        $str = '';
        $num = isset($num) && intval($num) ? intval($num) : 20;
        $cache = isset($cache) && intval($cache) ? intval($cache) : 0;
        $return = isset($return) && trim($return) ? trim($return) : 'data';
        if (!isset($urlrule))
            $urlrule = '';
        
        $is_cache = !empty($cache) && (intval($page) <= 1);
        if ($is_cache) {
            $str .= '$tag_cache_name = md5(implode(\'&\',' . self::arrToHtml($datas) . ').\'' . $tag_id . '\');if(!$' . $return . ' = getTplCache($tag_cache_name,' . $cache . ')){';
        }
        if (in_array($op, $tools)) {
            switch ($op) {
                case 'json':
                    if (isset($datas['url']) && !empty($datas['url'])) {
                        $str .= '$json = @file_get_contents("' . $datas['url'] . '");';
                        $str .= '$' . $return . ' = json_decode($json, true);';
                    }
                    break;

                case 'xml':
                    $str .= '$xml = $this->getComponent(\'xml\');';
                    $str .= '$xml_data = @file_get_contents(\'' . $datas['url'] . '\');';
                    $str .= '$' . $return . ' = $xml->xml_unserialize($xml_data);';
                    break;
				/**
                case 'get':
                    $str .= 'Free::load_sys_class("get_model", "model", 0);';
                    if ($datas['dbsource']) {
                        $dbsource = getcache('dbsource', 'commons');
                        if (isset($dbsource[$datas['dbsource']])) {
                            $str .= '$get_db = new get_model(' . var_export($dbsource, true) . ', \'' . $datas['dbsource'] . '\');';
                        } else {
                            return false;
                        }
                    } else {
                        $str .= '$get_db = new get_model();';
                    }
                    $num = isset($num) && intval($num) > 0 ? intval($num) : 20;
                    if (isset($start) && intval($start)) {
                        $limit = intval($start) . ',' . $num;
                    } else {
                        $limit = $num;
                    }
                    if (isset($page)) {
                        $str .= '$pagesize = ' . $num . ';';
                        $str .= '$page = intval(' . $page . ') ? intval(' . $page . ') : 1;if($page<=0){$page=1;}';
                        $str .= '$offset = ($page - 1) * $pagesize;';
                        $str .= '$endset = ($page) * $pagesize;';
                        $limit = '$offset,$endset';
                        if ($sql = preg_replace('/select([^from].*)from/i', "SELECT COUNT(*) as count FROM ", $datas['sql'])) {
                            $str .= '$r = $get_db->sql_query("' . $sql . '");$s = $get_db->fetch_next();$pages=pages($s[\'count\'], $page, $pagesize, $urlrule);';
                        }
                    }


                    $str .= '$r = $get_db->sql_query("' . $datas['sql'] . ' LIMIT ' . $limit . '");while(($s = $get_db->fetch_next()) != false) {$a[] = $s;}$' . $return . ' = $a;unset($a);';
                    break;

                case 'block':
                    $str .= '$block_tag = Free::loadAppClass(\'block_tag\', \'block\');';
                    $str .= 'echo $block_tag->pcTag(' . self::arrToHtml($datas) . ');';
                    break;
                    **/
            }
        } else {
            if (!isset($action) || empty($action))
                return false;
           // if (file_exists(PC_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $op . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $op . 'Tag.class.php')) {

                $str .= '$' . $op . '_tag = Free::loadAppClass("' . $op . 'Tag", "' . $op . '");if (method_exists($' . $op . '_tag, \'' . $action . '\')) {';
                if (isset($start) && intval($start)) {
                    $datas['limit'] = intval($start) . ',' . $num;
                } else {
                    $datas['limit'] = $num;
                }
                if (isset($page)) {
					$str .= '$page_class = $this->getComponent(\'page\');';
                    $str .= '$pagesize = ' . $num . ';';
                    $str .= '$page = intval(' . $page . ') ? intval(' . $page . ') : 1;if($page<=0){$page=1;}';
                    $datas['action'] = $action;
                    $str .= '$' . $op . '_total = $' . $op . '_tag->count(' . self::arrToHtml($datas) . ');';
                    $str .= '$pages = $page_class->show($' . $op . '_total, $page, $pagesize, $urlrule);';
					$str .= '$pagesize = $page_class->getPageRow();';
					$str .= '$offset = ($page - 1) * $pagesize;';
                    $str .= '$endset = ($page) * $pagesize;';
                    $datas['limit'] = '$offset.",".$endset';
                    
                }
                $str .= '$' . $return . ' = $' . $op . '_tag->' . $action . '(' . self::arrToHtml($datas) . ');';
                $str .= '}';
           // }
        }
        if ($is_cache) {
            $str .= 'if(!empty($' . $return . ')){setTplCache($tag_cache_name, $' . $return . ');}';
            $str .= '}';
        }
        return "<" . "?php " . $str . "?" . ">";
    }

    
    /**
     * PC标签结束
     */
    private static function endPcTag() {
        /**return '<?php if(defined(\'IN_ADMIN\') && !defined(\'HTML\')) {echo \'</div>\';}?>';
		**/
    }
    /**
     * 表单标签解析
     */
    private static function formTag($op, $data)
    {
    	preg_match_all("/([a-z]+)\=[\"]?([^\"]+)[\"]?/i", stripslashes($data), $matches, PREG_SET_ORDER);
    	$datas = array();
    	$eval = '';
    	foreach ($matches as $v) 
    	{
    		$eval .= '$datas[\'' . $v[1] . '\'] = ' . $v[2] . ';';
    	}
    	echo  $eval;
    	eval($eval);
    	
    	dump($datas);exit;
    	$parseStr   = '';
    	$tools = array('select','editor','radio','checkbox');
    	if (in_array($op, $tools)) 
    	{
    		switch($op)
    		{
    			case 'select':
    				$name       = $datas['name'];
    				$options    = $datas['options'];
    				$values     = $datas['values'];
    				$output     = $datas['output'];
    				$multiple   = $datas['multiple'];
    				$id         = $datas['id'];
    				$size       = $datas['size'];
    				$first      = $datas['first'];
    				$selected   = $datas['selected'];
    				$style      = $datas['style'];
    				$ondblclick = $datas['dblclick'];
    				$onchange	= $datas['change'];
    				
    				if(!empty($multiple)) {
    					$parseStr = '<select id="'.$id.'" name="'.$name.'" ondblclick="'.$ondblclick.'" onchange="'.$onchange.'" multiple="multiple" class="'.$style.'" size="'.$size.'" >';
    				}else {
    					$parseStr = '<select id="'.$id.'" name="'.$name.'" onchange="'.$onchange.'" ondblclick="'.$ondblclick.'" class="'.$style.'" >';
    				}
    				if(!empty($first)) {
    					$parseStr .= '<option value="" >'.$first.'</option>';
    				}
    				if(!empty($options)) {
    					$parseStr   .= '<?php  foreach($'.$options.' as $key=>$val) { ?>';
    					if(!empty($selected)) {
    						$parseStr   .= '<?php if(!empty($'.$selected.') && ($'.$selected.' == $key || in_array($key,$'.$selected.'))) { ?>';
    						$parseStr   .= '<option selected="selected" value="<?php echo $key ?>"><?php echo $val ?></option>';
    						$parseStr   .= '<?php }else { ?><option value="<?php echo $key ?>"><?php echo $val ?></option>';
    						$parseStr   .= '<?php } ?>';
    					}else {
    						$parseStr   .= '<option value="<?php echo $key ?>"><?php echo $val ?></option>';
    					}
    					$parseStr   .= '<?php } ?>';
    				}else if(!empty($values)) {
    					$parseStr   .= '<?php  for($i=0;$i<count($'.$values.');$i++) { ?>';
    					if(!empty($selected)) {
    						$parseStr   .= '<?php if(isset($'.$selected.') && ((is_string($'.$selected.') && $'.$selected.' == $'.$values.'[$i]) || (is_array($'.$selected.') && in_array($'.$values.'[$i],$'.$selected.')))) { ?>';
    						$parseStr   .= '<option selected="selected" value="<?php echo $'.$values.'[$i] ?>"><?php echo $'.$output.'[$i] ?></option>';
    						$parseStr   .= '<?php }else { ?><option value="<?php echo $'.$values.'[$i] ?>"><?php echo $'.$output.'[$i] ?></option>';
    						$parseStr   .= '<?php } ?>';
    					}else {
    						$parseStr   .= '<option value="<?php echo $'.$values.'[$i] ?>"><?php echo $'.$output.'[$i] ?></option>';
    					}
    					$parseStr   .= '<?php } ?>';
    				}
    				$parseStr   .= '</select>';
    				
    				break;
    			case 'radio':
    				$name       = $datas['name'];
    				$radios     = $datas['radios'];
    				$checked    = $datas['checked'];
    				$separator  = $datas['separator'];
    				
    				foreach($radios as $key=>$val) {
    					if($checked == $key ) {
    						$parseStr .= '<input type="radio" checked="checked" name="'.$name.'[]" value="'.$key.'">'.$val.$separator;
    					}else {
    						$parseStr .= '<input type="radio" name="'.$name.'[]" value="'.$key.'">'.$val.$separator;
    					}
    				}
    				break;
    			case 'checkbox':
    				$name       = $datas['name'];
    				$checkboxes = $datas['checkboxes'];
    				$checked    = $datas['checked'];
    				$separator  = $datas['separator'];
    				$parseStr   = '';
    				foreach($checkboxes as $key=>$val) {
    					if($checked == $key  || in_array($key,$checked) ) {
    						$parseStr .= '<input type="checkbox" checked="checked" name="'.$name.'[]" value="'.$key.'">'.$val.$separator;
    					}else {
    						$parseStr .= '<input type="checkbox" name="'.$name.'[]" value="'.$key.'">'.$val.$separator;
    					}
    				}
    		}
    			
    	}
    	return $parseStr;
    }
    

    /**
     * 转换数据为HTML代码
     * @param array $data 数组
     */
    private static function arrToHtml($data) {
        if (is_array($data)) {
            $str = 'array(';
            foreach ($data as $key => $val) {
                if (is_array($val)) {
                    $str .= "'$key'=>" . self::arrToHtml($val) . ",";
                } else {
                    if (strpos($val, '$') === 0) {
                        $str .= "'$key'=>$val,";
                    } else {
                        $str .= "'$key'=>'" . addslashes($val) . "',";
                    }
                }
            }
            return $str . ')';
        }
        return false;
    }
	

}

?>