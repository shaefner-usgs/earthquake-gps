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

      _hidePoly,
      _onEachFeature,
      _pointToLayer,
      _showPoly,
      _style;

  _initialize = function () {
    options = Util.extend({}, _DEFAULTS, options);

    _icons = {};

    // .on('mouseover', function () {
    //   _showPoly(this.getAttribute('class'));
    // })
    // .on('mouseout', function () {
    //   _hidePoly(this.getAttribute('class'));
    // });
  };

  _hidePoly = function (polyId) {
    var el = document.querySelector('.' + polyId);
    el.classList.add('off');
  };

  _showPoly = function (polyId) {
    var el = document.querySelector('.' + polyId);
    el.classList.remove('off');
  };

  _style = function (feature) {
    var className;

    className = feature.id;
    if (feature.geometry.type === 'Polygon') {
      return {
        className: className + ' off',
        weight: 2
      };
    }
  };

  /**
   * Leaflet GeoJSON option: called on each created feature layer. Useful for
   * attaching events and popups to features.
   */
  _onEachFeature = function (feature, layer) {
    var label,
        polyId;

    if (feature.geometry.type === 'Point') {
      label = feature.properties.name;
      layer.bindLabel(label, {
        pane: 'popupPane'
      });

      polyId = feature.id.replace('point', 'poly');

      layer.on({
        mouseover: function () {
          _showPoly(polyId);
        },
        mouseout: function () {
          _hidePoly(polyId);
        }
      });


    }
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
    pointToLayer: _pointToLayer,
    style: _style
  });
};

L.networksLayer = NetworksLayer;

module.exports = NetworksLayer;
