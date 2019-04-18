'use strict';


/**
 * Programmatically make changes to a document based on CSS media query (MQ) status
 *
 * To use, pass in the HTML Element with the MQ defined during instantiation,
 * then watch for the Custom Event 'breakpoint-change' to set up JS behaviors.
 *
 * (Be sure to set a hidden (i.e. 'display: none') content property value on the
 *   Element's :after selector within the CSS media query for each MQ state for
 *   JS to track the breakpoints as they are triggered. The value will be exposed
 *   in a property called 'layout' on the Event object)
 *
 * @param options {Object}
 *   {
 *     el: Element
 *   }
 *
 * Adapted from: http://zerosixthree.se/detecting-media-queries-with-javascript/
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
