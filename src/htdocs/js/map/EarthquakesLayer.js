/* global L */
'use strict';


var Util = require('util/Util');

require('leaflet.label');


var _COLORS,
    _DEFAULTS;

_COLORS = {
  pasthour: '#f00',
  pastday: '#f90',
  pastweek: '#ff0',
  pastmonth: '#ffb'
};
_DEFAULTS = {
  weight: 1,
  opacity: 0.9,
  fillOpacity: 0.9,
  color: '#000'
};


/**
 * Factory for Earthquakes overlay
 *
 * @param data {Object}
 *     Geojson data
 * @param options {Object}
 *     L.Path options
 *
 * @return {L.FeatureGroup}
 */
var EarthquakesLayer = function (data, options) {
  var _initialize,

      _onEachFeature,
      _pointToLayer;


  _initialize = function () {
    options = Util.extend({}, _DEFAULTS, options);
  };


  /**
   * Leaflet GeoJSON option: called on each created feature layer. Useful for
   * attaching events and popups to features.
   *
   * @param feature {Object}
   * @param layer (L.Layer)
   */
  _onEachFeature = function (feature, layer) {
    var data,
        label,
        labelTemplate,
        link,
        popup,
        popupTemplate;

    link = 'http://earthquake.usgs.gov/earthquakes/eventpage/' + feature.id;
    data = {
      mag: feature.properties.mag,
      datetime: feature.properties.datetime,
      place: feature.properties.place,
      link: link
    };

    labelTemplate = 'M{mag} - {datetime}';
    label = L.Util.template(labelTemplate, data);

    popupTemplate = '<div class="popup eq">' +
        '<h1>M{mag}, {place}</h1>' +
        '<time>{datetime}</time>' +
        '<p><a href="{link}" target="_blank">Details</a> &raquo;</p>' +
      '</div>';
    popup = L.Util.template(popupTemplate, data);

    layer.bindPopup(popup, {maxWidth: '265'}).bindLabel(label);
  };

  /**
   * Leaflet GeoJSON option: used for creating layers for GeoJSON points
   *
   * @param feature {Object}
   * @param latlng {L.LatLng}
   *
   * @return marker {L.CircleMarker}
   */
  _pointToLayer = function (feature, latlng) {
    var fillColor,
        props,
        radius;

    props = feature.properties;
    fillColor = _COLORS[props.age];
    radius = 3 * parseInt(Math.pow(10, (0.11 * props.mag)), 10);

    options.fillColor = fillColor;
    options.radius = radius;

    return L.circleMarker(latlng, options);
  };

  _initialize();

  return L.geoJson(data, {
    onEachFeature: _onEachFeature,
    pointToLayer: _pointToLayer
  });
};


L.earthquakesLayer = EarthquakesLayer;

module.exports = EarthquakesLayer;
