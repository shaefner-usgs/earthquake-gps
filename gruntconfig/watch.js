'use strict';

var config = require('./config');

var watch = {
  gruntfile: {
    files: [
      'Gruntfile.js',
      'gruntconfig/**/*.js'
    ],
    tasks: [
      'jshint:gruntfile'
    ]
  },

  livereload: {
    options: {
      livereload: config.liveReloadPort,
    },
    files: [
      config.build + '/' + config.src + '/htdocs/**/*'
    ]
  },

  postcss: {
    files: [
      config.src + '/htdocs/**/*.scss'
    ],
    tasks: [
      'postcss:dev'
    ]
  },

  resources: {
    files: [
      config.src + '/**/*',
      '!' + config.src + '/**/*.scss',
      '!' + config.src + '/**/*.js',
    ],
    tasks: [
      'copy:dev'
    ]
  },

  scripts: {
    files: [
      config.src + '/htdocs/**/*.js'
    ],
    tasks: [
      'jshint:dev', // check for errors first
      'browserify'
    ]
  }

};

module.exports = watch;
