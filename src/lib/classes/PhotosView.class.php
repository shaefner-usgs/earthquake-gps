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
      print '<pre>';
      print var_dump($this->model);
      print '</pre>';
    }
  }
}
