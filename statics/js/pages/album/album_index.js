/*!
 * PHPWind UI Library 
 * @Copyright 	: Copyright 2011, phpwind.com
 * @Descript	: 相册增删改
 * @Author		: siweiran@gmail.com
 * @Depend		: core.js、jquery.js(1.7 or later)
 * $Id: dialog.js 8433 2012-04-18 12:23:53Z chris.chencq $			:
 */;
(function () {
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
    
    //限制字符 不区分中英文字符数
    function check(obj, num) {
        var $obj = obj,
            max = $obj.attr('maxlength') || num,
            $info = $obj.next(),
            objVal = $obj.val().length;
        if (objVal > 0) {
            $info.html('<b>' + objVal + "</b>/" + max);
        }
        $obj.bind('keyup change', function () {
            var _val = $(this).val(),
                _curL = _val.length;
            if (_curL == 0) {
                $info.html("0/" + max);
            } else if (_curL < max) {
                $info.html(_curL + "/" + max);
            } else {
                $info.html('<b style="color:#f30">' + _curL + "</b>/" + max);
            }
        })
    }

    function checkLength(obj, maxlen,isfocus) {
        var len = obj.val().length;
        if (len > 0 && len <= maxlen){
        	return true;
        }
        else{
        	if(isfocus){
        		obj.focus();
        	}
        	return false;
        }
    }
    
    //新建相册
    $("#J_new_album").click(function (e) {
        e.preventDefault();
        var $this=$(this),
        	url=$this.attr("href"),
        	submitF=$this.attr("data-callback");
        var callback=function(data){
        	if(ajaxTempError(data)){
        		Wind.dialog.html(data,{
        			position	: 'fixed',
					title : '创建相册',
					isMask		: false,
					isDrag : true,
					callback : function(){
						 var $form=$("#J_newAlbum_form"),
						 	albumName = $form.find("input[name=name]"),
			            	albumDescrip =$form.find("textarea[name=descrip]"),
			        		albumSubmit=$("#btn_add_newAlbum");
						 	//即时显示字数
				            check(albumName, 10) || check(albumDescrip, 100);
				            albumSubmit.on('click',function (e) {
				                e.preventDefault();
				                if (checkLength(albumName, 10,true) && checkLength(albumDescrip, 100) && submitF) {
				                	callBackObj[submitF]();
				                }
				            })
					}
        		})
        	}
        }
        if($("#J_newAlbum_form").length){
        	return false;
        }else{
        	showAjaxData(url,null,"GET","html",callback);
        }
    })
    
 //callBackObj用于封装新建相册点击"确定"的回调
    var callBackObj={
    	postData:function(){
            var $form = $("#J_newAlbum_form"),
            	formData = $form.serialize(),
            	url = $form.attr('action');
            var callBack = function (data) {
                if (data.state === "success") {
                    resultTip({
                        msg: "新建相册成功",
                    });
                    reloadPage(window);
                } else {
                    resultTip({
                        error: true,
                        msg: data.message[0],
                        follow: false
                    });
                }
            }
        showAjaxData(url, formData, null, 'json', callBack);
    	},
    	addForUpdate:function(){
            var $form = $("#J_newAlbum_form"),
            	formData = $form.serialize(),
            	url = $form.attr('action'),
            	$J_album_list=$("#J_album_list");
            var callBack = function (data) {
                if (data.state === "success") {
                	var obj=data.data,
                		html='<option data-num="'+obj.num+'" value="'+obj.albumid+'">'+obj.name+'</option>';
                	$J_album_list.append(html);
                	$J_album_list.find("option[value="+obj.albumid+"]").attr("selected",true);
                    resultTip({
                        msg: "新建相册成功"
                    });
                    Wind.dialog.closeAll();
                } else {
                    resultTip({
                        error: true,
                        msg: data.message[0],
                        follow: false
                    });
                }
            }
        showAjaxData(url, formData, null, 'json', callBack);
    	}
    }
    
    //删除相册
    $("#album_home li .del").click(function (e) {
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
                $("#album" + $delId).remove();
            }
        });
    })

    //编辑相册
    $("#album_home li .edit").click(function (e) {
        e.preventDefault();
        var $this = $(this),
        	url=$this.attr("href");
        var callback=function(data){       	
        	if(ajaxTempError(data)){
        		Wind.dialog.html(data,{
        			position	: 'fixed',	
					title : '编辑相册',
					isMask		: false,
					isDrag : true,
					callback : function(){
						 var $form=$("#J_editAlbum_form"),
						 	albumName = $form.find("input[name=name]"),
			            	albumDescrip =$form.find("textarea[name=descrip]"),
			        		albumEditSubmit=$("#btn_edit_newAlbum");
						 	//即时显示字数
				            check(albumName, 10) || check(albumDescrip, 100);
				             sendEditData($form);
					}
        		})
        	}
        }
        showAjaxData(url,null,"GET",'html',callback); 
    })
    
    function sendEditData($obj) {
        var $sub = $("#btn_edit_newAlbum"),
            ealbumName = $obj.find("input[name=name]"),
            ealbumDescrip = $obj.find("textarea[name=descrip]");
            check(ealbumName, 10) || check(ealbumDescrip, 100);
        $sub.on('click', function (e) {
            e.preventDefault();
            var url = $obj.attr("action"),
                formData = $obj.serialize();
            var callBack = function (data) {
                    if (data.state === "success") {
                        reloadPage(window);
                    } else {
                        resultTip({
                            error: true,
                            msg: data.message[0],
                            follow: false
                        });
                    }
                }
            if (checkLength(ealbumName, 10,true) && checkLength(ealbumDescrip,100)){
            	showAjaxData(url, formData, null, 'json', callBack);
            }
        })
    }
})()