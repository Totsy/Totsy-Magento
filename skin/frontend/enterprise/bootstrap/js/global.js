// All jQuery that is global for the site
// wrapped in self-executing anonymous function for no conflict use of $ alias
// appropriately name-spaced
// not implemented, maybe a phase 2 cleanup
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
    
    });
    
})(jQuery);