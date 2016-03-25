'use strict';

var autoprefixer = require('autoprefixer'),
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
            'node_modules/leaflet-fullscreen/dist'
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
  }
};

module.exports = postcss;
