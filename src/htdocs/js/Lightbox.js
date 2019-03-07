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
 * Class for creating a lightbox using free SimplBox Library
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

      _addCaption,
      _addClickHandler,
      _addCloseButton,
      _addCssClass,
      _addOverlay,
      _addSpinner,
      _removeComponent;


  _this = {};

  _initialize = function () {
    var callbacks,
        el,
        simplbox;

    callbacks = {
      onImageLoadEnd: function () {
        _removeComponent('simplbox-loading');
        _addClickHandler(simplbox);
        _addCssClass();
      },
      onImageLoadStart: function () {
        _addSpinner();
        _addCaption(simplbox);
      },
      onEnd: function () {
        _removeComponent('simplbox-caption');
        _removeComponent('simplbox-close');
        _removeComponent('simplbox-overlay');
      },
      onStart: function () {
        _addOverlay();
        _addCloseButton(simplbox);
      }
    };
    options = Util.extend({}, _DEFAULTS, callbacks, options);
    el = options.el;

    simplbox = new SimplBox(el, options);
    simplbox.init();
  };


  /**
   * Add caption
   */
  _addCaption = function (base) {
    var div,
        fragment,
        title;

    fragment = document.createDocumentFragment();
    div = document.createElement('div');
    title = base.m_Alt.replace(/\(/, '<small>(').replace(/\)/, ')</small>');

    _removeComponent('simplbox-caption');

    div.setAttribute('id', 'simplbox-caption');
    div.innerHTML = title;
    fragment.appendChild(div);
    document.body.appendChild(fragment);
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
   * Add close button
   */
  _addCloseButton = function (base) {
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
   * Add material-icons class for navigation arrows on photos
   */
  _addCssClass = function () {
    var id;

    id = SimplBox.options.imageElementId;
    document.getElementById(id).classList.add('material-icons');
  };

  /**
   * Add screen overlay
   */
  _addOverlay = function () {
    var div;

    div = document.createElement('div');
    div.setAttribute('id', 'simplbox-overlay');
    document.body.appendChild(div);
  };

  /**
   * Add loading spinner
   */
  _addSpinner = function () {
    var div1,
        div2;

    div1 = document.createElement('div');
    div2 = document.createElement('div');
    div1.setAttribute('id', 'simplbox-loading');
    div1.appendChild(div2);
    document.body.appendChild(div1);
  };

  /**
  * Remove lightbox component from DOM
  *
  * @param id {String}
  *  id of component to remove
  */
  _removeComponent = function (id) {
    var el;

    el = document.getElementById(id);
    if (el) {
      el.parentNode.removeChild(el);
    }
  };


  _initialize();
  options = null;
  return _this;
};

module.exports = Lightbox;
