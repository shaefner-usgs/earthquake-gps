'use strict';

var config = require('./config');

var copy = {
  dev: {
    cwd: config.src,
    dest: config.build + '/' + config.src,
    expand: true, // keep file structure relative to cwd
    src: [
      '**/*',
      '!**/*.scss',
      '!**/*.js'
    ]
  },

  dist: {
    cwd: config.build + '/' + config.src,
    dest: config.dist,
    expand: true,
    src: [
      '**/*',
      '!**/*.css',
      '!**/*.js'
    ]
  },

  c3: {
    cwd: 'node_modules/c3',
    dest: config.build + '/' + config.src + '/htdocs/lib/c3',
    expand: true,
    src: [
      'c3.css',
      'c3.js',
      '!c3.min.js'
    ]
  },

  d3: {
    cwd: 'node_modules/d3',
    dest: config.build + '/' + config.src + '/htdocs/lib/d3',
    expand: true,
    src: [
      'd3.js',
      '!d3.min.js'
    ]
  },

  dygraph: {
    cwd: 'node_modules/dygraphs/dist',
    dest: config.build + '/' + config.src + '/htdocs/lib/dygraph',
    expand: true,
    src: [
      'dygraph.css',
      'dygraph.js',
      'dygraph.js.map',
      '!dygraph.min.js'
    ]
  },

  leaflet: {
    expand: true,
    cwd: 'node_modules/leaflet/dist',
    dest: config.build + '/' + config.src + '/htdocs/lib/leaflet',
    rename: function (dest, src) {
      var newName;

      // swap -src version to be default and add -min to compressed version
      // this is nice for debugging but allows production to use default
      // version as compressed
      newName = src.replace('leaflet.js', 'leaflet-min.js');
      newName = newName.replace('leaflet-src.js', 'leaflet.js');
      newName = newName.replace('leaflet.js.map', 'leaflet-src.js.map');

      return dest + '/' + newName;
    },
    src: [
      '**/*'
    ]
  },

  leaflet_fullscreen: {
    cwd: 'node_modules/leaflet-fullscreen/dist',
    dest: config.build + '/' + config.src + '/htdocs/img',
    expand: true,
    src: [
      '*.png'
    ]
  },

  simplbox: {
    cwd: 'node_modules/SimplBox',
    dest: config.build + '/' + config.src + '/htdocs/lib/simplbox',
    expand: true,
    src: [
      'simplbox.js'
    ]
  }
};

module.exports = copy;
