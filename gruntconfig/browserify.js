'use strict';

var config = require('./config');

var browserify = {
  // global options (extended with options below)
  options: {
    browserifyOptions: {
      debug: true, // create sourcemaps
      paths: [ // ordered list of where to find required components
        config.src + '/htdocs/js',
        'node_modules/hazdev-webutils/src',
        'node_modules/leaflet'
      ]
    }
  },

  // copy via browserify so that L is 'requirable'
  leaflet: {
    src: [], // using alias to define leaflet src
    dest: config.build + '/' + config.src + '/htdocs/lib/leaflet-0.7.x/leaflet.js',
    options: {
      alias: [
        'dist/leaflet-src.js:leaflet' // src:keyword (to require)
      ]
    }
  },

  // example leaflet instance
  map: {
    src: [config.src + '/htdocs/js/map/index.js'],
    dest: config.build + '/' + config.src + '/htdocs/js/map/index.js',
    options: {
      external: [
        'leaflet' // don't bundle leaflet b/c it's provided by target above
      ]
    }
  },

  qc: {
    src: [config.src + '/htdocs/js/qc/index.js'],
    dest: config.build + '/' + config.src + '/htdocs/js/qc/index.js'
  }

};

module.exports = browserify;
