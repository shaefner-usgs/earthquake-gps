/* global L */
'use strict';


/**
 * Leaflet.RestoreMap plugin
 *   (based on https://github.com/makinacorpus/Leaflet.RestoreView)
 *
 * Added functionality:
 * - remembers selected layers, including grouped layers
 *     (compatible with https://github.com/ismyrnow/Leaflet.groupedlayercontrol)
 * - remembers fullscreen mode
 *     (compatible with https://github.com/Leaflet/Leaflet.fullscreen)
 * - options to share layer settings across site
 *
 * Usage: map.RestoreMap(options)
 *
 * @param options {Object}
 *        optional settings
 *        {
 *          baseLayers: {Object <Layer Config>},
 *              req'd for restoring basemap setting
 *          id: {String},
 *              req'd for saving each page's settings separately
 *          layerStorage {String <'local' | 'session'>}
 *              local: persist layer settings
 *          overlays: {Object <Layer Config>},
 *              req'd for restoring overlay settings,
 *          shareLayers: {Boolean}
 *              share layer settings amongst all pages
 *          viewStorage {String <'local' | 'session'>}
 *              local: persist map view settings
 *        }
 *
 * <Layer Config> : http://leafletjs.com/reference.html#control-layers-config
 */
var RestoreMapMixin = {
  restoreMap: function (options) {
    var defaultId,
        layers,
        layersId,
        map,
        storage,
        view,
        viewId,

        // methods
        _baselayerchange,
        _fullscreenchange,
        _getIndex,
        _getOverlay,
        _initialize,
        _initSaveSettings,
        _isEmpty,
        _moveend,
        _overlayadd,
        _overlayremove,
        _restoreSettings,
        _setLayers;

    map = this;


    _initialize = function () {
      options = L.extend({
        baseLayers: null,
        id: null,
        layerStorage: 'local',
        overlays: null,
        shareLayers: false,
        viewStorage: 'session'
      }, options);

      // setup local/sessionStorage for layers and map views
      storage = {
        local: window.localStorage || {},
        session: window.sessionStorage || {}
      };
      layers = JSON.parse(storage[options.layerStorage].mapLayers || '{}');
      view = JSON.parse(storage[options.viewStorage].mapView || '{}');

      // default key used to store settings
      defaultId = '_global_';

      // Use defaultId if unique id not supplied
      if (!options.id) {
        options.id = defaultId;
      }

      // If shareLayers is 'on', then always use defaultId for layer settings
      layersId = options.id;
      if (options.shareLayers) {
        layersId = defaultId;
      }
      viewId = options.id;

      // Create obj templates for storing layers and views
      if (!layers[layersId]) {
        layers[layersId] = {
          add: [],
          remove: []
        };
      }
      if (!view[viewId]) {
        view[viewId] = {};
      }

      _initSaveSettings();
      _restoreSettings();
    };


    // Handler for when base layer changes
    _baselayerchange = function (e) {
      layers[layersId].base = e.name;

      storage[options.layerStorage].mapLayers = JSON.stringify(layers);
    };

    // Handler for when fullscreen mode changes
    _fullscreenchange = function () {
      if (map.isFullscreen()) {
        view[viewId].fs = true;
      } else {
        view[viewId].fs = false;
      }

      storage[options.viewStorage].mapView = JSON.stringify(view);
    };

    // Get array index of layer containing layerName, or return -1
    _getIndex = function (layers, layerName) {
      var r = -1;

      layers.forEach(function(layer, i) {
        if (layer.name === layerName) {
          r = i;
        }
      });

      return r;
    };

    _getOverlay = function (layer) {
      var overlay;

      if (layer.group) {
        overlay = options.overlays[layer.group][layer.name];
      } else {
        overlay = options.overlays[layer.name];
      }

      return overlay;
    };

    // Setup listeners to store settings in local/sessionStorage
    _initSaveSettings = function () {
      if (!map.__initRestore) {
        // map extent, size
        map.on('fullscreenchange', _fullscreenchange, map);
        map.on('moveend', _moveend, map);

        // map layers
        map.on('baselayerchange', _baselayerchange, map);
        map.on('overlayadd', _overlayadd, map);
        map.on('overlayremove', _overlayremove, map);

        map.__initRestore = true;
      }
    };

    // Check if javascript obj contains props
    _isEmpty = function (obj) {
      return (Object.getOwnPropertyNames(obj).length === 0);
    };

    // Handler for when map extent change
    _moveend = function () {
      if (!map._loaded) {
        return; // don't access map bounds if view is not set
      }
      view[viewId].lat = map.getCenter().lat;
      view[viewId].lng = map.getCenter().lng;
      view[viewId].zoom = map.getZoom();

      storage[options.viewStorage].mapView = JSON.stringify(view);
    };

    // Handler for when overlays are added
    _overlayadd = function (e) {
      _setLayers(e, 'add');

      storage[options.layerStorage].mapLayers = JSON.stringify(layers);
    };

    // Handler for when overlays are removed
    _overlayremove = function (e) {
      _setLayers(e, 'remove');

      storage[options.layerStorage].mapLayers = JSON.stringify(layers);
    };

    // Restore settings: map extent, full screen mode and chosen layers
    _restoreSettings = function () {
      try {
        // Restore view
        if (!_isEmpty(view[viewId])) {
          map.setView(
            L.latLng(view[viewId].lat, view[viewId].lng),
            view[viewId].zoom,
            true
          );
          if (view[viewId].fs) {
            map.toggleFullscreen();
          }
        }

        // Restore layers
        if (!_isEmpty(layers[layersId])) {
          var selBaseLayerName = layers[layersId].base;

          if (selBaseLayerName) {
            Object.keys(options.baseLayers).forEach(function(layerName) {
              var baseLayer = options.baseLayers[layerName];

              if (layerName === selBaseLayerName) {
                map.addLayer(baseLayer);
              } else {
                map.removeLayer(baseLayer);
              }
            }, map);
          }

          layers[layersId].add.forEach(function(layer) {
            var overlay = _getOverlay(layer);

            if (overlay && !map.hasLayer(overlay)) {
              map.addLayer(overlay);
            }
          }, map);

          layers[layersId].remove.forEach(function(layer) {
            var overlay = _getOverlay(layer);

            if (overlay && map.hasLayer(overlay)) {
              map.removeLayer(overlay);
            }
          }, map);
        }
      }
      catch (err) {
        console.log(err);
      }
    };

    // Set list of layers to add/remove on map
    _setLayers = function (e, type) {
      var group,
          index;

      group = null;
      if (e.group) {
        group = e.group.name;
      }
      index = {
        add: _getIndex(layers[layersId].add, e.name),
        remove: _getIndex(layers[layersId].remove, e.name)
      };

      // Loop thru add/remove layer lists
      Object.keys(index).forEach(function(listType) {
        if (listType === type) { // add layer to list if not present
          if (index[listType] === -1) { // layer is not in list
            layers[layersId][listType].push({
              group: group,
              name: e.name
            });
          }
        } else { // remove layer from list if present
          if (index[listType] !== -1) { // layer is in list
            layers[layersId][listType].splice(index[listType], 1);
          }
        }
      });
    };

    _initialize();
  }
};

L.Map.include(RestoreMapMixin);
