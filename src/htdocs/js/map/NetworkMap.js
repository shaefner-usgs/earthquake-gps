/* global L, MOUNT_PATH, NETWORK */
'use strict';


var Xhr = require('util/Xhr');

// Leaflet plugins
require('leaflet-fullscreen');
require('map/MousePosition');
require('map/Restoreview');

// Factories for creating map layers (returns e.g. "L.earthquakesLayer()")
require('map/DarkLayer');
require('map/EarthquakesLayer');
require('map/FaultsLayer');
require('map/GreyscaleLayer');
require('map/SatelliteLayer');
require('map/StationsLayer');
require('map/TerrainLayer');


/**
 * Factory for leaflet map instance on the 'network' page
 *
 * @param options {Object}
 */
var NetworkMap = function (options) {
  var _this,
      _initialize,

      _earthquakes,
      _el,
      _map,
      _stations,

      _attachPopupLinks,
      _getMapLayers,
      _initMap,
      _loadEarthquakesLayer,
      _loadStationsLayer,
      _showCounts;


  _this = {};

  _initialize = function (options) {
    options = options || {};
    _el = options.el || document.createElement('div');

    _initMap();
    _attachPopupLinks();
  };


  /**
   * Attach handlers for map popups to list of stations below the map
   */
  _attachPopupLinks = function () {
    var a, i, li, lis,
        openPopup;

    openPopup = function(e) {
      e.preventDefault();
      _stations.openPopup(e.target.station);
    };

    lis = document.querySelectorAll('.stations li');
    for (i = 0; i < lis.length; i ++) {
      li = lis[i];
      a = document.createElement('a');
      a.station = li.querySelector('a').textContent;
      a.setAttribute('class', 'bubble');
      a.setAttribute('href', '#');
      li.appendChild(a);
      a.addEventListener('click', openPopup);
    }
  };

  /**
   * Get all map layers that will be displayed on map
   *
   * @return layers {Object}
   *     {
   *       baseLayers: {Object}
   *       overlays: {Object}
   *       defaults: {Array}
   *     }
   */
  _getMapLayers = function () {
    var dark,
        faults,
        greyscale,
        layers,
        name,
        satellite,
        terrain;

    _earthquakes = L.earthquakesLayer(); // data added via ajax
    _loadEarthquakesLayer();
    _stations = L.stationsLayer(); // data added via ajax
    _loadStationsLayer();

    dark = L.darkLayer();
    greyscale = L.greyscaleLayer();
    satellite = L.satelliteLayer();
    terrain = L.terrainLayer();
    faults = L.faultsLayer();

    layers = {};
    layers.baseLayers = {
      'Terrain': terrain,
      'Satellite': satellite,
      'Greyscale': greyscale,
      'Dark': dark
    };
    layers.overlays = {
      'Faults': faults,
      'Earthquakes': _earthquakes
    };
    layers.defaults = [terrain, _earthquakes];

    // Add stations to overlays / defaults
    Object.keys(_stations.layers).forEach(function(key) {
      name = _stations.names[key] +
        '<span class="' + key + '"></span>'; // hook to add station count
      layers.overlays[name] = _stations.layers[key];
      layers.defaults.push(_stations.layers[key]);
    });

    return layers;
  };

  /**
   * Get earthquakes layer from geojson data via ajax
   */
  _loadEarthquakesLayer = function () {
    Xhr.ajax({
      url: MOUNT_PATH + '/_getEarthquakes.json.php',
      success: function (data) {
        _earthquakes.addData(data);
      },
      error: function (status) {
        console.log(status);
      }
    });
  };

  /**
   * Get stations layer from geojson data via ajax
   */
  _loadStationsLayer = function () {
    Xhr.ajax({
      url: MOUNT_PATH + '/_getStations.json.php?network=' + NETWORK,
      success: function (data) {
        var bounds,
            mapView;

        _stations.addData(data);

        // Set map extent to stored bounds or to extent of points
        mapView = JSON.parse(window.localStorage.getItem('mapView')) || {};
        if (!mapView.hasOwnProperty('networks')) {
          bounds = _stations.getBounds();
          _map.fitBounds(bounds);
        }

        // Show station counts
        _showCounts();
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
    L.control.mousePosition().addTo(_map);
    L.control.scale().addTo(_map);

    // Remember user's map settings (selected layers, map extent)
    _map.restoreView({
      baseLayers: layers.baseLayers,
      id: NETWORK,
      overlays: layers.overlays,
      shareLayers: true
    });
  };

  /**
   * Add count dynamically so it doesn't affect the layer name
   *
   * restoreView plugin uses the name, and layer state can be shared by
   * multiple pages
   */
  _showCounts = function () {
    var sel;

    Object.keys(_stations.layers).forEach(function(key) {
      sel = document.querySelector('.leaflet-control .' + key);
      sel.innerHTML = ' (' + _stations.count[key] + ')';
    });
  };


  _initialize(options);
  options = null;
  return _this;
};


module.exports = NetworkMap;
