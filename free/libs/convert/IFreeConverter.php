<?php
/**
 * 编码转化器通用接口定义
 * 
 */
interface IFreeConverter {

	/**
	 * 编码转化
	 * 
	 * 对输入的字符串进行从原编码到目标编码的转化,请确定原编码与目标编码
	 * @param string $srcText
	 * @return string 转化后的编码
	 */
	public function convert($str);
}

?>