/* global L, NETWORK, MOUNT_PATH */
'use strict';


var Icon = require('map/Icon'),
    Util = require('util/Util');

require('leaflet.label');


var _DEFAULTS,
    _LAYERNAMES,
    _MARKER_DEFAULTS,
    _SHAPES;

_MARKER_DEFAULTS = {
  alt: 'GPS station'
};
_DEFAULTS = {
  data: {},
  markerOptions: _MARKER_DEFAULTS,
  station: null
};
_LAYERNAMES = {
  blue: 'Past 3 days',
  yellow: '4&ndash;7 days ago',
  orange: '8&ndash;14 days ago',
  red: 'Over 14 days ago'
};
_SHAPES = {
  campaign: 'triangle',
  continuous: 'square'
};


/**
 * Factory for Stations overlay
 *
 * @param options {Object}
 *     {
 *       data: {String} Geojson data
 *       markerOptions: {Object} L.Marker options
 *     }
 *
 * @return {L.FeatureGroup}
 *     {
 *       count: {Object}
 *       layers: {Object}
 *       name: {Object}
 *       getBounds: {Function}
 *       openPopup: {Function}
 *     }
 */
var StationsLayer = function (options) {
  var _this,
      _initialize,

      _bounds,
      _icons,
      _markerOptions,
      _points,
      _station,

      _getColor,
      _getMarker,
      _getPopup,
      _initLayers,
      _onEachFeature,
      _pointToLayer;


  _this = L.featureGroup();

  _initialize = function (options) {
    options = Util.extend({}, _DEFAULTS, options);
    _markerOptions = Util.extend({}, _MARKER_DEFAULTS, options.markerOptions);

    _bounds = new L.LatLngBounds();
    _icons = {};
    _points = {};

    if (options.station) {
      // Station user is currently viewing (not passed from Network map)
      _station = options.station;
    } else {
      // Network map classes stations by age...set up individual layers
      _initLayers();
    }

    L.geoJson(options.data, {
      onEachFeature: _onEachFeature,
      pointToLayer: _pointToLayer
    });
  };


  /**
   * Create a layerGroup for each group of stations (classed by age)
   * (also set up a count to keep track of how many stations are in each group)
   */
  _initLayers = function () {
    _this.count = {};
    _this.layers = {};
    _this.names = _LAYERNAMES;
    Object.keys(_LAYERNAMES).forEach(function (key) {
      _this.count[key] = 0;
      _this.layers[key] = L.layerGroup();
      _this.addLayer(_this.layers[key]); // add to featureGroup
    });
  };

  /**
   * Get icon color
   *
   * @param days {Integer}
   *     days since station last updated
   *
   * @return color {String}
   */
  _getColor = function (days) {
    var color = 'red'; //default

    if (days > 14) {
      color = 'red';
    } else if (days > 7) {
      color = 'orange';
    } else if (days > 3) {
      color = 'yellow';
    } else if (days >= 0) {
      color = 'blue';
    }

    return color;
  };

  /**
   * Get Leaflet marker
   *
   * @param options {Object}
   *
   * @return L.marker
   */
  _getMarker = function (options) {
    var key;

    key = options.shape + '+' + options.color;
    _markerOptions.icon = Icon.getIcon(key);

    _markerOptions.zIndexOffset = 0;
    if (options.selected) {
      _markerOptions.zIndexOffset = 1000;
    }

    return L.marker(options.latlng, _markerOptions);
  };

  /**
   * Get popup content
   *
   * @param feature {Object}
   *
   * @return popup {String}
   */
  _getPopup = function (feature, type) {
    var data,
        popup,
        popupTemplate,
        station;

    station = feature.properties.station;
    data = {
      baseUri: MOUNT_PATH + '/' + NETWORK + '/' + station,
      elevation: Math.round(feature.properties.elevation * 100) / 100,
      imgSrc: MOUNT_PATH + '/data/networks/' + NETWORK + '/' + station +
        '/nafixed/' + station + '.png',
      lat: Math.round(feature.geometry.coordinates[1] * 1000) / 1000,
      lon: Math.round(feature.geometry.coordinates[0] * 1000) / 1000,
      network: NETWORK,
      station: station.toUpperCase(),
      x: feature.properties.x,
      y: feature.properties.y,
      z: feature.properties.z
    };
    if (type === 'network') { // using layer on network page
      popupTemplate = '<div class="popup station">' +
          '<h2>Station {station}</h2>' +
          '<span>({lat}, {lon})</span>' +
          '<ul class="no-style pipelist">' +
            '<li><a href="{baseUri}">Station Details</a></li>';
      if (feature.properties.type === 'campaign') {
        popupTemplate += '<li><a href="{baseUri}/photos">Photos</a></li>';
      }
      popupTemplate += '<li><a href="{baseUri}/logs">Field Logs</a></li>' +
          '</ul>' +
          '<a href="{baseUri}"><img src="{imgSrc}" alt="plot" /></a>' +
        '</div>';
    } else { // using layer on station page
      popupTemplate = '<div class="popup">' +
          '<h2>Station {station}</h2>' +
          '<dl>' +
            '<dt>Lat, Lon (Elevation)</dt><dd>{lat}, {lon} ({elevation}m)</dd>' +
            '<dt>X, Y, Z Position</dt><dd>{x}, {y}, {z}</dd>' +
          '</dl>' +
          '<p><a href="https://www.google.com/maps/dir//{lat},{lon}/data=!4m2!4m1!3e0">Google Map</a></p>' +
        '</div>';
    }
    popup = L.Util.template(popupTemplate, data);

    return popup;
  };

  /**
   * Leaflet GeoJSON option: called on each created feature layer. Useful for
   * attaching events and popups to features.
   *
   * @param feature {Object}
   * @param layer (L.Layer)
   */
  _onEachFeature = function (feature, layer) {
    var label,
        popup;

    label = feature.properties.station.toUpperCase();
    layer.bindLabel(label, {
      pane: 'popupPane'
    });

    if (_station) { // user viewing a Station page
      // Only include popup on selected station
      if (feature.properties.station === _station) {
        popup = _getPopup(feature, 'station');
        layer.bindPopup(popup);
      }
    } else {
      // Include popup on every station
      popup = _getPopup(feature, 'network');
      layer.bindPopup(popup, {
        autoPanPadding: L.point(50, 50),
        minWidth: 256,
      });
    }

    // Store point so its popup can be accessed by openPopup()
    _points[label] = layer;
  };

  /**
   * Leaflet GeoJSON option: used for creating layers for GeoJSON points
   *
   * @param feature {Object}
   * @param latlng {L.LatLng}
   *
   * @return marker {L.Marker}
   */
  _pointToLayer = function (feature, latlng) {
    var color,
        marker,
        selected,
        shape;

    shape = _SHAPES[feature.properties.type];

    if (_station) { // user viewing a Station page
      // Highlight the selected station only
      color = 'grey';
      selected = false;

      if (feature.properties.station === _station) {
        color = 'blue';
        selected = true;

        _bounds.extend(latlng);
      }

      marker = _getMarker({
        color: color,
        latlng: latlng,
        selected: selected,
        shape: shape
      });

      // Add marker to layer
      _this.addLayer(marker);

      // Clicking marker sends user to selected station page
      if (feature.properties.station !== _station) {
        marker.href = feature.properties.station;
        marker.on('click', function () {
          window.location = this.href;
        });
      }
    }
    else {
      // Color stations by days since last update
      color = _getColor(feature.properties.days);
      marker = _getMarker({
        color: color,
        latlng: latlng,
        shape: shape
      });

      // Group stations in separate layers by type
      _this.layers[color].addLayer(marker);
      _this.count[color] ++;

      _bounds.extend(latlng);
    }

    return marker;
  };

  /**
   * Get bounds for station layers
   *
   * @return {L.LatLngBounds}
   */
  _this.getBounds = function () {
    return _bounds;
  };

  /**
   * Hide label for a given station
   *
   * @param station {String}
   */
  _this.hideLabel = function (station) {
    _points[station].hideLabel();
  };

  /**
   * Open popup for a given station
   *
   * @param station {String}
   */
  _this.openPopup = function (station) {
    _points[station].openPopup();
  };

  /**
   * Show label for a given station
   *
   * @param station {String}
   */
  _this.showLabel = function (station) {
    _points[station].showLabel();
  };


  _initialize(options);
  options = null;
  return _this;
};


L.stationsLayer = StationsLayer;

module.exports = StationsLayer;
