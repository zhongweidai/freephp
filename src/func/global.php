<?php
function free_array_merge()
{
	$ret = array();
	if(func_num_args()<2)
	{
		if(func_num_args()==1)
		{
			return (array)func_get_arg(0);
		}
	}
	elseif ( func_num_args()>2 )
	{
		$arg_list = func_get_args();
		$p1 = array_shift($arg_list);
		$p2 = array_shift($arg_list);
		$ret = free_array_merge( $p1,$p2 );
		array_unshift($arg_list,$ret);
		$ret = call_user_func_array( 'free_array_merge',$arg_list);
	}
	else{
		list($a,$b)  = func_get_args();
		if (!is_array($a) && is_array($b)){
			return $b;
		}
		if (is_array($a) && !is_array($b)){
			return $a;
		}
		if (!is_array($a) && !is_array($b)){
			return $b;
		}
		$ret = $a;
		foreach($b as $k=>$v){
			if ( isset($ret[$k]) ){
				$ret[$k] = free_array_merge($ret[$k],$v);				
			}
			else 
			{
				$ret[$k] = $v;
			}
		}
	}
	return $ret;
}