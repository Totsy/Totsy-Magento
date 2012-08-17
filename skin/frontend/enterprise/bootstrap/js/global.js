// Sandbox for all jQuery that is global for the site
// - wrapped in self-executing anonymous function for no conflict use of $ alias
// - use appropriate namespacing, don't pollute global namespace (not implemented, maybe a phase 2 cleanup)
(function($) {

    // SillyExample namespace
    if (typeof(SillyExample) === "undefined") {
        var SillyExample = {
            init : function() {
                // define events handlers and such
                $('#logo').on('click', SillyExample.clickHandler);
            },
            
            // results / actions
            clickHandler : function() {
                alert('OOPs there goes the namespace');
            }
        };
    }
    
    // MyNameSpace
    if (typeof(MyNameSpace) === 'undefined') {
        var MyNameSpace = {
            init: function() {
                console.log('Running MyNameSpace.init');
            }
        };
    }
    

    // DOM is ready, now do stuf
    $(function() {
        
        //SillyExample.init();
        //MyNameSpace.init();
 
 
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