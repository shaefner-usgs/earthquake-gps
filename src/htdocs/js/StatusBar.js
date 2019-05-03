'use strict';


var Util = require('util/Util');

var _DEFAULTS = {
  message: 'No message set (use message property when creating).',
  type: 'info'
};


/**
 * Factory for creating a non-modal status bar
 *
 *   @param options {Object}
 *       {
 *         message: {Element|String} <p> element or String
 *         type: {String <info|warning|error>} optional
 *       }
 */
var StatusBar = function (options) {
  var _this,
      _initialize,

      _message,
      _statusBar,
      _type,

      _addListeners,
      _getHtml;


      _this = {};

  _initialize = function (options) {
    options = Util.extend({}, _DEFAULTS, options);

    _message = options.message;
    _type = options.type;

    // if message param is not an element, assume it's a string
    if (_message.nodeType !== 1) {
      _message.toString();
    }

    _statusBar = _getHtml();
    _addListeners();
  };


  /**
   * Add click handler for closing status bar
   */
  _addListeners = function () {
    var closeButton;

    closeButton = _statusBar.querySelector('.material-icons');
    closeButton.addEventListener('click', function () {
      _this.remove();
    });
  };

  /**
   * Get HTML content for status bar
   */
  _getHtml = function () {
    var closeButton,
        div;

    closeButton = document.createElement('i');
    closeButton.classList.add('material-icons');
    closeButton.innerHTML = 'cancel';

    div = document.createElement('div');
    div.classList.add('sb-' + _type, 'status-bar');
    if (_message.nodeType === 1) { // element
      div.appendChild(_message);
    } else { // string
      div.innerHTML = '<p>' + _message + '</p>';
    }
    div.appendChild(closeButton);

    return div;
  };

  /**
   * Add status bar to page
   */
  _this.add = function () {
    _this.remove();

    document.body.appendChild(_statusBar);
  };

  /**
   * Remove status bar from page
   */
  _this.remove = function () {
    var statusBar = document.querySelector('.status-bar');
    if (statusBar) {
      statusBar.parentNode.removeChild(statusBar);
    }
  };


  _initialize(options);
  options = null;
  return _this;
};


module.exports = StatusBar;
