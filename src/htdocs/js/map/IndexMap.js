/* global L */
'use strict';


// Leaflet plugins
require('leaflet-fullscreen');
require('map/RestoreMap');

// Factories for creating map layers (returns e.g. "L.earthquakesLayer()")
require('map/DarkLayer');
require('map/GreyscaleLayer');
require('map/NetworksLayer');
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
      _networks,

      _addListeners,
      _getMapLayers,
      _initMap;


  _this = {};

  _initialize = function (options) {
    options = options || {};
    _el = options.el || document.createElement('div');

    _initMap();
  };


  /**
   * Add event listeners for newtork buttons to show tooltips/polygons on map
   */
  _addListeners = function () {
    var button,
        i,
        id,
        onMouseout,
        onMouseover;

    onMouseout = function (e) {
      id = e.target.className.replace(/\D/g, ''); // number portion only
      _networks.hideOverlays(id);
    };
    onMouseover = function (e) {
      id = e.target.className.replace(/\D/g, ''); // number portion only
      _networks.showOverlays(id);
    };

    button = document.querySelectorAll('.networks a');
    for (i = 0; i < button.length; i ++) {
      button[i].addEventListener('mouseover', onMouseover);
      button[i].addEventListener('mouseout', onMouseout);
    }
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

    dark = L.darkLayer();
    greyscale = L.greyscaleLayer();
    satellite = L.satelliteLayer();
    terrain = L.terrainLayer();

    _networks = L.networksLayer({
      data: window.data.networks
    });

    layers = {
      baseLayers: {
        'Terrain': terrain,
        'Satellite': satellite,
        'Greyscale': greyscale,
        'Dark': dark
      },
      overlays: {
        'Networks': _networks
      },
      defaults: [terrain, _networks]
    };

    return layers;
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
    map = L.map(_el, {
      layers: layers.defaults,
      scrollWheelZoom: false
    });

    // Set initial map extent to contain Contiguous U.S.
    //bounds = _networks.getBounds();
    bounds = L.latLngBounds([49, -126], [24, -66]);
    map.fitBounds(bounds);

    // Add controllers
    L.control.fullscreen({ pseudoFullscreen: true }).addTo(map);
    L.control.layers(layers.baseLayers, layers.overlays).addTo(map);
    L.control.scale().addTo(map);

    // Remember user's map settings (selected layers, map extent)
    map.restoreMap({
      baseLayers: layers.baseLayers,
      id: 'networks',
      overlays: layers.overlays,
      scope: 'GPS',
      shareLayers: true
    });

    // Add listeners to buttons below map
    _addListeners();
  };


  _initialize(options);
  options = null;
  return _this;
};


module.exports = IndexMap;
