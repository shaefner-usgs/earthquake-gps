/* global L, NETWORK, MOUNT_PATH */
'use strict';


var Icon = require('map/Icon'),
    Util = require('util/Util');


var _DEFAULTS,
    _LAYERNAMES,
    _MARKER_DEFAULTS,
    _SHAPES;

_MARKER_DEFAULTS = {
  alt: 'GPS station'
};
_DEFAULTS = {
  data: {},
  markerOptions: _MARKER_DEFAULTS,
  station: null
};
_LAYERNAMES = {
  blue: 'Past 3 days',
  yellow: '4&ndash;7 days ago',
  orange: '8&ndash;14 days ago',
  red: 'Over 14 days ago'
};
_SHAPES = {
  campaign: 'triangle',
  continuous: 'square'
};


/**
 * Factory for Stations overlay, which is used on both Network and Station pages
 *
 * @param options {Object}
 *     {
 *       data: {String} Geojson data
 *       markerOptions: {Object} L.Marker options
 *       station: {Object} selected station on Station page
 *     }
 *
 * @return {L.FeatureGroup}
 *     {
 *       count: {Object}
 *       layers: {Object}
 *       markers: {Object}
 *       names: {Object}
 *       getBounds: {Function},
 *       hideTooltip {Function},
 *       showTooltip {Function}
 *     }
 */
var StationsLayer = function (options) {
  var _this,
      _initialize,

      _bounds,
      _markerOptions,
      _station,

      _getColor,
      _getMarker,
      _getPopup,
      _initLayers,
      _onEachFeature,
      _pointToLayer;


  _this = L.featureGroup();

  _initialize = function (options) {
    options = Util.extend({}, _DEFAULTS, options);
    _markerOptions = Util.extend({}, _MARKER_DEFAULTS, options.markerOptions);

    _bounds = new L.LatLngBounds();

    _this.markers = {};

    if (options.station) { // map on Station page
      // Station user is currently viewing
      _station = options.station;
    } else { // map on Network page
      // Set up individual layers grouped by age
      _initLayers();
    }

    L.geoJson(options.data, {
      onEachFeature: _onEachFeature,
      pointToLayer: _pointToLayer
    });
  };


  /**
   * Get icon color based on the number of days since the last update
   *
   * @param days {Integer}
   *     days since station last updated
   *
   * @return color {String}
   */
  _getColor = function (days) {
    var color = 'red'; //default

    if (days > 14) {
      color = 'red';
    } else if (days > 7) {
      color = 'orange';
    } else if (days > 3) {
      color = 'yellow';
    } else if (days >= 0) {
      color = 'blue';
    } else {
      color = 'grey';
    }

    return color;
  };

  /**
   * Get Leaflet marker
   *
   * @param options {Object}
   *
   * @return L.marker {Object}
   */
  _getMarker = function (options) {
    var key;

    key = options.shape + '+' + options.color;
    _markerOptions.icon = Icon.getIcon(key);

    _markerOptions.zIndexOffset = 0;
    if (options.selected) {
      _markerOptions.zIndexOffset = 1000; // bring to top
    }

    return L.marker(options.latlng, _markerOptions);
  };

  /**
   * Get popup content
   *
   * @param feature {Object}
   * @param mapType {String}
   *
   * @return popup {String}
   */
  _getPopup = function (feature, mapType) {
    var data,
        popup,
        popupTemplate,
        station;

    station = feature.properties.station;
    data = {
      baseUri: MOUNT_PATH + '/' + NETWORK + '/' + station,
      elevation: Math.round(feature.properties.elevation * 100) / 100,
      imgSrc: MOUNT_PATH + '/data/networks/' + NETWORK + '/' + station +
        '/nafixed/' + station + '.png',
      lat: Math.round(feature.geometry.coordinates[1] * 100000) / 100000,
      lon: Math.round(feature.geometry.coordinates[0] * 100000) / 100000,
      network: NETWORK,
      station: station.toUpperCase(),
      x: feature.properties.x,
      y: feature.properties.y,
      z: feature.properties.z
    };
    if (mapType === 'network') { // map on Network page
      popupTemplate = '<div class="popup station">' +
          '<h2>Station {station}</h2>' +
          '<span>({lat}, {lon})</span>' +
          '<ul class="no-style pipelist">' +
            '<li><a href="{baseUri}">Station Details</a></li>';
      if (feature.properties.type === 'campaign') {
        popupTemplate += '<li><a href="{baseUri}/photos">Photos</a></li>';
      }
      popupTemplate += '<li><a href="{baseUri}/logs">Field Logs</a></li>' +
          '</ul>' +
          '<a href="{baseUri}"><img src="{imgSrc}" alt="plot" /></a>' +
        '</div>';
    } else { // map on Station page
      popupTemplate = '<div class="popup">' +
          '<h2>Station {station}</h2>' +
          '<dl>' +
            '<dt>Lat, Lon (Elevation)</dt><dd>{lat}, {lon} ({elevation}m)</dd>' +
            '<dt>X, Y, Z Position</dt><dd>{x}, {y}, {z}</dd>' +
          '</dl>' +
          '<p><a href="https://maps.google.com/?q={lat},{lon}">Google Map</a></p>' +
        '</div>';
    }
    popup = L.Util.template(popupTemplate, data);

    return popup;
  };

  /**
   * Create a layerGroup for each group of stations (classed by age)
   * (also set up a count to keep track of how many stations are in each group)
   */
  _initLayers = function () {
    _this.count = {};
    _this.layers = {};
    _this.names = _LAYERNAMES;

    Object.keys(_LAYERNAMES).forEach(function (key) {
      _this.count[key] = 0;
      _this.layers[key] = L.layerGroup();
      _this.addLayer(_this.layers[key]); // add to featureGroup
    });
  };

  /**
   * Leaflet GeoJSON option: called on each created feature layer. Useful for
   * attaching events and popups to features.
   *
   * @param feature {Object}
   * @param layer (L.Layer)
   */
  _onEachFeature = function (feature, layer) {
    var id,
        popup,
        tooltip;

    id = feature.id;
    tooltip = feature.properties.station.toUpperCase();

    layer.bindTooltip(tooltip);

    if (_station) { // map on Station page
      // Include popup on selected station only
      if (feature.properties.station === _station) {
        popup = _getPopup(feature, 'station');
        layer.bindPopup(popup);
      }
    } else { // map on Network page
      // Include popup on every station
      popup = _getPopup(feature, 'network');
      layer.bindPopup(popup, {
        autoPanPadding: L.point(50, 50),
        minWidth: 256,
      });
    }
  };

  /**
   * Leaflet GeoJSON option: used for creating layers for GeoJSON points
   *
   * @param feature {Object}
   * @param latlng {L.LatLng}
   *
   * @return marker {L.Marker}
   */
  _pointToLayer = function (feature, latlng) {
    var color,
        marker,
        popup,
        selected,
        shape,
        tooltip;

    tooltip = feature.properties.station.toUpperCase();
    shape = _SHAPES[feature.properties.type];

    if (_station) { // map on Station page
      // Highlight the selected station only
      color = 'grey';
      selected = false;

      if (feature.properties.station === _station) {
        color = _getColor(feature.properties.days);
        selected = true;

        _bounds.extend(latlng);
      }

      marker = _getMarker({
        color: color,
        latlng: latlng,
        selected: selected,
        shape: shape
      });
      marker.href = feature.properties.station;

      // Add marker to layer
      _this.addLayer(marker);
    }
    else { // map on Network page
      // Color stations by days since last update
      color = _getColor(feature.properties.days);
      marker = _getMarker({
        color: color,
        latlng: latlng,
        shape: shape
      });
      marker.href = NETWORK + '/' + feature.properties.station;

      // Group stations in separate layers by age
      _this.layers[color].addLayer(marker);
      _this.count[color] ++;

      _bounds.extend(latlng);
    }

    // save ref to marker for popups/tooltips
    _this.markers[tooltip] = marker;

    // Clicking marker sends user to selected station page
    if (feature.properties.station !== _station) {
      marker.on('click', function () {
        popup = marker.getPopup();
        marker.unbindPopup(); // don't show popup when user clicks a marker
        window.location = this.href;
        marker.bindPopup(popup); // put popup back
      });
    }

    return marker;
  };

  /**
   * Get bounds for station layers
   *
   * @return {L.LatLngBounds}
   */
  _this.getBounds = function () {
    return _bounds;
  };

  /**
   * Hide tooltip on map
   *
   * @param tooltip {String}
   *     tooltip to hide
   */
  _this.hideTooltip = function (tooltip) {
    var marker = _this.markers[tooltip];

    if (marker) {
      marker.closeTooltip();
    }
  };

  /**
   * Show tooltip on map
   *
   * @param tooltip {String}
   *     tooltip to show
   */
  _this.showTooltip = function (tooltip) {
    var marker = _this.markers[tooltip];

    if (marker) {
      marker.openTooltip();
    }
  };


  _initialize(options);
  options = null;
  return _this;
};


L.stationsLayer = StationsLayer;

module.exports = StationsLayer;
