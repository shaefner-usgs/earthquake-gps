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

  private function _getFile ($type, $suffix) {
    $baseUri = $GLOBALS['MOUNT_PATH'] . '/data';
    $dataPath = $this->_getPath($type);
    $file = "$baseUri/$dataPath/" . $this->model->station . $suffix;

    return $file;
  }

  private function _getMap () {
    return '<div class="map"></div>';
  }

  private function _getPath ($type) {
    return 'networks/' . $this->model->network . '/' . $this->model->station .
      '/' . $type;
  }

  private function _getPlots () {
    $lookup = [
      'flickernoise' => 'Flicker Noise',
      'randomwalk' => 'Random Walk',
      'rms' => 'RMS (mm)',
      'sigma' => 'Uncertainty (mm/yr)	',
      'velocity' => 'Velocity (mm/yr)	',
      'whitenoise' => 'White Noise'
    ];
    $explanation = '
      <p>These plots depict the north, east and up components of
      the station as a function of time. <a href="/monitoring/gps/plots.php">More
      detailed explanation</a> &raquo;</p>
      <p>Dashed lines show offsets due to:</p>
      <ul class="no-style">
        <li><mark class="green">Green</mark> &ndash; antenna changes from site logs</li>
        <li><mark class="red">Red</mark> &ndash; earthquakes</li>
        <li><mark class="blue">Blue</mark> &ndash; manually entered</li>
      </ul>';
    $html = '<div class="tablist">';

    $types = [
      'itrf2008' => 'ITRF2008',
      'nafixed' => 'NA-fixed',
      'cleaned' => 'Cleaned'
    ];

    foreach ($types as $type => $name) {
      $baseDir = $GLOBALS['DATA_DIR'];
      $baseImg = $this->model->station . '.png';
      $baseUri = $GLOBALS['MOUNT_PATH'] . '/data';
      $dataPath = $this->_getPath($type);

      $baseImgSrc = "$baseUri/$dataPath/$baseImg";

      if (is_file("$baseDir/$dataPath/$baseImg")) {
        $toggle = '
          <nav class="' . $type . '">
            <span>Detrended:</span>
            <ul class="no-style pipelist">
              <li><a href="' . $this->_getFile($type, '_30.png') . '">Past 30 days</a></li>
              <li><a href="' . $this->_getFile($type, '_90.png') . '">Past 90 days</a></li>
              <li><a href="' . $this->_getFile($type, '_365.png') . '">Past year</a></li>
              <li><a href="' . $this->_getFile($type, '_730.png') . '">Past 2 years</a></li>
              <li><a href="' . $this->_getFile($type, '.png') . '" class="selected">All data</a></li>
            </ul>
            <span>Trended:</span>
            <ul class="no-style pipelist">
              <li><a href="' . $this->_getFile($type, '_wtrend.png') . '">All data</a></li>
            </ul>
          </nav>';

        $downloads = '
          <nav class="downloads">
            <span>Plot:</span>
            <ul class="no-style">
              <li><a href="' . $this->_getFile($type, '.gmt.gz') . '">
                <abbr title="Generic Mapping Tools">GMT</abbr> Script
              </a></li>
            </ul>
            <span>Raw Data:</span>
            <ul class="no-style">
              <li><a href="' . $this->_getFile($type, '.rneu') .'">All</a></li>
            </ul>
            <span>Detrended Data:</span>
            <ul class="no-style pipelist">
              <li><a href="' . $this->_getFile($type, '_N.data.gz') .'">North</a></li>
              <li><a href="' . $this->_getFile($type, '_E.data.gz') .'">East</a></li>
              <li><a href="' . $this->_getFile($type, '_U.data.gz') .'">Up</a></li>
            </ul>
            <span>Trended Data:</span>
            <ul class="no-style pipelist">
              <li><a href="' . $this->_getFile($type, '_N_wtrend.data.gz') .'">North</a></li>
              <li><a href="' . $this->_getFile($type, '_E_wtrend.data.gz') .'">East</a></li>
              <li><a href="' . $this->_getFile($type, '_U_wtrend.data.gz') .'">Up</a></li>
            </ul>
          </nav>
        ';

        $table = '<table>';
        $rows = '';
        foreach($this->model->velocities[$type] as $direction => $data) {
          $rows .= '<tr><th>' . ucfirst($direction) . '</th>';
          $header = '<tr><td class="empty"></td>';
          foreach($data as $key => $value) {
            $rows .= "<td>$value</td>";
            $header .= '<th>' . $lookup[$key] . '</th>';
          }
          $rows .= '</tr>';
          $header .= '</tr>';
        }
        $table .= $header;
        $table .= $rows;
        $table .= '</table>';

        $html .= sprintf('
          <section class="panel" data-title="%s">
            <header>
              <h2>%s</h2>
            </header>
            %s
            <img src="%s" class="toggle" alt="Plot showing %s data (All data)" />
            %s
            <h3>Downloads</h3>
            %s
            <h3>Table</h3>
            %s
          </section>',
          $name,
          $name,
          $toggle,
          $baseImgSrc,
          $name,
          $explanation,
          $downloads,
          $table
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
