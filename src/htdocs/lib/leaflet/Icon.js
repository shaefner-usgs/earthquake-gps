'use strict';

var L = require('leaflet'),
    Util = require('util/Util');

var _ICONS = {};

var _DEFAULTS = {
  iconSize: [20, 30],
  iconAnchor: [10, 14],
  popupAnchor: [0.5, -10],
  labelAnchor: [5, -4]
};

var Icon = function (options) {
  options = Util.extend({}, _DEFAULTS, options);
  return options;
};

/**
 * Factory for creating Leaflet icons
 *
 * @param key {String}
 *        contains 'shape+color' (e.g. 'triangle+red')
 *
 * @return _ICONS[key] {Object}
 *         Leaflet Icon
 */
Icon.getIcon = function (key) {
  var iconUrl,
      iconRetinaUrl,
      options;

  iconUrl = '/monitoring/gps/img/pin-s-' + key + '.png';
  iconRetinaUrl = '/monitoring/gps/img/pin-s-' + key + '-2x.png';

  options = Icon({
    iconUrl: iconUrl,
    iconRetinaUrl: iconRetinaUrl
  });

  // Don't recreate existing icons
  if (!_ICONS[key]) {
    _ICONS[key] = L.icon(options);
  }

  return _ICONS[key];
};

module.exports = Icon;
