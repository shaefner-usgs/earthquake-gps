'use strict';


var config = require('./config');

var copy = {
  options: {
    mode: true,
    timestamp: true
  },

  build: {
    cwd: config.src,
    dest: config.build + '/' + config.src,
    expand: true,
    filter: 'isFile',
    src: [
      '**/*',
      '!**/*.js',
      '!**/*.scss'
    ]
  },

  dist: {
    cwd: config.build + '/' + config.src,
    dest: config.dist,
    expand: true,
    filter: 'isFile',
    src: [
      '**/*',
      '!**/*.css',
      '!**/*.js',
      '!**/*.map'
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
    cwd: 'node_modules/leaflet/dist',
    dest: config.build + '/' + config.src + '/htdocs/lib/leaflet',
    expand: true,
    filter: 'isFile',
    rename: function (dest, src) {
      // grab 'src' version of Leaflet to make debugging easier (uglified for dist)
      var newName = src.replace(/leaflet-src\.js$/, 'leaflet.js');

      return dest + '/' + newName;
    },
    src: [
      'images/**/*',
      'leaflet-src.js',
      'leaflet-src.js.map',
      'leaflet.css'
    ]
  },

  leaflet_fullscreen: {
    cwd: 'node_modules/leaflet-fullscreen/dist',
    dest: config.build + '/' + config.src + '/htdocs/lib/leaflet-fullscreen',
    expand: true,
    src: [
      '**/*.png'
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
