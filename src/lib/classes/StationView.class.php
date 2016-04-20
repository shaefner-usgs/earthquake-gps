<?php

/**
 * Station view
 * - creates the HTML for station.php
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class StationView {
  private $model;

  public function __construct (StationModel $model) {
    $this->model = $model;
  }

  private function _getCampaignList () {
    return '<h2>Campaign List</h2>';
  }

  private function _getStationDetails () {
    return '<h2>Station Details</h2>';
  }

  private function _getDisclaimer () {
    return '<p><small>These results are preliminary. The station positions are
      unchecked and should not be used for any engineering applications. There
      may be errors in the antenna heights. The velocities are very dependent
      on the length of the span of observations. The presence of outliers
      (errant observations) sometimes contaminates the velocities.</small></p>';
  }

  public function render () {
    print $this->_getStationDetails();
    print $this->_getCampaignList();

    // campaign station -> photos
    // continuous station -> kinematic plots

    print '<pre>';
    print var_dump($this->model);
    print '</pre>';

    print $this->_getDisclaimer();
  }
}
