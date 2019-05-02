/* global L, MOUNT_PATH, NETWORK */
'use strict';


var Xhr = require('util/Xhr');

// Leaflet plugins
require('leaflet-fullscreen');
require('leaflet-groupedlayercontrol');
require('map/MousePosition');
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

      _addListeners,
      _addPopupIcons,
      _getId,
      _getMapLayers,
      _initMap,
      _loadEarthquakesLayer,
      _loadStationsLayer,
      _setLegendState,
      _showCounts;


  _this = {};

  _initialize = function (options) {
    options = options || {};
    _el = options.el || document.createElement('div');

    // Load eqs, stations layers which each call initMap() when finished
    _loadEarthquakesLayer();
    _loadStationsLayer();
  };


  /**
   * Add event listeners for:
   *   1. station buttons to show labels/popups on map
   *   2. greying out legend item when layer is turned off in controller
   */
  _addListeners = function () {
    var button,
        buttons,
        color,
        i,
        icon,
        legendItem,
        onClick,
        onMouseout,
        onMouseover;

    onClick = function (e) {
      var button,
          marker,
          station;

      button = e.target.parentNode.querySelector('.button');
      station = button.textContent.match(/\w+/)[0]; // ignore '*' (high RMS value)
      marker = _stations.markers[station.toUpperCase()];
      marker.openPopup();

      e.preventDefault();
    };
    onMouseout = function (e) {
      _stations.hideLabel(_getId(e.target));
    };
    onMouseover = function (e) {
      _stations.showLabel(_getId(e.target));
    };

    buttons = document.querySelectorAll('.stations a:first-child');
    for (i = 0; i < buttons.length; i ++) {
      button = buttons[i];
      button.addEventListener('mouseover', onMouseover);
      button.addEventListener('mouseout', onMouseout);

      icon = button.nextElementSibling;
      icon.addEventListener('mouseover', onMouseover);
      icon.addEventListener('click', onClick);
    }

    _map.on('overlayadd overlayremove', function (e) {
      color = e.name.match(/blue|orange|red|yellow/)[0];
      legendItem = document.querySelector('.legend .' + color);

      if (e.type === 'overlayremove') {
        legendItem.classList.add('greyed-out');
      } else {
        legendItem.classList.remove('greyed-out');
      }
    });
  };

  /**
   * Add popup icons to station buttons
   */
  _addPopupIcons = function () {
    var buttons,
        i,
        icon;

    buttons = document.querySelectorAll('.stations li');
    for (i = 0; i < buttons.length; i ++) {
      icon = document.createElement('a');
      icon.setAttribute('href', '#');
      icon.setAttribute('class', 'icon');
      icon.setAttribute('title', 'View station popup');

      buttons[i].appendChild(icon);
    }
  };

  /**
   * Get id of feature (station), which is attached to the button element
   *
   * @param el {Element}
   *     button element or its sibling
   *
   * @return id {Integer}
   */
  _getId = function (el) {
    var id;

    if (el.classList.contains('icon')) {
      el = el.parentNode.querySelector('.button');
    }

    id = el.className.replace(/\D/g, ''); // number portion only

    return id;
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
      'Stations, Last Updated': {},
      'Geology': {
        'Faults': faults,
        'M2.5+ Earthquakes': _earthquakes
      }
    };
    layers.defaults = [terrain, _earthquakes];

    // Add stations to overlays / defaults
    Object.keys(_stations.layers).forEach(function(key) {
      name = _stations.names[key] +
        '<span class="' + key + '"></span>'; // hook to add station count
      layers.overlays['Stations, Last Updated'][name] = _stations.layers[key];
      layers.defaults.push(_stations.layers[key]);
    });

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
        layers;

    layers = _getMapLayers();

    // Create map
    _map = L.map(_el, {
      layers: layers.defaults,
      scrollWheelZoom: false
    });

    // Set intial map extent to contain stations overlay
    bounds = _stations.getBounds();
    _map.fitBounds(bounds);

    // Add controllers
    L.control.fullscreen({ pseudoFullscreen: true }).addTo(_map);
    L.control.groupedLayers(layers.baseLayers, layers.overlays).addTo(_map);
    L.control.mousePosition().addTo(_map);
    L.control.scale().addTo(_map);

    // Remember user's map settings (selected layers, map extent)
    _map.restoreMap({
      baseLayers: layers.baseLayers,
      id: NETWORK,
      overlays: layers.overlays,
      scope: 'GPS',
      shareLayers: true
    });

    // Add popup icons & listeners, set legend state, and show station counts
    _addPopupIcons();
    _addListeners();
    _setLegendState();
    _showCounts();
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
   * Grey out layers that are already turned off when map loads
   */
  _setLegendState = function () {
    var layer,
        legendItem;

    Object.keys(_stations.layers).forEach(function(key) {
      layer = _stations.layers[key];
      if (!_map.hasLayer(layer)) {
        legendItem = document.querySelector('.legend .' + key);
        legendItem.classList.add('greyed-out');
      }
    });
  };

  /**
   * Add count dynamically so it doesn't affect the layer name
   *
   * restoreMap plugin uses the name, and layer state is shared by
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
