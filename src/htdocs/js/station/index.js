'use strict';

var TabList = require('tablist/TabList');

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
        link.classList.add('sel');
      } else {
        link.classList.remove('sel');
      }
    });
  };

  _initialize();

  return _this;
};

Plots(document.querySelector('.nav-toggle'));

//TabList.tabbifyAll();

// Temporary hack: implement these methods here b/c class method tabbifyOne
// is wiping out my event listeners

var tabbifyOne = function (el) {
  var tabs = [],
      panels,
      panel,
      i, len,
      tablist;

  panels = el.querySelectorAll('.panel');
  for (i = 0, len = panels.length; i < len; i++) {
    panel = panels[i];
    tabs.push({
      'title': panel.getAttribute('data-title') ||
          panel.querySelector('header').innerHTML,
      'content': panel,
      'selected': panel.getAttribute('data-selected') === 'true'
    });
  }

  tablist = TabList({
    'tabs': tabs
  });

  el.parentNode.replaceChild(tablist.el, el);
};

var tabbifyAll = function () {
  var lists,
      i;
  lists = document.querySelectorAll('.tablist');
  for (i = lists.length - 1; i >= 0; i--) {
    tabbifyOne(lists[i]);
  }
};

tabbifyAll();
