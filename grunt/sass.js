'use strict';


var config = require('./config'),
    dartSass = require('sass');

var sass = {
  options: {
    implementation: dartSass,
    includePaths: [
      'node_modules/hazdev-tablist/src',
      'node_modules/hazdev-template/src',
      'node_modules/leaflet-fullscreen/dist',
      'node_modules/leaflet-groupedlayercontrol/src'
    ],
    outputStyle: 'expanded',
    sourceMap: true,
    sourceMapContents: true
  },

  build: {
    files: [{
      cwd: config.src,
      dest: config.build + '/' + config.src,
      expand: true,
      ext: '.css',
      extDot: 'last',
      src: [
        'htdocs/**/*.scss'
      ]
    }]
  }
};


module.exports = sass;
