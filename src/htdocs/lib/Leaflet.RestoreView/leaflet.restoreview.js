'use strict';

/* globals L */

var RestoreViewMixin = {
  restoreView: function () {
    var layers,
        storage,
        view;

    storage = window.localStorage || {};
    layers = JSON.parse(storage.mapLayers || '{"base":"", "add":[], "remove":[]}');

    // Save settings: setup listeners, store settings in localStorage
    if (!this.__initRestore) {
      // map extent
      this.on('moveend', function () {
        if (!this._loaded) {
          return;  // Never access map bounds if view is not set.
        }
        view = {
          lat: this.getCenter().lat,
          lng: this.getCenter().lng,
          zoom: this.getZoom()
        };
        storage.mapView = JSON.stringify(view);
      }, this);

      // map layers
      this.on('baselayerchange', function (e) {
        layers.base = e.name;
        storage.mapLayers = JSON.stringify(layers);
      }, this);
      this.on('overlayadd', function (e) {
        var add = layers.add.indexOf(e.name),
            remove = layers.remove.indexOf(e.name);
        if (add === -1) {
          layers.add.push(e.name);
        }
        layers.remove.splice(remove, 1);
        storage.mapLayers = JSON.stringify(layers);
      }, this);
      this.on('overlayremove', function (e) {
        var add = layers.add.indexOf(e.name),
            remove = layers.remove.indexOf(e.name);
        if (remove === -1) {
          layers.remove.push(e.name);
        }
        layers.add.splice(add, 1);
        storage.mapLayers = JSON.stringify(layers);
      }, this);

      this.__initRestore = true;
    }

    // Restore settings
    try {
      view = JSON.parse(storage.mapView || '{}');
      this.setView(L.latLng(view.lat, view.lng), view.zoom, true);
      return true;
    }
    catch (err) {
      return false;
    }
  }
};

L.Map.include(RestoreViewMixin);
