'use strict';

/* globals L */

var RestoreViewMixin = {
  restoreView: function () {
    var storage,
        view;

    storage = window.localStorage || {};

    // Save settings: setup listeners, store settings in localStorage
    if (!this.__initRestore) {
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
      this.__initRestore = true;
    }

    // Restore settings
    try {
      view = JSON.parse(storage.mapView || '');
      this.setView(L.latLng(view.lat, view.lng), view.zoom, true);
      return true;
    }
    catch (err) {
      return false;
    }
  }
};

L.Map.include(RestoreViewMixin);
