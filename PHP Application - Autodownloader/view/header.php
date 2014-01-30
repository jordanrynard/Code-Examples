<html>
<head>
	<meta name="viewport" content="width=480, user-scalable=no"/>
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta names="apple-mobile-web-app-status-bar-style" content="black-translucent" />
	<link href="/view/images/favicon.png" rel="icon" type="image/png" />
	<link href="/view/reset.css" rel="stylesheet" />
	<link href="/view/style.css" rel="stylesheet" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script src="<?=SOCKETIO_URL?>/socket.io/socket.io.js"></script> <? // node_modules/socket.io/node_modules/socket.io-client/dist/socket.io.js ?>
	<script>globals = {view:'<?=$this->view?>',list:'<?=$this->list?>',url:'<?=URL?>',socketioUrl:'<?=SOCKETIO_URL?>'} </script>
	<script src="/view/script.js"></script>
	<script src="/view/<?=$this->view?>.js"></script>
	<link href="//vjs.zencdn.net/4.2/video-js.css" rel="stylesheet">
	<script src="//vjs.zencdn.net/4.2/video.js"></script></head>
<body class="<?=$this->view?>">
	<div class="wrapper">
		<div id="top-bar">
			<a href="/<?=$this->view?>/mylist/" class="<?=$this->isMyList()?'selected':''?>">My List</a>
			<a href="/<?=$this->view?>/popular/" class="<?=$this->isPopular()?'selected':''?>">Trending</a>
			<a href="/<?=$this->view?>/search/" class="<?=$this->isSearch()?'selected':''?>">Search</a>
		</div>
		<div id="bottom-bar">
			<a href="/tv/" class="<?=$this->isTV()?'selected':''?>">TV</a>
			<a href="/episodes/" class="<?=$this->isEpisodes()?'selected':''?>">Episodes</a>
			<a href="/movies/" class="<?=$this->isMovies()?'selected':''?>">Movies</a>
		</div>
		<iframe id="remote-control" src="/libraries/play_to_xbmc/remote.html" scrolling="no"></iframe>
