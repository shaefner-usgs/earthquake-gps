'use strict';

var L = require('leaflet');

var Icon = {};

var _icons = {};

/**
 * Factory for creating Leaflet icons
 *
 * @param key {String}
 *        contains 'shape+color' (e.g. 'triangle+red')
 *
 * @return _icons[key] {Object}
 *         Leaflet Icon
 */
Icon.create = function (key) {
  // Don't recreate existing icons
  if (!_icons[key]) {
    var icon_options = {
      iconSize: [20, 30],
      iconAnchor: [10, 14],
      popupAnchor: [0.5, -10],
      labelAnchor: [5, -4],
      iconUrl: '/monitoring/gps/img/pin-s-' + key + '.png',
      iconRetinaUrl: '/monitoring/gps/img/pin-s-' + key + '-2x.png'
    };

    _icons[key] = L.icon(icon_options);
  }

  return _icons[key];
};

module.exports = Icon;
