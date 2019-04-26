/* global L, NETWORK, MOUNT_PATH */
'use strict';


var Icon = require('map/Icon'),
    Util = require('util/Util');

require('leaflet.label');


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
 * Factory for Stations overlay
 *
 * @param options {Object}
 *     {
 *       data: {String} Geojson data
 *       markerOptions: {Object} L.Marker options
 *     }
 *
 * @return {L.FeatureGroup}
 *     {
 *       count: {Object}
 *       layers: {Object}
 *       markers: {Object}
 *       name: {Object}
 *       getBounds: {Function}
 *     }
 */
var StationsLayer = function (options) {
  var _this,
      _initialize,

      _bounds,
      _icons,
      _ids,
      _markerOptions,
      _station,

      _addListeners,
      _addPopupIcons,
      _getColor,
      _getId,
      _getMarker,
      _getPopup,
      _hideLabel,
      _initLayers,
      _onEachFeature,
      _pointToLayer,
      _showLabel;


  _this = L.featureGroup();

  _initialize = function (options) {
    options = Util.extend({}, _DEFAULTS, options);
    _markerOptions = Util.extend({}, _MARKER_DEFAULTS, options.markerOptions);

    _bounds = new L.LatLngBounds();
    _icons = {};
    _ids = [];

    _this.markers = {};

    if (options.station) { // map on station page
      // Station user is currently viewing
      _station = options.station;
    } else { // map on network page
      // Set up individual layers grouped by age
      _initLayers();
    }

    L.geoJson(options.data, {
      onEachFeature: _onEachFeature,
      pointToLayer: _pointToLayer
    });

    _addPopupIcons();
    _addListeners();
  };


  /**
   * Add event listeners for station buttons to show labels/popups on map
   */
  _addListeners = function () {
    var button,
        buttons,
        i,
        icon,
        onClick,
        onMouseout,
        onMouseover;

    onClick = function (e) {
      var button,
          station;

      button = e.target.parentNode.querySelector('.button');
      station = button.textContent.match(/\w+/); // ignore '*' (high RMS value)

      _this.markers[station[0].toUpperCase()].openPopup();
      e.preventDefault();
    };
    onMouseout = function (e) {
      _hideLabel(_getId(e.target));
    };
    onMouseover = function (e) {
      _showLabel(_getId(e.target));
    };

    buttons = document.querySelectorAll('.stations a:first-child');
    for (i = 0; i < buttons.length; i ++) {
      button = buttons[i];
      button.addEventListener('mouseover', onMouseover);
      button.addEventListener('mouseout', onMouseout);

      icon = button.nextElementSibling;
      icon.addEventListener('mouseover', onMouseover);
      icon.addEventListener('click', onClick);
    }
  };

  /**
   * Add popup icons to station buttons
   */
  _addPopupIcons = function () {
    var buttons,
        i,
        icon;

    buttons = document.querySelectorAll('.stations li');
    for (i = 0; i < buttons.length; i ++) {
      icon = document.createElement('a');
      icon.setAttribute('href', '#');
      icon.setAttribute('class', 'icon');
      icon.setAttribute('title', 'View station popup');

      buttons[i].appendChild(icon);
    }
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
   * Get id of feature (station), which is attached to the button element
   *
   * @param el {Element}
   *     button element or its sibling
   */
  _getId = function (el) {
    var id;

    if (el.classList.contains('icon')) {
      el = el.parentNode.querySelector('.button');
    }

    id = el.className.replace(/\D/g, ''); // number portion only

    return id;
  };

  /**
   * Get Leaflet marker
   *
   * @param options {Object}
   *
   * @return L.marker
   */
  _getMarker = function (options) {
    var key;

    key = options.shape + '+' + options.color;
    _markerOptions.icon = Icon.getIcon(key);

    _markerOptions.zIndexOffset = 0;
    if (options.selected) {
      _markerOptions.zIndexOffset = 1000;
    }

    return L.marker(options.latlng, _markerOptions);
  };

  /**
   * Get popup content
   *
   * @param feature {Object}
   *
   * @return popup {String}
   */
  _getPopup = function (feature, type) {
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
    if (type === 'network') { // using layer on network page
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
    } else { // using layer on station page
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
   * Hide label on map
   *
   * @param id {Int}
   *     optional; id number of feature to hide (hides all if no id is given)
   */
  _hideLabel = function (id) {
    var ids,
        label;

    ids = _ids; // all ids
    if (id) {
      ids = [id];
    }

    ids.forEach(function(id) {
      label = document.querySelector('.label' + id);

      if (label) { // in case map isn't rendered yet
        label.classList.add('off');
      }
    });
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
        label,
        labelId,
        popup;

    id = feature.id;
    label = feature.properties.station.toUpperCase();
    labelId = 'label' + id;

    layer.on({
      mouseover: function () {
        _showLabel(id);
      },
      mouseout: function () {
        _hideLabel(id);
      }
    }).bindLabel(label, {
      className: labelId + ' off', // labels off by default
      noHide: true,
      pane: 'popupPane'
    });

    _ids.push(id);

    if (_station) { // user viewing a Station page
      // Only include popup on selected station
      if (feature.properties.station === _station) {
        popup = _getPopup(feature, 'station');
        layer.bindPopup(popup);
      }
    } else {
      // Include popup on every station
      popup = _getPopup(feature, 'network');
      layer.bindPopup(popup, {
        autoPanPadding: L.point(50, 50),
        minWidth: 256,
      });
    }

    // Store point so its popup can be accessed by openPopup()
    _this.markers[label] = layer;
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
        shape;

    shape = _SHAPES[feature.properties.type];

    if (_station) { // user viewing a Station page
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
    else { // user viewing a Network page
      // Color stations by days since last update
      color = _getColor(feature.properties.days);
      marker = _getMarker({
        color: color,
        latlng: latlng,
        shape: shape
      });
      marker.href = NETWORK + '/' + feature.properties.station;

      // Group stations in separate layers by type
      _this.layers[color].addLayer(marker);
      _this.count[color] ++;

      _bounds.extend(latlng);
    }

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
   * Show label on map
   *
   * @param id {Int}
   *     id number of feature to show
   */
  _showLabel = function (id) {
    var label = document.querySelector('.label' + id);

    if (label) { // in case map isn't rendered yet
      _hideLabel();
      label.classList.remove('off');
    }
  };

  /**
   * Get bounds for station layers
   *
   * @return {L.LatLngBounds}
   */
  _this.getBounds = function () {
    return _bounds;
  };


  _initialize(options);
  options = null;
  return _this;
};


L.stationsLayer = StationsLayer;

module.exports = StationsLayer;
