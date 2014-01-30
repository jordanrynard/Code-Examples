// Used to extract Youtube VideoId from Trailer URL so can use Youtube 'Embed' code
function getYoutubeVideoId(url){
    if(url.indexOf('?') === -1)
        return null;
    var query = decodeURI(url).split('?')[1];
    var params = query.split('&');      
    for(var i=0,l = params.length;i<l;i++)
        if(params[i].indexOf('v=') === 0)
            return params[i].replace('v=','');
    return null;
}

// View Trailer
viewTrailer = function(event){
	var videoId = getYoutubeVideoId($(this).attr("href"));
	// IPHONE
	if (isAppleDevice()){
		$(this).prepend('<iframe class="video-player player" type="text/html" width="100%" height="100%" src="http://www.youtube.com/embed/'+videoId+'?autoplay=1&modestbranding=1&showinfo=0&rel=0" frameborder="0" allowfullscreen></iframe>');
		return false;
	}

	// BROWSER
	$("body").append("<div class='overlay'></div>");
	$(".overlay").prepend('<iframe class="video-player" type="text/html" width="520" height="320px" src="http://www.youtube.com/embed/'+videoId+'?autoplay=1&modestbranding=1&showinfo=0&rel=0" frameborder="0" allowfullscreen></iframe>');
	videoPlayerWidth = $(".video-player").width();
	$(".video-player").css({marginLeft:-(videoPlayerWidth/2)});
	return false;
}


// Rotten Tomatoes (AJAX Inserted because of API restriction - and other reasons)
$(window).load(function(){
	if (globals.view == 'movies'){
		el = {};
		timeout = 0;
		$(".rt_critics-score").each(function(index, value){
			timeout += 200; // 100 wasn't slow enough
			setTimeout(function(){
				el[index] = $(".rt_critics-score:eq("+index+")"); // .get() is the opposite of .index() -- index returns the index value of an object, get returns the object of an index value
				$.getJSON(el[index].attr("data-url")+"&callback=?", function(result){
					if (result.ratings.critics_score > 0){
						el[index].append(result.ratings.critics_score+"%");
					}
				});
			}, timeout);
		});
	}
});

// Movie Button Actions
$(document).on("click.Movies", "a.view-trailer", viewTrailer);