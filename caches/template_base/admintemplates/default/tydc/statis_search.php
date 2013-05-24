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
		<li><a href="<?php echo U('tydc/statis/'.$dc);?>">全员统计报表</a></li>
		<li><a href="<?php echo U('tydc/statis/single', array('dc' => $dc));?>">单项统计报表</a></li>
		<li class="current"><a href="<?php echo U('tydc/statis/search', array('dc' => $dc));?>">自定义统计报表</a></li>
	</ul>
	
</div>
<form action="<?php echo U('tydc/statis/search', array('dc' => $dc));?>" method="post">
<select class="select_2" name="info[type]" id="search_type">
    <option value="">统计类型</option>
    <option value="avg" selected="selected">单项平均值</option>
    <option value="user">员工参与比例</option>
	<!--option value="total">员工统计均值</option-->
</select>
<?php if($dc == 'stress') { ?>
<select class="select_2" name="info[cat]" id="search_cat">
    <option value="">维度</option>
    <?php $n=1; if(is_array($options_cat)) foreach($options_cat AS $k => $v) { ?>
    <option value="<?php echo $k;?>" <?php if($info[cat] == $k) { ?>selected<?php } ?>><?php echo $v;?></option>
    <?php $n++;}unset($n); ?>
</select>
<?php } ?>
<select class="select_2" name="info[worktime][]" id="search_worktime1">
    <option value="0">入职年限（起）</option>
    <?php $n=1; if(is_array($options_worktime)) foreach($options_worktime AS $k => $v) { ?>
    <option value="<?php echo $k;?>" <?php if($info[worktime][0] == $k) { ?>selected<?php } ?>><?php echo $v;?></option>
    <?php $n++;}unset($n); ?>
</select>——
<select class="select_2" name="info[worktime][]" id="search_worktime2">
    <option value="-1">入职年限（止）</option>
    <?php $n=1; if(is_array($options_worktime)) foreach($options_worktime AS $k => $v) { ?>
    <option value="<?php echo $k;?>" <?php if($info[worktime][1] == $k) { ?>selected<?php } ?>><?php echo $v;?></option>
    <?php $n++;}unset($n); ?>
</select>
<select class="select_2" name="info[gender]" id="search_gender">
    <option value="">性别</option>
    <?php $n=1; if(is_array($options_gender)) foreach($options_gender AS $k => $v) { ?>
    <option value="<?php echo $k;?>" <?php if($info[gender] == $k) { ?>selected<?php } ?>><?php echo $v;?></option>
    <?php $n++;}unset($n); ?>
</select>
<select class="select_2" name="info[age]" id="search_age">
    <option value="">年龄</option>
    <?php $n=1; if(is_array($options_age)) foreach($options_age AS $k => $v) { ?>
    <option value="<?php echo $k;?>" <?php if($info[age] == $k) { ?>selected<?php } ?>><?php echo $v;?></option>
    <?php $n++;}unset($n); ?>
</select>
<select class="select_2" name="info[department]" id="search_department">
    <option value="">部门</option>
    <?php echo $options_department_str;?>
</select>
<input value="生成图表" class="btn btn_submit" type="submit"/>
<input type="hidden" name="__hash__" value="<?php echo $this->getComponent('token')->saveToken('csrf_token');?>" /></form>
<br />
<div id="placeholder" style="width:800px;height:450px"></div>
<div class="table_list">
    <table width="100%">
        <colgroup>
            <col width="60">
            <col width="400">
            <col>
        </colgroup>
        <thead>
            <tr>
                <td width="10%">编号</td>
                <td width="70%">调查内容</td>
                <td width="10%">平均分</td>
            </tr>
        </thead>
	    <tbody id="J_tr">
        <?php $n=1;if(is_array($result_list)) foreach($result_list AS $v) { ?>
			<?php if($v['ID'] != 20) { ?>
            <tr>
                <td><?php echo $v['ID'];?></td>
                <td><?php echo $v['TITLE'];?></td>
                <td><?php echo $v['VALUE'];?></td>
            </tr>
			<?php } ?>
        <?php $n++;}unset($n); ?>
        </tbody>
    </table>
</div>
</div>
<?php include $this->_view->templateResolve("admin","footer",$__style__,$__app__); ?>
<?php if($items) { ?>
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
                <?php if($dc == 'stress') { ?>
				max: 4,
                ticks : 4,
                tickFormatter : function(v) {
                   return label_data[v];
                },
                <?php } else { ?>
				max: 5,
                <?php } ?>
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
						 items_data[x] +"<br />平均值：<span class='red'>" + y + "</span>");
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
<?php } ?>
</body>
</html>