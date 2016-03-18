'use strict';

var L = require('leaflet'),
    Util = require('util/Util');

/**
 * Leaflet.RestoreView plugin https://github.com/makinacorpus/Leaflet.RestoreView
 * (with added functionality for remembering selected layers)
 *
 * Usage: map.restoreView(options)
 *
 * @param options {Object}
 *        optional settings
 *        {
 *          baseLayers: {Object <Layer Config>}, // req'd for restoring basemap
 *          id: {String}, // req'd for saving each page's settings separately
 *          overlays: {Object <Layer Config>}, // req'd for restoring overlays,
 *          shareLayers: {Boolean} // share layer settings amongst all pages
 *        }
 *
 * <Layer Config> : http://leafletjs.com/reference.html#control-layers-config
 */

var RestoreViewMixin = {
  restoreView: function (options) {
    var defaultId,
        layerId,
        layers,
        storage,
        view,

        // methods
        _baselayerchange,
        _moveend,
        _overlayadd,
        _overlayremove;

    defaultId = '_global_';

    options = Util.extend({
      baseLayers: null,
      id: defaultId,
      overlays: null,
      shareLayers: false
    }, options);

    storage = window.localStorage || {};
    layers = JSON.parse(storage.mapLayers || '{}');
    view = JSON.parse(storage.mapView || '{}');

    // Store layers with a unique key unless shareLayers is 'on'
    layerId = options.id;
    if (options.shareLayers) {
      layerId = defaultId;
    }
    // Create obj template for storing layers
    if (!layers[layerId]) {
      layers[layerId] = {
        add: [],
        remove: []
      };
    }

    // Invoked when base layer changes
    _baselayerchange = function (e) {
      layers[layerId].base = e.name;

      storage.mapLayers = JSON.stringify(layers);
    };

    // Invoked when map extent change
    _moveend = function () {
      if (!this._loaded) {
        return;  // Never access map bounds if view is not set.
      }
      view[options.id] = {
        lat: this.getCenter().lat,
        lng: this.getCenter().lng,
        zoom: this.getZoom()
      };

      storage.mapView = JSON.stringify(view);
    };

    // Invoked when adding overlays
    _overlayadd = function (e) {
      var add_index = layers[layerId].add.indexOf(e.name),
          remove_index = layers[layerId].remove.indexOf(e.name);
      if (add_index === -1) { // add layer if not already in 'add' list
        layers[layerId].add.push(e.name);
      }
      if (remove_index !== -1) { // remove layer if in 'remove' list
        layers[layerId].remove.splice(remove_index, 1);
      }

      storage.mapLayers = JSON.stringify(layers);
    };

    // Invoked when removing overlays
    _overlayremove = function (e) {
      var add_index = layers[layerId].add.indexOf(e.name),
          remove_index = layers[layerId].remove.indexOf(e.name);
      if (remove_index === -1) { // add layer if not already in 'remove' list
        layers[layerId].remove.push(e.name);
      }
      if (add_index !== -1) { // remove layer if in 'add' list
        layers[layerId].add.splice(add_index, 1);
      }

      storage.mapLayers = JSON.stringify(layers);
    };

    // Save settings: setup listeners which store settings in localStorage
    if (!this.__initRestore) {
      // map extent
      this.on('moveend', _moveend, this);
      // map layers
      this.on('baselayerchange', _baselayerchange, this);
      this.on('overlayadd', _overlayadd, this);
      this.on('overlayremove', _overlayremove, this);

      this.__initRestore = true;
    }

    // Restore settings: map extent and chosen layers
    try {
      this.setView(L.latLng(
        view[options.id].lat,
        view[options.id].lng),
        view[options.id].zoom,
        true
      );

      if (options.baseLayers) {
        var selBaseLayer = layers[layerId].base;
        if (selBaseLayer) {
          var keys = Object.keys(options.baseLayers);
          keys.forEach(function(layer) {
            var baseLayer = options.baseLayers[layer];
            if (layer === selBaseLayer) {
              this.addLayer(baseLayer);
            } else {
              this.removeLayer(baseLayer);
            }
          }, this);
        }
      }

      if (options.overlays) {
        layers[layerId].add.forEach(function(layer) {
          var overlay = options.overlays[layer];
          if (!this.hasLayer(overlay)) {
            this.addLayer(overlay);
          }
        }, this);
        layers[layerId].remove.forEach(function(layer) {
          var overlay = options.overlays[layer];
          if (this.hasLayer(overlay)) {
            this.removeLayer(overlay);
          }
        }, this);
      }

      return true;
    }
    catch (err) {
      console.log(err);
      return false;
    }
  }
};

L.Map.include(RestoreViewMixin);
