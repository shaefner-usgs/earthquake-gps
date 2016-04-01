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
    var i,
        id,
        listener,
        networkLinks;

    options = Util.extend({}, _DEFAULTS, options);
    _icons = {};

    listener = function (e) {
      id = e.target.className.replace('link', ''); // number portion only

      if (e.type === 'mouseover') {
        _show(id);
      } else if (e.type === 'mouseout') {
        _hide(id);
      }
    };

    networkLinks = document.querySelectorAll('.networks a');
    for (i = 0; i < networkLinks.length; i ++) {
      networkLinks[i].addEventListener('mouseover', listener);
      networkLinks[i].addEventListener('mouseout', listener);
    }
  };

  _hide = function (id) {
    var label = document.querySelector('.label' + id),
        poly = document.querySelector('.poly' + id);

    label.classList.add('off');
    if (poly) {
      poly.classList.add('off');
    }
  };

  _show = function (id) {
    var label = document.querySelector('.label' + id),
        poly = document.querySelector('.poly' + id);

    label.classList.remove('off');
    if (poly) {
      poly.classList.remove('off');
    }
  };

  _style = function (feature) {
    if (feature.geometry.type === 'Polygon') {
      return {
        className: feature.id + ' off', // polygons off by default
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
        label,
        labelId;

    if (feature.geometry.type === 'Point') {
      id = feature.id.replace('point', ''); // number portion only
      label = feature.properties.name;
      labelId = 'label' + id;

      layer.on({
        mouseover: function () {
          _show(id);
        },
        mouseout: function () {
          _hide(id);
        }
      }).bindLabel(label, {
        className: labelId + ' off', // labels off by default
        noHide: true,
        pane: 'popupPane'
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
