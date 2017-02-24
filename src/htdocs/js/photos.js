'use strict';


var Lightbox = require('Lightbox');

var initButtons,
    onMouseEvent;

Lightbox({
  el: document.querySelectorAll('[data-simplbox]')
});


/**
 * Show / hide buttons for full-size images
 */
onMouseEvent = function (e) {
  var button = e.target.parentNode.nextElementSibling;

  if (e.type === 'mouseover') {
    button.classList.remove('hide');
  } else {
    button.classList.add('hide');
  }
};

/**
 * Set up event listeners for full-size image buttons
 */
initButtons = function () {
  var photos = document.querySelectorAll('.photos img');

  Array.prototype.slice.call(photos).forEach(function(photo) {
    var button = photo.parentNode.nextElementSibling;

    button.classList.add('hide'); // hide all buttons initially
    button.addEventListener('mouseover', function () {
      this.classList.remove('hide'); // make button persistent
    });

    photo.addEventListener('mouseover', onMouseEvent);
    photo.addEventListener('mouseout', onMouseEvent);
  });
};

initButtons();
