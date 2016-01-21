<?php

/**
 * Station view
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class StationView {

  public function render () {
    echo this._getStationDetailsMarkup();
    echo this._getCampaignListMarkup();
    //  ....
  }

  private function _getStationDetailsMarkup () {
    return '<h2>Station Details</h2>';
  }

}
