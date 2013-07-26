(function ($) {
  $(document).ready(function() {
	$(".gallery-slide").mouseover(function(){
		$(".panel-overlay",this).fadeIn("slow");
	}).mouseout(function(){
		$(".panel-overlay",this).fadeOut("slow");	
	});
  });
})(jQuery);