/*!
 * PHPWind UI Library 
 * @Copyright 	: Copyright 2011, phpwind.com
 * @Descript	: 相册图片浏览
 * @Author		: siweiran@gmail.com
 * @Depend		: core.js、jquery.js(1.7 or later)
 * $Id: dialog.js 8433 2012-04-18 12:23:53Z chris.chencq $			:
 */;
(function () {
    var preview, imgContent = $("#imgContent"),
        pName = $("#photoName"),
        pDescript = $("#photoDescrip"),
        commtentsNum = $("#photo_comments_num"),
        comment_nums = $("#comment_nums"),
        _pre = $("#photo_pre"),
        _next = $("#photo_next"),
    	report = $("#report");
    function loadImage() {
        imgContent.attr("src", preview.src);
    }
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
    var slider = {
        photoList: $("#photo_mini_list"),
        photo_list_item: $("#photo_mini_list li"),
        pic_edit: $("#pic_edit"),
        pic_del: $("#pic_del"),
        pic_handle: $("#curPicId"),
        comment_content: $("#photo_comments"),
        commentV: $("#J_fresh_post_ta"),             
        page: 10,//每版显示10条       
        dis: 59,//单位缩略图间距              
        perNum: 5,//每次移动5条           
        exifInfoArr:[],//保存照片EXIF信息        
        picCommentArr:[],//保存照片的评论信息
        
        init: function () {
            this.changHash();
            var _this = this,
                curId = location.hash.substr(1);
            //重写photo_mini_list的宽度
            _this.photoList.css("width", _this.photo_list_item.length * _this.dis);
            //确定当前小图的位置
            _this.setPosition($("#photo_" + curId));
            //举报的图片id
            report.data("typeid",curId);
            //左右按钮绑定事件
            _this.buttonClick();
            //点击缩略图
            $("#photo_mini_list a").bind('click', function (e) {
                e.preventDefault();
                var $this = $(this);
                _this.displayImage($this);
            })
            $("#nextPic").bind('click', function () {
                _this.nextPic();
            })
            $("#prePic").bind('click', function () {
                _this.prePic();
            })
            _this.operatePic();
            _this.submitComment();
            _this.loadExif();
            //删除回复操作
            _this.comment_content.find('.pop_close').live('click', function (e) {
                e.preventDefault();
                var $this = $(this),
                    $id = $this.attr('data-del');
                _this.deleteComment($this, $id);
            })
            
            //left、right键
            $(document).bind('keydown',function(event){
            	if(event.keyCode==37){
            		_this.prePic();
            	}
            	if(event.keyCode==39){
            		 _this.nextPic();
            	}
            })
        },
        //改变url的hash值,用于刷新页面后定向当前页
        changHash: function () {
            var _this = this,
                currentId = _this.pic_handle.val(),
                hash = (!window.location.hash) ? "#" + currentId : window.location.hash;
            window.location.hash = hash;
        },
        //左右按钮绑定事件
        buttonClick:function(){
        	var _this=this;
                _pre.on('click', function (e) {
                    e.preventDefault();
                  var  p_left = _this.photoList.css("left");
                    if(p_left!="0px"){
                    	_this.move("right");
                    }else{
                    	return false;
                    }              
                })
                _next.on('click', function (e) {
                    e.preventDefault();
                    if (!_this.isLastEleView()) {
                        _this.move("left");
                    }else{
                    	return false;
                    }
                })
        },
        //判断最后一个元素是否出现在可视范围内
        isLastEleView:function(){
        	var _this=this,
        		lastEle=$("#photo_mini_list li:last"),
        	   p_left = parseInt(_this.photoList.css("left")),
               s_left = p_left < 0 ? Math.abs(p_left) : p_left,
            	wrapwidth=this.page*this.dis;
        	if(s_left+wrapwidth >= lastEle.position().left){
        		return true;
        	}else{
        		return false;
        	}
        },
        //点击缩略图显示大图 ,ele为a元素
        displayImage: function (ele) {
            var _this = this,
                url = ele.attr('data-img'),
                $id = ele.attr("data-id");
            ele.parent().addClass('current').siblings().removeClass('current');
            //先执行onload事件然后再给src赋值
            preview = new Image();
            preview.onload = loadImage;
            preview.onerror = function () {};
            preview.src = url;
            location.hash = $id;
            //改变ID,用于删除、编辑操作
            _this.pic_handle.val($id);            
            //举报的图片id
            report.data("typeid",$id);         
            _this.firstLastMove(ele.parent());
            _this.loadPicInfo(ele);
            _this.loadComment($id)
        },
        //对图片的编辑、删除操作
        operatePic: function () {
            var _this = this;
            _this.pic_del.bind('click', function (e) {
                e.preventDefault();
                _this.deletePic($(this));
            })
            _this.pic_edit.bind('click', function (e) {
                e.preventDefault();
                _this.editPic($(this));
            })
        },
        //获取当前图片的索引 从1开始
        getSlideIndex: function (cur) {
            return this.photo_list_item.index(cur) + 1;
        },
        //根据当前索引计算cur元素的left值
        computeLeft: function (cur) {
            var index = this.getSlideIndex(cur),
                page = this.page,
                dis = this.dis,
                left = 0,
                num = parseInt(index / page);
            if (index < page) {
                left = 0;
            }
            //如果当前显示的索引刚好是10的整数倍,小图居中显示,前四后五
            else if (index % page == 0) {
                left = ((num - 1) * page + 5) * dis;
            } else {
                left = num * page * dis;
            }
            return left;
        },
        //当前板块的最左边或者最右边
        firstLastMove: function (ele) {
            var cur_li = ele,
                _this = this,
                cur_left = cur_li.position().left,
                p_left = parseInt(_this.photoList.css("left")),
                s_left = p_left < 0 ? Math.abs(p_left) : p_left,
                index = _this.getSlideIndex(cur_li),
                r_dis = (_this.page-1)*_this.dis;
            if (s_left == cur_left && index > 1) {
                _this.move('right');
            }
            if (s_left + r_dis === cur_left) {
                _this.move('left');
            }
        },
        setPosition: function (cur) {
            var left = this.computeLeft(cur),
                _left = "-" + left + "px",
                ele = cur.find('a');
            this.displayImage(ele);
            this.photoList.css({
                "left": _left
            });
            this.buttonisActive()
        },
        nextPic: function () {
            var nextEl = this.photoList.find(".current").next();
            if(nextEl.length){
               var link = nextEl.find("a");
                link.trigger('click');
            }else{
                resultTip({
                    msg: "这已是最后一张啦"
                });
                return false;
            }
        },
        prePic: function () {
            var preEl = this.photoList.find(".current").prev();
            if(preEl.length){
                var link = preEl.find("a");
                link.trigger('click');
            }else{
                resultTip({
                    msg: "这是第一张哦"
                });
                return false;
            }
        },
        move: function (dir) {
            //每次移动5个图片的距离
            var moveLen = this.perNum * this.dis;
            var _this=this;
            if(!this.photoList.is(":animated")){
	            if (dir == "left") {
	                this.photoList.animate({
	                    left: '-=' + moveLen
	                },function(){_this.buttonisActive()});
	            } else {
	                this.photoList.animate({
	                    left: '+=' + moveLen
	                },function(){_this.buttonisActive()});
	            }
            }
        },
        buttonisActive: function () {
        	var  p_left = this.photoList.css("left");
            if (this.photo_list_item.length<this.page) {
                _pre.addClass("pre_disabled");
                _next.addClass("next_disabled");
            } else if (p_left=="0px") {
            	_pre.addClass("pre_disabled");
            	_next.removeClass("next_disabled");
                return false;
            } else if (this.isLastEleView()) {
                _next.addClass("next_disabled");
                _pre.removeClass("pre_disabled");
                return false;
            }else{
            	  _pre.removeClass("pre_disabled");
                  _next.removeClass("next_disabled");
            }
        },
        //加载图片的标题以及评论
        loadPicInfo: function (ele) {
            pName.html(ele.attr("data-pname"));
            pDescript.html(ele.attr("data-pdes"));
        },
        //删除图片
        deletePic: function (ele) {
            var _this = this,
                id = _this.pic_handle.val();
            ajaxConfirm({
                elem: ele,
                href: ele.attr('href') + "&photoid=" + id,
                msg: "确定要删除这张图片吗?",
                callback: function () {
                    resultTip({
                        msg: '删除成功'
                    });
                    _this.nextPic();
                    $("#photo_" + id).remove();
                }
            });
        },
        //编辑图片
        editPic: function (ele) {
            var _this = this,
                id = _this.pic_handle.val(),
                url = ele.attr('href');
            var sucCallBack = function (data) {
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
        						 _this.sendEditData($form);
    						 })
    					}
            		})
            	}
               }
            if($("#J_edit_photo").length){
            	return false;
            }else{
            	showAjaxData(url, { photoid: id }, "post", 'html', sucCallBack);
            }
        },
        sendEditData: function ($obj) {
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
 
        },
        //发表评论
        submitComment: function () {
            var _this = this,
                $btnSubmit = $("#btn_submit_comment"),
                $form = $("#photoCommentForm");
            $btnSubmit.click(function (e) {
                e.preventDefault();
                var $comment = $.trim(_this.commentV.val()),
                    url = $form.attr("action"),
                    csrf_token = $form.find("input[name=csrf_token]").val(),
                    id = _this.pic_handle.val();
                if ($comment == "") {
                    resultTip({
                        error: true,
                        msg: '评论内容不能为空!'
                    });
                    return false;
                } else {
                    data = {
                        content: $comment,
                        photoid: id,
                        csrf_token: csrf_token
                    };
                    var sucCallBack = function (data) {
                            if (!data.state && ajaxTempError(data)) {
                                _this.commentV.val('');
                                resultTip({
                                    msg: "评论成功"
                                });
                              //写入评论数组
                                _this.picCommentArr[id]=data;
                                _this.comment_content.html(_this.picCommentArr[id]);
                            } else {
                                resultTip({
                                    error: true,
                                    msg: data.message[0],
                                    follow: false
                                });
                            }
                        }
                    
                    showAjaxData(url, data, null, 'html', sucCallBack);
                }
            })
        },
        //加载评论
        loadComment: function (id) {
            var _this = this;
            var sucCallBack = function (data) {
                    if (!data.state && ajaxTempError(data)) {
                        _this.comment_content.html(data);
                        //写入评论数组
                        _this.picCommentArr[id]=data;
                        //改写评论数字
                        var comms = $("#comment_num").html();
                        comment_nums.html("评论 (" + comms + ")");
                    } else {
                        resultTip({
                            error: true,
                            msg: data.message[0],
                            follow: false
                        });
                    }
                  //用户名标签化，验证函数是否已存在
                    if($.isFunction(window.userCard)) {
                    	userCard();
                    }else{
                    	Wind.js(GV.JS_ROOT+ 'pages/common/userCard.js?v='+ GV.JS_VERSION);
                    }
                }
            if(_this.picCommentArr[id]){
            	_this.comment_content.html(_this.picCommentArr[id]);
            }else{
            	 showAjaxData(url.loadCommentUrl, {photoid: id}, "post", 'html', sucCallBack);
            }
        },
        //加载图片EXIF信息
        loadExif: function () {
        	var _this = this,
        		$exif = $("#exif"),
        		$loadExifInfo = $("#loadExifInfo"),
        		$loadExifList = $loadExifInfo.find("ul");
        	$exif.mouseover(function(){
        		var currentId = _this.pic_handle.val();
        		$loadExifInfo.show();
        		$loadExifList.html('<div class="pop_loading"></div>');
        		var callback=function(data){
        		if(data.state==="success"){
        			var info=data.data;
        			if(info.length>0){
        			var infoArr=['<li>厂商：'+info[0]+'</li>','<li>型号：'+info[1]+'</li>','<li>日期：'+info[2]+'</li>',
							'<li>大小：'+info[3]+'</li>','<li>焦距：'+info[4]+'</li>','<li>光圈：'+info[5]+'</li>',
							'<li>曝光时间：'+info[6]+'</li>','<li>感光度：'+info[7]+'</li>','<li>曝光程序：'+info[8]+'</li>',
							'<li>测光模式：'+info[9]+'</li>'].join("");
        			}else{
        				var infoArr=['<li>图片没有EXIF信息</li>'].join("");
        			}
        			//将exif信息写到到数组中，避免重复请求
        			_this.exifInfoArr[currentId]=infoArr;
        			$loadExifList.html(infoArr);
        		}else{
        			$loadExifList.html(data.message[0]);
        		}
        	}
        	if(_this.exifInfoArr[currentId]){
        		$loadExifList.html(_this.exifInfoArr[currentId]);
        	}else{
            	showAjaxData(url.getExifUrl,{photoid:currentId},"GET",'json',callback);
        	}
        	}).mouseout(function(){
        		$loadExifInfo.hide();
        	})
        },
        //删除回复
        deleteComment: function (ele, id) {
            var _this = this,
            	url=ele.attr('href'),
                currentId = _this.pic_handle.val();          
            var params = {
            		message : '确定要删除这条评论吗？',
            		type : 'confirm',
            		isMask : false,
            		follow : ele,
            		onOk : function () {
            			var callback=function(data){
            				if(!data.state && ajaxTempError(data)){
            					resultTip({ msg: '删除成功'});
                                //写入评论数组
                                _this.picCommentArr[currentId]=data;
                                _this.comment_content.html(_this.picCommentArr[currentId]);
            				}
            			}
            		showAjaxData(url,null,"GET",'html',callback);
            		}
            	}
            Wind.dialog(params);
        }
    }
    slider.init();
})()
