// Socket IO
var socket = io.connect(globals.socketioUrl);
socket.on('download_status', function (data) {
	var button = $("ul.main-list li[data-data_id="+data.data_id+"]").find("a.button.download");
	button.attr({'data-status':data.column});
	button.children("span").html(data.text);
});

// iPhone Detection
function isAppleDevice(){
    return (
        (navigator.userAgent.toLowerCase().indexOf("ipad") > -1) ||
        (navigator.userAgent.toLowerCase().indexOf("iphone") > -1) ||
        (navigator.userAgent.toLowerCase().indexOf("ipod") > -1)
    );
}

// Play Video File
playVideo = function(event){
	var downloadButton = $(this); 
	downloadButton.disable();
	var video_id = downloadButton.parents(".container").attr("data-data_id");
	var data = {
		class:'XBMC', 
		method:'play',
		data_id:video_id
	};
	downloadButton.showLoading();
	// Add a delay for visual purposes
	var milliseconds = (new Date).getTime();
	$.post("/ajax.php", data, function(result){
		console.log(result);
		if (typeof $.parseJSON(result).success=='undefined'){
			console.log("There was a problem playing the video with XBMC idMovie or idEpisode: "+data.data_id);
			console.log(result);
			return false;
		}
		var remaining = 500 - ((new Date).getTime() - milliseconds);
		setTimeout(function(){
			downloadButton.doneLoading();
			downloadButton.enable();
		}, remaining);
	});
	return false;
};

function padForTime(number) {  
	return (number < 10 ? '0' : '') + number
}

// Now Playing
nowPlaying = function(){
	var data = {
		class:'XBMC', 
		method:'nowPlaying',
		data_id:1
	};
	$.post("/ajax.php", data, function(result){
		if (typeof $.parseJSON(result).success=='undefined'){
			console.log("There was a problem getting the Now Playing info.");
			console.log(result);
			return false;
		}
		var nowPlaying = $.parseJSON(result).result.item;
		if (nowPlaying.label.length <= 0){
			string = "Nothing Playing";
		} else {
			var string = "";
			string += (nowPlaying.label.length > 35) ? nowPlaying.label.substring(0,35)+"..." : nowPlaying.label;
			string += " &nbsp;["+ padForTime(nowPlaying.time.hours) + ":" + padForTime(nowPlaying.time.minutes) + ":" + padForTime(nowPlaying.time.seconds) + " / " + padForTime(nowPlaying.totaltime.hours) + ":" + padForTime(nowPlaying.totaltime.minutes) + ":" + padForTime(nowPlaying.totaltime.seconds) + "]";
			if (nowPlaying.speed == 0){
				string += " &nbsp;<i>[paused]</i>";
			}
		}
		$("iframe").contents().find("#nowPlayingRow").html("<div>"+string+"</div>");
	});
	return false;
};

// Mark Watched / Unwatched for Movies and Episodes
markWatchedUnwatched = function(event){
	event.stopPropagation();
	event.preventDefault();
	var data = {
		class:'Data', 
		method:'markWatchedUnwatched',
		data_id:$(this).parents(".container").attr("data-data_id"),
		data: { 
			watched: !$(this).find(".banner_watched").length > 0 ? 1 : 0
		}
	};
	$.post("/ajax.php", data, function(result){
		if (typeof $.parseJSON(result).success=='undefined'){
			console.log("There was a problem setting the Watched/Unwatched status.");
			console.log(result);
		}
		return false;
	});
	if (data.data.watched){
		$(this).append('<img src="/view/images/banner_watched_2.png" class="banner_watched"/>');
	} else {
		$(this).find(".banner_watched").remove();
	}
	return false;
};


// Visual Implementation for waiting after Button Click (ensures what needs to get done gets done before we give a visual cue)
jQuery.fn.showLoading = function() {
    var loadingImage = "/view/images/loading-medium.gif";
	this.find("span").hide();
    this.append('<span class="loading"><img src="'+loadingImage+'"/></span>');
};
jQuery.fn.doneLoading = function() {
	this.find(".loading").remove();
	this.find("span").show();
};
jQuery.fn.enable = function() {
	this.removeClass("disabled");
};
jQuery.fn.disable = function() {
	this.addClass("disabled");
};


/*=================================================================================================================================================*/


$(document).ready(function(){
	
 // Search Form
	$(".search_form").submit(function(){
		$(this).find(".please-wait").show();
	});

 // Long Titles (scrolling on hover)
  	$("li.container").hover(function(){
		var title_container = $(this).find(".title span");
		var diff = title_container.outerWidth() - title_container.parent().width();
		if (diff > 0){
			var magic_num = 18.07; // Based on 3000 seconds
			var speed = magic_num * diff;
			title_container.animate({left:-diff},speed,'linear',function(){
				title_container.animate({left:0},speed,'linear');
			});
		}
	}, function(){
		$(this).find(".title span").stop().css({left:'auto'});
	});

// Now Playing
  	setInterval(function(){
  		nowPlaying();
  	}, 5000);
  	// So it updates immediately once you press Play/Pause (we also need to wait for the iframe to load to do stuff to it)
  	$("iframe").load(function(){
		nowPlaying();
	  	$("iframe").contents().find("body").on("click", "button", function(){
	  		var button = $(this);
	  		button.addClass("selected");
	  		setTimeout(function(){
	  			button.removeClass("selected");
	  		},500);
	  		nowPlaying(); 
	  	});
  	});

});


// Add/Remove Episode or Movie
addRemoveEpisodeMovie = function(event){
	var downloadButton = $(this); 
	downloadButton.disable();
	var data = {
		class:'Torrents', 
		data_id:downloadButton.parents(".container").attr("data-data_id")
	};
	$.extend(data, event.data);
	downloadButton.showLoading();
	// Add a delay for visual purposes
	var milliseconds = (new Date).getTime();
	$.post("/ajax.php", data, function(result){
		if (typeof $.parseJSON(result).success=='undefined'){
			console.log("There was a problem adding the movie");
			console.log(result);
			return false;
		}
		var remaining = 500 - ((new Date).getTime() - milliseconds);
		setTimeout(function(){
			downloadButton.doneLoading();
			downloadButton.enable();
			if (data.method=='add'){
				downloadButton.attr({'data-status':'selected'});
			} else {
				downloadButton.attr({'data-status':'download'});
			}
		}, remaining);
	});
	return false;
};


/*====================================================================================================================*/


$(document).on("click.EpisodesMovies", "ul.main-list a", false);

$(document).on("click.EpisodesMovies", "a.button.download[data-status=downloaded]", playVideo);
$(document).on("click.EpisodesMovies", "a.button.download[data-status=download]:not(.disabled)", { method:'add' }, addRemoveEpisodeMovie);
$(document).on("click.EpisodesMovies", "a.button.download[data-status=selected]:not(.disabled)", { method:'remove' }, addRemoveEpisodeMovie);

$(document).on("click.Movies", "ul.main-list > li.container > .image", markWatchedUnwatched);
$(document).on("click.Shows", "ul.sublist li.container > .image", markWatchedUnwatched);


