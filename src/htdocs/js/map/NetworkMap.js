/* global L, NETWORK */
'use strict';


var StatusBar = require('StatusBar'),
    Xhr = require('util/Xhr');

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
      _statusBar,

      _addListeners,
      _addPopupIcons,
      _getStationName,
      _getMapLayers,
      _initMap,
      _loadEarthquakesLayer,
      _openPopup,
      _setLegendState,
      _showCounts;


  _this = {};

  _initialize = function (options) {
    options = options || {};
    _el = options.el || document.createElement('div');

    _earthquakes = L.layerGroup(); // use empty layer until ajax request completes

    _initMap();
    _loadEarthquakesLayer();
  };


  /**
   * Add event listeners for:
   *   1. station buttons to show tooltips/popups on map
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
          color,
          layer,
          marker,
          station;

      button = e.target.parentNode.querySelector('.button');
      color = button.getAttribute('class').match(/blue|orange|red|yellow/)[0];
      layer = _stations.layers[color];
      station = button.textContent.match(/\w+/)[0]; // ignore '*' (high RMS value)
      marker = _stations.markers[station.toUpperCase()];

      _openPopup(marker, layer);
      e.preventDefault();
    };
    onMouseout = function (e) {
      _stations.hideTooltip(_getStationName(e.target));
    };
    onMouseover = function (e) {
      _stations.showTooltip(_getStationName(e.target));
    };

    buttons = document.querySelectorAll('.stations a:first-child');
    for (i = 0; i < buttons.length; i ++) {
      button = buttons[i];
      button.addEventListener('mouseover', onMouseover);
      button.addEventListener('mouseout', onMouseout);

      icon = button.nextElementSibling;
      icon.addEventListener('mouseover', onMouseover);
      icon.addEventListener('mouseout', onMouseout);
      icon.addEventListener('click', onClick);
    }

    _map.on('overlayadd overlayremove', function (e) {
      color = e.name.match(/<span class="(blue|orange|red|yellow)">/);
      if (color) {
        legendItem = document.querySelector('.legend .' + color[1]);

        if (e.type === 'overlayremove') {
          legendItem.classList.add('greyed-out');
        } else {
          legendItem.classList.remove('greyed-out');
        }
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
   * Get station name, which is the text to the button element
   *
   * @param el {Element}
   *     button element or its sibling
   *
   * @return {String}
   */
  _getStationName = function (el) {
    if (el.classList.contains('icon')) {
      el = el.parentNode.querySelector('.button');
    }

    return el.textContent;
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
    faults = L.faultsLayer();
    greyscale = L.greyscaleLayer();
    satellite = L.satelliteLayer();
    terrain = L.terrainLayer();

    _stations = L.stationsLayer({
      data: window.data.stations
    });

    layers = {
      baseLayers: {
        'Terrain': terrain,
        'Satellite': satellite,
        'Greyscale': greyscale,
        'Dark': dark
      },
      overlays: {
        'Stations, Last Updated': {},
        'Geology': {
          'Faults': faults,
          'M 2.5+ Earthquakes <div class="spinner"></div>': _earthquakes
        }
      },
      defaults: [terrain, _earthquakes]
    };

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

  /**
   * Open popups on map from buttons below map; alert user if layer is turned off
   *
   * @param marker {L.marker}
   * @param layer {L.layer}
   */
  _openPopup = function (marker, layer) {
    var p;

    if (_map.hasLayer(marker)) {
      if (_statusBar) {
        _statusBar.hide(); // hide any prev. existing status bar
      }
      marker.openPopup();
    } else {
      p = document.createElement('p');
      p.innerHTML = 'Map layer is off. <a href="#" class="turn-on">Turn it on</a>.';
      p.querySelector('.turn-on').addEventListener('click', function (e) {
        _map.addLayer(layer);
        marker.openPopup();
        _statusBar.hide();
        _showCounts(); // counts get removed when layer is dynamically turned on

        e.preventDefault();
      });
      _statusBar = StatusBar({
        message: p
      });
      _statusBar.show();
    }
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
