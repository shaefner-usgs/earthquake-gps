/* global L */
'use strict';


var Util = require('util/Util');


/**
 * Leaflet.RestoreView plugin https://github.com/makinacorpus/Leaflet.RestoreView
 * with added functionality:
 * - remembering selected layers
 * - fullscreen mode (compatible with https://github.com/Leaflet/Leaflet.fullscreen)
 *
 * Usage: map.restoreView(options)
 *
 * @param options {Object}
 *        optional settings
 *        {
 *          baseLayers: {Object <Layer Config>},
 *              req'd for restoring basemap
 *          id: {String},
 *              req'd for saving each page's settings separately
 *          overlays: {Object <Layer Config>},
 *              req'd for restoring overlays,
 *          shareLayers: {Boolean}
 *              share layer settings amongst all pages
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
        _fullscreenchange,
        _isEmpty,
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

    // Create obj templates for storing layers
    if (!layers[layersId]) {
      layers[layersId] = {
        add: [],
        remove: []
      };
    }
    if (!view[viewId]) {
      view[viewId] = {};
    }

    // Invoked when base layer changes
    _baselayerchange = function (e) {
      layers[layersId].base = e.name;

      storage.mapLayers = JSON.stringify(layers);
    };

    // Invoked when fullscreen mode changes
    _fullscreenchange = function () {
      if (this.isFullscreen()) {
        view[viewId].fs = true;
      } else {
        view[viewId].fs = false;
      }

      storage.mapView = JSON.stringify(view);
    };

    // Check if javascript obj contains props
    _isEmpty = function (obj) {
      return (Object.getOwnPropertyNames(obj).length === 0);
    };

    // Invoked when map extent change
    _moveend = function () {
      if (!this._loaded) {
        return;  // Never access map bounds if view is not set.
      }
      view[viewId].lat = this.getCenter().lat;
      view[viewId].lng = this.getCenter().lng;
      view[viewId].zoom = this.getZoom();

      storage.mapView = JSON.stringify(view);
    };

    // Invoked when adding overlays
    _overlayadd = function (e) {
      var add_index = layers[layersId].add.indexOf(e.name),
          remove_index = layers[layersId].remove.indexOf(e.name);
      if (add_index === -1) { // add layer if not already in 'add' list
        layers[layersId].add.push(e.name);
      }
      if (remove_index !== -1) { // remove layer if it's in 'remove' list
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
      if (add_index !== -1) { // remove layer if it's in 'add' list
        layers[layersId].add.splice(add_index, 1);
      }

      storage.mapLayers = JSON.stringify(layers);
    };

    // Save settings: setup listeners which store settings in localStorage
    if (!this.__initRestore) {
      // map extent, size
      this.on('fullscreenchange', _fullscreenchange, this);
      this.on('moveend', _moveend, this);
      // map layers
      this.on('baselayerchange', _baselayerchange, this);
      this.on('overlayadd', _overlayadd, this);
      this.on('overlayremove', _overlayremove, this);

      this.__initRestore = true;
    }

    // Restore settings: map extent, full screen mode and chosen layers
    try {
      if (!_isEmpty(view[viewId])) {
        this.setView(
          L.latLng(view[viewId].lat, view[viewId].lng),
          view[viewId].zoom,
          true
        );
        if (view[viewId].fs) {
          this.toggleFullscreen();
        }
      }

      if (!_isEmpty(layers[layersId])) {
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
