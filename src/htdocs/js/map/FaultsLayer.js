/* global L */
'use strict';


require('map/Utfgrid');
require('map/MouseOverLayer');


/**
 * Factory for Faults overlay
 *
 * @return {Object}
 *         Leaflet layerGroup
 */
var FaultsLayer = function () {
  var _faults,
      _plates,
      _urlPrefix;

  _urlPrefix = 'http://escweb.wr.usgs.gov/template/functions/tiles/';

  // L.mouseOverLayer is an extended L.layerGroup class that adds utfGrid mouseovers
  _faults = L.mouseOverLayer({
    dataUrl: _urlPrefix + 'faults/{z}/{x}/{y}.grid.json?callback={cb}',
    tileOpts: {
      minZoom: 6,
      maxZoom: 17
    },
    tileUrl: _urlPrefix + 'faults/{z}/{x}/{y}.png',
    tiptext: '{NAME}'
  });

  _plates = L.mouseOverLayer({
    dataUrl: _urlPrefix + 'plates/{z}/{x}/{y}.grid.json?callback={cb}',
    tileOpts: {
      minZoom: 0,
      maxZoom: 5
    },
    tileUrl: _urlPrefix + 'plates/{z}/{x}/{y}.png',
    tiptext: '{Name}'
  });

  return L.layerGroup([_plates, _faults]);
};


L.faultsLayer = FaultsLayer;

module.exports = FaultsLayer;
