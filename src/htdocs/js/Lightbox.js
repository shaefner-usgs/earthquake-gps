/* global SimplBox */
'use strict';


var Util = require('util/Util');


var _DEFAULTS = {
  animationSpeed: 0,
  imageSize: 0.8,
  quitOnDocumentClick: true,
  quitOnImageClick: false
};

/**
 * Class for creating a lightbox using SimplBox
 *
 * @param options {Object}
 *  {
 *    el: elem(s) to attach lightbox to
 *    SimplBox options...
 *  }
 */
var Lightbox = function (options) {
  var _this,
      _initialize,

      _addClickHandler,
      _addCssClass,
      _captionOn,
      _closeButtonOn,
      _componentOff,
      _overlayOn,
      _preLoadIconOn;


  _this = {};

  _initialize = function () {
    var callbacks,
        el,
        simplbox;

    callbacks = {
      onImageLoadEnd: function () {
        _componentOff('simplbox-loading');
        _addClickHandler(simplbox);
        _addCssClass();
      },
      onImageLoadStart: function () {
        _preLoadIconOn();
        _captionOn(simplbox);
      },
      onEnd: function () {
        _componentOff('simplbox-caption');
        _componentOff('simplbox-close');
        _componentOff('simplbox-overlay');
      },
      onStart: function () {
        _overlayOn();
        _closeButtonOn(simplbox);
      }
    };
    options = Util.extend({}, _DEFAULTS, callbacks, options);
    el = options.el;

    simplbox = new SimplBox(el, options);
    simplbox.init();
  };


  /**
   * Add option for clicking on photos to navigate
   */
  _addClickHandler = function (base) {
    var div,
        id;

    id = SimplBox.options.imageElementId;
    div = document.getElementById(id);

    base.API_AddEvent(div, 'click touchend', function (e) {
      var divPos = div.getBoundingClientRect(),
          divX = e.clientX - divPos.left;

      // navigate left or right depending on where user clicked
      if ((div.clientWidth / divX) > 2) {
        base.leftAnimationFunction();
      } else {
        base.rightAnimationFunction();
      }
      return false;
    });
  };

  /**
   * Add material-icons class for navigation arrows on photos
   */
  _addCssClass = function () {
    var id;

    id = SimplBox.options.imageElementId;
    document.getElementById(id).classList.add('material-icons');
  };

  /**
   * Add caption
   */
  _captionOn = function (base) {
    var div,
        fragment,
        title;

    fragment = document.createDocumentFragment();
    div = document.createElement('div');
    title = base.m_Alt.replace(/\(/, '<small>(').replace(/\)/, ')</small>');

    _componentOff('simplbox-caption');

    div.setAttribute('id', 'simplbox-caption');
    div.innerHTML = title;
    fragment.appendChild(div);
    document.body.appendChild(fragment);
  };

  /**
   * Add close button
   */
  _closeButtonOn = function (base) {
    var div;

    div = document.createElement('div');
    div.setAttribute('id', 'simplbox-close');
    div.innerHTML = '<i class="material-icons">&#xE5C9;</i>';
    document.body.appendChild(div);
    base.API_AddEvent(div, 'click touchend', function () {
      base.API_RemoveImageElement();
      return false;
    });
  };

  /**
  * Remove lightbox component from DOM
  *
  * @param id {String}
  *  id of component to remove
  */
  _componentOff = function (id) {
    var el;

    el = document.getElementById(id);
    if (el) {
      el.parentNode.removeChild(el);
    }
  };

  /**
   * Add screen overlay
   */
  _overlayOn = function () {
    var div;

    div = document.createElement('div');
    div.setAttribute('id', 'simplbox-overlay');
    document.body.appendChild(div);
  };

  /**
   * Add loading spinner
   */
  _preLoadIconOn = function () {
    var div1,
        div2;

    div1 = document.createElement('div');
    div2 = document.createElement('div');
    div1.setAttribute('id', 'simplbox-loading');
    div1.appendChild(div2);
    document.body.appendChild(div1);
  };


  _initialize();
  options = null;
  return _this;
};

module.exports = Lightbox;
