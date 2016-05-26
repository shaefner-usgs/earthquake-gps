'use strict';

var Plots = require ('Plots'),
    TabList = require('tablist/TabList');

Plots({
  el: document.querySelector('.nav-toggle')
});

TabList.tabbifyAll();
