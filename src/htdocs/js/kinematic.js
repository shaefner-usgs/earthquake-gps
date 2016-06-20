'use strict';


var TimeSeries = require('TimeSeries');

var graphs = [];

TimeSeries({
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


  // zoom and pan controls
  // only need to manipulate one (e.g. north) timeseries plot;
  // they're all bound together by drawCallback when created

  // $('#reset').addClass('disabled');
  //
  // $('#reset').on('click', function(e) {
  //   e.preventDefault();
  //   north.reset();
  //   $('#reset').addClass('disabled');
  // });
  // $('#left, #right').on('click', function(e) {
  //   e.preventDefault();
  //   var dir = ($(this).attr('id') === 'left' ? -1 : 1);
  //   north.pan(dir);
  // });
