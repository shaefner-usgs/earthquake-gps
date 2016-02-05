<?php

class PhotosView {
  private $_collection;

  public function __construct (PhotoCollection $collection) {
    $this->_collection = $collection;
  }

  public function render () {
    if (!$this->_collection->photos) {
      print '<p class="alert warning">No Photos Found</p>';
    } else {
      foreach ($this->_collection->photos as $date => $photos) {
        print "<h2>$date</h2>";
        print '<ul>';
        foreach ($photos as $photo) {
          printf('<li>
              <h3>%s</h3>
              <a href="%s/screen/%s">
                <img src="%s/thumb/%s" alt="thumbnail image (%s)"/>
              </a>
            </li>',
            $photo->name,
            $this->_collection->path,
            $photo->file,
            $this->_collection->path,
            $photo->file,
            $photo->name
          );
        }
        print '</ul>';
      }
    }
  }
}
