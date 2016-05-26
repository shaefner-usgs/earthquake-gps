'use strict';

var Plots = require ('Plots'),
    StationMap = require('map/StationMap'),
    TabList = require('tablist/TabList');

Plots({
  el: document.querySelector('.nav-toggle')
});

StationMap({
  el: document.querySelector('.map')
});

TabList.tabbifyAll();
