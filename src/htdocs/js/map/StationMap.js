/* global L, MOUNT_PATH */
'use strict';


var Xhr = require('util/Xhr');

// Factories for creating map layers (returns e.g. "L.earthquakesLayer()")
require('map/StationsLayer');
require('map/TerrainLayer');

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
console.log(_el);
    // Load stations layer which calls initMap() when finished
    _loadStationsLayer();
  };


  _initMap = function () {
    var map;
console.log(_el);
    // Create map
    map = L.map(_el, {
      layers: [L.terrainLayer(), _stations],
      scrollWheelZoom: false,
      center: [38, -123],
      zoom: 4
    });
  };

  _loadStationsLayer = function () {
    Xhr.ajax({
      url: MOUNT_PATH + '/_getStations.json.php',
      success: function (data) {
        _stations = L.stationsLayer({
          data: data
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
