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

  c3: {
    cwd: 'node_modules/c3',
    dest: config.build + '/' + config.src + '/htdocs/lib/c3',
    expand: true,
    src: [
      'c3.css',
      'c3.js'
    ]
  },

  d3: {
    cwd: 'node_modules/d3',
    dest: config.build + '/' + config.src + '/htdocs/lib/d3',
    expand: true,
    src: [
      'd3.js'
    ]
  },

  leaflet: {
    cwd: 'node_modules/leaflet/dist',
    dest: config.build + '/' + config.src + '/htdocs/lib/leaflet-0.7.x',
    expand: true,
    src: [
      'leaflet.css',
      'images/*'
    ]
  }
};

module.exports = copy;
