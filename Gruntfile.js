'use strict';

module.exports = function (grunt) {

  var gruntConfig = require('./gruntconfig');

  // Configure tasks
  grunt.initConfig(gruntConfig);

  // Load grunt tasks
  gruntConfig.tasks.forEach(grunt.loadNpmTasks);

  // Setup cli tasks
  grunt.registerTask('default', [
    'jshint', // check first for errors
    'browserify',
    'copy:dev',
    'copy:c3',
    'copy:d3',
    'copy:leaflet',
    'configureRewriteRules',
    'configureProxies:dev', // don't need to define (defined by module)
    'connect:template',
    'connect:dev',
    'postcss:dev',
    'watch'
  ]);
};
