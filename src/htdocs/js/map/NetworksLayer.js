/* global L, MOUNT_PATH */
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
      _ids,
      _markerOptions,

      _addListeners,
      _hideLabelPoly,
      _onEachFeature,
      _pointToLayer,
      _showLabelPoly,
      _style;


  _initialize = function () {
    options = Util.extend({}, _DEFAULTS, options);
    _markerOptions = Util.extend({}, _MARKER_DEFAULTS, options.markerOptions);

    _icons = {};
    _ids = [];

    _this = L.geoJson(options.data, {
      onEachFeature: _onEachFeature,
      pointToLayer: _pointToLayer,
      style: _style
    });

    _addListeners();
  };


  /**
   * Add event listeners for newtork buttons to show labels/polygons on map
   */
  _addListeners = function () {
    var button,
        i,
        id,
        onMouseout,
        onMouseover;

    onMouseout = function (e) {
      id = e.target.className.replace(/\D/g, ''); // number portion only
      _hideLabelPoly(id);
    };
    onMouseover = function (e) {
      id = e.target.className.replace(/\D/g, ''); // number portion only
      _showLabelPoly(id);
    };

    button = document.querySelectorAll('.networks a');
    for (i = 0; i < button.length; i ++) {
      button[i].addEventListener('mouseover', onMouseover);
      button[i].addEventListener('mouseout', onMouseout);
    }
  };

  /**
   * Hide label & polygon on map
   *
   * @param id {Int}
   *     optional; id number of feature to hide (hides all if no id is given)
   */
  _hideLabelPoly = function (id) {
    var ids,
        label,
        poly;

    ids = _ids; // all ids
    if (id) {
      ids = [id];
    }

    ids.forEach(function(id) {
      label = document.querySelector('.label' + id);
      poly = document.querySelector('.poly' + id);

      if (label) { // in case map isn't rendered yet
        label.classList.add('off');
      }
      if (poly) {
        poly.classList.add('off');
      }
    });
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
          _showLabelPoly(id);
        },
        mouseout: function () {
          _hideLabelPoly(id);
        }
      }).bindLabel(label, {
        className: labelId + ' off', // labels off by default
        noHide: true,
        pane: 'popupPane'
      });

      _ids.push(id);
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
      window.location = MOUNT_PATH + '/' + this.href;
    });

    return marker;
  };

  /**
   * Show label & polygon on map
   *
   * @param id {Int}
   *     id number of feature to show
   */
  _showLabelPoly = function (id) {
    var label = document.querySelector('.label' + id),
        poly = document.querySelector('.poly' + id);

    _hideLabelPoly();

    if (label) { // in case map isn't rendered yet
      label.classList.remove('off');
    }
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
