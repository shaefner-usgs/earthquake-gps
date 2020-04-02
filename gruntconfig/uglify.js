'use strict';

var config = require('./config');

var uglify = {
  options: {
    compress: {},
    mangle: true,
    report: 'min'
  },
  dist: {
    files: [{
      cwd: config.build + '/' + config.src,
      dest: config.dist,
      expand: true,
      src: ['**/*.js', '!**/leaflet*esm.js']
    }]
  }
};

module.exports = uglify;
