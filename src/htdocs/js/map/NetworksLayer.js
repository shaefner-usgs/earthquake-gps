/* global L */
'use strict';


var Icon = require('map/Icon'),
    Util = require('util/Util');

require('leaflet.label');


var _DEFAULTS,
    _MARKER_DEFAULTS,
    _SHAPES;

_MARKER_DEFAULTS = {
  alt: 'GPS network'
};
_DEFAULTS = {
  data: {},
  markerOptions: _MARKER_DEFAULTS
};
_SHAPES = {
  campaign: 'triangle',
  continuous: 'square'
};


/**
 * Factory for Networks overlay
 *
 * @param data {String}
 *     Geojson data
 * @param options {Object}
 *     {
 *       data: {String} Geojson data
 *       markerOptions: {Object} L.Marker options
 *     }
 *
 * @return {L.FeatureGroup}
 */
var NetworksLayer = function (options) {
  var _this,
      _initialize,

      _icons,
      _markerOptions,

      _attachEvents,
      _hide,
      _onEachFeature,
      _pointToLayer,
      _show,
      _style;


  _initialize = function () {
    options = Util.extend({}, _DEFAULTS, options);
    _markerOptions = Util.extend({}, _MARKER_DEFAULTS, options.markerOptions);

    _icons = {};

    _this = L.geoJson(options.data, {
      onEachFeature: _onEachFeature,
      pointToLayer: _pointToLayer,
      style: _style
    });

    _attachEvents();
  };


  /**
   * Attach mouseover events to list of networks below map
   */
  _attachEvents = function () {
    var i,
        id,
        mouseout,
        mouseover,
        networkLinks;

    mouseout = function (e) {
      id = e.target.className.replace('link', ''); // number portion only
      _hide(id);
    };
    mouseover = function (e) {
      id = e.target.className.replace('link', ''); // number portion only
      _show(id);
    };

    networkLinks = document.querySelectorAll('.networks a');
    for (i = 0; i < networkLinks.length; i ++) {
      networkLinks[i].addEventListener('mouseover', mouseover);
      networkLinks[i].addEventListener('mouseout', mouseout);
    }
  };

  /**
   * Hide label & polygon
   *
   * @param id {Int}
   *     id number of features to hide
   */
  _hide = function (id) {
    var label = document.querySelector('.label' + id),
        poly = document.querySelector('.poly' + id);

    label.classList.add('off');
    if (poly) {
      poly.classList.add('off');
    }
  };

  /**
   * Leaflet GeoJSON option: called on each created feature layer. Useful for
   * attaching events and popups to features.
   *
   * @param feature {Object}
   * @param layer (L.Layer)
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
   * @param feature {Object}
   * @param latlng {L.LatLng}
   *
   * @return marker {L.Marker}
   */
  _pointToLayer = function (feature, latlng) {
    var key,
        marker,
        shape;

    shape = _SHAPES[feature.properties.type];
    key = shape + '+grey';
    _markerOptions.icon = Icon.getIcon(key);
    marker = L.marker(latlng, _markerOptions);

    // Clicking marker sends user to selected network page
    marker.href = feature.properties.name;
    marker.on('click', function () {
      window.location = './' + this.href + '/';
    });

    return marker;
  };

  /**
   * Show label & polygon
   *
   * @param id {Int}
   *     id number of features to show
   */
  _show = function (id) {
    var label = document.querySelector('.label' + id),
        poly = document.querySelector('.poly' + id);

    label.classList.remove('off');
    if (poly) {
      poly.classList.remove('off');
    }
  };

  /**
   * Leaflet GeoJSON option: used to get style options for vector layers
   *
   * @param feature {Object}
   *
   * @return {Object}
   */
  _style = function (feature) {
    if (feature.geometry.type === 'Polygon') {
      return {
        className: feature.id + ' off', // polygons off by default
        weight: 2
      };
    }
  };


  _initialize(options);
  options = null;
  return _this;
};


L.networksLayer = NetworksLayer;

module.exports = NetworksLayer;
