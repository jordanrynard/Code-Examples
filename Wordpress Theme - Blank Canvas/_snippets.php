<script>
(function($){
	function doSlideshow(){
		var visibleLi = jQuery("#slideshow").find("li.active");
		if (visibleLi.next().is("li")){
			nextLi = visibleLi.next();
		} else {
			nextLi = jQuery("#slideshow").find("li:first");
		}
		nextLi.stop(true, true).addClass("active").fadeIn(3000);
		visibleLi.stop(true, true).removeClass("active").fadeOut(3000);
	}
	$(document).ready(function(){
		var slideshow = setInterval(function(){
			doSlideshow();
		}, 5000);
		jQuery("#slideshow-nav a").mouseenter(function(){
			clearInterval(slideshow);
			slideshow = false;
		}).mouseleave(function(){
			slideshow = setInterval(function(){
				doSlideshow();
			}, 5000);
		});
		
		jQuery("#slideshow-nav a").click(function(){
			var visibleLi = jQuery("#slideshow").find("li.active");
			if (jQuery(this).hasClass('left')){
				if (visibleLi.prev().is("li")){
					nextLi = visibleLi.prev();
				} else {
					nextLi = jQuery("#slideshow").find("li:last");
				}
			} else {
				if (visibleLi.next().is("li")){
					nextLi = visibleLi.next();
				} else {
					nextLi = jQuery("#slideshow").find("li:first");
				}
			}
			nextLi.stop(true, true).addClass("active").fadeIn(1000);
			visibleLi.stop(true, true).removeClass("active").fadeOut(1000);
			return false;
		});
	});
})(jQuery);
</script>



<script>
jQuery(document).ready(function(){

	// SEARCH FIELD default text
	jQuery("form#contact-form input[type=text], form#contact-form textarea").focus(function(){		
		var defaultAttr = jQuery(this).attr("data-text");		
		if (typeof defaultAttr !== 'undefined' && defaultAttr !== false){			
			if (jQuery(this).val() == defaultAttr){				
				jQuery(this).val("");			
			}		
		}	
	});	
	jQuery("form#contact-form input[type=text], form#contact-form textarea").blur(function(){		
		var defaultAttr = jQuery(this).attr("data-text");		
		if (typeof defaultAttr !== 'undefined' && defaultAttr !== false){			
			if (jQuery(this).val() == ""){				
				jQuery(this).val(defaultAttr);			
			}		
		}	
	});
	
	// CONTACT FORM SUBMIT	
	jQuery("form#contact-form .submit").click(function(){
		jQuery(this).parents("form").submit();
	});
	jQuery("form#contact-form").submit(function(){		
		if(jQuery(this).find(".arcadias-error").length >= 0){			
			var errorString = "";			
			jQuery(this).find(".required").each(function(){				
				if (jQuery(this).val() == jQuery(this).attr("data-text")){					
					errorString = "Please don't leave any required fields blank";					
					return false;				
				}			
			});			
			jQuery(this).find(":not(.required)").each(function(){				
				if (jQuery(this).val() == jQuery(this).attr("data-text")){					
					jQuery(this).val("");
				}			
			});			
			if (errorString != ""){				
				jQuery(this).find(".arcadias-error").show().html(errorString);				
				return false;					
			}		
		} else {
			alert("You don't have an error field set");
		}
		return true;	
	});
	
});
</script>




<link href="<?=get_bloginfo('template_url')?>/scripts/lightbox/css/lightbox.css" rel="stylesheet" />
<script src="<?=get_bloginfo('template_url')?>/scripts/lightbox/jquery.lightbox.js"></script>
<script>
(function($){
	$(document).ready(function(){
		$(".lightbox").lightbox({
			fileLoadingImage : '<?=get_bloginfo('template_url')?>/scripts/lightbox/images/loading.gif',
			fileBottomNavCloseImage : '<?=get_bloginfo('template_url')?>/scripts/lightbox/images/closelabel.gif',
			fitToScreen: true
		});
	});
})(jQuery);
</script>
