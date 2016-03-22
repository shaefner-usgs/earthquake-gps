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
 * @param url {String}
 *        URL of geojson file containing stations
 * @param options {Object}
 *        Leaflet Path options
 *
 * @return {Object}
 *         Leaflet geoJson featureGroup
 */
var StationsLayer = function (url, options) {
  var _icons,

      // methods
      _getColor,
      _getIcon,
      _onEachFeature,
      _pointToLayer;

  options = Util.extend({

  }, options);


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


  _getIcon = function (days, type) {
    var color,
        key,
        options,
        shape;

    color = _getColor(days);
    shape = SHAPES[type];
    key = shape + '+' + color;

    if (typeof(_icons[key]) === 'undefined') { // don't recreate existing icons
      options = {
        iconUrl: '/monitoring/gps/images/pin-s-' + key + '.png',
        iconRetinaUrl: '/monitoring/gps/images/pin-s-' + key + '-2x.png',
        iconSize: [20, 30],
        iconAnchor: [10, 14],
        popupAnchor: [0.5, -10],
        labelAnchor: [5, -4],
      };
      _icons[key] = L.icon(options);
    }

    return _icons[key];
  };


  _onEachFeature = function (/*feature, layer*/) {

  };


  _pointToLayer = function (feature, latlng) {

    var icon = _getIcon(feature.properties.days, feature.properties.type),
        marker = L.marker(latlng, {
          icon: icon
        });

    return marker;
  };

  return L.geoJson.ajax(url, {
    onEachFeature: _onEachFeature,
    pointToLayer: _pointToLayer
  });
};

L.stationsLayer = StationsLayer;

module.exports = StationsLayer;
