/*!
 * PHPWind UI Library 
 * @Copyright 	: Copyright 2011, phpwind.com
 * @Descript	: 相册图片列表
 * @Author		: siweiran@gmail.com
 * @Depend		: core.js、jquery.js(1.7 or later)
 * $Id: dialog.js 8433 2012-04-18 12:23:53Z chris.chencq $			:
 */
;(function(){
	var $photoList=$("#photoList");
	function showAjaxData(url,data,type,dataType,sucCallBack,errCallBack){
		$.ajax({
			url:url,
			data:data,
			type:type?type:"POST",
			dataType:dataType,
			success:function(data){
				sucCallBack(data);
			},
			error:function(){
				resultTip({
					error : true,
					msg : "请求出错,请重试",
					follow : false
				});
			}
		})
	}
	//图片列表编辑
	$photoList.find('a.edit').click(function(e){
		e.preventDefault();
		var $this=$(this),url=$this.attr("href");
		$.ajax({
			type:"POST",
			url:url,
			dataType:"html",
			beforeSend:function(){
				
			},
			success:function(data){
				var $form=$(data);			
				$form.appendTo($("body"));
				$form.find('.pop_close,.btn_close_edit').bind('click',function(e){
					e.preventDefault();
					$form.remove();
				});
				sendEditData($form);
			},
			error:function(){
			}
		})
	})
	
function sendEditData($obj){
		$sub=$obj.find(".btn_submit"),$edit_photo=$("#edit_photo");
		if($edit_photo){
			var photoName=$edit_photo.find("input[name=name]"),
			photoDescrip=$edit_photo.find("textarea[name=descrip]");
			}
		$sub.bind('click',function(e){
			e.preventDefault();
			var url=$obj.attr("action"),
				formData=$obj.serialize();
			var callBack=function(data){
				if(data.state==="success"){
					reloadPage(window);
				}else{
					resultTip({
						error : true,
						msg : data.message[0],
						follow : false
					});
				}
			}
			showAjaxData(url,formData,null,'json',callBack,null)	
		})
	}
	//删除图片
	$photoList.find('a.del').click(function(e){
		e.preventDefault();
		var $this = $(this);
		ajaxConfirm({
			elem : $this,
			href : $this.prop('href'),
			msg : $this.data('msg'),
			callback : function(){
				resultTip({
					msg : '删除成功'
				});
				$delId=$this.attr("data-del");
				$("#photo"+$delId).remove();
			}
		});
	})
	
})()