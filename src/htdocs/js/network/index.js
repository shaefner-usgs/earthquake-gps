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

// Define map layers
var dark = L.darkLayer(),
    earthquakes = L.earthquakesLayer('_getEarthquakes.json.php'),
    //faults = L.faultsLayer(),
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
//      'Faults': faults,
      'Earthquakes': earthquakes
    };

console.log(stations);

// Create map
var map = L.map(document.querySelector('.map'), {
  layers: [greyscale, earthquakes, stations],
  scrollWheelZoom: false
});

// Set intial map extent to stations overlay
console.log(stations.getBounds()); // featureGroup

var bounds = stations.getMapBounds(); // stations overlay
console.log(bounds);
console.log(bounds.isValid());
map.fitBounds(bounds);

// Add controllers
L.control.layers(baseLayers, overlays).addTo(map);
L.control.mousePosition().addTo(map);
L.control.scale().addTo(map);

// Remember user's map settings (selected layers, map extent)
map.restoreView({
  baseLayers: baseLayers,
  id: network,
  overlays: overlays,
  shareLayers: true
});
