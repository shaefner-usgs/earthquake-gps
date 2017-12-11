'use strict';

var Util = require('util/Util'),
    View = require('mvc/View');


var _DEFAULTS = {};

// 120 days in milliseconds
var _120_DAYS_MS = 120 * 24 * 60 * 60 * 1000;

// two years in milliseconds
var _TWO_YEARS_MS = 730 * 24 * 60 * 60 * 1000;


/**
 * Navigation view sets time range of QC data to be displayed.
 */
var NavigationView = function (options) {
  var _this,
      _initialize,
      // variables
      _past60Days,
      _pastYear,
      // methods
      _clearDisabled,
      _onPast60DaysClick,
      _onPastYearClick;


  _this = View(options);

  _initialize = function (options) {
    var el;

    options = Util.extend({}, _DEFAULTS, options);

    el = _this.el;
    el.innerHTML =
        '<button class="pastYear">Past 2 Years</button>' +
        '<button class="past60Days">Past 120 Days</button>';

    _past60Days = el.querySelector('.past60Days');
    _pastYear = el.querySelector('.pastYear');

    _past60Days.addEventListener('click', _onPast60DaysClick);
    _pastYear.addEventListener('click', _onPastYearClick);

    _onPastYearClick(); // default
  };

  /**
   * Remove disabled attribute from child elements in this view.
   */
  _clearDisabled = function () {
    var el;

    el = _this.el.querySelector('[disabled]');
    if (el) {
      el.removeAttribute('disabled');
    }
  };


  /**
   * Set start time to one year ago.
   */
  _onPast60DaysClick = function () {
    _clearDisabled();
    _this.model.set({
      end: null,
      start: new Date(new Date().getTime() - _120_DAYS_MS)
    });
    _past60Days.setAttribute('disabled', 'disabled');
  };

  /**
   * Set start time to one year ago.
   */
  _onPastYearClick = function () {
    _clearDisabled();
    _this.model.set({
      end: null,
      start: new Date(new Date().getTime() - _TWO_YEARS_MS)
    });
    _pastYear.setAttribute('disabled', 'disabled');
  };


  /**
   * Free references.
   */
  _this.destroy = Util.compose(function () {
    _past60Days.removeEventListener('click', _onPast60DaysClick);
    _pastYear.removeEventListener('click', _onPastYearClick);

    _initialize = null;
    _onPast60DaysClick = null;
    _onPastYearClick = null;
    _pastYear = null;
    _this = null;
  }, _this.destroy);


  _initialize(options);
  options = null;
  return _this;
};

module.exports = NavigationView;
