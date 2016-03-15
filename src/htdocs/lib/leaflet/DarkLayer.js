'use strict';

var L = require('leaflet'),
    Util = require('util/Util');

/**
 * Factory for Dark base layer
 *
 * @param options {Object}
 *        Leaflet tileLayer options
 *
 * @return {Object}
 *         Leaflet tileLayer
 */
var DarkLayer = function (options) {
  options = Util.extend({
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">' +
      'OpenStreetMap</a> &copy; <a href="http://cartodb.com/attributions">' +
      'CartoDB</a>',
    detectRetina: false,
    maxZoom: 19,
    subdomains: 'abcd'
  }, options);

  return L.tileLayer(
    'http://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png',
    options
  );
};

L.darkLayer = DarkLayer;

module.exports = DarkLayer;
