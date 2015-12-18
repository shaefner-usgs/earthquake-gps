'use strict';

var config = require('./config');

var rewrites = [
  {
    from: '^' + config.ini.MOUNT_PATH + '/?(.*)$',
    to: '/$1'
  }
];

// handle proxies for template, rewrites, php parsing
var addMiddleware = function (connect, options, middlewares) {
  middlewares.unshift(
    require('grunt-connect-proxy/lib/utils').proxyRequest,
    require('http-rewrite-middleware').getMiddleware(rewrites),
    require('gateway')(options.base[0], {
      '.php': 'php-cgi',
      'env': {
        'PHPRC': 'node_modules/hazdev-template/dist/conf/php.ini'
      }
    })
  );

  return middlewares;
};

var connect = {
  // global options (extended with options below)
  options: {
    hostname: '*' // allow access by others
  },
  proxies: [
    {
      context: config.ini.MOUNT_PATH + '/data', // data on dev server
      host: config.ini.DATA_HOST,
      port: 80
    },
    {
      context: '/theme/', // 'local' template
      host: 'localhost',
      port: config.templatePort,
      rewrite: {
        '^/theme': ''
      }
    }
  ],

  // web server for .build folder
  dev: {
    options: {
      base: [ // Document roots
        config.build + '/' + config.src + '/htdocs',
        config.etc
      ],
      livereload: config.liveReloadPort,
      middleware: addMiddleware,
      open: 'http://localhost:' + config.buildPort + config.ini.MOUNT_PATH +
        '/index.php',
      port: config.buildPort
    }
  },

  // web server for template
  template: {
    options: {
      base: ['node_modules/hazdev-template/dist/htdocs'],
      middleware: addMiddleware,
      port: config.templatePort
    }
  }
};

module.exports = connect;
