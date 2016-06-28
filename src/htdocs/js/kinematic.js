'use strict';


var TimeSeries = require('TimeSeries');

var graphs = [];

var north = TimeSeries({
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

var disableReset,
    pan,
    reset;

pan = document.querySelectorAll('.pan');
reset = document.querySelector('.reset');

disableReset = function () {
  reset.setAttribute('disabled', 'disabled');
};

// Pan graphs left / right
Array.prototype.slice.call(pan).forEach(function(button) {
  button.addEventListener('click', function() {
    var dir = this.classList.contains('left') ? -1 : 1;
    console.log('value: ', this.classList.contains('left'));
    north.pan(dir); // all graphs are synced
    reset.removeAttribute('disabled');
  });
});

// Reset graphs
reset.addEventListener('click', function() {
  north.reset(); // all graphs are synced
  disableReset();
});

disableReset();
