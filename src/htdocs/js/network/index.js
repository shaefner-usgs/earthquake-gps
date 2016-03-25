/* global network */ // passed via var embedded in html page

'use strict';

var L = require('leaflet'), // aliased in browserify.js
    Xhr = require('util/Xhr');

// Leaflet plugins
require('leaflet-fullscreen');
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

var earthquakes,
    stations,

    getEarthquakesLayer,
    getStationsLayer,
    initialize;

// Get stations layer
getStationsLayer = function () {
  Xhr.ajax({
    url: '_getStations.json.php?network=' + network,
    success: function (data) {
      stations = L.stationsLayer(data);
      initialize();
    },
    error: function (status) {
      console.log(status);
    }
  });
};

// Get earthquakes layer
getEarthquakesLayer = function () {
  Xhr.ajax({
    url: '_getEarthquakes.json.php',
    success: function (data) {
      earthquakes = L.earthquakesLayer(data);
      initialize();
    },
    error: function (status) {
      console.log(status);
    }
  });
};

// Store geojson layers and call initialize() when ajax request is finished
getStationsLayer();
getEarthquakesLayer();

initialize = function () {
  if (!stations || !earthquakes) { // check that both layers are set
    return;
  }

  // Define map layers (stations and earthquakes defined separately)
  var dark = L.darkLayer(),
      //faults = L.faultsLayer(),
      greyscale = L.greyscaleLayer(),
      satellite = L.satelliteLayer(),
      terrain = L.terrainLayer();

  var baseLayers = {
    'Greyscale': greyscale,
    'Terrain': terrain,
    'Satellite': satellite,
    'Dark': dark
  },
  overlays = {
    'Stations': stations,
    //'Faults': faults,
    'Earthquakes': earthquakes
  };

  // Create map
  var map = L.map(document.querySelector('.map'), {
    layers: [greyscale, earthquakes, stations],
    scrollWheelZoom: false
  });

  // Set intial map extent to contain stations overlay
  var bounds = stations.getBounds();
  map.fitBounds(bounds);

  // Add controllers
  L.control.fullscreen({ pseudoFullscreen: true }).addTo(map);
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
};
