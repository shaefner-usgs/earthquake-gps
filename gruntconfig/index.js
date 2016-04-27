'use strict';

var gruntConfig = {
  // Import config files for tasks
  browserify: require('./browserify'),
  clean: require('./clean'),
  connect: require('./connect'),
  copy: require('./copy'),
  jshint: require('./jshint'),
  postcss: require('./postcss'),
  uglify: require('./uglify'),
  watch: require('./watch'),

  // Define tasks array
  tasks: [
    'grunt-browserify',
    'grunt-connect-proxy', // webserver proxy plugin
    'grunt-connect-rewrite', // webserver rewrite plugin
    'grunt-contrib-clean',
    'grunt-contrib-connect', // webserver
    'grunt-contrib-copy',
    'grunt-contrib-jshint',
    'grunt-postcss',
    'grunt-contrib-uglify',
    'grunt-contrib-watch',
  ]
};

module.exports = gruntConfig;
