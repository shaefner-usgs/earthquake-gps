<?php

class PhotosView {
  private $model;

  public function __construct (Photos $model) {
    $this->model = $model;
  }

  public function render () {
    if (!$this->model->photos) {
      print '<p class="alert warning">No Photos Found</p>';
    } else {
      foreach ($this->model->photos as $date => $photos) {
        print "<h2>$date</h2>";
        print '<ul>';
        foreach ($photos as $photo) {
          printf('<li><h3>%s</h3><img src="%s/thumb/%s" /></li>',
            $photo['name'],
            $this->model->path,
            $photo['file']
          );
        }
        print '</ul>';
      }
    }
  }
}
