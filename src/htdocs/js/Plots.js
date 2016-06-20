/* global MOUNT_PATH */
'use strict';


/**
 * Class for dynamically swapping plot images on GPS station page
 *
 * @param options {Object}
 */
var Plots = function (options) {
  var _this,
      _initialize,

      _el,

      _addListeners,
      _getLinks,
      _togglePlot,
      _toggleSel;


  _this = {};

  _initialize = function () {
    options = options || {};
    _el = options.el || document.createElement('div');

    // add EventListeners
    _addListeners();
  };


  /**
   * EventListeners to toggle plots by date range
   */
  _addListeners = function () {
    var links;

    links = _getLinks();
    links.forEach(function(link) {
      link.addEventListener('click', _togglePlot);
    });
  };

  /**
   * Get toggle links
   *
   * @return {Array} (not a NodeList)
   */
  _getLinks = function () {
    return [].slice.call(_el.querySelectorAll('a'));
  };

  /**
   * Swap plot image
   *
   * @param e {Event}
   */
  _togglePlot = function (e) {
    var alt,
        img;

    e.preventDefault();

    img = _el.parentNode.querySelector('.toggle');
    alt = img.getAttribute('alt').replace(/\(.*\)/, '(' + e.target.textContent + ')');
    img.setAttribute('alt', alt);
    if (e.target.href.indexOf('#no-data') !== -1) {
      img.setAttribute('src', MOUNT_PATH + '/img/nodata.png');
    } else {
      img.setAttribute('src', e.target.href);
    }

    _toggleSel(e.target);
  };

  /**
   * Toggle sel class on link tags
   *
   * @param target {Elem}
   */
  _toggleSel = function (target) {
    var links;

    links = _getLinks();
    links.forEach(function(link) {
      if (link === target) {
        link.classList.add('selected');
      } else {
        link.classList.remove('selected');
      }
    });
  };

  _initialize();
  return _this;
};


module.exports = Plots;
