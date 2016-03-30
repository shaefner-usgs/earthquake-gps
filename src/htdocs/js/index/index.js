'use strict';

var L = require('leaflet'), // aliased in browserify.js
    Xhr = require('util/Xhr');

// Leaflet plugins
require('leaflet-fullscreen');
require('leaflet/MousePosition');
require('leaflet/Restoreview');

// Factories for creating map layers (returns e.g. "L.earthquakesLayer()")
require('leaflet/DarkLayer');
require('leaflet/GreyscaleLayer');
require('leaflet/NetworksLayer.js');
require('leaflet/SatelliteLayer');
require('leaflet/TerrainLayer');

var IndexMap = function () {
  var _this,
      _initialize,

      _networks,

      _getMapLayers,
      _getNetworksLayer,
      _initMap;

  _this = {};

  _initialize = function () {
    // Get netwoks layer and call initMap() when finished
    _getNetworksLayer();
  };

  /**
   * Get all map layers that will be displayed on map
   *
   * @return layers {Object} {
   *   baseLayers: {Object}
   *   overlays: {Object}
   *   defaults: {Array}
   * }
   */
  _getMapLayers = function () {
    var dark,
        greyscale,
        layers,
        satellite,
        terrain;

    dark = L.darkLayer();
    greyscale = L.greyscaleLayer();
    satellite = L.satelliteLayer();
    terrain = L.terrainLayer();

    layers = {};
    layers.baseLayers = {
      'Terrain': terrain,
      'Satellite': satellite,
      'Greyscale': greyscale,
      'Dark': dark
    };
    layers.overlays = {
      'Networks': _networks
    };
    layers.defaults = [terrain, _networks];

    return layers;
  };

  /**
   * Get networks layer from geojson data via ajax
   */
  _getNetworksLayer = function () {
    Xhr.ajax({
      url: '_getNetworks.json.php',
      success: function (data) {
        _networks = L.networksLayer(data);
        _initMap();
      },
      error: function (status) {
        console.log(status);
      }
    });
  };

  /**
   * Create Leaflet map instance
   */
  _initMap = function () {
    var bounds,
        layers,
        map;

    layers = _getMapLayers();

    // Create map
    map = L.map(document.querySelector('.map'), {
      layers: layers.defaults,
      scrollWheelZoom: false
    });

    // Set intial map extent to contain networks overlay
    bounds = _networks.getBounds();
    map.fitBounds(bounds);

    // Add controllers
    L.control.layers(layers.baseLayers, layers.overlays).addTo(map);
    L.control.scale().addTo(map);

    // Remember user's map settings (selected layers, map extent)
    map.restoreView({
      baseLayers: layers.baseLayers,
      id: 'networks',
      overlays: layers.overlays,
      shareLayers: false
    });
  };

  _initialize();

  return _this;
};

IndexMap();
