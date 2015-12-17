'use strict';

var config = require('./config');

var browserify = {
  // global options (extended with options below)
  options: {
    browserifyOptions: {
      debug: true, // create sourcemaps
      paths: [ // ordered list of where to find required components
        config.src + '/htdocs/js',
        'node_modules/hazdev-webutils/src'
      ]
    }
  },

  qc: {
    src: [config.src + '/htdocs/js/qc/index.js'],
    dest: config.build + '/' + config.src + '/htdocs/js/qc/index.js'
  }

};

module.exports = browserify;
