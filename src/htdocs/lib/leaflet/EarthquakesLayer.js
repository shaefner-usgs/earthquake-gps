'use strict';

var L = require('leaflet'),
    Util = require('util/Util');

require('leaflet-ajax');

/**
 * Factory for Earthquakes overlay
 *
 * @param url {String}
 *        URL of geojson file containing eqs
 * @param options {Object}
 *        Leaflet Path options
 *
 * @return {Object}
 *         Leaflet geoJson featureGroup
 */
var EarthquakesLayer = function (url, options) {
  var _colors,
      // methods
      _onEachFeature,
      _pointToLayer;

  options = Util.extend({
    weight: 1,
    opacity: 0.9,
    fillOpacity: 0.9,
    color: '#000'
  }, options);

  _colors = {
    pasthour: '#f00',
    pastday: '#f90',
    pastweek: '#ff0',
    pastmonth: '#ffb'
  };

  _onEachFeature = function (feature, layer) {
    var html,
        link,
        props;

    props = feature.properties;
    link = 'http://earthquake.usgs.gov/earthquakes/eventpage/' + feature.id;
    html = '<div class="popup eq">' +
        '<h1>M' + props.mag + ', ' + props.place + '</h1>' +
        '<time>' + props.datetime + '</time>' +
        '<p><a href="' + link + '" target="_blank">Details</a> &raquo;</p>' +
      '</div>';

    layer.bindPopup(html, {maxWidth: '265'});
  };

  _pointToLayer = function (feature, latlng) {
    var fillColor,
        props,
        radius;

    props = feature.properties;
    fillColor = _colors[props.age];
    radius = 3 * parseInt(Math.pow(10, (0.11 * props.mag)), 10);

    options.fillColor = fillColor;
    options.radius = radius;

    return L.circleMarker(latlng, options);
  };

  return L.geoJson.ajax(url, {
    onEachFeature: _onEachFeature,
    pointToLayer: _pointToLayer
  });

};

L.earthquakesLayer = EarthquakesLayer;

module.exports = EarthquakesLayer;
