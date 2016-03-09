'use strict';

/* globals L */

/**
 * Leaflet.RestoreView plugin https://github.com/makinacorpus/Leaflet.RestoreView
 * (with added functionality for remembering selected layers)
 *
 * Usage: map.restoreView({
 *   baseMaps: <Layer Config>,
 *   overlays: <Layer Config>
 * })
 *
 * Layers object is optional, but required for remembering selected layers
 * <Layer Config>: http://leafletjs.com/reference.html#control-layers-config
 */

var RestoreViewMixin = {
  restoreView: function (options) {
    var baseMaps,
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
    baseMaps = options.baseMaps || null;
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
      if (add_index === -1) { // add layer if not already in list
        layers.add.push(e.name);
      }
      layers.remove.splice(remove_index, 1); // remove layer

      storage.mapLayers = JSON.stringify(layers);
    };

    // invoked when removing overlays
    _overlayremove = function (e) {
      var add_index = layers.add.indexOf(e.name),
          remove_index = layers.remove.indexOf(e.name);
      if (remove_index === -1) { // add layer if not already in list
        layers.remove.push(e.name);
      }
      layers.add.splice(add_index, 1); // remove layer

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

      if (baseMaps) {
        var baseLayer = layers.base;
        if (baseLayer) {
          var baseLayers = Object.keys(baseMaps);
          baseLayers.forEach(function(layer) {
            if (layer === baseLayer) {
              this.addLayer(baseMaps[layer]);
            } else {
              this.removeLayer(baseMaps[layer]);
            }
          }, this);
        }
      }
      if (overlays) {
        layers.add.forEach(function(layer) {
          this.addLayer(overlays[layer]);
        }, this);

        layers.remove.forEach(function(layer) {
          this.removeLayer(overlays[layer]);
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
