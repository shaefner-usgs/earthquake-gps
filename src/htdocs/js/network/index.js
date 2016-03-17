/* global network */ // passed via var embedded in html page

'use strict';

var L = require('leaflet'); // aliased in browserify.js

// Leaflet plugins
require('leaflet/MousePosition');
require('leaflet/Restoreview');

// Factories for creating map layers (returns e.g. "L.earthquakesLayer()")
require('leaflet/DarkLayer');
require('leaflet/EarthquakesLayer');
require('leaflet/FaultsLayer');
require('leaflet/GreyscaleLayer');
require('leaflet/SatelliteLayer');
require('leaflet/StationsLayer');
require('leaflet/TerrainLayer');

console.log('network: ', network);

// Define map layers
var dark = L.darkLayer(),
    earthquakes = L.earthquakesLayer('_getEarthquakes.json.php'),
    faults = L.faultsLayer(),
    greyscale = L.greyscaleLayer(),
    satellite = L.satelliteLayer(),
    stations = L.stationsLayer('_getStations.json.php?network=' + network),
    terrain = L.terrainLayer(),

    baseLayers = {
      'Greyscale': greyscale,
      'Terrain': terrain,
      'Satellite': satellite,
      'Dark': dark
    },
    overlays = {
      'Stations': stations,
      'Earthquakes': earthquakes,
      'Faults': faults
    };

// Create map
var map = L.map(document.querySelector('.map'), {
  center: [38, -123],
  layers: [greyscale, earthquakes, faults],
  scrollWheelZoom: false,
  zoom: 9
});

// Add controllers
L.control.layers(baseLayers, overlays).addTo(map);
L.control.mousePosition().addTo(map);
L.control.scale().addTo(map);

// Remember user's map settings (selected layers, map extent)
map.restoreView({
  baseLayers: baseLayers,
  overlays: overlays
});
