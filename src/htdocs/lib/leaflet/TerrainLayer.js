'use strict';

var L = require('leaflet'),
    Util = require('util/Util');

/**
 * Factory for ESRI Terrain base layer
 *
 * @param options {Object}
 *        Leaflet tileLayer options
 *
 * @return {Object}
 *         Leaflet tileLayer
 */
var TerrainLayer = function (options) {
  options = Util.extend({
    attribution: 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ, TomTom, ' +
      'Intermap, iPC, USGS, FAO, NPS, NRCAN, GeoBase, Kadaster NL, Ordnance ' +
      'Survey, Esri Japan, METI, Esri China (Hong Kong), and the GIS User Community',
    detectRetina: false
  }, options);

  return L.tileLayer(
    'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}',
    options
  );
};

L.terrainLayer = TerrainLayer;

module.exports = TerrainLayer;
