'use strict';

var L = require('leaflet');

/**
 * Leaflet.RestoreView plugin https://github.com/makinacorpus/Leaflet.RestoreView
 * (with added functionality for remembering selected layers)
 *
 * Usage: map.restoreView({
 *   baseLayers: <Layer Config>,
 *   overlays: <Layer Config>
 * })
 *
 * Layers object is optional, but required for remembering selected layers
 * <Layer Config>: http://leafletjs.com/reference.html#control-layers-config
 */

var RestoreViewMixin = {
  restoreView: function (options) {
    var baseLayers,
        layers,
        overlays,
        storage,
        view,

        //methods
        _baselayerchange,
        _moveend,
        _overlayadd,
        _overlayremove;

    storage = window.localStorage || {};
    layers = JSON.parse(storage.mapLayers || '{"base":"", "add":[], "remove":[]}');
    view = JSON.parse(storage.mapView || '{}');

    options = options || {};
    baseLayers = options.baseLayers || null;
    overlays = options.overlays || null;

    // invoked when base layer changes
    _baselayerchange = function (e) {
      layers.base = e.name;

      storage.mapLayers = JSON.stringify(layers);
    };

    // invoked when map extent change
    _moveend = function () {
      if (!this._loaded) {
        return;  // Never access map bounds if view is not set.
      }
      view = {
        lat: this.getCenter().lat,
        lng: this.getCenter().lng,
        zoom: this.getZoom()
      };

      storage.mapView = JSON.stringify(view);
    };

    // invoked when adding overlays
    _overlayadd = function (e) {
      var add_index = layers.add.indexOf(e.name),
          remove_index = layers.remove.indexOf(e.name);
      if (add_index === -1) { // add layer if not already in 'add' list
        layers.add.push(e.name);
      }
      if (remove_index !== -1) { // remove layer if in 'remove' list
        layers.remove.splice(remove_index, 1);
      }

      storage.mapLayers = JSON.stringify(layers);
    };

    // invoked when removing overlays
    _overlayremove = function (e) {
      var add_index = layers.add.indexOf(e.name),
          remove_index = layers.remove.indexOf(e.name);
      if (remove_index === -1) { // add layer if not already in 'remove' list
        layers.remove.push(e.name);
      }
      if (add_index !== -1) { // remove layer if in 'add' list
        layers.add.splice(add_index, 1);
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
      this.setView(L.latLng(view.lat, view.lng), view.zoom, true);

      if (baseLayers) {
        var selBaseLayer = layers.base;
        if (selBaseLayer) {
          var keys = Object.keys(baseLayers);
          keys.forEach(function(layer) {
            var baseLayer = baseLayers[layer];
            if (layer === selBaseLayer) {
              this.addLayer(baseLayer);
            } else {
              this.removeLayer(baseLayer);
            }
          }, this);
        }
      }
      if (overlays) {
        layers.add.forEach(function(layer) {
          var overlay = overlays[layer];
          if (!this.hasLayer(overlay)) {
            this.addLayer(overlay);
          }
        }, this);

        layers.remove.forEach(function(layer) {
          var overlay = overlays[layer];
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
