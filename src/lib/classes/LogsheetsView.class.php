<?php

class LogsheetsView {
  private $_collection;

  public function __construct (LogsheetCollection $collection) {
    $this->_collection = $collection;
  }

  public function render () {
    if (!$this->_collection->logsheets) {
      print '<p class="alert warning">No Logsheets Found</p>';
    } else {
      // loop thru each logsheet (grouped by date)
      foreach ($this->_collection->logsheets as $date => $logsheets) {
        print '<h2>' . date('F j, Y', strtotime($date)) . '</h2>';
        print '<ul>';
        foreach ($logsheets as $logsheet) {
          printf ('<li>
              <a href="%s/%s">%s</a>
            </li>',
            $this->_collection->path,
            $logsheet->file,
            $logsheet->type
          );
        }
        print '</ul>';
      }
    }
  }
}
