'use strict';

var L = require('leaflet'); // aliased in browserify.js
require('Leaflet.RestoreView/leaflet.restoreview');

var osm = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
});
var mq = L.tileLayer('http://otile1.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png');

var marker1 = L.marker([38, -121]).bindPopup('Popup');
var marker2 = L.marker([38, -122]).bindPopup('Popup');
var points = L.layerGroup([marker1, marker2]);

var baseMaps = {
  'OpenStreetMap': osm,
  'Mapquest': mq
};
var overlays = {
  //'Cities1': points1,
  'Points': points
};

window.map = L.map('map', {
  layers: [mq, points]
});

// Default map params
if (!window.map.restoreView()) {
  window.map.setView([38, -121], 10);
  //map.addLayer(mq);
}

L.control.layers(baseMaps, overlays).addTo(window.map);

//console.log(points);
