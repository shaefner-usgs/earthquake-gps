'use strict';

var config = require('./config');


var MOUNT_PATH = config.ini.MOUNT_PATH;


// handle proxies for template, rewrites, php parsing
var addMiddleware = function (connect, options, middlewares) {
  middlewares.unshift(
    require('grunt-connect-rewrite/lib/utils').rewriteRequest,
    require('grunt-connect-proxy/lib/utils').proxyRequest,
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
      context: '/data', // data on dev server
      host: config.ini.DATA_HOST,
      port: 80,
      headers: {
        'accept-encoding': 'identity',
        host: config.ini.DATA_HOST
      },
      rewrite: {
        '^/data': MOUNT_PATH + '/data'
      }
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

  rules: [
    {
      from: '^(' + MOUNT_PATH + ')(.*)/+$',
      to: 'http://localhost:' + config.buildPort + '$1$2', // strip final '/'
      redirect: 'permanent'
    },
    {
      from: '^' + MOUNT_PATH + '/stations/?([a-z0-9]+)?$',
      to: '/stationlist.php?filter=$1'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)$',
      to: '/network.php?network=$1'
    },
    {
      from: '^' + MOUNT_PATH + '/(([a-zA-Z0-9_-]+)/)?kml(/(last|timespan|years))?$',
      to: '/kml.php?network=$2&sortBy=$4'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/noise$',
      to: '/noise.php?network=$1'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/notupdated$',
      to: '/notupdated.php?network=$1'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/offsets$',
      to: '/offsets.php?network=$1'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/velocities$',
      to: '/velocities.php?network=$1'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/waypoints$',
      to: '/waypoints.php?network=$1'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/([a-zA-Z0-9]{4})$',
      to: '/station.php?network=$1&station=$2'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/([a-zA-Z0-9]{4})/kinematic$',
      to: '/kinematic.php?network=$1&station=$2'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/([a-zA-Z0-9]{4})/kinematic/data$',
      to: '/_getKinematic.csv.php?station=$2'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/([a-zA-Z0-9]{4})/logs$',
      to: '/logsheets.php?network=$1&station=$2'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/([a-zA-Z0-9]{4})/offsets$',
      to: '/offsets.php?network=$1&station=$2'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/([a-zA-Z0-9]{4})/photos$',
      to: '/photos.php?network=$1&station=$2'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/([a-zA-Z0-9]{4})/qc$',
      to: '/qc.php?network=$1&station=$2'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/([a-zA-Z0-9]{4})/qc/data$',
      to: '/_getQcData.csv.php?network=$1&station=$2'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/([a-zA-Z0-9]{4})/qc/table$',
      to: '/qctable.php?network=$1&station=$2'
    },
    {
      from: '^' + MOUNT_PATH + '/?(.*)$',
      to: '/$1'
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
      open: 'http://localhost:' + config.buildPort + MOUNT_PATH,
      port: config.buildPort
    }
  },

  // web server for dist folder
  dist: {
    options: {
      base: [ // Document roots
        config.dist + '/htdocs'
      ],
      middleware: addMiddleware,
      open: 'http://localhost:' + config.distPort + MOUNT_PATH,
      port: config.distPort
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
