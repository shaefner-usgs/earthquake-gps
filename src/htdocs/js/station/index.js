'use strict';

var TabList = require('tablist/TabList');

var Plots = function (el) {
  var _this,
      _initialize,

      _togglePlot,
      _addListeners;

  _this = {};

  _initialize = function () {
    _addListeners();
  };

  _addListeners = function () {
    var as;

    as = el.querySelectorAll('a');
    [].slice.call(as).forEach(function(a) {
      a.addEventListener('click', _togglePlot);
    });
  };

  _togglePlot = function (e) {
    var img;

    e.preventDefault();
    img = el.parentNode.querySelector('.toggle');
    img.setAttribute('src', e.srcElement.href);
  };

  _initialize();
};

Plots(document.querySelector('.nav-toggle'));

TabList.tabbifyAll();
