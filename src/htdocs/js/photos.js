///* global SimplBox */
'use strict';

/* jshint ignore:start */
var preLoadIconOn = function () {
          var div1 = document.createElement('div'),
              div2 = document.createElement('div');
              div1.setAttribute('id', 'simplbox-loading');
              div1.appendChild(div2);
          document.body.appendChild(div1);
      },
      preLoadIconOff = function () {
          var el = document.getElementById('simplbox-loading');
          el.parentNode.removeChild(el);
      },
      overlayOn = function () {
          var div = document.createElement('div');
          div.setAttribute('id', 'simplbox-overlay');
          document.body.appendChild(div);
      },
      overlayOff = function () {
          var el = document.getElementById('simplbox-overlay');
          el.parentNode.removeChild(el);
      },
      closeButtonOn = function (base) {
          var div = document.createElement('div');
          div.setAttribute('id', 'simplbox-close');
          document.body.appendChild(div);
          div = document.getElementById('simplbox-close');
          base.API_AddEvent(div, 'click touchend', function () {
              base.API_RemoveImageElement();
              return false;
          });
      },
      closeButtonOff = function () {
          var el = document.getElementById('simplbox-close');
          el.parentNode.removeChild(el);
      },
      captionOn = function (base) {
          var documentFragment = document.createDocumentFragment(),
              newElement = document.createElement('div'),
              newText = document.createTextNode(base.m_Alt);

          captionOff();

          newElement.setAttribute('id', 'simplbox-caption');
          newElement.appendChild(newText);
          documentFragment.appendChild(newElement);
          document.body.appendChild(documentFragment);
      },
      captionOff = function () {
          var el = document.getElementById('simplbox-caption');
          if (el) {
            el.parentNode.removeChild(el);
          }
      };


var thumbs = document.querySelectorAll('[data-simplbox]');
var simplbox = new SimplBox(thumbs, {
  imageSize: 0.8,
  quitOnDocumentClick: true,
  quitOnImageClick: false,
  animationSpeed: 0,
  onStart: function () {
      overlayOn();
      closeButtonOn(simplbox);
  },
  onEnd: function () {
      overlayOff();
      closeButtonOff();
      captionOff();
  },
  onImageLoadStart: function () {
      preLoadIconOn();
      captionOn(simplbox);
  },
  onImageLoadEnd: function () {
      preLoadIconOff();
  }
});

simplbox.init();

/* jshint ignore:end */
