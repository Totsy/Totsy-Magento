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