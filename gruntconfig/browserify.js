'use strict';

var config = require('./config');

var browserify = {
  // global options (extended with options below)
  options: {
    browserifyOptions: {
      debug: true, // create sourcemaps
      paths: [ // ordered list of where to find required components
        config.src + '/htdocs/js',
        'node_modules/hazdev-tablist/src',
        'node_modules/hazdev-webutils/src'
      ]
    }
  },

  index: {
    src: [config.src + '/htdocs/js/index.js'],
    dest: config.build + '/' + config.src + '/htdocs/js/index.js'
  },

  kinematic: {
    src: [config.src + '/htdocs/js/kinematic.js'],
    dest: config.build + '/' + config.src + '/htdocs/js/kinematic.js'
  },

  network: {
    src: [config.src + '/htdocs/js/network.js'],
    dest: config.build + '/' + config.src + '/htdocs/js/network.js'
  },

  photos: {
    src: [config.src + '/htdocs/js/photos.js'],
    dest: config.build + '/' + config.src + '/htdocs/js/photos.js'
  },

  station: {
    src: [config.src + '/htdocs/js/station.js'],
    dest: config.build + '/' + config.src + '/htdocs/js/station.js'
  },

  qc: {
    src: [config.src + '/htdocs/js/qc.js'],
    dest: config.build + '/' + config.src + '/htdocs/js/qc.js'
  },

  velocities: {
    src: [config.src + '/htdocs/js/sortAndTabifyTable.js'],
    dest: config.build + '/' + config.src + '/htdocs/js/sortAndTabifyTable.js'
  }

};

module.exports = browserify;
