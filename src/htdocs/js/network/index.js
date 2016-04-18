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

      _attachPopupLinks,
      _getEarthquakesLayer,
      _getMapLayers,
      _getStationsLayer,
      _initMap,
      _showCounts;

  _this = {};

  _initialize = function () {
    // Get eqs, stations layers which each call initMap() when finished
    _getEarthquakesLayer();
    _getStationsLayer();

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
   * Get earthquakes layer from geojson data via ajax
   */
  _getEarthquakesLayer = function () {
    Xhr.ajax({
      url: '/_getEarthquakes.json.php',
      success: function (data) {
        _earthquakes = L.earthquakesLayer(data);
        _initMap();
      },
      error: function (status) {
        console.log(status);
      }
    });
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
  //      faults,
        greyscale,
        layers,
        name,
        satellite,
        terrain;

    dark = L.darkLayer();
    greyscale = L.greyscaleLayer();
    satellite = L.satelliteLayer();
    terrain = L.terrainLayer();
    //faults = L.faultsLayer();

    layers = {};
    layers.baseLayers = {
      'Terrain': terrain,
      'Satellite': satellite,
      'Greyscale': greyscale,
      'Dark': dark
    };
    layers.overlays = {
      //'Faults': faults,
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
   * Get stations layer from geojson data via ajax
   */
  _getStationsLayer = function () {
    Xhr.ajax({
      url: '/_getStations.json.php?network=' + network,
      success: function (data) {
        _stations = L.stationsLayer(data);
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
    if (!_stations || !_earthquakes) { // check that both ajax layers are set
      return;
    }
    var bounds,
        layers,
        map;

    layers = _getMapLayers();

    // Create map
    map = L.map(document.querySelector('.map'), {
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

    // Show station counts
    _showCounts();
  };

  /**
   * Add count dynamically so it doesn't affect the layer name
   *
   * restorView plugin uses the name, and layer state can be shared by
   * multiple pages
   */
  _showCounts = function () {
    var sel;

    Object.keys(_stations.layers).forEach(function(key) {
      sel = document.querySelector('.leaflet-control .' + key);
      sel.innerHTML = ' (' + _stations.count[key] + ')';
      console.log(sel);
    });
  };

  _initialize();

  return _this;
};

NetworkMap();
