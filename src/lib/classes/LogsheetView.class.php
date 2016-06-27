<?php

class LogsheetView {
  private $_collection;

  public function __construct (LogsheetCollection $collection) {
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

  private function _getLogSheets () {
    if (!$this->_collection->logsheets) {
      $logSheetsHtml = '<p class="alert info">No Logsheets Found</p>';
    } else {
      $logSheetsHtml = '';
      // loop thru each logsheet (grouped by date)
      foreach ($this->_collection->logsheets as $date => $logsheets) {
        $logSheetsHtml .= '<h2>' . date('F j, Y', strtotime($date)) . '</h2>';
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

  public function render () {
    print $this->_getLogSheets();
    print $this->_getBackLink();
  }
}
