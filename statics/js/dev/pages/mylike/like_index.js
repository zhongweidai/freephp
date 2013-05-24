/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 喜欢瀑布流效果
 * @Author	: siweiran@gmail.com
 * @Depend	: jquery.js(1.7 or later), like.js
 * $Id: like_index.js 5804 2012-03-12 08:58:35Z hao.lin $
 */
//热门喜欢模板
var template = '\
	<div class="box">\
		<% if(thumbAttach!=""){%>\
		<div class="img"><a href="<%=url%>"><img src="<%=thumbAttach%>" width="200"></a></div>\
		<%}%>\
		<div class="descrip"><%=intro%><a href="<%=url%>">更多&gt;&gt;</a></div>\
		<dl class="user">\
		<% if(smallAvatar!=""){%>\
		<dt><img src="<%=smallAvatar%>" width="30" height="30" class="J_avatar" data-type="small" /></dt>\
		<%}%>\
		<dd>\
		<p class="name"><a href="<%=space%>"><%=author%></a></p>\
		<p class="time"><%=threadTime%></p>\
		</dd>\
		</dl>\
		<div class="num J_like_btn" title="点击添加到我的喜欢" data-url="'+ LIKE_PLUS +'" data-role="hot"><em class="J_like_count"><%=like%></em></div>\
</div>';

//Ta的喜欢模板
var templateTa=	'\
		<div class="box">\
			<% if(image!=null){%>\
				<div class="img"><a href="<%=url%>"><img src="<%=image%>" width="200"></a></div>\
			<%}%>\
		<div class="descrip"><%=descrip%> <a href="<%=url%>">更多&gt;&gt;</a></div>\
		<dl class="user">\
			<dt>\
			<a class="J_user_card_show" data-uid="<%=uid%>" href="<%=space%>"><img  class="J_avatar" src="<%=avatar%>" data-type="small" width="30" height="30" /></a></dt>\
			<dd>\
				<p class="name"><a href="<%=space%>" class="J_user_card_show" data-uid="<%=uid%>"><%=username%></a></p>\
				<p class="time"> <%=lasttime%></p>\
			</dd>\
		</dl>\
		<div class="num J_like_btn" title="点击添加到我的喜欢" data-url="'+ LIKE_PLUS +'" data-role="hot"><em class="J_like_count"><%=like_count%></em></div>\
			<% if(reply_pid!=null){%>\
				<div class="reply">\
					<div class="arrow">\
						<em></em>\
						<span></span>\
						</div>\
					<p><a href="<%=reply_space%>" target="_blank"><%=reply_username%></a>：<%=reply_content%></p>\
				</div>\
			<%}%>\
		</div>';

function isObj(obj) {
		   return Object.prototype.toString.call(obj) == '[object Object]';
}

function picFall(options){
	//ajax地址
	this.url=options.url;
	//列对象
	this.colObj=[$('#like_Col_0'),$('#like_Col_1'),$('#like_Col_2'),$('#like_Col_3')];
	//obj不一样,传递的参数也不一样
	this.obj=options.obj;
	//模板
	this.template=options.template;
	//差值
	this.dis=options.dis||20;
	//每次取多少条数据
	this.getNum=20;
	this.start=0;
	this.isload=true;
	this.init();
}
picFall.prototype={
	constructor:'picFall',
	//初始化
	init:function(){
		this.getAjax();
		this.scroll()
	},
	//往col里面添加图片
	addPic:function(obj){
		var $whichCol=this.shortCol();
		obj.appendTo($whichCol);

		//global.js
		avatarError(obj.find('img.J_avatar'));

		//绑定喜欢组件
		$('.J_like_btn').like();
	},
	//计算最短的哪一列
	shortCol:function(whichCol,el){
		for(var i=0,shortcol=0;el=this.colObj[i];i++){
			h=el.height();
			if(i==0){
				shortcol=h;
				whichCol=el;
			}
			if(h<shortcol){
				shortcol=h;
				whichCol=el;
			}
		}
		return whichCol;
	},
	getAjax:function(){
		var _this=this,
			params=_this.obj==="hotLike"?{moduleid:moduleid,pageid:pageid,start:_this.start*_this.getNum}:{start:_this.start*_this.getNum};
		$.ajax({
			   type:"POST",
			   url:_this.url,
			   dataType:'json',
			   data:params,
			   beforeSend:function(){
				   $("#loading").show()
			   },
			   success:function(data){
				   var result=data.data;				 
				   if(isObj(result)){
				   $.each(result,function(i, o){
							var ele=result[i];
							var html = Wind.tmpl(_this.template,ele).replace(/_KEY/g, o.fromid);//替换模板
							_this.addPic($(html))
							})
				   }else{
				          resultTip({
	                            error: true,
	                            msg: "没有数据啦"
	                        });
				          //没有数据解除滚动条的绑定事件
				          $(window).unbind('scroll');
				   }							
			   },
			   complete:function(){
				   $("#loading").hide()
			   },
			   error:function(result){
					resultTip({
						error : true,
						msg : result,
						follow : false
					});
			   }
			   })
	},
	scroll:function(){
		var windowHeight=$(window).height(),_this=this,timer=false;
		$(window).scroll(function(){
			var scrollTop=$(document).scrollTop(),scrollHeight=$(document).height();
			//防止滚动时触发多次
			if(timer){
				clearTimeout(timer)
			}
			timer=setTimeout(function(){
				if(windowHeight+scrollTop>scrollHeight-_this.dis){
					if(_this.start<4){
					_this.start++;
					_this.getAjax();
					}
				}
			},200)				
			})
	}
}
