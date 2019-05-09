'use strict';


var Lightbox = require('Lightbox');

var addListeners,
    getButton;

Lightbox({
  el: document.querySelectorAll('[data-simplbox]')
});


/**
 * Add event listeners for full-size (download) image buttons on thumbnails
 */
addListeners = function () {
  var onMouseout,
      onMouseover,
      thumbs;

  onMouseout = function (e) {
    getButton(e.target).classList.add('hide');
  };
  onMouseover = function (e) {
    getButton(e.target).classList.remove('hide');
  };

  thumbs = document.querySelectorAll('.photos img');
  Array.prototype.slice.call(thumbs).forEach(function(thumb) {
    var button = getButton(thumb);

    button.classList.add('hide'); // hide all buttons initially

    thumb.addEventListener('mouseout', onMouseout);
    [button, thumb].forEach(function(el) {
      el.addEventListener('mouseover', onMouseover);
      el.addEventListener('click', function () {
        button.classList.add('hide'); // hide on click
      });
    });
  });
};

/**
 * Get download button element
 *
 * @param el {Element}
 *    button element or <img> associated with download button
 */
getButton = function (el) {
  var button = el; // default

  if (el.nodeName.toLowerCase() === 'img') {
    button = el.parentNode.nextElementSibling;
  }

  return button;
};

addListeners();
