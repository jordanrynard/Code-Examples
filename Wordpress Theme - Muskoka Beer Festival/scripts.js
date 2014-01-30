(function($){
	zindexCount = 1;

	$(document).ready(function(){

		// rel=external
		$('a[rel*=external]').click(function(){
			window.open(this.href);
			return false;
		});

		$("#arrow_right_button").click(function(){
			var activeImage = $(".slider ul.slider-ul li.active");
			var nextImage = $(".slider ul.slider-ul li.active").next("li");
			if (nextImage.length <= 0){
				nextImage = $(".slider ul.slider-ul li:first");
			}
			if($(activeImage).is(':animated') || nextImage.is(':animated')){
				return false;
			}
			active_image_id = activeImage.attr("data-id");
			next_image_id = nextImage.attr("data-id");
			//
			zindexCount++;			
			activeImage.animate({marginLeft:'-2000px'},750).css({zIndex:zindexCount}).removeClass("active");
			nextImage.css({marginLeft:'2000px'}).animate({marginLeft:'0'}, 750).css({zIndex:999}).addClass("active");
			$(".slider-nav li[data-id="+active_image_id+"]").removeClass("active");
			$(".slider-nav li[data-id="+next_image_id+"]").addClass("active");
			return false;
		});

		if ($(".slider-nav li").length > 1){
			setInterval(function(){
				$("#arrow_right_button").trigger("click");
			},4500);
		} else {
			$(".slider-nav").hide();
		}

		$(".slider-nav a").click(function(){
			return false;
		});

	});

})(jQuery);