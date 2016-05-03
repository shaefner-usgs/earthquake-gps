'use strict';


var Plots = function (el) {
  var _this,
      _initialize,

      _addListeners,
      _getLinks,
      _togglePlot,
      _toggleSel;


  _this = {};

  _initialize = function () {
    if (el) {
      _addListeners();
    }
  };


  /**
   * Add EventListeners to toggle links
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
    return [].slice.call(el.querySelectorAll('a'));
  };

  /**
   * Swap plot image
   *
   * @param e {Object}
   */
  _togglePlot = function (e) {
    var img;

    e.preventDefault();
    img = el.parentNode.querySelector('.toggle');
    img.setAttribute('src', e.target.href);

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
