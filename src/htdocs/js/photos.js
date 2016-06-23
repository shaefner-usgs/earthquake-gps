/* global SimplBox */
'use strict';


var addClickHandler = function (base) {
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

addCssClass = function () {
  var id = SimplBox.options.imageElementId;
  document.getElementById(id).classList.add('material-icons');
},

captionOff = function () {
  var el = document.getElementById('simplbox-caption');
  if (el) {
    el.parentNode.removeChild(el);
  }
},

captionOn = function (base) {
  var fragment = document.createDocumentFragment(),
  div = document.createElement('div'),
  text = document.createTextNode(base.m_Alt);

  captionOff();

  div.setAttribute('id', 'simplbox-caption');
  div.appendChild(text);
  fragment.appendChild(div);
  document.body.appendChild(fragment);
},

closeButtonOff = function () {
  var el = document.getElementById('simplbox-close');
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

overlayOff = function () {
  var el = document.getElementById('simplbox-overlay');
  if (el) {
    el.parentNode.removeChild(el);
  }
},

overlayOn = function () {
  var div = document.createElement('div');
  div.setAttribute('id', 'simplbox-overlay');
  document.body.appendChild(div);
},

preLoadIconOff = function () {
  var el = document.getElementById('simplbox-loading');
  el.parentNode.removeChild(el);
},

preLoadIconOn = function () {
  var div1 = document.createElement('div'),
      div2 = document.createElement('div');
      div1.setAttribute('id', 'simplbox-loading');
      div1.appendChild(div2);
    document.body.appendChild(div1);
};


// Initialize SimplBox
var thumbs = document.querySelectorAll('[data-simplbox]');

var simplbox = new SimplBox(thumbs, {
  animationSpeed: 0,
  imageSize: 0.8,
  quitOnDocumentClick: true,
  quitOnImageClick: false,
  onImageLoadEnd: function () {
    preLoadIconOff();
    addClickHandler(simplbox);
    addCssClass();
  },
  onImageLoadStart: function () {
    preLoadIconOn();
    captionOn(simplbox);
  },
  onEnd: function () {
    overlayOff();
    closeButtonOff();
    captionOff();
  },
  onStart: function () {
    overlayOn();
    closeButtonOn(simplbox);
  }
});

simplbox.init();
