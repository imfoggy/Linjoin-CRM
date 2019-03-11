<!doctype html>
<html>
<head>
    <meta charset="UTF-8">  
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">    
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">   
	<title>Linjoin CRM 跳转提示</title>
</head>
<style>
	body{
		background: #f9f9f9;
	}	
	.main{
		width: 312px;
		height: 132px;
		position: fixed;
		left: calc(50% - 206px);
		top: calc(50% - 61px);
	}
	.er-p1{
		font-size: 20px;
		font-weight: bold;
		color: #333333;
		text-align: center;
	}
	.er-p2{
		font-size: 16px;
    	color: #827676;
    	font-weight: normal;
    	text-align: center;
	}
	.img-er{
		display: block;
		margin: 0 auto 24px;
	}
	@media (max-width:716px){
		.main{
			width: 100%;
			left: 0;
		}
	}
</style>
<body>
<?php switch ($code){ ?>
	<?php case 1:?>
	<div class="main">
		<img src="/jump/t3.png" class="img-er">
		<p class="er-p1"><?php echo(strip_tags($msg));?></p>
		<p class="er-p2">页面自动 <a id="href" href="<?php echo($url);?>">跳转</a> 等待时间： <b id="wait"><?php echo($wait);?></b></p>
	</div>
	<?php break;?>
	<?php case 0:?>
	<div class="main">
		<img src="/jump/t4.png" class="img-er">
		<p class="er-p1"><?php echo(strip_tags($msg));?></p>
		<p class="er-p2">页面自动 <a id="href" href="<?php echo($url);?>">跳转</a> 等待时间： <b id="wait"><?php echo($wait);?></b></p>
	</div>
	<?php break;?>
<?php } ?>
<script type="text/javascript">
    (function(){
        var wait = document.getElementById('wait'),
            href = document.getElementById('href').href;
        var interval = setInterval(function(){
            var time = --wait.innerHTML;
            if(time <= 0) {
                location.href = href;
                clearInterval(interval);
            };
        }, 1000);
    })();
</script>
</body>
</html>
