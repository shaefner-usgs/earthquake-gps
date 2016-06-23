/* global SimplBox */
'use strict';


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
          if (el) {
            el.parentNode.removeChild(el);
          }
      },
      closeButtonOn = function (base) {
          var div = document.createElement('div');
          div.setAttribute('id', 'simplbox-close');
          div.innerHTML = '<i class="material-icons">cancel</i>';
          document.body.appendChild(div);
          base.API_AddEvent(div, 'click touchend', function () {
              base.API_RemoveImageElement();
              return false;
          });
      },
      addCssClass = function () {
        var id = SimplBox.options.imageElementId;
        document.getElementById(id).classList.add('material-icons');
      },
      addClickHandler = function (base) {
        var id = SimplBox.options.imageElementId,
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
      },
      closeButtonOff = function () {
          var el = document.getElementById('simplbox-close');
          if (el) {
            el.parentNode.removeChild(el);
          }
      },
      captionOff = function () {
        var el = document.getElementById('simplbox-caption');
        if (el) {
          el.parentNode.removeChild(el);
        }
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
      addClickHandler(simplbox);
      addCssClass();
  }
});

simplbox.init();
