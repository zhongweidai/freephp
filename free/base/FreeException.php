<?php
/**
 * 通用异常类型,大部分异常都是继承自该异常
 * 
 * @author DaiZhongwei <daizhongw@gmail.com> 2012-8-8
 * @package base
 */
class FreeException extends Exception {
	/* 系统错误 */
	const ERROR_SYSTEM_ERROR = '0';
	/* 类错误 */
	const ERROR_CLASS_NOT_EXIST = '100';
	const ERROR_CLASS_TYPE_ERROR = '101';
	const ERROR_CLASS_METHOD_NOT_EXIST = '102';
	const ERROR_OBJECT_NOT_EXIST = '103';
	/* 参数错误 */
	const ERROR_PARAMETER_TYPE_ERROR = '110';
	/* 配置错误 */
	const ERROR_CONFIG_ERROR = '120';
	/* 返回值类型错误 */
	const ERROR_RETURN_TYPE_ERROR = '130';
	/* 控制器或操作不存在 */
	const ERROR_NOT_EXIST_HANDLE = '404';
	/* 模板不存在 */
	const ERROR_NOT_EXIST_TEMPLATE = '104';
	/**
	 * 异常构造函数
	 * 
	 * @param $message		     异常信息
	 * @param $code			     异常代号 默认为0
	 * @param $innerException 内部异常 默认为null
	 */
	public function __construct($message, $code = 0) {
		$message = $this->buildMessage($message, $code);
		$file = $this->getFile();
		$line = $this->getLine();
		include(PC_PATH . 'base/view/error.php');
		die();
		//parent::__construct($message, $code);
	}

	/**
	 * 根据exception code返回构建的异常信息描述
	 * 
	 * @param string $message 用户自定义的信息
	 * @param int $code  异常号
	 * @return string 组装后的异常信息
	 */
	public function buildMessage($message, $code) {
		$message = str_replace(array("<br />", "<br>", "\r\n"), '', $message);
		eval('$message="' . addcslashes($this->messageMapper($code), '"') . '";');
		return $message;
	}


	/**
	 * 自定义异常号的对应异常信息
	 * 
	 * @param int $code 异常号
	 * @return string 返回异常号对应的异常组装信息原型
	 */
	protected function messageMapper($code) 
	{
		$messages = array(
			self::ERROR_SYSTEM_ERROR => 'System error \'$message\'.', 
			self::ERROR_CLASS_TYPE_ERROR => 'Incorrect class type \'$message\'.', 
			self::ERROR_CLASS_NOT_EXIST => 'Unable to create instance for \'$message\' , class is not exist.', 
			self::ERROR_CLASS_METHOD_NOT_EXIST => 'Unable to access the method \'$message\' in current class , the method is not exist or is protected.', 
			self::ERROR_OBJECT_NOT_EXIST => 'Unable to access the object in current class \'$message\' ', 
			self::ERROR_CONFIG_ERROR => 'Incorrect config. the config about \'$message\' error.', 
			self::ERROR_PARAMETER_TYPE_ERROR => 'Incorrect parameter type \'$message\'.', 
			self::ERROR_RETURN_TYPE_ERROR => 'Incorrect return type for \'$message\'.',
			self::ERROR_NOT_EXIST_HANDLE => 'Incorrect handle type for \'$message\'.',);
		return isset($messages[$code]) ? $messages[$code] : '$message';
	}
}