'use strict';


var IndexMap = require('map/IndexMap'),
    MediaQueries = require('MediaQueries');

var height,
    initialHeight,
    navButtons,
    numNetworks;

// Initialize map
IndexMap({
  el: document.querySelector('.map')
});

// Set up js-based media queries
navButtons = document.querySelector('.networks');
MediaQueries({
  el: navButtons
});

// Change height of container for nav-buttons below map when CSS breakpoint is triggered
initialHeight = parseInt(navButtons.style.height, 10);
numNetworks = parseInt(document.querySelector('h3.count').textContent, 10);
window.addEventListener('breakpoint-change', function(e) {
  var layout = e.detail.layout;

  // remove double quotes on value in FF, Chrome (seems like a bug...)
  layout = layout.replace(/"/g, '');

  if (layout === 'narrow') {
    height = Math.ceil(numNetworks / 2) * 36;
  } else { // default (wide)
    height = initialHeight;
  }
  navButtons.style.height = height + 'px';
});
