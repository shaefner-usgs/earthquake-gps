/* global L, MOUNT_PATH, NETWORK, STATION */
'use strict';


var Xhr = require('util/Xhr');

// Leaflet plugins
require('leaflet-fullscreen');
require('leaflet-groupedlayercontrol');
require('map/RestoreMap');

// Factories for creating map layers (returns e.g. "L.earthquakesLayer()")
require('map/DarkLayer');
require('map/EarthquakesLayer');
require('map/FaultsLayer');
require('map/GreyscaleLayer');
require('map/SatelliteLayer');
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

      _earthquakes,
      _el,
      _stations,

      _initMap,
      _getMapLayers,
      _loadEarthquakesLayer,
      _loadStationsLayer;


  _this = {};

  _initialize = function (options) {
    options = options || {};
    _el = options.el || document.createElement('div');

    // Load eqs, stations layers which each call initMap() when finished
    _loadEarthquakesLayer();
    _loadStationsLayer();
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
        satellite,
        selStation,
        stations,
        terrain;

    // Separate selected station into its own layer
    selStation = _stations.markers[STATION.toUpperCase()];
    stations = {};
    stations['Station ' + STATION.toUpperCase()] = selStation;
    stations['Other stations'] = _stations.removeLayer(selStation);

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
    layers.overlays = {};
    layers.overlays[NETWORK + ' Network'] = stations;
    layers.overlays.Geology = {
      'Faults': faults,
      'M2.5+ Earthquakes': _earthquakes
    };
    layers.defaults = [terrain, _earthquakes, selStation, _stations];

    return layers;
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

    // bounds contain only selected station
    bounds = _stations.getBounds();
    layers = _getMapLayers();

    // Create map
    map = L.map(_el, {
      layers: layers.defaults,
      scrollWheelZoom: false,
      center: bounds.getCenter(),
      zoom: 7
    });

    // Add controllers
    L.control.fullscreen({ pseudoFullscreen: true }).addTo(map);
    L.control.groupedLayers(layers.baseLayers, layers.overlays).addTo(map);
    L.control.scale().addTo(map);

    // Remember user's map settings (selected layers, map extent)
    map.restoreMap({
      baseLayers: layers.baseLayers,
      id: NETWORK + '-' + STATION,
      overlays: layers.overlays,
      scope: 'GPS',
      shareLayers: true
    });

    _stations.markers[STATION.toUpperCase()].openPopup();
  };

  /**
   * Load earthquakes layer from geojson data via ajax
   */
  _loadEarthquakesLayer = function () {
    var url;

    url = 'https://earthquake.usgs.gov/fdsnws/event/1/query?format=geojson&minmagnitude=2.5&orderby=time-asc';

    Xhr.ajax({
      url: url,
      success: function (data) {
        _earthquakes = L.earthquakesLayer({
          data: data
        });
        _initMap();
      },
      error: function (status) {
        console.log(status);
      }
    });
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
