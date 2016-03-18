'use strict';

var L = require('leaflet'),
    Util = require('util/Util');

require('leaflet-ajax');

/**
 * Factory for Stations overlay
 *
 * @param url {String}
 *        URL of geojson file containing stations
 * @param options {Object}
 *        Leaflet Path options
 *
 * @return {Object}
 *         Leaflet geoJson featureGroup
 */
var StationsLayer = function (url, options) {
  var
      // methods
      _onEachFeature,
      _pointToLayer;

  options = Util.extend({

  }, options);


  _onEachFeature = function (/*feature, layer*/) {

  };

  _pointToLayer = function (feature, latlng) {
    console.log(feature);
    return L.marker(latlng);
  };

  return L.geoJson.ajax(url, {
    onEachFeature: _onEachFeature,
    pointToLayer: _pointToLayer
  });
};

L.stationsLayer = StationsLayer;

module.exports = StationsLayer;
