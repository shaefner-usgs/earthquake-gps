'use strict';

var L = require('leaflet'); // aliased in browserify.js

require('leaflet/Restoreview');

// Defines the layer factories (e.g.) "L.earthquakesLayer()"
require('leaflet/DarkLayer');
require('leaflet/EarthquakesLayer');
require('leaflet/FaultsLayer');
require('leaflet/GreyscaleLayer');
require('leaflet/SatelliteLayer');
require('leaflet/TerrainLayer');

// Map layers
var dark = L.darkLayer(),
    earthquakes = L.earthquakesLayer('_getEarthquakes.json.php'),
    faults = L.faultsLayer(),
    greyscale = L.greyscaleLayer(),
    terrain = L.terrainLayer(),
    satellite = L.satelliteLayer(),
    baseLayers = {
      'Greyscale': greyscale,
      'Terrain': terrain,
      'Satellite': satellite,
      'Dark': dark
    },
    overlays = {
      'Earthquakes': earthquakes,
      'Faults': faults
    };

// Create map
var map = L.map('map', {
  center: [38, -123],
  layers: [greyscale, earthquakes, faults],
  scrollWheelZoom: false,
  zoom: 9
});

// Add controllers
L.control.layers(baseLayers, overlays).addTo(map);

// Remember user's map settings (selected layers, map extent)
map.restoreView({
  baseLayers: baseLayers,
  overlays: overlays
});
