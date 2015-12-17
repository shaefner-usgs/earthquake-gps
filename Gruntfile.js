'use strict';

module.exports = function (grunt) {

  var gruntConfig = require('./gruntconfig');

  // Load grunt tasks
  gruntConfig.tasks.forEach(grunt.loadNpmTasks);

  // Configure tasks
  grunt.initConfig(gruntConfig);

  // Execute tasks
  grunt.registerTask('default', [
    'browserify',
    'copy:dev',
    'copy:c3',
    'copy:d3',
    'copy:leaflet',
    'configureProxies:dev', // don't need to define
    'connect:template',
    'connect:dev',
    'watch'
  ]);
}
