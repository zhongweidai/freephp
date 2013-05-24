<?php
/**
 * 挂件执行文件
 *
 * @usage none
 */
class headerWidget extends AbstractWidgetViewServerBase
{
    var $_name = 'header';

    /**
     * 获取挂件显示数据
     *
     * @return array
     */
    function _getData() {
         return $this->options;
    }

    /**
     * 获取配置时需要的数据
     *
     * @return void
     */
    function getConfigDataSrc() {
    }

    /**
     * 处理配置请求，返回处理后的配置数组
     *
     * @param array $input
     * @return array
     */
    function parseConfig($input) {
        return $input;
    }
}

?>