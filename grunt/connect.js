'use strict';


var config = require('./config'),
    gateway = require('gateway'),
    proxy = require('http-proxy-middleware'),
    rewrite = require('http-rewrite-middleware');

var MOUNT_PATH = config.ini.MOUNT_PATH;

var addMiddleware = function(connect, options, middlewares) {
  middlewares.unshift( // add custom middleware to default middlewares[]
    proxy.createProxyMiddleware(MOUNT_PATH + '/data', {
      changeOrigin: true,
      headers: {
        'accept-encoding': 'identity',
      },
      target: 'https://' + config.ini.DATA_HOST
    }),
    proxy.createProxyMiddleware('/theme', {
      pathRewrite: {
        '^/theme': ''
      },
      target: 'http://localhost:' + config.templatePort
    }),
    rewrite.getMiddleware(getRewrites()),
    gateway(options.base[0], {
      '.php': 'php-cgi',
      'env': {
        'PHPRC': 'node_modules/hazdev-template/dist/conf/php.ini'
      }
    })
  );

  return middlewares;
};

var connect = {
  options: {
    hostname: '*'
  },

  build: {
    options: {
      base: config.build + '/' + config.src + '/htdocs',
      livereload: config.liveReloadPort,
      middleware: addMiddleware,
      port: config.buildPort
    }
  },

  dist: {
    options: {
      base: config.dist + '/htdocs',
      keepalive: true,
      middleware: addMiddleware,
      port: config.distPort
    }
  },

  template: {
    options: {
      base: 'node_modules/hazdev-template/dist/htdocs',
      middleware: addMiddleware,
      port: config.templatePort
    }
  }
};


/**
 * Get a list of rewrite rules for app
 *
 * @return {Array}
 */
function getRewrites() {
  return [
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
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/([a-zA-Z0-9_-]{4})$',
      to: '/station.php?network=$1&station=$2'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/([a-zA-Z0-9_-]{4})/kinematic$',
      to: '/kinematic.php?network=$1&station=$2'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/([a-zA-Z0-9_-]{4})/kinematic/data$',
      to: '/_getKinematic.csv.php?station=$2'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/([a-zA-Z0-9_-]{4})/logs$',
      to: '/logsheets.php?network=$1&station=$2'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/([a-zA-Z0-9_-]{4})/photos$',
      to: '/photos.php?network=$1&station=$2'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/([a-zA-Z0-9_-]{4})/qc$',
      to: '/qc.php?network=$1&station=$2'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/([a-zA-Z0-9_-]{4})/qc/table$',
      to: '/qctable.php?network=$1&station=$2'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/([a-zA-Z0-9_-]{4})/qc/data$',
      to: '/_getQcData.csv.php?network=$1&station=$2'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/([a-zA-Z0-9_-]{4})/(filtered|itrf2008|itrf2014|na2014|nafixed)/deleted$',
      to: '/deleted.php?network=$1&station=$2&datatype=$3'
    },
    {
      from: '^' + MOUNT_PATH + '/([a-zA-Z0-9_-]+)/([a-zA-Z0-9_-]{4})/metadata$',
      to: '/metadata.php?station=$2'
    },
    {
      from: '^' + MOUNT_PATH + '/?(.*)$',
      to: '/$1'
    }
  ];
}


module.exports = connect;
