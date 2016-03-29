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
  return L.icon(options);
};

/**
 * Create Leaflet icons
 *
 * @param key {String}
 *        contains 'shape+color' (e.g. 'triangle+red')
 *
 * @return _ICONS[key] {Object}
 *         Leaflet Icon
 */
Icon.getIcon = function (key, options) {
  options = Util.extend({}, _DEFAULTS, options);

  options.iconRetinaUrl = '/monitoring/gps/img/pin-s-' + key + '-2x.png';
  options.iconUrl = '/monitoring/gps/img/pin-s-' + key + '.png';

  // Don't recreate existing icons
  if (!_ICONS[key]) {
    _ICONS[key] = Icon(options);
  }

  return _ICONS[key];
};

module.exports = Icon;
