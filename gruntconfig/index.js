'use strict';

var gruntConfig = {
  // Import config files for tasks
  browserify: require('./browserify'),
  connect: require('./connect'),
  copy: require('./copy'),
  watch: require('./watch'),

  // Define tasks array
  tasks: [
    'grunt-browserify',
    'grunt-connect-proxy', // webserver plugin
    'grunt-contrib-connect', // webserver
    'grunt-contrib-copy',
    'grunt-contrib-watch'
  ]
};

module.exports = gruntConfig;
