'use strict';

var Icon = require('leaflet/Icon'),
    L = require('leaflet'),
    Util = require('util/Util');

require('leaflet.label');

var _DEFAULTS = {
  alt: 'GPS network'
};
var _SHAPES = {
  campaign: 'triangle',
  continuous: 'square'
};

/**
 * Factory for Earthquakes overlay
 *
 * @param data {String}
 *        contents of geojson file containing networks
 * @param options {Object}
 *        Leaflet Marker options
 *
 * @return {Object}
 *         Leaflet GeoJson featureGroup
 */
var NetworksLayer = function (data, options) {
  var _initialize,

      _icons,

      _onEachFeature,
      _pointToLayer;

  _initialize = function () {
    options = Util.extend({}, _DEFAULTS, options);

    _icons = {};
  };

  /**
   * Leaflet GeoJSON option: called on each created feature layer. Useful for
   * attaching events and popups to features.
   */
  _onEachFeature = function (feature, layer) {
    var label = feature.properties.name;

    layer.bindLabel(label, {
      pane: 'popupPane'
    });
  };

  /**
   * Leaflet GeoJSON option: used for creating layers for GeoJSON points
   *
   * @return marker {Object}
   *         Leaflet marker
   */
  _pointToLayer = function (feature, latlng) {
    var key,
        marker,
        shape;

    shape = _SHAPES[feature.properties.type];
    key = shape + '+grey';

    options.icon = Icon.getIcon(key);

    marker = L.marker(latlng, options);

    return marker;
  };

  _initialize();

  return L.geoJson(data, {
    onEachFeature: _onEachFeature,
    pointToLayer: _pointToLayer
  });
};

L.networksLayer = NetworksLayer;

module.exports = NetworksLayer;
