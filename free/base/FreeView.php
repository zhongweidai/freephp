<?php
/**
 * 模板基类
 * @author Dai Zhongwei <daizhongw@gmail.com> 2011-7-10
 * @copyright ©2006-2103 
 * @version $$Id$$
 * @package base
 */
class FreeView extends FreeBase {
	/**
	*	模板变量分配
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
	* @param string $m 模块名
     * @param string $filename 文件名
	* @param string $style 风格名
	* @param string $version 版本
	+----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	public function templateResolve($m,$filename,$style,$app = APP)
	{
		$app_configs = Free::loadConfig('application',$app);
		
		//!defined('PLATFORM_VERSION') && define('PLATFORM_VERSION' , isset($app_config['version'])  ? $app_config['version']  : 'base');
		$version = $app_configs['version'];
		$template_component = $this->getComponent('template');
		
		$tpl_suffix = $template_component->getTplSuffix();
		$complie_suffix = $template_component->getComplieSuffix();
		$app = strtolower($app);
		$base_template_path = FREE_PATH . 'view' . DIRECTORY_SEPARATOR . 'base' . DIRECTORY_SEPARATOR .$app. 'templates'. DIRECTORY_SEPARATOR . $style . DIRECTORY_SEPARATOR . $m . DIRECTORY_SEPARATOR;
		$base_complie_path = CACHE_PATH . 'template_base' . DIRECTORY_SEPARATOR .$app. 'templates' . DIRECTORY_SEPARATOR . $style . DIRECTORY_SEPARATOR . $m . DIRECTORY_SEPARATOR;
        if($version === 'base')
		{	
			if(!file_exists($base_template_path . $filename . $tpl_suffix))
			{
				$base_template_path = FREE_PATH . 'view' . DIRECTORY_SEPARATOR . 'base' . DIRECTORY_SEPARATOR .$app. 'templates'. DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . $m . DIRECTORY_SEPARATOR;
				
				$base_complie_path = CACHE_PATH . 'template_base' . DIRECTORY_SEPARATOR .$app. 'templates' . DIRECTORY_SEPARATOR . 'default'  . DIRECTORY_SEPARATOR . $m . DIRECTORY_SEPARATOR;
			}
			$template = $template_component->compile($filename,$base_template_path,$base_complie_path);
			if($template === false)
			{
				throw new FreeException($base_template_path . $filename . $tpl_suffix .' Template does not exist.' ,'104');
			}else{
				//$this->template_complie_path = $template;
				return $template;
			}
		}else{
			$template_path =  FREE_PATH . (isset($app_configs['template-path']) ? $app_configs['template-path'] :'view' . DIRECTORY_SEPARATOR . PLATFORM_VERSION . DIRECTORY_SEPARATOR .$app. 'templates' . DIRECTORY_SEPARATOR ). $style . DIRECTORY_SEPARATOR . $m . DIRECTORY_SEPARATOR;
			$complie_path = CACHE_PATH  . (isset($app_configs['template-complie-path']) ? $app_configs['template-complie-path'] : 'template_' . PLATFORM_VERSION . DIRECTORY_SEPARATOR .$app. 'templates' . DIRECTORY_SEPARATOR) . $style . DIRECTORY_SEPARATOR . $m . DIRECTORY_SEPARATOR;
			if(!file_exists($template_path . $filename . $tpl_suffix))
			{
				$template_path = FREE_PATH . (isset($app_configs['template-path']) ? $app_configs['template-path'] : 'view' . DIRECTORY_SEPARATOR . PLATFORM_VERSION . DIRECTORY_SEPARATOR .$app. 'templates' . DIRECTORY_SEPARATOR ) . 'default' . DIRECTORY_SEPARATOR . $m . DIRECTORY_SEPARATOR;
				
				$complie_path =  CACHE_PATH .(isset($app_configs['template-complie-path']) ? $app_configs['template-complie-path'] :  'template_' . PLATFORM_VERSION . DIRECTORY_SEPARATOR .$app. 'templates' . DIRECTORY_SEPARATOR). 'default'  . DIRECTORY_SEPARATOR . $m . DIRECTORY_SEPARATOR;
			}
			$template =$template_component->compile($filename,$template_path,$complie_path);
			if($template === false)
			{
				return self::templateResolve($m,$filename,$style,'base');
			}else{
				//$this->template_complie_path = $template;
				return $template;
			}
		}
	}
	
}
?>