'use strict';

var config = require('./config');

var watch = {
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

  // postcss: {
  //   files: [
  //
  //   ]
  // },

  scripts: {
    files: [
      config.src + '/htdocs/**/*.js'
    ],
    tasks: [
      'browserify:qc'
    ]
  },

  livereload: {
    options: {
      livereload: config.liveReloadPort,
    },
    files: [
      config.build + '/' + config.src + '/htdocs/**/*'
    ]
  }
};

module.exports = watch;
