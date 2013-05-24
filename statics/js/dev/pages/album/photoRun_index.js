/*!
 * PHPWind UI Library 
 * @Copyright 	: Copyright 2011, phpwind.com
 * @Descript	: 相册图片列表
 * @Author		: siweiran@gmail.com
 * @Depend		: core.js、jquery.js(1.7 or later)
 * $Id: dialog.js 8433 2012-04-18 12:23:53Z chris.chencq $			:
 */;
(function () {
    var $photoList = $("#photoList");
    function showAjaxData(url, data, type, dataType, sucCallBack) {
        $.ajax({
            url: url,
            data: data,
            type: type ? type : "POST",
            dataType: dataType,
            success: function (data) {
                sucCallBack(data);
            },
            error: function () {
                resultTip({
                    error: true,
                    msg: "请求出错,请重试",
                    follow: false
                });
            }
        })
    }
    //图片列表编辑
    $photoList.find('a.edit').click(function (e) {
        e.preventDefault();
        var $this = $(this),
        	_this = this,
            url = $this.attr("href");
        var callback = function (data) {
        	if(ajaxTempError(data)){
        		Wind.dialog.html(data,{
        			position	: 'fixed',
					title : '编辑照片',
					isMask		: false,
					isDrag : true,
					callback : function(){
						 var $form=$("#J_edit_photo"),
						 	$btnSubmit=$("#btn_edit_photo");
						 $btnSubmit.on('click',function(e){
							 e.preventDefault();
							 sendEditData($form);
						 })
					}
        		})
        	}
           }
        if($("#J_edit_photo").length){
        	return false;
        }else{
        	showAjaxData(url, null, "GET", 'html', callback);
        }
    })

    function sendEditData($obj) {
        var url = $obj.attr("action"),
        	formData = $obj.serialize();
        var callBack = function (data) {
            if (data.state === "success") {
                resultTip({
                    msg: "编辑成功"
                });
                window.location.reload();
            } else {
                resultTip({
                    error: true,
                    msg: data.message[0],
                    follow: false
                });
            }
        }
        showAjaxData(url, formData, null, 'json', callBack)
   }
    //删除图片
    $photoList.find('a.del').click(function (e) {
        e.preventDefault();
        var $this = $(this);
        ajaxConfirm({
            elem: $this,
            href: $this.prop('href'),
            msg: $this.data('msg'),
            callback: function () {
                resultTip({
                    msg: '删除成功'
                });
                $delId = $this.attr("data-del");
                $("#photo" + $delId).remove();
            }
        });
    })

})()