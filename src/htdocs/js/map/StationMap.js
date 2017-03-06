/* global L, MOUNT_PATH, NETWORK, STATION */
'use strict';


var Xhr = require('util/Xhr');

// Leaflet plugins
require('leaflet-fullscreen');

// Factories for creating map layers (returns e.g. "L.earthquakesLayer()")
require('map/StationsLayer');
require('map/TerrainLayer');


/**
 * Factory for leaflet map instance on the 'station' page
 *
 * @param options {Object}
 */
var StationMap = function (options) {
  var _this,
      _initialize,

      _el,
      _stations,

      _initMap,
      _loadStationsLayer;


  _this = {};

  _initialize = function (options) {
    options = options || {};
    _el = options.el || document.createElement('div');

    // Load stations layer which calls initMap() when finished
    _loadStationsLayer();
  };


  /**
   * Create Leaflet map instance
   */
  _initMap = function () {
    var bounds,
        map;

    // bounds contain only selected station
    bounds = _stations.getBounds();

    // Create map
    map = L.map(_el, {
      layers: [L.terrainLayer(), _stations],
      scrollWheelZoom: false,
      center: bounds.getCenter(),
      zoom: 7
    });
    
    // Add controllers
    L.control.fullscreen({ pseudoFullscreen: true }).addTo(map);
    L.control.scale().addTo(map);

    _stations.openPopup(STATION.toUpperCase());
  };

  /**
   * Load stations layer from geojson data via ajax
   */
  _loadStationsLayer = function () {
    Xhr.ajax({
      url: MOUNT_PATH + '/_getStations.json.php?network=' + NETWORK,
      success: function (data) {
        _stations = L.stationsLayer({
          data: data,
          station: STATION
        });
        _initMap();
      },
      error: function (status) {
        console.log(status);
      }
    });
  };


  _initialize(options);
  options = null;
  return _this;
};


module.exports = StationMap;
