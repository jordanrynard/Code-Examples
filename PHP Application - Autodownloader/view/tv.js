// Used for staying in place when page is refreshed
getHashParams = function(){
    var hashParams = {};
    var e,
        a = /\+/g,  // Regex for replacing addition symbol with a space
        r = /([^&;=]+)=?([^&;]*)/g,
        d = function (s) { return decodeURIComponent(s.replace(a, " ")); },
        q = window.location.hash.substring(1);
    while (e = r.exec(q))
       hashParams[0] = d(e[1]);
    return hashParams;
}

// Show / Hide a Season
showHideSeason = function(event){
	var el = $(this);
	var top = el.offset().top;
	// Close Self
	if (el.parent("li").children("ul").is(":visible")){
		el.parent("li").children("ul").slideUp();
		return false;		
	}
	// Close All, Open Self
	$("ul.sublist > li.season").children("ul:visible").each(function(){
		// Only subtract if we're hiding something that's BEFORE where we're going
		if ($(this).parents("li.season").index() < el.parents("li.season").index()){
			top -= $(this).height();
		}
	});
	$("ul.sublist > li.season").children("ul:visible").slideUp(500);
	// Find top now that whatever is closed
	// Load the images (so we don't load them all at once)
	el.parent("li").children("ul").find(".image").children("img[src=]").each(function(){
		var image = $(this).attr("data-src");
		$(this).attr({src:image});
	});
	// Show the episodes
	$('html, body').animate({
		scrollTop: top - 200
	}, 500, function(){
		el.parent("li").children("ul").slideDown(function(){
		});
	});
	// Hash stuff for keeping our place when refreshing the page
//	window.location.hash = $.trim(el.attr("href"),"#");
	event.preventDefault();
	return false;
}


changeAutoShowsSetting = function(event){
	var button = $(this);
	var buttons = button.siblings("a").addBack(); 
	buttons.disable();
	var data = {
		class:'Auto',
		data_id:$(buttons.get(0)).parents(".container").attr("data-data_id")
	};
	$.extend(data, event.data);
	button.showLoading();
	// Add a delay for visual purposes
	var milliseconds = (new Date).getTime();
	$.post("/ajax.php", data, function(result){
		if (typeof $.parseJSON(result).success=='undefined'){
			console.log("There was a problem updating the show");
			console.log(result);
			return false;
		}
		var remaining = 500 - ((new Date).getTime() - milliseconds);
		setTimeout(function(){
			button.doneLoading();
			buttons.enable();
			buttons.attr({'data-setting':data.data});
		}, remaining);
	});
	event.stopPropagation();
	return false;
}


showSeasons = function(event){
	var sublist = $(this).find("ul.sublist");
	var top = sublist.parent("li.container").offset().top;
	if (sublist.is(":visible")){
		$("ul.sublist").slideUp(500);	
		return;
	}
	$("ul.sublist:visible").each(function(){
		// Only subtract if we're hiding something that's BEFORE where we're going
		if ($(this).parents("li.container").index() < sublist.parents("li.container").index()){
			top -= $(this).height();
		}
	});
	$("ul.sublist").slideUp(500);		
	sublist.slideDown(500);
	$('html, body').animate({
		scrollTop: top - 200
	}, 500);
}

$(document).ready(function(){
	// Show Seasons

	// On page refresh, keep the same Season open
	// Currently disabled
	var season = getHashParams();
	if (!$.isEmptyObject(season)){
		if (season[0].indexOf("season") > -1){
			var split = season[0].split("-");
			var num = split[1];
			console.log(split);
			$("li.season[data-season='"+split[1]+"']").children("ul").show();
		}
	}

});

// Buttons
$(document).on("click.Shows", "a.button.download.all[data-setting!=all]:not(.disabled)", { data:'all', method:'setShow' }, changeAutoShowsSetting);
$(document).on("click.Shows", "a.button.download.new[data-setting!=new]:not(.disabled)", { data:'new', method:'setShow' }, changeAutoShowsSetting);
$(document).on("click.Shows", "a.button.download.all[data-setting=all]:not(.disabled)", { data:'', method:'removeShow' }, changeAutoShowsSetting);
$(document).on("click.Shows", "a.button.download.new[data-setting=new]:not(.disabled)", { data:'', method:'removeShow' }, changeAutoShowsSetting);

$(document).on("click.seasonSelect", ".season_title", showHideSeason);
$(document).on("click.seasonSelect", ".tv ul.main-list > li.container", showSeasons);