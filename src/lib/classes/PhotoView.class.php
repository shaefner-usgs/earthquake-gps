<?php

/**
 * Photo view
 * - creates the HTML for photos.php
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class PhotoView {
  private $_collection;

  public function __construct (PhotoCollection $collection) {
    $this->_collection = $collection;
  }

  private function _getBackLink () {
    return sprintf('<p class="back">&laquo;
        <a href="%s/%s/%s">Back to Station %s</a>
      </p>',
      $GLOBALS['MOUNT_PATH'],
      $this->_collection->network,
      $this->_collection->station,
      strtoupper($this->_collection->station)
    );
  }

  private function _getNavLinks () {
    $navLinksHtml = sprintf('<ul class="pipelist no-style">
        <li><strong>Photos</strong></li>
        <li><a href="%s/%s/%s/logs">Field Logs</a></li>
      </ul>',
      $GLOBALS['MOUNT_PATH'],
      $this->_collection->network,
      $this->_collection->station
    );

    return $navLinksHtml;
  }

  private function _getPhotos () {
    if (!$this->_collection->photos) {
      $photosHtml = '<p class="alert info">No Photos</p>';
    } else {
      $photosHtml = '';
      $count = 0;
      $total = $this->_collection->count;

      // loop thru each photo (grouped by date taken)
      foreach ($this->_collection->photos as $date => $photos) {
        $dateString = date('F j, Y', strtotime($date));
        $photosHtml .= '<h3>' . $dateString . '</h3>';
        $photosHtml .= '<ul class="no-style photos">';
        foreach ($photos as $photo) {
          $count ++;
          $photosHtml .= sprintf('<li class="%s">
              <h4>%s</h4>
              <a href="%s/screen/%s" data-simplbox><img width="144" height="144" src="%s/thumb/%s" alt="%s - %s (%d of %d)"/></a>
              <a class="fullsize" href="%s/full/%s"><i class="material-icons" title="Full resolution photo">&#xE2C4;</i></a>
            </li>',
            $photo->code,
            $photo->type,
            $this->_collection->path,
            $photo->file,
            $this->_collection->path,
            $photo->file,
            $photo->type,
            $dateString,
            $count,
            $total,
            $this->_collection->path,
            $photo->file
          );
        }
        $photosHtml .= '</ul>';
      }
    }

    return $photosHtml;
  }

  public function render () {
    print $this->_getNavLinks();
    print $this->_getPhotos();
    print $this->_getBackLink();
  }
}
