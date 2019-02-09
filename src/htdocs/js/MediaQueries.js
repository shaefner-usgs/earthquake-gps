'use strict';
// Adapted from: http://zerosixthree.se/detecting-media-queries-with-javascript/

/**
 * Sets up a javascript-based MQ on an element that has a CSS MQ already defined
 * (Be sure to set the content property on the Element's :after selector within
 *   the CSS mediaquery for JS to track when it's triggered)
 *
 * @param options {Object}
 *   {
 *     el: Element
 *   }
 */
var MediaQueries = function (options) {
  var _this,
      _initialize,

      _afterElement,
      _currentBreakpoint,
      _el,
      _lastBreakpoint,

      _resizeListener;


  _this = {};

  _initialize = function (options) {
    _el = options.el || document.createElement('div');

    if (window.getComputedStyle) {
      _afterElement = window.getComputedStyle(_el, ':after');
      _currentBreakpoint = '';
      _lastBreakpoint = '';

      _resizeListener();
    }
  };

  _resizeListener = function () {
    var event;

    ['resize', 'orientationchange', 'load'].forEach(function(evt) {
      window.addEventListener(evt, function() {
        _currentBreakpoint = _afterElement.getPropertyValue('content');

        if (_currentBreakpoint !== _lastBreakpoint) {
          if (window.CustomEvent) { // Compliant browsers
            event = new CustomEvent('breakpoint-change', {detail: {layout: _currentBreakpoint}});
          } else {
            event = document.createEvent('CustomEvent');
            event.initCustomEvent('breakpoint-change', true, true, {layout: _currentBreakpoint});
          }
          window.dispatchEvent(event);

          _lastBreakpoint = _currentBreakpoint;
        }
      });
    });
  };


  _initialize(options);
  options = null;
  return _this;
};

module.exports = MediaQueries;
