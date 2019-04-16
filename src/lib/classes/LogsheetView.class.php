<?php

/**
 * Logsheet view
 * - creates the HTML for logsheets.php
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class LogsheetView {
  private $_collection;

  public function __construct (LogsheetCollection $collection) {
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

  private function _getLogSheets () {
    if (!$this->_collection->logsheets) {
      $logSheetsHtml = '<p class="alert info">No Field Logs</p>';
    } else {
      $logSheetsHtml = '';
      // loop thru each logsheet (grouped by date)
      foreach ($this->_collection->logsheets as $date => $logsheets) {
        $logSheetsHtml .= '<h3>' . date('F j, Y', strtotime($date)) . '</h3>';
        $logSheetsHtml .= '<ul class="no-style pipelist">';
        foreach ($logsheets as $logsheet) {
          $logSheetsHtml .= sprintf ('<li><a href="%s/%s">%s</a></li>',
            $this->_collection->path,
            $logsheet->file,
            $logsheet->type
          );
        }
        $logSheetsHtml .= '</ul>';
      }
    }

    return $logSheetsHtml;
  }

  private function _getNavLinks () {
    $navLinksHtml = '';
    if ($this->_collection->stationType === 'campaign') {
      $navLinksHtml .= sprintf('<ul class="pipelist no-style">
          <li><a href="%s/%s/%s/photos">Photos</a></li>
          <li><strong>Field Logs</strong></li>
        </ul>',
        $GLOBALS['MOUNT_PATH'],
        $this->_collection->network,
        $this->_collection->station
      );
    }

    return $navLinksHtml;
  }

  public function render () {
    print $this->_getNavLinks();
    print $this->_getLogSheets();
    print $this->_getBackLink();
  }
}
