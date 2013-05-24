<?php
class FreePage {
    // 起始行数
    public $firstRow	;
    // 列表每页显示行数
    public $listRows	;
    // 页数跳转时要带的参数
    public $parameter  ;
    // 分页总页面数
    protected $totalPages  ;
    // 总行数
    protected $totalRows  ;
    // 当前页数
    protected $nowPage    ;
    // 分页的栏的总页数
    protected $coolPages   ;
    // 分页栏每页显示的页数
    protected $rollPage   ;
	// 分页显示定制
    protected $config  =	array('header'=>'条记录','prev'=>'上一页','next'=>'下一页','first'=>'第一页','last'=>'最后一页','theme'=>' %totalRow% %header% %nowPage%/%totalPage% 页 %listRows% %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end%','listrows'=>'');

    
    const DEFAULT_NUM = 20; //每页默认条数
    
    //是否显示选择页下拉框
    
    protected $is_select = 1;
    
    
    public function __construct() {

    }

    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
    }
	
	public function setPageRow($listRows=0)
	{
        if(!empty($listRows))
        {
            $this->listRows = $listRows;
            $this->is_select = 0;
            return ;
        }
        $cookie_key = APP . '-' . Free::getApp()->_module . '-' . Free::getApp()->_controller . '-' . Free::getApp()->_action . '-pageRow';
        if(Free::getApp()->getRequest()->getGet('pageRow') >= 1)
		{
			$listRows = intval(Free::getApp()->getRequest()->getGet('pageRow') );
			FreeCookie::set($cookie_key,$listRows);
			$this->listRows = $listRows;
		}else{
			$crow = FreeCookie::get($cookie_key);
			$this->listRows = $crow ? $crow : self::DEFAULT_NUM;
		}
	}
    
	public function getPageRow()
	{
		return $this->listRows;
	}
	public function show($totalRows,$nowPage,$listRows=0,$paget_style='default',$rollPage=5,$parameter='')
	{
		$this->totalRows = $totalRows;
		$this->parameter = $parameter;
		$this->rollPage = $rollPage;
		$this->setPageRow($listRows);
		$this->totalPages = ceil($this->totalRows/$this->listRows);     //总页数
        $this->coolPages  = ceil($this->totalPages/$this->rollPage);
        $this->nowPage  = $nowPage;
        if(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
            $this->nowPage = $this->totalPages;
        }
        $this->firstRow = $this->listRows*($this->nowPage-1);
        
		return $this->styleDefault();
	}
    public function styleDefault() {
        if(0 == $this->totalRows) return '';
        $p = 'page';
		$pR = 'pageRow';
        $nowCoolPage      = ceil($this->nowPage/$this->rollPage);
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
        $parse = parse_url($url);
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
			unset($params[$pR]);
            $url   =  U('',$params);
        }
		$lrstr = '';
		for($i=5;$i<=30;$i+=5)
		{
			//if($this->listRows == $i)
			$lrstr .= '<option value="' . $i . '"' . (($this->listRows == $i)? 'selected' : '') . '>' . $i . '</option>';
		}
		
		$this->is_select && $this->setConfig('listrows','每页显示<select onchange="location.href=\'' . $url .'&pageRow=' . '\'+this.value">' . $lrstr . '</select>');
        //上下翻页字符串
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($upRow>0){
            $upPage="<a class='pages_pre' href='".$url."&".$p."=$upRow'>« ".$this->config['prev']."</a>";
        }else{
            $upPage="";
        }

        if ($downRow <= $this->totalPages){
            $downPage="<a class='pages_next' href='".$url."&".$p."=$downRow'>".$this->config['next']." »</a>";
        }else{
            $downPage="";
        }
        // << < > >>
        if($nowCoolPage == 1){
            $theFirst = "";
            $prePage = "";
        }else{
            $preRow =  $this->nowPage-$this->rollPage;
            $prePage = "<a class='pages_pre' href='".$url."&".$p."=$preRow' >« 上".$this->rollPage."页</a>";
            $theFirst = "<a href='".$url."&".$p."=1' >".$this->config['first']."</a>";
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage = "";
            $theEnd="";
        }else{
            $nextRow = $this->nowPage+$this->rollPage;
            $theEndRow = $this->totalPages;
            $nextPage = "<a class='pages_next'  href='".$url."&".$p."=$nextRow' >下".$this->rollPage."页»</a>";
            $theEnd = "<a href='".$url."&".$p."=$theEndRow' >".$this->config['last']."</a>";
        }
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page=($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "&nbsp;<a href='".$url."&".$p."=$page'>".$page."</a>";
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= "<strong>".$page."</strong>";
                }
            }
        }
        $pageStr	 =	 str_replace(
            array('%header%','%listRows%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->config['listrows'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);
        return $pageStr;
    }

}


/**
 * 分页
 */
class FreePage1 {

	/**
	* 分页函数
	* 
	* @param $num 信息总数
	* @param $curr_page 当前分页
	* @param $perpage 每页显示数
	* @param $urlrule URL规则
	* @param $array 需要传递的数组，用于增加额外的方法
	* @return 分页
	*/
function pages($num, $curr_page, $perpage = 20, $array = array(), $setpages = 6,$pagestyle = 'default') {
	if ($perpage <= 0) {
		$perpage = 20;
	}

	$urlrule = url_par('page={$page}');

    $multipage = '';
    if ($num > $perpage) {
        $page = $setpages + 1;
        $offset = ceil($setpages / 2 - 1);
        $pages = ceil($num / $perpage);
        $from = $curr_page - $offset;
        $to = $curr_page + $offset;
        $more = 0;
        if ($page >= $pages) {
            $from = 2;
            $to = $pages - 1;
        } else {
            if ($from <= 1) {
                $to = $page - 1;
                $from = 2;
            } elseif ($to >= $pages) {
                $from = $pages - ($page - 2);
                $to = $pages - 1;
            }
            $more = 1;
        }
       switch($pagestyle){
       	case 'select_page':
       		/*** 下拉框分页开始*/
       		$multipage .= "<div id='thePage'>";
       		$multipage .= "<form name=form_page id=form_page method=get action=index.php>";
			$multipage .= ' <a href="' . pageurl($urlrule, 1, $array) . '"><span id="pageHome" title="首页"></span></a>';
			//上一页
			if ($curr_page > 0) {
			    $multipage .= ' <a href="' . pageurl($urlrule, $curr_page - 1, $array) . '"><span id="previous" title="上一页"></span></a>';
			}
			$multipage .= '<select id=page name=page>';
			$multipage .= "<option>1/".$pages."</option>";
			for ($i = 2; $i <= $pages; $i++) {
					if ($i != $curr_page) {
					$multipage .= "<option value=$i>".$i."/".$pages."</option>";
					}else{
						$multipage .= "<option value=$i selected=selected>".$i."/".$pages."</option>";
					}
			}
			$multipage .= '</select>';
			$multipage .= '<input type=hidden value='.$_GET['m'].' name=m>';
			$multipage .= '<input type=hidden value='.$_GET['c'].' name=c>';
			$multipage .= '<input type=hidden value='.$_GET['a'].' name=a>';
			$multipage .= '<input type=hidden value='.$_GET['catid'].' name=catid>';
			$multipage .= '<input type=hidden value='.$_GET['cate'].' name=cate>';
			$multipage .= '<input type=hidden value='.$_GET['strkey'].' name=strkey>';
			
			if(!empty($_GET['siteid'])){
				$multipage .= '<input type=hidden value='.$_GET['siteid'].' name=siteid>';
			}
			$multipage .= '<input type=hidden value='.$_GET['pc_hash'].' name=pc_hash>';
			$multipage .= '<input type=submit value=go name=go>';
			//下一页
			if ($curr_page < $pages) {
			    if ($curr_page < $pages - 5 && $more) {
			        $multipage .= ' <a href="' . pageurl($urlrule, $curr_page + 1, $array) . '"><span id="next" title="下一页"></span></a>';
			    } else {
			        $multipage .= ' <a href="' . pageurl($urlrule, $curr_page + 1, $array) . '"><span id="next" title="下一页"></span></a>';
			    }
			} elseif ($curr_page == $pages) {
			    $multipage .= '<a href="' . pageurl($urlrule, $curr_page, $array) . '"><span id="next" title="下一页"></span></a>';
			} else {
			    $multipage .= '<a href="' . pageurl($urlrule, $curr_page + 1, $array) . '"><span id="next" title="下一页"></span></a>';
			}
			$multipage .= ' <a href="' . pageurl($urlrule, $pages, $array) . '"><span id="end" title="尾页"></span></a>';
			$multipage .= '</form>';
			$multipage .= '</div>';
			/*** 下拉框分页结束*/
       		break;
       		
       	case 'default':	
       	default:
       		$multipage .= '<a class="a1">' . $num . L('page_item') . '</a>';
	        if ($curr_page > 0) {
	            $multipage .= ' <a href="' . pageurl($urlrule, $curr_page - 1, $array) . '" class="a1">' . L('previous') . '</a>';
	            $pagehome = pageurl($urlrule, 1, $array);
	            $pageprev = pageurl($urlrule, $curr_page - 1, $array);
	            if ($curr_page == 1) {
	            	$pagenow  = pageurl($urlrule, 1, $array);
	                $multipage .= ' <span style="color:#0066CC;">1</span>';
	            } elseif ($curr_page > 6 && $more) {
	            	$pagenow  = pageurl($urlrule, 1, $array);
	                $multipage .= ' <a href="' . pageurl($urlrule, 1, $array) . '">1</a><span>..</span>';
	            } else {
	            	$pagenow  = pageurl($urlrule, 1, $array);
	                $multipage .= ' <a href="' . pageurl($urlrule, 1, $array) . '">1</a>';
	            }
	        }
	        for ($i = $from; $i <= $to; $i++) {
	            if ($i != $curr_page) {
	                $multipage .= ' <a href="' . pageurl($urlrule, $i, $array) . '">' . $i . '</a>';
	            } else {
	            	$pagenow  = pageurl($urlrule, $i, $array);
	                $multipage .= ' <span style="color:#0066CC;">' . $i . '</span>';
	            }
	        }
	        if ($curr_page < $pages) {
	            if ($curr_page < $pages - 5 && $more) {
	                $multipage .= ' <span>..</span><a href="' . pageurl($urlrule, $pages, $array) . '">' . $pages . '</a> <a href="' . pageurl($urlrule, $curr_page + 1, $array) . '" class="a1">' . L('next') . '</a>';
	                $pageend  = pageurl($urlrule, $pages, $array);
	                $pagenext = pageurl($urlrule, $curr_page + 1, $array);
	            } else {
	                $multipage .= ' <a href="' . pageurl($urlrule, $pages, $array) . '">' . $pages . '</a> <a href="' . pageurl($urlrule, $curr_page + 1, $array) . '" class="a1">' . L('next') . '</a>';
	                $pageend  = pageurl($urlrule, $pages, $array);
	                $pagenext = pageurl($urlrule, $curr_page + 1, $array);
	            }
	        } elseif ($curr_page == $pages) {
	            $multipage .= ' <span style="color:#0066CC;">' . $pages . '</span> <a href="' . pageurl($urlrule, $curr_page, $array) . '" class="a1">' . L('next') . '</a>';
	            $pageend = pageurl($urlrule, $pages, $array);
	            $pagenext = pageurl($urlrule, $curr_page, $array);
	            $pagenow  = pageurl($urlrule, $pages, $array);
	        } else {
	            $multipage .= ' <a href="' . pageurl($urlrule, $pages, $array) . '">' . $pages . '</a> <a href="' . pageurl($urlrule, $curr_page + 1, $array) . '" class="a1">' . L('next') . '</a>';
	            $pageend  = pageurl($urlrule, $pages, $array);
	            $pagenext = pageurl($urlrule, $curr_page + 1, $array);
	            $pagenow  = pageurl($urlrule, $pages, $array);
	        }
       		break;	
       	
       }
       
    }
    
    /*
    $arr_page = array(
    	'pagehome' => $pagehome,
    	'pagenow' => $pagenow,
    	'pagenext' => $pagenext,
    	'pageprev' => $pageprev,
    	'pageend' => $pageend
    );
    */
    return $multipage;
}

/**
 * 分页函数
 * 
 * @param $num 信息总数
 * @param $curr_page 当前分页
 * @param $perpage 每页显示数
 * @param $urlrule URL规则
 * @param $array 需要传递的数组，用于增加额外的方法
 * @return 分页
 */
function pages1($num, $curr_page, $perpage = 20, $urlrule = '', $array = array(), $setpages = 10) {

    //echo $num."#".$curr_page."#";exit;

    if (defined('URLRULE') && $urlrule == '') {
        file_put_contents("D:/rr.txt", URLRULE);
        $urlrule = URLRULE;
        $array = $GLOBALS['URL_ARRAY'];
    } elseif ($urlrule == '') {
        $urlrule = url_par('page={$page}');
    }
    $str = '';
    if ($num > $perpage) {
        $totalPage = ceil($num / $perpage);
        $str = '<div class="pagination">';
        if ($curr_page > 1) {
            $str .= " <span> <a href=\"" . pageurl($urlrule, $curr_page - 1) . "\">上一页</a> </span> ";
        } 
        $str .= ' <span> <strong>'.$curr_page.'</strong>/'.$totalPage.' <a href="'.pageurl($urlrule, $curr_page - 1).'&show=all">全文</a> </span> ';
        if ($curr_page >= $totalPage) {
            //$str .= " [ <a href=\"javascript:;\">下一页</a> ] [ <a href=\"javascript:;\">尾页</a> ]";
        } else {
            //$str .= " [ <a href=\"" . pageurl($urlrule, $curr_page + 1) . "\">下一页</a> ] [ <a href=\"" . pageurl($urlrule, $totalPage) . "\">尾页</a> ]";
            $str .= " <span><a href=\"" . pageurl($urlrule, $curr_page + 1) . "\">下一页</a></span>";
        }

        $str .= "</div>";
    }
    return $str;
}

/**
 * 返回分页路径
 * 
 * @param $urlrule 分页规则
 * @param $page 当前页
 * @param $array 需要传递的数组，用于增加额外的方法
 * @return 完整的URL路径
 */
function pageurl($urlrule, $page, $array = array()) {
    if (strpos($urlrule, '~')) {
        $urlrules = explode('~', $urlrule);
        $urlrule = $page < 2 ? $urlrules[0] : $urlrules[1];
    }
    $findme = array('{$page}');
    $replaceme = array($page);
    if (is_array($array))
        foreach ($array as $k => $v) {
            $findme[] = '{$' . $k . '}';
            $replaceme[] = $v;
        }
    $url = str_replace($findme, $replaceme, $urlrule);
    $url = str_replace(array('http://', '//', '~'), array('~', '/', 'http://'), $url);
    return $url;
}

/**
 * URL路径解析，pages 函数的辅助函数
 *
 * @param $par 传入需要解析的变量 默认为，page={$page}
 * @param $url URL地址
 * @return URL
 */
function url_par($par, $url = '') {
    if ($url == '')
        $url = get_url();
    $pos = strpos($url, '?');
    if ($pos === false) {
        $url .= '?' . $par;
    } else {
        $querystring = substr(strstr($url, '?'), 1);
        parse_str($querystring, $pars);
        $query_array = array();
        foreach ($pars as $k => $v) {
            if (!in_array($k, array("page", "pc_hash"))) {
                $query_array[$k] = $v;
            }
        }
        $querystring = http_build_query($query_array) . '&' . $par;
        $url = substr($url, 0, $pos) . '?' . $querystring;
    }
    return $url;
}

}

?>