'use strict';

var L = require('leaflet'),
    Util = require('util/Util');

require('leaflet-ajax');

var SHAPES = {
  continuous: 'square',
  campaign: 'triangle'
};

/**
 * Factory for Stations overlay
 *
 * @param data {String}
 *        contents of geojson file containing stations
 * @param options {Object}
 *        Leaflet Marker options
 *
 * @return {Object}
 *         Leaflet GeoJson featureGroup
 */
var StationsLayer = function (data, options) {
  var _icons,

      // methods
      _getColor,
      _getIcon,
      _onEachFeature,
      _pointToLayer;

  options = Util.extend({
    alt: 'GPS station'
  }, options);

  _icons = {};

  /**
   * Get icon color
   *
   * @param days {Integer}
   *        days since station last updated
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
   * Get Leaflet icon for station
   *
   * @param days {Integer}
   * @param type {String}
   *        'campaign' or 'continuous'
   *
   * @return _icons[key] {Object}
   *         Leaflet Icon
   */
  _getIcon = function (days, type) {
    var color,
        icon_options,
        key,
        shape;

    color = _getColor(days);
    shape = SHAPES[type];
    key = shape + '+' + color;

    // Don't recreate existing icons
    if (typeof(_icons[key]) === 'undefined') {
      icon_options = {
        iconSize: [20, 30],
        iconAnchor: [10, 14],
        popupAnchor: [0.5, -10],
        labelAnchor: [5, -4],
        iconUrl: '/monitoring/gps/img/pin-s-' + key + '.png',
        iconRetinaUrl: '/monitoring/gps/img/pin-s-' + key + '-2x.png'
      };

      _icons[key] = L.icon(icon_options);
    }

    return _icons[key];
  };

  /**
   * Leaflet GeoJSON option: called on each created feature layer. Useful for
   * attaching events and popups to features.
   */
  _onEachFeature = function (/*feature, layer*/) {

  };

  /**
   * Leaflet GeoJSON option: used for creating layers for GeoJSON points
   *
   * @return marker {Object}
   *         Leaflet marker
   */
  _pointToLayer = function (feature, latlng) {
    options.icon = _getIcon(feature.properties.days, feature.properties.type);

    return L.marker(latlng, options);
  };

  return L.geoJson(data, {
    onEachFeature: _onEachFeature,
    pointToLayer: _pointToLayer
  });
};

L.stationsLayer = StationsLayer;

module.exports = StationsLayer;
