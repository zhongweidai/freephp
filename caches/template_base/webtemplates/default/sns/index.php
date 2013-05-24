<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>������</title>
<link href="http://www.qqyard.com/tdgz/css/style.min.css" rel="stylesheet" type="text/css" />
<link href="http://www.qqyard.com/tdgz/css/icons.css" rel="stylesheet" type="text/css" />
<link href="http://www.qqyard.com/tdgz/css/buttons.css" rel="stylesheet" type="text/css" />
<link href="http://www.qqyard.com/tdgz/css/alert.css" rel="stylesheet" type="text/css" />
<link href="http://www.qqyard.com/tdgz/css/form.css" rel="stylesheet" type="text/css" />
<link href="http://www.qqyard.com/tdgz/css/table.css" rel="stylesheet" type="text/css" />
<link href="http://www.qqyard.com/tdgz/css/labelsBadges.css" rel="stylesheet" type="text/css" />
<link href="http://www.qqyard.com/tdgz/css/nav.css" rel="stylesheet" type="text/css" />
<script src="http://www.qqyard.com/tdgz/js/jquery.min.js"></script>
<script src="http://www.qqyard.com/tdgz/js/swipe.min.js"></script>
<script src="http://www.qqyard.com/tdgz/js/template.min.js"></script>
<script src="http://www.qqyard.com/tdgz/js/handle.js"></script>
<script type="text/javascript">
	$(window).load(function(){$("#splash").fadeOut(1000)});
</script>
</head>

<body>
<!-- ��������   -->
<div id="splash"> 
	<img id="splash-title" src="images/skinBlue/splash/main2.png" alt="splash title" />
	<img id="loading" src="images/skinBlue/splash/loading.png" alt="loading" />
</div>
<!--�����������-->

<header>
	<nav class="maxWidth">
		<a class="logo left" href="#"></a>
		<a class="headerButton right" href="javascript:" title="����">&#xe074;</a>
		<a class="headerButton right" href="javascript:" title="ѡ�����">&#xe068;</a>
		<a class="headerButton right" href="login.php" title="�û���¼">&#xe062;</a>
	</nav>
	<div class="clear headerBorder"></div>
	<div class="headerPopUp">
		<div class="maxWidth">
			<div id="mainSearch">
           	 <form>
				<input type="text" placeholder="������ؼ���" value="" />
				<button>����</button>
              <input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form>
			</div>
		</div>
	</div>
	<div class="headerPopUp" id="city">
   		<script id="showCityTemp">
           $(function(){$.markTemp('cityTemp','json/city.json','showCityTemp')});
        </script>
	</div>
</header>

<!--============================== ������ʽ ==============================-->
<div class="maxWidth" id="main">

	<div id="leftWrapper">
		
		<div id="slideWrapper">
			<span id="prev">&#xe013;</span>
			<span id="next">&#xe015;</span>
			<div class="touch"></div>
			<div class="touch R"></div>
			<div id="slidePics">
				<ul>
					<li><a href="sms.php"><img src="images/sample/1.png" alt=""><span>��Ϣ����</span></a></li>
					<li><a href="sms.php"><img src="images/sample/1.png" alt="123"><span>��ˮ��</span></a></li>
					<li><a href="sms.php"><img src="images/sample/1.png" alt=""><span>��ˮ��</span></a></li>
				</ul>
			</div>
            <div id="slideStatus"></div>
		</div>
		
		
		<dl class="column">
			<dt class="tagTitle" id="c1_title"></dt>
			<dd>
                <script id="showNewsList">
					$(function(){$.markTemp('news&hotTemp','json/homeNews.json','showNewsList')});
				</script>
			</dd>
			<dd class="none">
				<script id="showHotList">
					$(function(){$.markTemp('news&hotTemp','json/homeHot.json','showHotList')});
				</script>
			</dd>
            <dd class="none">
				<script id="showRoadNews">
					$(function(){$.markTemp('news&hotTemp','json/homeRoadCondition.json','showRoadNews')});
				</script>
			</dd>
		</dl>
		<script id="showHomeCategoryTemp">
           $(function(){$.markTemp('homeCategoryTemp','json/homeCategoryList.json','showHomeCategoryTemp')});
        </script>
	</div>
	<div id="rightWrapper">
         <script id="showHomeWeather">
               $(function(){
			   $.markTemp('homeWeather','json/homeWeather_guiyang.json','showHomeWeather')});
         </script>
        <script id="showOffersList">
           $(function(){
			   //$.markTemp('offersTemp','homeOffers.json','showOffersList')
			   $.getJSON("json/homeOffers.json", function(data){
				    var html = template('offersTemp', data);
					$('#showOffersList').after(html);
				 	//alert($("#offers").html());
					$("#offers").slideUp({
						"li_h":"230",
						 "time":3000,
						 "movetime":1000
					},'#offers');	
				});
			});
        </script>
        <script id="showWeiBoList">
			$(function(){$.markTemp('weiboTemp','json/weibo.json','showWeiBoList')});
        </script>
	</div>
</div>
<!--============================== ������ʽ ==============================-->

<!--============================== ģ�� ====================================-->
<!--����ѡ��-->
<script id="cityTemp" type="template">
<menu class="maxWidth">
	{each data}
	<a href="{link}"><span>{$value.city}</span></a>
	{/each}
</menu>
</script>

<!--Ƶ������ģ��-->
<script id="homeCategoryTemp" type="template">
<dl class="column" id="homeCategoryList">
	<dt><a href="{link}" class="titleReadmore">�鿴����</a>{column} [��ҳ:{$Math data.length/4}/Ƶ��:{data.length}]</dt>
	<dd>
		<?php forLoop i = 0; i < $Math (data.length/4); i ++; ?>
		<?php if(i === 0) { ?>
		<ul class="textList on" data-index="{i+1}">
		{else if i>0}
		<ul class="textList none" data-index="{i+1}">
		<?php } ?>
			{each data as value index}
			<?php if(index >= i*4 && index+1 <= (i+1)*4) { ?>
			<li class="channelList">
				<a class="btn {value.color}" href="{value.channelLink}">{value.channelName}</a>
				{each data[index].subListing as value indexI}
				<a href="{value.itemLink}">{value.itemName}</a>
				{/each}
			</li>
			<?php } ?>
			{/each}
		</ul>
		{/forLoop}
		<div class="buttonArea" style="margin:0; padding:10px">
			<a disabled class="left btn btn-small">��һҳ</a>
			<span class="badge"><span>1</span>/{$Math (data.length/4)}</span>
			<a class="right btn btn-small">��һҳ</a>
		</div>
	</dd>
</dl>
</script>

<!--����������Ϣģ��-->
<script id="news&hotTemp" type="template">
{$columnTitle column 'c1_title'}
<ul class="list">
{each data as value index}
	<?php if(index < 3 ) { ?>
	<li><a href="{value.link}"><span>{value.date}</span><strong>[{value.sort}]</strong>&nbsp;{value.title}</a></li>
	<?php } ?>
{/each}
	<li><a href="{link}">�鿴����...</a></li>
</ul>
</script>

<!--΢��ģ��-->
<script id="weiboTemp" type="template">
<dl class="column">
	<dt><a href="{link}" class="titleReadmore">����</a>{column}</dt>
	<dd>
		<ul class="list">
			{each data as value index}
			<?php if(index < 5 ) { ?>
			<li><a href="{value.link}"><span>{value.date}</span><strong>[{value.sort}]</strong>&nbsp;{value.title}</a></li>
			<?php } ?>
			{/each}
		</ul>
	</dd>
</dl>
</script>

<!--�Ż���Ϣģ��-->
<script id="offersTemp" type="template">
<dl class="column">
	<dt><a href="{link}" class="titleReadmore">����</a>{column}</dt>
	<dd>
		<ul class="list listImg" id="offers" style="height:230px;overflow:hidden;">
		{each data as value index}
			<li>
				<a href="{value.link}">
					<img src="{value.imgSrc}" width="90" alt="{value.link}" />
					<div class="listText"><h3 class="title">{value.title}</h3>{value.phone}<br>{value.address}</div>
				</a>
			</li>
		{/each}
		</ul>
	</dd>
</dl>
</script>

<!--����ģ��-->
<script id="homeWeather" type="template">
<dl class="column">
	<dt><a href="{link}" class="titleReadmore">�鿴δ������</a>{column}</dt>
	<dd>
		<ul class="homeWeather-style-list">
			<li>
				<img src="{data[0].icon}" />
				<div><h3>��������:{data[0].temperature}</h3>{data[0].weather}; ����:{data[0].windForce}; ���ʪ��:{data[0].humidity}</div></a>
			</li>
		</ul>
	</dd>
</dl>
</script>
<!--============================== ģ����� ====================================-->

<!--============================== ģ��ƴװ���ƽű� ==============================-->
<script>
//��ȡ����
template.helper('$Math', function (content) {
	var l = Math.ceil(content);
	//alert(l);
	return l;
});



$(function(){
	$('#leftWrapper').delegate('#homeCategoryList .buttonArea .btn','click',function(){
		//alert($('#homeCategoryList').find('ul.on').attr('data-index'));
		var node = $(this);
		var dl = $('#homeCategoryList');
		var ulLength = dl.find('ul').length;
		var ulOn = dl.find('ul.on');
		var ulIndex = ulOn.attr('data-index');
		var leftBtn = dl.find(".buttonArea a.left");
		var rightBtn = dl.find(".buttonArea a.right");
		
		//alert(ulIndex);
		if(node.hasClass('left')){
			dl.find('ul.on').prev().addClass('on').next().removeClass('on');
			dl.find('ul.on').next().addClass('none').prev().removeClass('none');
			var ulIndex = dl.find('ul.on').attr('data-index');
		}else if(ulIndex < ulLength){
			dl.find('ul.on').next().addClass('on').prev().removeClass('on');
			dl.find('ul.on').prev().addClass('none').next().removeClass('none');
			var ulIndex = dl.find('ul.on').attr('data-index');
			
		}
		dl.find("span.badge span").html(ulIndex);
		if(ulIndex == ulLength){
			rightBtn.attr('disabled','')
		}else{
			rightBtn.removeAttr('disabled');
		}
		if(ulIndex == 1){
			leftBtn.attr('disabled','')
		}else{
			leftBtn.removeAttr('disabled');
		}
	});
	
});
</script>
<!--============================== ģ��ƴװ���ƽű����� ==============================-->

<div class="clear"></div>
<footer><a>����</a> | <a>�ͻ�������</a> | <a id="teamBuilder">�����Ŷ�</a><br>�������߳���[ǭB2-20010020-5] </footer>

<!--�����Ŷ�����-->
<div id="teamBuilderCont">
<span title="�ر�"></span>
<marquee direction="up" scrollamount="3">
    <h3>�������߳��С��̲��ŷ��������Ŷ�</h3>
    <h4>�ܼ��ƣ����������ң���</h4>
    �����ԡ������������������ƽ������ָ١����Ф��<br>
    <h4>��Ʒ���/����֧�ţ��人����ͨѶ�������޹�˾����</h4>
    ����,��Ӫ��,����,�շ���,������,��Сΰ,������,����ͼ,����,����
</marquee>
</div>
<!--�����Ŷ����� ����-->
</body>
</html>
