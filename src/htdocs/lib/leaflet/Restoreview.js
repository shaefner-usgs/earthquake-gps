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
        layersId,
        layers,
        storage,
        view,
        viewId,

        // methods
        _baselayerchange,
        _moveend,
        _overlayadd,
        _overlayremove;

    defaultId = '_global_'; // will be shared amongst all pages if not set

    options = Util.extend({
      baseLayers: null,
      id: defaultId,
      overlays: null,
      shareLayers: false
    }, options);

    storage = window.localStorage || {};
    layers = JSON.parse(storage.mapLayers || '{}');
    view = JSON.parse(storage.mapView || '{}');

    // Store settings with unique id from user (or defaultId if not supplied)
    // If shareLayers is 'on', then always use (shared) defaultId for layers
    layersId = options.id;
    if (options.shareLayers) {
      layersId = defaultId;
    }
    viewId = options.id;

    // Create obj template for storing layers
    if (!layers[layersId]) {
      layers[layersId] = {
        add: [],
        remove: []
      };
    }

    // Invoked when base layer changes
    _baselayerchange = function (e) {
      layers[layersId].base = e.name;

      storage.mapLayers = JSON.stringify(layers);
    };

    // Invoked when map extent change
    _moveend = function () {
      if (!this._loaded) {
        return;  // Never access map bounds if view is not set.
      }
      view[viewId] = {
        lat: this.getCenter().lat,
        lng: this.getCenter().lng,
        zoom: this.getZoom()
      };

      storage.mapView = JSON.stringify(view);
    };

    // Invoked when adding overlays
    _overlayadd = function (e) {
      var add_index = layers[layersId].add.indexOf(e.name),
          remove_index = layers[layersId].remove.indexOf(e.name);
      if (add_index === -1) { // add layer if not already in 'add' list
        layers[layersId].add.push(e.name);
      }
      if (remove_index !== -1) { // remove layer if in 'remove' list
        layers[layersId].remove.splice(remove_index, 1);
      }

      storage.mapLayers = JSON.stringify(layers);
    };

    // Invoked when removing overlays
    _overlayremove = function (e) {
      var add_index = layers[layersId].add.indexOf(e.name),
          remove_index = layers[layersId].remove.indexOf(e.name);
      if (remove_index === -1) { // add layer if not already in 'remove' list
        layers[layersId].remove.push(e.name);
      }
      if (add_index !== -1) { // remove layer if in 'add' list
        layers[layersId].add.splice(add_index, 1);
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
      if (view[viewId]) {
        this.setView(
          L.latLng(view[viewId].lat, view[viewId].lng),
          view[viewId].zoom,
          true
        );
      }

      if (layers[layersId]) {
        var selBaseLayer = layers[layersId].base;
        if (selBaseLayer) {
          var keys = Object.keys(options.baseLayers);
          keys.forEach(function(layerName) {
            var baseLayer = options.baseLayers[layerName];
            if (layerName === selBaseLayer) {
              this.addLayer(baseLayer);
            } else {
              this.removeLayer(baseLayer);
            }
          }, this);
        }

        layers[layersId].add.forEach(function(layerName) {
          var overlay = options.overlays[layerName];
          if (overlay && !this.hasLayer(overlay)) {
            this.addLayer(overlay);
          }
        }, this);

        layers[layersId].remove.forEach(function(layerName) {
          var overlay = options.overlays[layerName];
          if (overlay && this.hasLayer(overlay)) {
            this.removeLayer(overlay);
          }
        }, this);
      }
    }
    catch (err) {
      console.log(err);
    }
  }
};

L.Map.include(RestoreViewMixin);
