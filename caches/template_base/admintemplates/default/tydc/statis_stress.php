<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head>
<?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body>
<div class="wrap">
    <div class="fr">
		<form class="J_custom_ajaxForm" method="post" action="">
			<button class="btn btn_submit J_ajax_submit_btn fr" data-url="<?php echo U('tydc/statis/cache');?>" data-form="J_custom_ajaxForm">刷新统计缓存</button>
		<input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form>
</div>
<div class="nav">
	<ul class="cc">
		<li class="current"><a href="<?php echo U('tydc/statis/'.$dc);?>">全员统计报表</a></li>
		<li ><a href="<?php echo U('tydc/statis/single', array('dc' => $dc));?>">单项统计报表</a></li>
        <li><a href="<?php echo U('tydc/statis/search', array('dc' => $dc));?>">自定义统计报表</a></li>
	</ul>
	
</div>
<div class="nav_minor">
<ul class="cc">
    <li <?php if($orderby == '') { ?>class="current"<?php } ?>><a href="<?php echo U('tydc/statis/'.$dc);?>">单项平均值</a></li>
    <li <?php if($orderby == 'worktime') { ?>class="current"<?php } ?>><a href="<?php echo U('tydc/statis/'.$dc, array('orderby' => 'worktime'));?>">入职年限分段分布图</a></li>
    <li <?php if($orderby == 'gender') { ?>class="current"<?php } ?>><a href="<?php echo U('tydc/statis/'.$dc, array('orderby' => 'gender'));?>">性别分布图</a></li>
    <li <?php if($orderby == 'age') { ?>class="current"<?php } ?>><a href="<?php echo U('tydc/statis/'.$dc, array('orderby' => 'age'));?>">年龄分段分布图</a></li>
</ul>
</div>
<!--
<div class="h_a">提示信息</div>
<div class="mb10 prompt_text">
	<li>提示</li>
</div>
-->
<div id="placeholder" style="width:800px;height:450px"></div>

</div>
<?php include $this->_view->templateResolve("admin","footer",$__style__,$__app__); ?>
<script type="text/javascript" src="<?php echo JS_PATH;?>../dc/js/flot/excanvas.min.js"></script>
<script type="text/javascript" src="<?php echo JS_PATH;?>../dc/js/flot/jquery.flot.min.js"></script>
<script type="text/javascript">
    $(function() {

		var items_data = new Array(),
            json_data = <?php echo $json_data;?>,
            label_data = ['完全没有', '很少', '有时', '经常', '总是'];

        <?php $n=1;if(is_array($items)) foreach($items AS $v) { ?>
        items_data[<?php echo $n;?>] = "<?php echo $v['TITLE'];?>";
        <?php $n++;}unset($n); ?>

		var plot = $.plot("#placeholder", json_data, {
			series: {
				lines: {
					show: true
				},
				points: {
					show: true
				}
			},
			grid: {
                backgroundColor: { colors: ["#fff", "#eee"] },
				hoverable: true,
				clickable: true
			},
            legend: {
            },
            xaxis: {
				min: 1,
				max: <?php echo count($items);?>,
                ticks: 20,
                tickDecimals: 0
			},
			yaxis: {
				min: 0,
				max: 4,
                ticks : 4,
                tickFormatter : function(v) {
                   return label_data[v];
                },
                tickDecimals: 0
			}
		});

		function showTooltip(x, y, contents) {
			$("<div id='tooltip'>" + contents + "</div>").css({
				position: "absolute",
				display: "none",
				top: y + 5,
				left: x + 5,
				border: "1px solid #fdd",
				padding: "2px",
				"background-color": "#fee",
				opacity: 0.80
			}).appendTo("body").fadeIn(200);
		}

		var previousPoint = null;
		$("#placeholder").bind("plothover", function (event, pos, item) {
			if (item) {
				if (previousPoint != item.dataIndex) {

					previousPoint = item.dataIndex;

					$("#tooltip").remove();
					var x = item.datapoint[0],
					y = item.datapoint[1];

					showTooltip(item.pageX, item.pageY,
						 "["+x+"] "+items_data[x]);
				}
			} else {
			    $("#tooltip").remove();
				previousPoint = null;
			}
		});

		$("#placeholder").bind("plotclick", function (event, pos, item) {
			if (item) {
				//$("#clickdata").text(" - click point " + item.dataIndex + " in " + item.series.label);
				//plot.highlight(item.series, item.datapoint);
			}
		});

	});

</script>
</body>
</html>