/* global c3 */

'use strict';

var Util = require('util/Util'),
    View = require('mvc/View');


var _C3_DEFAULTS = {
  axis: {
    x: {
      type: 'timeseries',
      tick: {
        count: 13,
        culling: {
          max: 8
        },
        format: '%Y-%m-%d',
        outer: false
      }
    },
    y: {
      label: {
        position: 'outer-middle'
      }
    },
    y2: {
      show: false,
      label: {
        position: 'outer-middle'
      }
    }
  },
  grid: {
    x: {
      show: true
    },
    y: {
      show: true
    }
  },
  padding: {
    right: 50
  },
  point: {
    show: true,
    r: 2.5,
    focus: {
      expand: {
        r: 4
      }
    }
  }
};

var _DEFAULTS = {};


/**
 * PlotView displays a plot of QC data.
 *
 * @param options {Object}
 * @param options.channels {Array<String>}
 *        channel names to include in plot.
 * @param options.title {String}
 *        plot header.
 * @param
 */
var PlotView = function (options) {
  var _this,
      _initialize,
      // variables
      _c3El,
      _data,
      _titleEl;


  _this = View(options);

  _initialize = function (options) {
    var el;

    options = Util.extend({}, _DEFAULTS, options);
    el = _this.el;
    el.innerHTML = '<header class="title"></header>' +
        '<div class="plot"></div>';

    _c3El = el.querySelector('.plot');
    _titleEl = el.querySelector('.title');

    _data = options.data;

    _this.model.set({
      channels: options.channels,
      title: options.title
    });

    _data.on('change:data', _this.render);
  };


  /**
   * Update plot.
   *
   * @param changed {Object}
   *        object with key/value pairs that changed.
   *        when null, assume all have changed.
   */
  _this.render = function (/*changed*/) {
    var axes,
        axis,
        c3options,
        channelMeta,
        channels,
        columns,
        data,
        prevUnit,
        names;

    data = _data.get('data');
    channelMeta = _data.get('channels');
    channels = _this.model.get('channels');

    // update title
    _titleEl.innerHTML = _this.model.get('title');

    if (data === null) {
      _this.el.classList.add('nodata');
      _c3El.innerHTML = '<p class="loading">Loading&hellip;</p>';
      return;
    }

    // deep clone
    c3options = JSON.parse(JSON.stringify(_C3_DEFAULTS));

    axes = {};
    axis = 'y';
    columns = [];
    prevUnit = null;
    names = {};
    channels.concat(['date']).forEach(function (channel) {
      var meta;
      meta = channelMeta[channel];

      columns.push([channel].concat(data[channel]));
      names[channel] = meta.title;

      if (channel === 'date') {
        return;
      }

      // channel axis
      if (prevUnit !== meta.units) {
        if (prevUnit !== null) {
          axis = 'y2';
        }
        prevUnit = meta.units;
        c3options.axis[axis].label.text = prevUnit;
        c3options.axis[axis].show = true;
      }
      axes[channel] = axis;
    });

    c3options.bindto = _c3El;
    c3options.data = {
      axes: axes,
      columns: columns,
      names: names,
      type: 'scatter',
      x: 'date'
    };

    c3.generate(c3options);
  };


  _initialize(options);
  options = null;
  return _this;
};

module.exports = PlotView;
