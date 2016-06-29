'use strict';


var Plots = require ('station/Plots'),
    StationMap = require('map/StationMap'),
    TabList = require('tablist/TabList');

Plots({
  el: document.querySelector('.itrf2008')
});

Plots({
  el: document.querySelector('.nafixed')
});

Plots({
  el: document.querySelector('.cleaned')
});

StationMap({
  el: document.querySelector('.map')
});

TabList.tabbifyAll();
