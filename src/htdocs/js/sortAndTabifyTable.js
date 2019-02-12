'use strict';

var Tablesort = require('tablesort'),
    TabList = require('tablist/TabList');


var SortAndTabifyTable = function () {
  var _this,
      _initialize,

      _initTableSort,

      _el;

  _this = {};

  _initialize = function () {
    _el = document.querySelector('.page-content');

    _initTableSort();

    TabList.tabbifyAll();
  };

  /*
   * Make table sortable
   *
   * @param className {String}
   *     className value of container elem
   */
  _initTableSort = function () {
    var tables,
        cleanNumber,
        compareNumber;

    // Add number sorting plugin to Tablesort
    // https://gist.github.com/tristen/e79963856608bf54e046
    cleanNumber = function (i) {
      return i.replace(/[^\-?0-9.]/g, '');
    };
    compareNumber = function (a, b) {
      a = parseFloat(a);
      b = parseFloat(b);

      a = isNaN(a) ? 0 : a;
      b = isNaN(b) ? 0 : b;

      return a - b;
    };
    Tablesort.extend('number', function(item) {
      return item.match(/^-?[£\x24Û¢´€]?\d+\s*([,\.]\d{0,2})/) || // Prefixed currency
        item.match(/^-?\d+\s*([,\.]\d{0,2})?[£\x24Û¢´€]/) || // Suffixed currency
        item.match(/^-?(\d)*-?([,\.]){0,1}-?(\d)+([E,e][\-+][\d]+)?%?$/); // Number
      }, function(a, b) {
        a = cleanNumber(a);
        b = cleanNumber(b);
        return compareNumber(b, a);
    });

    tables = _el.querySelectorAll('.sortable');
    if (tables) {
      for (var i = 0; i < tables.length; i ++) {
        new Tablesort(tables[i]);
      }
    }
  };

  _initialize();
  return _this;
};

module.exports = SortAndTabifyTable;
