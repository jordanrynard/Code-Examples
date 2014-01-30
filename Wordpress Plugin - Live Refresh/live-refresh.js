(function($){

	// We can pass this through wp_localize_script, but for now let's define it manually
	var fileAlterationMonitorLink = '/wp-content/plugins/live-refresh/file-alteration-monitor.php';
	var scanningGifLink = '/wp-content/plugins/live-refresh/img/scanning.gif';
	var savedImgLink = '/wp-content/plugins/live-refresh/img/green_circle.png';

	function getAbsoluteURLFromFileInfo(file_info){
		dirnameSplit = file_info.dirname.split('/wp-content/');
		absURL = '/wp-content/'+dirnameSplit[1]+"/"+file_info.basename;
		return absURL;
	}

	function updateCSS(stylesheet){
	    var queryString = '?reload=' + new Date().getTime();
	    $('link[href*="'+stylesheet+'"]').each(function() {
	        this.href = this.href.replace(/\?.*|$/, queryString);
	    });
	    // Visual Signal
	    $("body").remove(".saved-css.live-refresh");
		var savedImg = $("<img class='saved-css live-refresh' src='"+savedImgLink+"' style='position:fixed;top:40px;margin-top:4px;width:21px;left:1px;'/>");
		$("body").append(savedImg);
		savedImg.fadeOut(3000);
	}

	function fileAlterationMonitor(){
		$.getJSON(fileAlterationMonitorLink, {socketId:socketId}, function(data){
		}).always(function(){
			console.log("Idling...\n");	
		});
	}

	function startScanningVisual(){
		var scanningGif = $("<img class='scanning-gif live-refresh' src='"+scanningGifLink+"' style='position:fixed;top:28px;margin-top:1px; left:3px;'/>");
		$("body").append(scanningGif);
	}

	function stopScanningVisual(){
		$(".scanning-gif.live-refresh").remove();
	}

	$.ajaxSetup({ cache: false }); // Required so getJSON can be called in a loop

	// In version 2, have user obtain key manually and store it in DB
	var pusher_key = 'bfef05c109ea89dd1789';
	var pusher_channel = 'browser-update';
	var pusher = new Pusher(pusher_key);
	var channel = pusher.subscribe(pusher_channel);
	var socketId = null;
	var pusherDisconnectTime = 60 * 60 * 1000; // 1 hour

	channel.bind('startingScan', function(data) {
		console.log("==========================================================================================");
		console.log('The script has started looking for changes to files.');
		console.log(data);
		console.log("==========================================================================================\n");
		startScanningVisual();
	});

	channel.bind('processSCSS', function(data) {
		console.log("==========================================================================================");
		console.log('Processing SCSS');
		console.log('File: '+getAbsoluteURLFromFileInfo(data.file_info));
		console.log(data);
		console.log("==========================================================================================\n");
	});

	channel.bind('updateCSS', function(data) {
		console.log("==========================================================================================");
		console.log('Updating CSS');
		console.log('File: '+getAbsoluteURLFromFileInfo(data.file_info));
		console.log(data);
		console.log("==========================================================================================\n");
		stylesheet = getAbsoluteURLFromFileInfo(data.file_info);
		updateCSS(stylesheet);
	});

	channel.bind('refreshBrowser', function(data) {
		console.log("==========================================================================================");
		console.log('Refreshing Browser');
		console.log('File: '+getAbsoluteURLFromFileInfo(data.file_info));
		console.log(data);
		console.log("==========================================================================================\n");
		console.log("DONE. Please refresh now.\n");
		pusher.disconnect(); // It says this isn't required - but I'm going to do it anyway
		location.reload(true); // True says don't load from cache
	});

	channel.bind('scriptTimedOutWithoutChanges', function(data) {
		console.log("==========================================================================================");
		console.log('Script has now timed out.');
		console.log(data);
		console.log("==========================================================================================\n");
		// The script could have finished scanning, or the page that was running it was closed
		// Either way, we trigger all pages to keep scanning -- it's a race to the PID file
		stopScanningVisual();
		fileAlterationMonitor();
	});

	channel.bind('alreadyScanning', function(data) {
		console.log("==========================================================================================");
		console.log('Another instance just tried to scan for file changes - and failed. \nScript already running.');
		console.log(data);
		console.log("==========================================================================================\n");
	});



	/* Don't wrap this in .ready */
	pusher.connection.bind('connected', function() {
		socketId = pusher.connection.socket_id; // We use this form informing on this client that there's a script already running
		channel.bind('initiatingScan-'+socketId, function(data) {
			console.log("==========================================================================================");
			console.log('Initiating scan from this page.');
			console.log(data);
			console.log("==========================================================================================\n");
		});
		channel.bind('alreadyScanning-'+socketId, function(data) {
			console.log("==========================================================================================");
			console.log('The script is already scanning for file changes. \nEnding new attempt now.');
			console.log(data);
			console.log("==========================================================================================\n");
			startScanningVisual();
		});
		fileAlterationMonitor();
	});


	pusher.connection.bind('disconnected', function() {
		console.log("==========================================================================================");
		console.log('Disconnection has been forced on this Page instance.');
		console.log("==========================================================================================\n");
		stopScanningVisual();
	});

	setTimeout(function(){
		pusher.disconnect();
		alert("File Alteration Monitor has been Disengaged from this Page.");
	}, pusherDisconnectTime);



})(jQuery);