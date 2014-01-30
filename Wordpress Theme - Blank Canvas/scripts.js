(function($){

	$(document).ready(function(){

		// alert(ARC.template_url);
		
		// rel=external
		$('a[rel*=external]').click(function(){
			window.open(this.href);
			return false;
		});

	});

})(jQuery);