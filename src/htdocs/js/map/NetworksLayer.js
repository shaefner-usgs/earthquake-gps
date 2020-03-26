/* global L, MOUNT_PATH */
'use strict';


var Icon = require('map/Icon'),
    Util = require('util/Util');


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
 *     {
 *       hideOverlays: {Function}
 *       showOverlays: {Function}
 *     }
 */
var NetworksLayer = function (options) {
  var _this,
      _initialize,

      _ids,
      _markerOptions,
      _markers,

      _onEachFeature,
      _pointToLayer,
      _style;


  _this = L.featureGroup();

  _initialize = function () {
    options = Util.extend({}, _DEFAULTS, options);
    _markerOptions = Util.extend({}, _MARKER_DEFAULTS, options.markerOptions);

    _ids = [];
    _markers = {};

    L.geoJson(options.data, {
      onEachFeature: _onEachFeature,
      pointToLayer: _pointToLayer,
      style: _style
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
    var id;

    if (feature.geometry.type === 'Point') {
      id = feature.id.replace('point', ''); // number portion only

      layer.on({
        mouseover: function () {
          _this.showOverlays(id);
        },
        mouseout: function () {
          _this.hideOverlays(id);
        }
      }).bindTooltip(feature.properties.name);

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
    var id,
        key,
        marker,
        shape;

    id = feature.id.replace('point', ''); // number portion only
    shape = _SHAPES[feature.properties.type];
    key = shape + '+grey';
    _markerOptions.icon = Icon.getIcon(key);
    marker = L.marker(latlng, _markerOptions);
    _markers[id] = marker; // save ref to marker for tooltips

    // Clicking marker sends user to selected network page
    marker.href = feature.properties.name;
    marker.on('click', function () {
      window.location = MOUNT_PATH + '/' + this.href;
    });

    // Add marker to layer
    _this.addLayer(marker);

    return marker;
  };

  /**
   * Leaflet GeoJSON option: used to get style options for vector layers
   *
   * @param feature {Object}
   */
  _style = function (feature) {
    var latlng,
        latlngs,
        polygon;

    if (feature.geometry.type === 'Polygon') {
      // Flip order of lat, lng values
      latlngs = [];
      feature.geometry.coordinates[0].forEach(function (pair) {
        latlng = [pair[1], pair[0]];
        latlngs.push(latlng);
      });

      polygon = L.polygon(latlngs, {
        className: feature.id + ' off', // polygons off by default
        weight: 2
      });

      // Add polygon to layer
      _this.addLayer(polygon);
    }
  };

  /**
   * Hide marker's polygon, tooltip on map
   *
   * @param id {Integer}
   *     id number of feature to hide
   */
  _this.hideOverlays = function (id) {
    var marker,
        poly;

    marker = _markers[id];
    poly = document.querySelector('.poly' + id);

    if (marker) {
      marker.closeTooltip();
    }
    if (poly) {
      poly.classList.add('off');
    }
  };

  /**
   * Show marker's polygon, tooltip on map
   *
   * @param id {Integer}
   *     id number of feature to show
   */
  _this.showOverlays = function (id) {
    var marker,
        poly;

    marker = _markers[id];
    poly = document.querySelector('.poly' + id);

    if (marker) {
      marker.openTooltip();
    }
    if (poly) {
      poly.classList.remove('off');
    }
  };


  _initialize(options);
  options = null;
  return _this;
};


L.networksLayer = NetworksLayer;

module.exports = NetworksLayer;
