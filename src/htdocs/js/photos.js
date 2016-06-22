/* global SimplBox */
'use strict';


var thumbs = document.querySelectorAll('[data-simplbox]');
var simplbox = new SimplBox(thumbs);

console.log(simplbox);

/* jshint ignore:start */
simplbox.init({
  imageSize: 0.8,
  quitOnDocumentClick: true,
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
/* jshint ignore:end */
