'use strict';


var IndexMap = require('map/IndexMap'),
    MediaQueries = require('MediaQueries');

IndexMap({
  el: document.querySelector('.map')
});

MediaQueries({
  el: document.querySelector('.networks')
});

window.addEventListener('breakpoint-change', function(e) {
  console.log(e.detail.type);
});
