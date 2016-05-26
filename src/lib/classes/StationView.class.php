<?php

include '../conf/config.inc.php'; // app config

/**
 * Station view
 * - creates the HTML for station.php
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class StationView {
  private $model;

  public function __construct (Station $model) {
    $this->model = $model;
  }

  private function _getBackLink () {
    return sprintf('<p class="back">&laquo; <a href="%s/%s/">Back to %s network</a></p>',
      $GLOBALS['MOUNT_PATH'],
      $this->model->network,
      $this->model->network
    );
  }

  private function _getCampaignList () {
    return '<h2>Campaign List</h2>';
  }

  private function _getDisclaimer () {
    return '<p><small>These results are preliminary. The station positions are
      unchecked and should not be used for any engineering applications. There
      may be errors in the antenna heights. The velocities are very dependent
      on the length of the span of observations. The presence of outliers
      (errant observations) sometimes contaminates the velocities.</small></p>';
  }

  private function _getMap () {
    return '<div class="map"></div>';
  }

  private function _getPlots () {
    $explanation = '
      <p>These plots depict the north, east and up components of
      the station as a function of time. <a href="/monitoring/gps/plots.php">More
      detailed explanation</a> &raquo;</p>
      <p>Dashed lines show offsets due to:</p>
      <ul class="no-style">
        <li><mark class="green">Green</mark>: antenna changes from site logs</li>
        <li><mark class="red">Red</mark>: earthquakes</li>
        <li><mark class="blue">Blue</mark>: manually entered</li>
      </ul>';

    $types = [
      'itrf2008' => 'ITRF2008',
      'nafixed' => 'NA-fixed',
      'filtered' => 'Filtered NA-fixed',
      'cleaned' => 'Detrended'
    ];

    $html = '<div class="tablist">';

    foreach ($types as $type => $name) {
      $baseDir = $GLOBALS['DATA_DIR'];
      $baseUri = $GLOBALS['MOUNT_PATH'] . '/data';
      $imgPath = 'networks/' . $this->model->network . '/' .
        $this->model->station . '/' . $type;
      $file = $this->model->station . '.png';
      $nav = '';
      $toggle = '';

      if (is_file("$baseDir/$imgPath/$file")) {
        $imgSrc = "$baseUri/$imgPath/$file";
        if ($type === 'cleaned') {
          $toggle = 'toggle';
          $imgSrc30 = str_replace('.png', '_30.png', $imgSrc);
          $imgSrc90 = str_replace('.png', '_90.png', $imgSrc);
          $imgSrc365 = str_replace('.png', '_365.png', $imgSrc);
          $imgSrc730 = str_replace('.png', '_730.png', $imgSrc);
          $nav = '
            <ul class="nav-toggle no-style pipelist">
              <li><a href="' . $imgSrc30 . '">Past 30 days</a></li>
              <li><a href="' . $imgSrc90 . '">Past 90 days</a></li>
              <li><a href="' . $imgSrc365 . '">Past year</a></li>
              <li><a href="' . $imgSrc730 . '">Past 2 years</a></li>
              <li><a href="' . $imgSrc . '" class="selected">All data</a></li>
            </ul>';
        }
        $html .= sprintf('
          <section class="panel" data-title="%s">
            <header>
              <h3>%s</h3>
            </header>
            %s
            <img src="%s" class="%s" alt="Plot showing %s data" />
            %s
          </section>',
          $name,
          $name,
          $nav,
          $imgSrc,
          $toggle,
          $name,
          $explanation
        );
      }
    }
    $html .= '</div>';

    return $html;
  }

  private function _getStationDetails () {
    return '<h2>Station Details</h2>';
  }

  public function render () {

    // print '<section class="row">';
    //
    // print $this->_getStationDetails();
    // print $this->_getCampaignList();
    //
    // print '</section>';

    print $this->_getMap();

    print $this->_getPlots();

    // campaign station -> photos
    // continuous station -> kinematic plots

    // print '<pre>';
    // print var_dump($this->model);
    // print '</pre>';

    print $this->_getDisclaimer();
    print $this->_getBackLink();
  }
}
