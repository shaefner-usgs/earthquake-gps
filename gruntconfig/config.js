'use strict';

var BASE_PORT = 9090;

// import ini props
var iniConfig = require('ini').parse(require('fs')
  .readFileSync('src/conf/config.ini', 'utf-8'));

var config = {
  build: '.build',
  buildPort: BASE_PORT,
  dist: 'dist',
  distPort: BASE_PORT + 2,
  etc: 'etc',
  ini: iniConfig,
  liveReloadPort: BASE_PORT + 9,
  src: 'src',
  templatePort: BASE_PORT + 8,
};

module.exports = config;
