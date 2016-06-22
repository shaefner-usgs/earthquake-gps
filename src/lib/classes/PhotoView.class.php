<?php

class PhotoView {
  private $_collection;

  public function __construct (PhotoCollection $collection) {
    $this->_collection = $collection;
  }

  private function _getBackLink () {
    return sprintf('<p class="back">&laquo;
        <a href="%s/%s/%s/">Back to station %s</a>
      </p>',
      $GLOBALS['MOUNT_PATH'],
      $this->_collection->network,
      $this->_collection->station,
      strtoupper($this->_collection->station)
    );
  }

  private function _getPhotos () {
    if (!$this->_collection->photos) {
      $photosHtml = '<p class="alert info">No Photos Found</p>';
    } else {
      // loop thru each photo (grouped by date taken)
      foreach ($this->_collection->photos as $date => $photos) {
        $photosHtml = '<h2>' . date('F j, Y', strtotime($date)) . '</h2>';
        $photosHtml .= '<ul class="no-style photos">';
        foreach ($photos as $photo) {
          $photosHtml .= sprintf('<li class="%s">
              <h3>%s</h3>
              <a href="%s/screen/%s" data-simplbox><img src="%s/thumb/%s" alt="%s"/></a>
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
        $photosHtml .= '</ul>';
      }
    }

    return $photosHtml;
  }

  public function render () {
    print $this->_getPhotos();
    print $this->_getBackLink();
  }
}
