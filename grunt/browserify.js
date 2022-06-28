'use strict';


var babelify = require('babelify'),
    config = require('./config');

var browserify = {
  options: {
    //banner: '/* browserified: <%= grunt.template.today("mm-dd-yyyy hh:MM:ss") %> */\n',
    browserifyOptions: {
      debug: true, // inline sourcemaps
      paths: [
        config.src + '/htdocs/js',
        'node_modules/hazdev-tablist/src',
        'node_modules/hazdev-webutils/src'
      ]
    },
    noParse: [
      'utfgrid'
    ],
    transform: [
      babelify.configure({
        presets: [
          '@babel/preset-env'
        ],
        plugins: [
          '@babel/plugin-transform-runtime'
        ]
      })
    ]
  },

  build: {
    files: [{
      cwd: config.src,
      dest: config.build + '/' + config.src,
      expand: true,
      src: [
        'htdocs/js/index.js',
        'htdocs/js/kinematic.js',
        'htdocs/js/network.js',
        'htdocs/js/photos.js',
        'htdocs/js/qc.js',
        'htdocs/js/station.js',
        'htdocs/js/table.js'
      ]
    }]
  }
};


module.exports = browserify;
