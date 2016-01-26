<?php

/**
 * Station view
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class StationView {
  private $model;

  public function __construct ($model) {
    $this->model = $model;
  }

  private function _getCampaignListMarkup () {
    return '<h2>Campaign List</h2>';
  }

  private function _getStationDetailsMarkup () {
    return '<h2>Station Details</h2>';
  }

  public function render () {
    print $this->_getStationDetailsMarkup();
    print $this->_getCampaignListMarkup();

    print '<pre>';
    print var_dump($this->model);
    print '</pre>';
  }
}
