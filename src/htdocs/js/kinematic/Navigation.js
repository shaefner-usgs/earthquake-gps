'use strict';



/**
 * Navigation class: zoom and pan controls
 * (only need to manipulate one graph; they're bound together when created)
 *
 * @param timeseries {Object}
 *   Reference to one of the timeseries graphs
 */
var Navigation = function (timeseries) {
  var _this,
      _initialize,

      _panButtons,
      _resetButton,

      _disableReset,
      _initPan,
      _initReset;


  _this = {};

  _initialize = function () {
    _panButtons = document.querySelectorAll('.pan');
    _resetButton = document.querySelector('.reset');

    _initPan();
    _initReset();
    _disableReset();
  };

  /**
   * Disable reset button
   */
  _disableReset = function () {
    _resetButton.setAttribute('disabled', 'disabled');
  };

  /**
   * Add click handlers to pan graphs left / right
   */
  _initPan = function () {
    Array.prototype.slice.call(_panButtons).forEach(function(button) {
      button.addEventListener('click', function() {
        var direction = this.classList.contains('left') ? -1 : 1;
        timeseries.pan(direction);
        _resetButton.removeAttribute('disabled');
      });
    });
  };

  /**
   * Add click handler to reset graphs
   */
  _initReset = function () {
    _resetButton.addEventListener('click', function() {
      timeseries.reset();
      _disableReset();
    });
  };

  _initialize();
  return _this;
};

module.exports = Navigation;
