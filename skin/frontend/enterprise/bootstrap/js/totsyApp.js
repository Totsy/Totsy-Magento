// Browser debug stuff //
(function (a) {
    if (typeof b === "undefined") {
        var b = {
            init: function () {
                a("#logo").on("click", b.clickHandler)
            },
            clickHandler: function () {
                alert("OOPs there goes the namespace")
            }
        }
    }
    if (typeof c === "undefined") {
        var c = {
            init: function () {
                console.log("Running MyNameSpace.init")
            }
        }
    }
// Navigation //
    a(function () {
        a("nav a").each(function () {
            var b = a(this);
            if (b.length > 0) {
                if (b.attr("href") == "#") {
                    a(this).click(function (a) {
                        a.preventDefault()
                    })
                }
            }
        });
// To Top Button (footer) //
        a(window).scroll(function () {
            if (a(this).scrollTop() != 0) {
                a("#toTop").fadeIn()
            } else {
                a("#toTop").fadeOut()
            }
        });
        a("#toTop a").click(function (b) {
            b.preventDefault();
            a("body,html").animate({
                scrollTop: 0
            }, 800)
        })
    })
})(jQuery)

// Placeholder for inline form labels //
jQuery('input[placeholder], textarea[placeholder]').placeholder();

// @TODO: Combine into one single popup class instead of ID. keep it DRY
/* 
jQuery(document).ready(function () {
    jQuery("#facebook_pop").fancybox({
        'speedIn': 600,
        'speedOut': 200,
    }).trigger('click');
});

jQuery(document).ready(function () {
    jQuery("#invite_pop").fancybox({
        'speedIn': 600,
        'speedOut': 200,
    }).trigger('click');
});
*/

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
 
        /**
         * Following are one-offs… should still be namespaced
         */
                      
        /*
        * Prevent default if menu links are "#"
        */
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

        /*
         * Back to Top
         */
        $(window).scroll(function () {
            if ($(this).scrollTop() != 0) {
                $('#toTop').fadeIn();
            } else {
                $('#toTop').fadeOut();
            }
        });
        $('#toTop a').click(function (e) {
            e.preventDefault();
            $('body,html').animate({scrollTop: 0},800);
        });

        /*
         * FastShip image floatery
         */
         $('img[src*="icon-fastship-small.png"]').addClass('fastship-icon');
        
    });               
})(jQuery);

/* JavaScript Media Queries */
(function($) {
// media query change
$(window).resize(function(){
   console.log('resize called');
   var width = $(window).width();
   if(width >= 700 && width <= 420){
       $('#events-grid').removeClass('span4').addClass('span2'); }
   else{
       $('#events-grid').removeClass('span2').addClass('span4');
   }
})
.resize();//trigger the resize event on page load.
})(jQuery);