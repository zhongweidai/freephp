<?php defined('IN_FREE') or exit('No permission resources.'); ?><!doctype html>
<html>
<head>
<?php include $this->_view->templateResolve("admin","header",$__style__,$__app__); ?>
</head>
<body>
<div class="tips mb10 tal"><span>调查内容：<?php echo $result['DC_TITLE'];?></span></div>
<div id="plotArea" style="width: 400px; height: 300px;"></div>
<script type="text/javascript" src="<?php echo JS_PATH;?>jquery.js"></script>
<script type="text/javascript" src="<?php echo JS_PATH;?>../dc/js/flot/excanvas.min.js"></script>
<script type="text/javascript" src="<?php echo JS_PATH;?>../dc/js/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="<?php echo JS_PATH;?>../dc/js/flot/jquery.flot.pie.min.js"></script>
<script type="text/javascript">
    $(function() {
        var data = <?php echo $json_data;?>;

        $.plot($("#plotArea"), data,
        {
                series: {
                    pie: {
                        show: true,
                        radius: 1,
                        label: {
                            show: true,
                            radius: 3/4,
                            formatter: function(label, series){
                                return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">'+label+'<br/>'+Math.round(series.percent)+'%</div>';
                            },
                            background: { opacity: 0.5 }
                        }
                    }
                },
                legend: {
                    show: false
                }
        });

	});

</script>
</body>
</html>