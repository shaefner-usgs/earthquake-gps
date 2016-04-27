'use strict';

module.exports = function (grunt) {

  var gruntConfig = require('./gruntconfig');

  // Configure tasks
  grunt.initConfig(gruntConfig);

  // Load grunt tasks
  gruntConfig.tasks.forEach(grunt.loadNpmTasks);

  grunt.registerTask('build', [
    'clean', // clean first
    'jshint', // then check for errors
    'browserify',
    'copy:dev',
    'copy:c3',
    'copy:d3',
    'copy:leaflet',
    'copy:leaflet_fullscreen',
    'postcss:dev'
  ]);

  // Setup cli tasks
  grunt.registerTask('default', [
    'build',
    'configureRewriteRules',
    'configureProxies:dev', // don't need to define (defined by module)
    'connect:template',
    'connect:dev',
    'watch'
  ]);

  grunt.registerTask('dist', [
    'clean:dist',
    'build',
    'copy:dist',
    'postcss:dist',
    'uglify',
    'configureRewriteRules',
    'configureProxies:dist', // don't need to define (defined by module)
    'connect:template',
    'connect:dist:keepalive'
  ]);
};
