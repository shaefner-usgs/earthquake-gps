'use strict';


var Navigation = require('kinematic/Navigation'),
    TimeSeries = require('kinematic/TimeSeries');

var graphs,
    ts;

graphs = [];

ts = TimeSeries({
  direction: 'north',
  color: 'rgb(204,40,40)',
  el: document.querySelector('.north'),
  graphs: graphs
});

TimeSeries({
  direction: 'east',
  color: 'rgb(10,204,10)',
  el: document.querySelector('.east'),
  graphs: graphs
});

TimeSeries({
  direction: 'vertical',
  color: 'rgb(40,40,204)',
  el: document.querySelector('.vertical'),
  graphs: graphs
});

Navigation(ts);
