(function($) {

  var TDVerify = function(options) {
    var obj = this;

    this.init = function() {
      // Replaces the default options with the ones supplied by the user.
      obj.config = $.extend(true, {
        'license' : null,

        'email'    : 'email',
        'phone'    : 'phone',
        'fname'    : 'first_name',
        'lname'    : 'last_name',
        'address1' : 'address1',
        'address2' : 'address2',
        'city'     : 'city',
        'state'    : 'state',
        'zip'      : 'zip',
        'ip'       : 'ip',

        'settings' : {
          'async'         : true,
          'timeout'       : 5,
          'valid_email'   : true,  // True or 'mailbox' enables validation of email mailbox.
                                   // 'syntaxdomain' checks syntax and domain only.
          'valid_phone'   : false, // Validate phone number.
          'valid_postal'  : false, // Validate postal address.
          'reverse_email' : false, // Find postal address based on input email.
          'demographics'  : false  // Find demographics based on input email or
                                   // postal address. Must specify demographics package #.
        }
      }, options);

      obj.query = { 'settings' : {} };

      // If it fails the configuration check then we can't pass along the object.
      return obj.isValidConfig() ? this : false;
    };

    this.set = function(config) {
      obj.query = $.extend(true, obj.query, config);

      return this;
    };

    // This is the main process that fires off the AJAX request.
    this.process = function(config) {
      var config = config || {};
      var url = obj.getURL();

      var ajaxConfig = {
        'url'         : url,
        'cache'       : 'false',
        'callback'    : 'jsonp',
        'callbackParameter' : 'callback',
        'success'     : config.onSuccess,
        'error'       : config.onError
      };

      // Run that AJAX
      $.jsonp(ajaxConfig);

      return this;
    };

    // This grabs the parameters and builds the URL.
    this.getURL = function() {
      var params = obj.getParams();
      var urlParams = [];

      // Join all the key/pairs together and turn arrays into '+' delimited strings
      $.each(params, function(k, v) {
        if (v instanceof Array) {
          return urlParams.push(k + '=' + v.join('+'));
        }

        return urlParams.push(k + '=' + encodeURIComponent(v));
      });

      // Join all the parameters together for the URL
      this.url = 'https://api10.towerdata.com/person?' + urlParams.join('&');

      return this.url;
    };

    this.translate = function(key, category) {
      if (typeof key !== 'string') {
        return key;
      }

      var key = key.toLowerCase();

      var dict = {
        ''    : false,
        'off' : false,
        'on'  : true,

        'mailbox'      : 'email',
        'syntaxdomain' : 'email-domain-only',

        'valid_email'  : 'email',
        'valid_postal' : 'postal',
        'valid_phone'  : 'phone'
      };

      if (dict[category]) {
        return dict[category][key];
      }
      else if (dict[key]) {
        return dict[key];
      }
      else {
        return (dict[key] === false ? dict[key] : key);
      }
    };

    // This grabs a configuration value, turns it into a jQuery object if need be and cleans it up a bit..
    this.getValue = function(key, setting) {
      if (obj.query.settings[key] || obj.query[key]) {
        var value = (setting === true ? obj.query.settings[key] : obj.config[key]);
        return $.trim(value);
      }

      var id = (setting === true ? obj.config.settings[key] : obj.config[key]);

      if (id instanceof jQuery) {
        var el = id;
      }
      else if (id instanceof Element) {
        var el = $(id);
      }
      else if (typeof id === 'string') {
        var el = $('#' + id);
      }

      // Special case for checkboxes
      if (el && el.attr('type') === 'checkbox') {
        return el[0].checked;
      }

      return (el ? $.trim(el.val()) : false);
    };

    // This will build all of the parameters for the URL primarily by getURL()
    this.getParams = function() {
      var params = { 'validate' : [] };

      /* This will loop through all of the form elements provided
         and parse them out for their values, be them user-set, ids, or
         jQuery selectors.
      */

      for (var key in obj.config.settings) {
        var value = obj.translate(obj.query.settings[key] === undefined
                                    ? obj.getValue(key, true)
                                    : obj.query.settings[key]);

        // This inserts the data into the query object.
        obj.query.settings[key] = value;

        if (value === true) {
          if (key !== 'reverse_email' && key !== 'demographics') {
            var def = obj.translate(key);
            if (def) {
              params.validate.push(def);
            }
          }
        }
        else {
          if (key === 'valid_email') {
            params.validate.push(value);
          }
        }
      }

      /********************************************************/

      // This fills up the forms with the proper data
      for (var key in obj.config) {
        if (key === 'settings') {
          continue;
        }

        if (obj.query[key]) {
          params[key] = obj.query[key];
          continue;
        }

        var value = obj.getValue(key);

        obj.query[key] = value;

        if (key === 'license') {
          value = obj.config[key];
        }

        if (value && value.length > 0) {
          params[key] = value;
        }
      } 

      /***********************************************************/

      // Validation sorts of logic
      if (params.email !== false) {
        if ($.inArray('email', params.validate) || $.inArray('syntax-domain-only', params.validate)) {
	  // Enable email corrections if processing an email
          params.correct = 'email';
        }
        if (obj.query.settings.reverse_email === true) {
          params.find = 'postal';
        }
      }

      if (obj.query.settings.demographics !== false) {
        params.demos = obj.query.settings.demographics;
      }
      else {
        if (obj.query.settings.valid_postal === false) {
          $.each(['address1', 'address2', 'city', 'state', 'zip'], function(i, el) {
            delete params[el];
          });
        }
      }

      if (params.validate.length < 1) {
        params.validate = 'none';
      }

      if (obj.query.settings.timeout > 0) {
        params.timeout = obj.query.settings.timeout;
      }

      /************************************************************/

      return params;
    };

    // Checks the configuration data and makes sure nothing is out of place.
    this.isValidConfig = function() {
      // There was a function here. It's gone now.

      return true;
    };

  };

  /* This is what is called by the user and is where the options are passed.
     It will also initialize everything and return a new copy of the TDVerify object.
  */
  $.tdverify = function(options) {
    var plugin = new TDVerify(options);

    // If init() fails then we can't continue.
    return plugin.init() ? plugin : undefined;
  };

})(jQuery);


// jquery.jsonp 2.1.4 (c)2010 Julian Aubourg | MIT License
// http://code.google.com/p/jquery-jsonp/
(function(e,b){function d(){}function t(C){c=[C]}function m(C){f.insertBefore(C,f.firstChild)}function l(E,C,D){return E&&E.apply(C.context||C,D)}function k(C){return/\?/.test(C)?"&":"?"}var n="async",s="charset",q="",A="error",r="_jqjsp",w="on",o=w+"click",p=w+A,a=w+"load",i=w+"readystatechange",z="removeChild",g="<script/>",v="success",y="timeout",x=e.browser,f=e("head")[0]||document.documentElement,u={},j=0,c,h={callback:r,url:location.href};function B(C){C=e.extend({},h,C);var Q=C.complete,E=C.dataFilter,M=C.callbackParameter,R=C.callback,G=C.cache,J=C.pageCache,I=C.charset,D=C.url,L=C.data,P=C.timeout,O,K=0,H=d;C.abort=function(){!K++&&H()};if(l(C.beforeSend,C,[C])===false||K){return C}D=D||q;L=L?((typeof L)=="string"?L:e.param(L,C.traditional)):q;D+=L?(k(D)+L):q;M&&(D+=k(D)+encodeURIComponent(M)+"=?");!G&&!J&&(D+=k(D)+"_"+(new Date()).getTime()+"=");D=D.replace(/=\?(&|$)/,"="+R+"$1");function N(S){!K++&&b(function(){H();J&&(u[D]={s:[S]});E&&(S=E.apply(C,[S]));l(C.success,C,[S,v]);l(Q,C,[C,v])},0)}function F(S){!K++&&b(function(){H();J&&S!=y&&(u[D]=S);l(C.error,C,[C,S]);l(Q,C,[C,S])},0)}J&&(O=u[D])?(O.s?N(O.s[0]):F(O)):b(function(T,S,U){if(!K){U=P>0&&b(function(){F(y)},P);H=function(){U&&clearTimeout(U);T[i]=T[o]=T[a]=T[p]=null;f[z](T);S&&f[z](S)};window[R]=t;T=e(g)[0];T.id=r+j++;if(I){T[s]=I}function V(W){(T[o]||d)();W=c;c=undefined;W?N(W[0]):F(A)}if(x.msie){T.event=o;T.htmlFor=T.id;T[i]=function(){/loaded|complete/.test(T.readyState)&&V()}}else{T[p]=T[a]=V;x.opera?((S=e(g)[0]).text="jQuery('#"+T.id+"')[0]."+p+"()"):T[n]=n}T.src=D;m(T);S&&m(S)}},0);return C}B.setup=function(C){e.extend(h,C)};e.jsonp=B})(jQuery,setTimeout);
