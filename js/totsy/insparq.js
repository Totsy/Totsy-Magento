(function(d, t) {
    var g = d.createElement(t),
    s = d.getElementsByTagName(t)[0];
    g.type = 'text/javascript';
    g.async = true;
    g.src = '//pinboard.insparq.com/assets/flicker/insparqflicker.js';
    g.apikey = '48fa5885203f6957a1b65fb8a4c3304e'
    g.feedurl = '/pinboard'
    g.feedtext = 'What\'s \<br \\\>Hot'
    g.csspath = '/skin/frontend/enterprise/bootstrap/lib/insparq/toggle.css'
    g.mode = 'toggle'
    g.container = '#feedflicker'
    s.parentNode.insertBefore(g, s);
}(document, 'script'));
