// Patch for bootstrap-collapse.js - conflict with prototype.js
//  @ http://stackoverflow.com/a/12725287
//  @ http://stackoverflow.com/questions/12715254/twitter-bootstrap-transition-conflict-prototypejs#comment17225660_12725287
//  @ http://stackoverflow.com/questions/12136505/how-to-get-twitter-bootstrap-to-work-with-prototype-js
//  @ https://github.com/twitter/bootstrap/issues/5403

jQuery.fn.collapse.Constructor.prototype.transition = function (method, startEvent, completeEvent) {
  var that = this
    , complete = function () {
        if (startEvent.type == 'show') that.reset();
        that.transitioning = 0;
        that.$element.trigger(completeEvent);
      }

  //this.$element.trigger(startEvent);
  //if (startEvent.isDefaultPrevented()) return;
  this.transitioning = 1;
  this.$element[method]('in');
  (jQuery.support.transition && this.$element.hasClass('collapse')) ?
this.$element.one(jQuery.support.transition.end, complete) :
    complete();
};

jQuery.noConflict();