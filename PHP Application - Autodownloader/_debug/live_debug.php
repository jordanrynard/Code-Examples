<html>
<head>
	<title>Live Debugger | Media Center</title>
	<link href="/view/images/favicon.png" rel="icon" type="image/png" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script src="<?=SOCKETIO_URL?>/socket.io/socket.io.js"></script> <? // node_modules/socket.io/node_modules/socket.io-client/dist/socket.io.js ?>
	<script>
		spacer = 0;

		var socket = io.connect('<?=SOCKETIO_URL?>');
		socket.on('live_debug', function (data) {
			clearInterval(spacer);
			$("xmp").prepend(data.msg);
			spacerFunction();
		});

		function spacerFunction(){
			spacer = setInterval(function(){
				$("xmp").prepend("------------------------------------------------------------------------------------------------------------------\n");
			},10000);
		}
	</script>	
</head>
<body>
	<xmp>
	</xmp>
	<? phpinfo(); ?>
</body>
</html>