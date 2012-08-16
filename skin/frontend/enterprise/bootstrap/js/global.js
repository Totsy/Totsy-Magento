// All jQuery that is global for the site
// - wrapped in self-executing anonymous function for no conflict use of $ alias
// - use appropriate namespaceing (not implemented, maybe a phase 2 cleanup)
(function($) {

    // TopCartContent namespace
    if (typeof(TopCartContent) === "undefined") {
        TopCartContent = {
            // stuff here
        };
    }


    // DOM is ready
    $(function(){
        // now do stuff
 
 
        /* Prevent default if menu links are "#" */
        $('nav a').each( function() {
        	var nav = $(this); 
        	if( nav.length > 0 ) {
        		if( nav.attr('href') == '#' ) {
        			//console.log(nav);
        			$(this).click(
        				function(e) {
        					e.preventDefault();
        				}
        			);
        		}
        	}
        }); 
 
    
    });
    
})(jQuery);