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
    cwd: 'node_modules/dygraphs',
    dest: config.build + '/' + config.src + '/htdocs/lib/dygraph',
    expand: true,
    src: [
      'dygraph-combined.js*'
    ]
  },

  leaflet: {
    expand: true,
    cwd: 'node_modules/leaflet/dist',
    dest: config.build + '/' + config.src + '/htdocs/lib/leaflet-0.7.7',
    rename: function (dest, src) {
      var newName;

      // swap -src version to be default and add -min to compressed version
      // this is nice for debugging but allows production to use default
      // version as compressed
      newName = src.replace('leaflet.js', 'leaflet-min.js');
      newName = newName.replace('leaflet-src.js', 'leaflet.js');

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
  }
};

module.exports = copy;
