/* global L, MOUNT_PATH */
'use strict';


var Xhr = require('util/Xhr');

// Leaflet plugins
require('leaflet-fullscreen');
require('map/Restoreview');

// Factories for creating map layers (returns e.g. "L.earthquakesLayer()")
require('map/DarkLayer');
require('map/GreyscaleLayer');
require('map/NetworksLayer.js');
require('map/SatelliteLayer');
require('map/TerrainLayer');


/**
 * Factory for leaflet map instance on the 'main' page
 *
 * @param options {Object}
 */
var IndexMap = function (options) {
  var _this,
      _initialize,

      _el,
      _map,
      _networks,

      _getMapLayers,
      _loadNetworksLayer,
      _initMap;


  _this = {};

  _initialize = function (options) {
    options = options || {};
    _el = options.el || document.createElement('div');

    _initMap();
  };


  /**
   * Get all map layers that will be displayed on map
   *
   * @return layers {Object}
   *    {
   *      baseLayers: {Object}
   *      overlays: {Object}
   *      defaults: {Array}
   *    }
   */
  _getMapLayers = function () {
    var dark,
        greyscale,
        layers,
        satellite,
        terrain;

    _loadNetworksLayer();

    _networks = L.networksLayer(); // data added via ajax in _loadNetworksLayer
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
   * Load geojson network layer via ajax, then add it to the layer
   */
  _loadNetworksLayer = function () {
    Xhr.ajax({
      url: MOUNT_PATH + '/_getNetworks.json.php',
      success: function (data) {
        var bounds,
            mapView;

        _networks.addData(data);

        // Set map extent to stored bounds or to extent of points
        mapView = JSON.parse(window.localStorage.getItem('mapView')) || {};
        if (!mapView.hasOwnProperty('networks')) {
          bounds = _networks.getBounds();
          _map.fitBounds(bounds);
        }
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
    var layers;

    layers = _getMapLayers();

    // Create map
    _map = L.map(_el, {
      layers: layers.defaults,
      scrollWheelZoom: false
    });

    // Add controllers
    L.control.fullscreen({ pseudoFullscreen: true }).addTo(_map);
    L.control.layers(layers.baseLayers, layers.overlays).addTo(_map);
    L.control.scale().addTo(_map);

    // Remember user's map settings (selected layers, map extent)
    _map.restoreView({
      baseLayers: layers.baseLayers,
      id: 'networks',
      overlays: layers.overlays,
      shareLayers: false
    });
  };


  _initialize(options);
  options = null;
  return _this;
};


module.exports = IndexMap;
