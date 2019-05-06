'use strict';


var Util = require('util/Util');

var _DEFAULTS = {
  message: 'No message set (use message property when creating).',
  type: 'info'
};


/**
 * Factory for creating a non-modal status bar
 *
 * @param options {Object}
 *     {
 *       message: {Element|String} <p> element or String
 *       type: {String <info|warning|error>} optional
 *     }
 *
 * @return {Object}
 *     {
 *       hide: {Function}
 *       remove: {Function}
 *       show: {Function}
 *     }
 */
var StatusBar = function (options) {
  var _this,
      _initialize,

      _el,
      _message,
      _type,

      _add,
      _addListeners,
      _createEl;


      _this = {};

  _initialize = function (options) {
    options = Util.extend({}, _DEFAULTS, options);

    _message = options.message;
    _type = options.type;

    // if message param is not an element, assume it's a string
    if (_message.nodeType !== 1) {
      _message.toString();
    }

    _el = _createEl();
    _add();
  };


  /**
   * Add status bar to page
   */
  _add = function () {
    _this.remove(); // first, remove any existing status bars before adding another
    _addListeners();

    document.body.appendChild(_el);
  };

  /**
   * Add click handler for closing status bar
   */
  _addListeners = function () {
    var closeButton;

    closeButton = _el.querySelector('.material-icons');
    closeButton.addEventListener('click', function () {
      _this.hide();
    });
  };

  /**
   * Create status bar Element (hidden via css; call _this.show() to display)
   *
   * @return el {Element}
   */
  _createEl = function () {
    var closeButton,
        el;

    closeButton = document.createElement('i');
    closeButton.classList.add('material-icons');
    closeButton.innerHTML = 'cancel';

    el = document.createElement('div');
    el.classList.add('sb-' + _type, 'status-bar', 'hide'); // hidden by default
    if (_message.nodeType === 1) { // element
      el.appendChild(_message);
    } else { // string
      el.innerHTML = '<p>' + _message + '</p>';
    }
    el.appendChild(closeButton);

    return el;
  };

  /**
   * Hide status bar via CSS
   */
  _this.hide = function () {
    _el.classList.add('hide');
  };

  /**
   * Remove status bar from page
   */
  _this.remove = function () {
    var statusBar;

    statusBar = document.querySelector('.status-bar');
    if (statusBar) {
      statusBar.parentNode.removeChild(statusBar);
    }
  };

  /**
   * Show status bar via CSS
   */
  _this.show = function () {
    setTimeout(function() { // 'trick' browser into animating when adding to DOM
      _el.classList.remove('hide');
    }, 0);
  };


  _initialize(options);
  options = null;
  return _this;
};


module.exports = StatusBar;
