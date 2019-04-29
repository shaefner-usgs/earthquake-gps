/* global SimplBox */
'use strict';


var Util = require('util/Util');


var _DEFAULTS = {
  animationSpeed: 0,
  imageSize: 1,
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

      _addButtons,
      _addCaption,
      _addListeners,
      _addLoadingSpinner,
      _addOverlay,
      _createButton,
      _getPosition,
      _removeComponent,
      _rotatePhoto,
      _showNavButtons,

      _angle,
      _simplBox,
      _simplBoxId;


  _this = {};

  _initialize = function () {
    var callbacks,
        el;

    callbacks = {
      onImageLoadEnd: function () {
        _angle = 0;

        _removeComponent('simplbox-loading');

        if (!_simplBox.browser.isTouch) {
          _addListeners();
          _showNavButtons();
        }
      },
      onImageLoadStart: function () {
        _addLoadingSpinner();
        _addCaption();
      },
      onEnd: function () {
        _removeComponent('simplbox-buttons');
        _removeComponent('simplbox-caption');
        _removeComponent('simplbox-close');
        _removeComponent('simplbox-overlay');

        document.body.classList.remove('simplbox');
      },
      onStart: function () {
        _addOverlay();
        _addButtons();

        document.body.classList.add('simplbox');
      }
    };
    el = options.el;
    options = Util.extend({}, _DEFAULTS, callbacks, options);

    _simplBox = new SimplBox(el, options);
    _simplBoxId = SimplBox.options.imageElementId;

    _simplBox.init();
  };


  /**
   * Add action buttons (close, rotate)
   */
  _addButtons = function () {
    var buttons,
        close,
        left,
        right;

    close = _createButton('simplbox-close', 'cancel');
    left = _createButton('left', 'rotate_left');
    right = _createButton('right', 'rotate_right');

    buttons = document.createElement('div');
    buttons.setAttribute('id', 'simplbox-buttons');
    [left, right, close].forEach(function (item) {
      buttons.appendChild(item);
    });
    document.body.appendChild(buttons);

    _simplBox.API_AddEvent(close, 'click touchend', function (e) {
      _simplBox.API_RemoveImageElement();
      e.stopPropagation();
    });
    _simplBox.API_AddEvent(left, 'click touchend', function (e) {
      _rotatePhoto(-90);
      e.stopPropagation();
    }, false);
    _simplBox.API_AddEvent(right, 'click touchend', function (e) {
      _rotatePhoto(90);
      e.stopPropagation();
    });

  };

  /**
   * Add caption bar above photo
   */
  _addCaption = function () {
    var div,
        fragment,
        title;

    fragment = document.createDocumentFragment();
    div = document.createElement('div');
    title = _simplBox.m_Alt.replace(/\(/, '<small>(').replace(/\)/, ')</small>');

    _removeComponent('simplbox-caption');

    div.setAttribute('id', 'simplbox-caption');
    div.innerHTML = title;
    fragment.appendChild(div);
    document.body.appendChild(fragment);
  };

  /**
   * Add event listeners for prev / next photo navigation
   */
  _addListeners = function () {
    var div,
        position;

    div = document.getElementById(_simplBoxId);
    div.classList.add('material-icons');

    // Navigate to prev / next photo, depending on where user clicked
    _simplBox.API_AddEvent(div, 'click touchend', function (e) {
      position = _getPosition(div, e);
      if (position === 'left') {
        _simplBox.leftAnimationFunction();
      } else {
        _simplBox.rightAnimationFunction();
      }

      return false;
    });

    // Show navigation buttons on photo when user hovers
    div.addEventListener('mousemove', function (e) {
      position = _getPosition(div, e);
      if (position === 'left') {
        div.classList.add('left');
        div.classList.remove('right');
      } else {
        div.classList.add('right');
        div.classList.remove('left');
      }
    });
    div.addEventListener('mouseout', function () {
      div.classList.remove('left', 'right');
    });
  };

  /**
   * Add loading spinner
   */
  _addLoadingSpinner = function () {
    var div1,
        div2;

    div1 = document.createElement('div');
    div2 = document.createElement('div');
    div1.setAttribute('id', 'simplbox-loading');
    div1.appendChild(div2);
    document.body.appendChild(div1);
  };

  /**
   * Add dark screen overlay
   */
  _addOverlay = function () {
    var div;

    div = document.createElement('div');
    div.setAttribute('id', 'simplbox-overlay');
    document.body.appendChild(div);
  };

  /**
   * Create action button (rotate, close) element
   *
   * @param id {String}
   * @param icon {String}
   *   material icon name
   */
  _createButton = function (id, icon) {
    var div = document.createElement('div');

    div.setAttribute('id', id);
    div.classList.add('icon');
    div.innerHTML = '<i class="material-icons">' + icon + '</i>';

    return div;
  };

  /**
   * Get relative mouse position (right or left) over an element
   *
   * @param el {Element}
   * @param evt {Event}
   */
  _getPosition = function (el, evt) {
    var divPos = el.getBoundingClientRect(),
        divX = evt.clientX - divPos.left,
        position;

    if ((el.clientWidth / divX) > 2) {
      position = 'left';
    } else {
      position = 'right';
    }

    return position;
  };

  /**
  * Remove lightbox component from DOM
  *
  * @param id {String}
  *   id of component to remove
  */
  _removeComponent = function (id) {
    var el;

    el = document.getElementById(id);
    if (el) {
      el.parentNode.removeChild(el);
    }
  };

  /**
   * Rotate photo
   *
   * @param a {Number}
   *   value in degrees to rotate
   */
  _rotatePhoto = function (a) {
    var photo;

    _angle += a;
    photo = document.querySelector('#' + _simplBoxId + ' img');

    photo.style.transform = 'rotate(' + _angle + 'deg)';
  };

  /**
   * Briefly show navigation buttons on photo to alert user about photo navigation
   */
  _showNavButtons = function () {
    var div;

    div = document.getElementById(_simplBoxId);
    div.classList.add('left');
    div.classList.add('right');

    window.setTimeout(function () {
      div.classList.remove('left');
      div.classList.remove('right');
    }, 1500);
  };


  _initialize();
  options = null;
  return _this;
};


module.exports = Lightbox;
