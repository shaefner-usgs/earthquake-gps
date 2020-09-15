'use strict';


var Plots = require ('station/Plots'),
    StationMap = require('map/StationMap'),
    TabList = require('tablist/TabList');

var types = [
  'filtered',
  'itrf2008',
  'itrf2014',
  'na2014',
  'nafixed'
];

types.forEach(function(type) {
  Plots({
    el: document.querySelector('.' + type)
  });
});

StationMap({
  el: document.querySelector('.map')
});

TabList.tabbifyAll();
