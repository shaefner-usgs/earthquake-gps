'use strict';

var L = require('leaflet'); // aliased in browserify.js

// Leaflet plugins
require('leaflet/Restoreview');

// Factories for creating map layers (returns e.g. "L.earthquakesLayer()")
require('leaflet/DarkLayer');
require('leaflet/GreyscaleLayer');
require('leaflet/SatelliteLayer');
require('leaflet/TerrainLayer');

// Define map layers
var dark = L.darkLayer(),
    greyscale = L.greyscaleLayer(),
    satellite = L.satelliteLayer(),
    terrain = L.terrainLayer(),

    baseLayers = {
      'Greyscale': greyscale,
      'Terrain': terrain,
      'Satellite': satellite,
      'Dark': dark
    },
    overlays = {

    };

// Create map
var map = L.map(document.querySelector('.map'), {
  center: [38, -123],
  layers: [greyscale],
  scrollWheelZoom: false,
  zoom: 9
});

// Add controllers
L.control.layers(baseLayers, overlays).addTo(map);
L.control.scale().addTo(map);

// Remember user's map settings (selected layers, map extent)
map.restoreView({
  baseLayers: baseLayers,
  id: 'network-map',
  overlays: overlays,
  shareLayers: true
});
