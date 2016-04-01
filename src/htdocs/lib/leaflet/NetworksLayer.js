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

      _hide,
      _onEachFeature,
      _pointToLayer,
      _show,
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

  _hide = function (id) {
    var label = document.querySelector('.point' + id),
        poly = document.querySelector('.poly' + id);

    if (label) {
      label.classList.add('off');
    }
    if (poly) {
      poly.classList.add('off');
    }
  };

  _show = function (id) {
    var label = document.querySelector('.point' + id),
        poly = document.querySelector('.poly' + id);

    if (label) {
      label.classList.remove('off');
    }
    if (poly) {
      poly.classList.remove('off');
    }
  };

  _style = function (feature) {
    if (feature.geometry.type === 'Polygon') {
      return {
        className: feature.id + ' off',
        weight: 2
      };
    }
  };

  /**
   * Leaflet GeoJSON option: called on each created feature layer. Useful for
   * attaching events and popups to features.
   */
  _onEachFeature = function (feature, layer) {
    var id,
        label;

    if (feature.geometry.type === 'Point') {
      label = feature.properties.name;
      layer.bindLabel(label, {
        pane: 'popupPane',
        className: feature.id + ' off',
        noHide: false
      });

      id = feature.id.replace('point', '');
      layer.on({
        mouseover: function () {
          _show(id);
        },
        mouseout: function () {
          _hide(id);
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
