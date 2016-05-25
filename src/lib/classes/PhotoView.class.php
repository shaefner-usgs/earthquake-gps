<?php

class PhotoView {
  private $_collection;

  public function __construct (PhotoCollection $collection) {
    $this->_collection = $collection;
  }

  public function render () {
    if (!$this->_collection->photos) {
      print '<p class="alert warning">No Photos Found</p>';
    } else {
      // loop thru each photo (grouped by date taken)
      foreach ($this->_collection->photos as $date => $photos) {
        print '<h2>' . date('F j, Y', strtotime($date)) . '</h2>';
        print '<ul class="no-style photos">';
        foreach ($photos as $photo) {
          printf('<li class="%s">
              <h3>%s</h3>
              <a href="%s/screen/%s">
                <img src="%s/thumb/%s" alt="thumbnail image (%s)"/>
              </a>
            </li>',
            $photo->code,
            $photo->type,
            $this->_collection->path,
            $photo->file,
            $this->_collection->path,
            $photo->file,
            $photo->type
          );
        }
        print '</ul>';
      }
    }
  }
}
