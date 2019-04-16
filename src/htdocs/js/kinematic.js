'use strict';


var Navigation = require('kinematic/Navigation'),
    TimeSeries = require('kinematic/TimeSeries');

var graphs,
    ts;

graphs = [];

ts = TimeSeries({
  color: 'rgb(204,40,40)',
  component: 'north',
  el: document.querySelector('.north'),
  graphs: graphs
});

TimeSeries({
  color: 'rgb(10,204,10)',
  component: 'east',
  el: document.querySelector('.east'),
  graphs: graphs
});

TimeSeries({
  color: 'rgb(40,40,204)',
  component: 'up',
  el: document.querySelector('.up'),
  graphs: graphs
});

Navigation(ts);
