'use strict';

var TabList = require('tablist/TabList');

var initNavToggle,
    togglePlot;

togglePlot = function (e) {
  var img;

  e.preventDefault();
  img = document.querySelector('.toggle');
  img.setAttribute('src', e.srcElement.href);
};

initNavToggle = function () {
  var i,
      navToggleAs;

  navToggleAs = document.querySelectorAll('.nav-toggle a');
  //[].slice.call(navToggleAs).forEach(function(a) {
  for (i = 0; i < navToggleAs.length; i ++) {
    console.log(navToggleAs[i]);
    navToggleAs[i].addEventListener('click', togglePlot);
  }
  //});
};

initNavToggle();

TabList.tabbifyAll();
