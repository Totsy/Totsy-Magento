// Shepherd @HaraPartners

(function($) {
	$(document).ready(function() {
		// for (b in $.browser) alert(b);
		
		// when the browser is IE7, do something
		if ($.browser.msie && $.browser.version == "7.0") {
			$('html').attr('class', 'ie_seven');
		}
	});
})(jQuery);