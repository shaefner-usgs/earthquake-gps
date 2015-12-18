'use strict';

var gruntConfig = {
  // Import config files for tasks
  browserify: require('./browserify'),
  connect: require('./connect'),
  copy: require('./copy'),
  jshint: require('./jshint'),
  postcss: require('./postcss'),
  watch: require('./watch'),

  // Define tasks array
  tasks: [
    'grunt-browserify',
    'grunt-connect-proxy', // webserver plugin
    'grunt-contrib-connect', // webserver
    'grunt-contrib-copy',
    'grunt-contrib-jshint',
    'grunt-contrib-watch',
    'grunt-postcss'
  ]
};

module.exports = gruntConfig;
