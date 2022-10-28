/* global L, NETWORK, STATION */
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
      _map,
      _stations,

      _getMapLayers,
      _initMap,
      _loadEarthquakesLayer;


  _this = {};

  _initialize = function (options) {
    options = options || {};
    _el = options.el || document.createElement('div');

    _earthquakes = L.layerGroup(); // use empty layer until ajax request completes

    _initMap();
    _loadEarthquakesLayer();
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
    var count,
        dark,
        faults,
        greyscale,
        layers,
        otherStations,
        satellite,
        selStation,
        stations,
        terrain;

    dark = L.darkLayer();
    faults = L.faultsLayer();
    greyscale = L.greyscaleLayer();
    satellite = L.satelliteLayer();
    terrain = L.terrainLayer();

    _stations = L.stationsLayer({
      data: window.data.stations,
      map: _map,
      station: STATION
    });

    // Separate selected station into its own layer
    selStation = _stations.markers[STATION.toUpperCase()];
    otherStations = _stations.removeLayer(selStation);
    count = otherStations.getLayers().length;
    stations = {};
    stations['Station ' + STATION.toUpperCase()] = selStation;
    stations['Other stations <span>(' + count + ')</span>'] = otherStations;

    layers = {
      baseLayers: {
        'Terrain': terrain,
        'Satellite': satellite,
        'Greyscale': greyscale,
        'Dark': dark
      },
      overlays: {},
      defaults: [terrain, _earthquakes, selStation, otherStations]
    };

    // Add overlays separately in order to use a variable value in the key
    layers.overlays[NETWORK + ' Network'] = stations;
    layers.overlays.Geology =  {
      'Faults': faults,
      'M 2.5+ Earthquakes <div class="spinner"></div>': _earthquakes
    };

    return layers;
  };

  /**
   * Create Leaflet map instance
   */
  _initMap = function () {
    var bounds,
        layers;

    // Create map
    _map = L.map(_el, {
      scrollWheelZoom: false
    });

    // _map must be instantiated before calling _getMapLayers()
    layers = _getMapLayers();
    layers.defaults.forEach(layer => _map.addLayer(layer));

    bounds = _stations.getBounds();
    _map.setView(bounds.getCenter(), 7);

    // Add controllers
    L.control.fullscreen({ pseudoFullscreen: true }).addTo(_map);
    L.control.groupedLayers(layers.baseLayers, layers.overlays).addTo(_map);
    L.control.scale().addTo(_map);

    // Remember user's map settings (selected layers, map extent)
    _map.restoreMap({
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
    var eqs,
        spinner,
        url;

    url = 'https://earthquake.usgs.gov/fdsnws/event/1/query?format=geojson&minmagnitude=2.5&orderby=time-asc';

    Xhr.ajax({
      url: url,
      success: function (data) {
        eqs = L.earthquakesLayer({
          data: data
        });
        _earthquakes.addLayer(eqs);
        spinner = document.querySelector('.spinner');
        spinner.parentNode.removeChild(spinner);
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
