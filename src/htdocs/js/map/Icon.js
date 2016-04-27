/* global L, MOUNT_PATH */
'use strict';


var Util = require('util/Util');


var _DEFAULTS,
    _ICONS;

_DEFAULTS = {
  iconSize: [20, 30],
  iconAnchor: [10, 14],
  popupAnchor: [0.5, -10],
  labelAnchor: [5, -4]
};
_ICONS = {};

var Icon = function (options) {
  return L.icon(options);
};


/**
 * Create Leaflet icons
 *
 * @param key {String}
 *     contains 'shape+color' (e.g. 'triangle+red')
 *
 * @param options {Object}
 *     Leaflet Icon options (optional)
 *
 * @return _ICONS[key] {L.Icon}
 */
Icon.getIcon = function (key, options) {
  // Don't recreate existing icons
  if (!_ICONS.hasOwnProperty('key')) {
    options = Util.extend({}, _DEFAULTS, options);

    options.iconRetinaUrl = MOUNT_PATH + '/img/pin-s-' + key + '-2x.png';
    options.iconUrl = MOUNT_PATH + '/img/pin-s-' + key + '.png';

    _ICONS[key] = Icon(options);
  }

  return _ICONS[key];
};


module.exports = Icon;
