 /**
 * util Library
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: AJAX修改单项记录
 * @Depend	: core.js、jquery.js(1.7 or later)
 * $Id: jquery.listTable.js 1 2012-11-10 $
 */
;(function ( $, window, document, undefined ) {
    var pluginName = 'listTable';
    var defaults = {};

	function Plugin(element, options) {
		this.element = element;
        this.options = $.extend( {}, defaults, options) ;
        this.init();
    }
	
    Plugin.prototype.init = function () {
		var element = this.element,options = this.options;
        
        element.css('cursor', 'pointer');
        element.on('click',function(e) {
            e.preventDefault();
            if ($(this).has('input').length) return;
            var data_type = $(this).data('type'); //类型 edit 修改 toggle 切换状态
            var data_field = $(this).data('field'); //当前字段名
            var data_id = $(this).data('id') ? $(this).data('id') :$(this).parent().parent('tr').data('id'); //关联的记录id
            var data_url = $(this).data('url') ? $(this).data('url') : $(this).parent().parent('tr').data('url'); //ajax请求地址
            var data_value =  $(this).text(); //记录值
            var data_len = $(this).data('len')?'length_' + $(this).data('len'):''; //输入框长度样式
            
            if (data_type == 'edit') { //修改
                $(this).text('');
                $("<input />",{
                    type:'text',
                    name:'text',
                    val:data_value,
                    class:'input ' + data_len
                }).appendTo($(this)).focus();

                $(this).on('keyup',function(event) {
                    var keycode = event.which;
                    //处理回车的情况
                    if (keycode == 13) {
                        //$(this).find('input').blur();
                    }
                    //处理esc的情况
                    if(keycode == 27){
                        $(this).html(data_value);
                    }
                });

                $(this).find('input').on('blur',function(event) {
                    var options = {
                        data_obj: $(this).parent(),
                        data_url:data_url,
                        data_id: data_id,
                        data_field: data_field,
                        data_value: $(this).val()
                    };

                    ajaxForm(options);
                });
            } else if (data_type == 'toggle') {
                data_value = (data_value == '√') ? 1 : 0;
                var options = {
                        data_type: data_type,
                        data_obj: $(this),
                        data_url:data_url,
                        data_id: data_id,
                        data_field: data_field,
                        data_value: data_value
                    };

                ajaxForm(options);
            } else {
                return;
            }
        });

        function ajaxForm(options){
            $.ajax({
                type: "POST",
                url: options.data_url,
                dataType: 'json',
                data: "field="+options.data_field+"&id="+options.data_id+"&value="+options.data_value,
                success: function(msg){
					if (msg.state!='fail'){					
						if (options.data_type == 'toggle') {
							if (msg.data == 0) {
								options.data_obj.removeClass('green').addClass('red').text('×');
							} else {
								options.data_obj.removeClass('red').addClass('green').text('√');
							}
						} else {
							options.data_obj.text(msg.data);
						}
					}else if(msg.message){
						Wind.use('dialog', function(){
							Wind.dialog.alert( msg.message, function(){});
						});
					}
                 }
            });
        }
		
    };

	
    $.fn[pluginName] = Wind[pluginName]= function (options ) {
        return this.each(function () {
			new Plugin( $(this), options );
        });
    };

})(jQuery, window ,document);
