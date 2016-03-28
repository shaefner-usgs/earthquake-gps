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

var NetworkMap = function () {
  var _this,
      _initialize,

      _earthquakes,
      _stations,

      _getEarthquakesLayer,
      _getStationsLayer,
      _initMap,
      _getMapLayers;

  _this = {};

  _initialize = function () {
    // Store geojson data and call _initMap() when ajax requests finish
    _getEarthquakesLayer();
    _getStationsLayer();
  };

  // Get earthquakes layer
  _getEarthquakesLayer = function () {
    Xhr.ajax({
      url: '_getEarthquakes.json.php',
      success: function (data) {
        _earthquakes = L.earthquakesLayer(data);
        _initMap();
      },
      error: function (status) {
        console.log(status);
      }
    });
  };

  _getMapLayers = function () {
    var dark,
  //      faults,
        greyscale,
        layers,
        satellite,
        terrain;

    layers = {};
    dark = L.darkLayer();
    greyscale = L.greyscaleLayer();
    satellite = L.satelliteLayer();
    terrain = L.terrainLayer();
    //faults = L.faultsLayer();

    layers.baseLayers = {
      'Greyscale': greyscale,
      'Terrain': terrain,
      'Satellite': satellite,
      'Dark': dark
    };
    layers.overlays = {
      //'Faults': faults,
      'Earthquakes': _earthquakes
    };
    layers.defaults = [greyscale, _earthquakes];

    // Add stations to overlays / defaults (stored in multiple, unknown groups)
    Object.keys(_stations.layers).forEach(function(name) {
      layers.overlays[name] = _stations.layers[name];
      layers.defaults.push(_stations.layers[name]);
    });

    return layers;
  };

  // Get stations layer
  _getStationsLayer = function () {
    Xhr.ajax({
      url: '_getStations.json.php?network=' + network,
      success: function (data) {
        _stations = L.stationsLayer(data);
        _initMap();
      },
      error: function (status) {
        console.log(status);
      }
    });
  };



  _initMap = function () {
    if (!_stations || !_earthquakes) { // check that both ajax layers are set
      return;
    }

    var bounds,
        layers;

    layers = _getMapLayers();

    // Create map
    var map = L.map(document.querySelector('.map'), {
      layers: layers.defaults,
      scrollWheelZoom: false
    });

    // Set intial map extent to contain stations overlay
    bounds = _stations.getBounds();
    map.fitBounds(bounds);

    // Add controllers
    L.control.fullscreen({ pseudoFullscreen: true }).addTo(map);
    L.control.layers(layers.baseLayers, layers.overlays).addTo(map);
    L.control.mousePosition().addTo(map);
    L.control.scale().addTo(map);

    // Remember user's map settings (selected layers, map extent)
    map.restoreView({
      baseLayers: layers.baseLayers,
      id: network,
      overlays: layers.overlays,
      shareLayers: true
    });
  };

  _initialize();

  return _this;

};

NetworkMap();
//module.exports = NetworkMap;
