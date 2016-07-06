'use strict';

var autoprefixer = require('autoprefixer'),
    cssnano = require('cssnano'),
    postcssImport = require('postcss-import'),
    precss = require('precss');

var config = require('./config');

var postcss = {
  dev: {
    cwd: config.src + '/htdocs',
    dest: config.build + '/' + config.src + '/htdocs',
    expand: true,
    ext: '.css',
    extDot: 'last', // changes existing extension to 'ext' prop
    options: {
      map: 'inline',
      processors: [
        postcssImport({ // imports partials *and* 'regular' css files
          path: [
            config.src + '/htdocs/css',
            'node_modules/leaflet.label/dist',
            'node_modules/leaflet-fullscreen/dist',
            'node_modules/leaflet-groupedlayercontrol/src',
            'node_modules/hazdev-tablist/src'
          ]
        }),
        precss(), // do 'most' sass things
        autoprefixer({ // add vendor-specific prefixes
          'browsers': 'last 2 versions'
        })
      ]
    },
    src: [
      '**/*.scss',  // import any other css files from here
      '!**/_*.scss'
    ]
  },
  dist: {
    cwd: config.build + '/' + config.src + '/htdocs',
    dest: config.dist + '/htdocs',
    expand: true,
    options: {
      map: false,
      processors: [
        cssnano()
      ]
    },
    src: [
      '**/*.css'
    ]
  }
};

module.exports = postcss;
