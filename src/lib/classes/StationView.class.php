<?php

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
    $campaignList = '<h2>Campaign List</h2>';
    $networks = $this->model->networkList;

    $campaignList .= '<ul>';
    foreach($networks as $network) {
      $campaignList .= sprintf('<li><a href="%s/%s/">%s</a></li>',
        $GLOBALS['MOUNT_PATH'],
        $network,
        $network
      );
    }
    $campaignList .= '</ul>';

    return $campaignList;
  }

  private function _getData () {
    $data = '<div class="tablist">';
    $types = [
      'itrf2008' => 'ITRF2008',
      'nafixed' => 'NA-fixed',
      'cleaned' => 'Cleaned'
    ];

    $explanation = $this->_getExplanation();

    foreach($types as $type => $name) {
      $baseDir = $GLOBALS['DATA_DIR'];
      $baseImg = $this->model->station . '.png';
      $baseUri = $GLOBALS['MOUNT_PATH'] . '/data';
      $dataPath = $this->_getPath($type);

      $baseImgSrc = "$baseUri/$dataPath/$baseImg";

      $navDownloads = $this->_getNavDownloads($type);
      $navPlots = $this->_getNavPlots($type);
      $table = $this->_getTable($type);

      if (is_file("$baseDir/$dataPath/$baseImg")) {
        $data .= sprintf('
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
          $navPlots,
          $baseImgSrc,
          $name,
          $explanation,
          $navDownloads,
          $table
        );
      }
    }
    $data .= '</div>';

    return $data;
  }

  private function _getDisclaimer () {
    return '<p><small>These results are preliminary. The station positions are
      unchecked and should not be used for any engineering applications. There
      may be errors in the antenna heights. The velocities are very dependent
      on the length of the span of observations. The presence of outliers
      (errant observations) sometimes contaminates the velocities.</small></p>';
  }

  private function _getExplanation () {
    return '<p>These plots depict the north, east and up components of
      the station as a function of time. <a href="/monitoring/gps/plots.php">
      More detailed explanation</a> &raquo;</p>
      <p>Dashed lines show offsets due to:</p>
      <ul class="no-style">
        <li><mark class="green">Green</mark> &ndash; antenna changes from site logs</li>
        <li><mark class="red">Red</mark> &ndash; earthquakes</li>
        <li><mark class="blue">Blue</mark> &ndash; manually entered</li>
      </ul>';
  }

  private function _getHref ($type, $suffix) {
    $baseUri = $GLOBALS['MOUNT_PATH'] . '/data';
    $dataPath = $this->_getPath($type);
    $href = "$baseUri/$dataPath/" . $this->model->station . $suffix;

    return $href;
  }

  private function _getLinkList () {
    $linkList = '<h2>Station Details</h2>';
    $links = $this->model->links;

    $linkList .= '<ul>';
    foreach($links as $key => $value) {
      $linkList .= '<li><a href="' . $value . '">' . $key . '</a></li>';
    }
    $linkList .= '</ul>';

    return $linkList;
  }

  private function _getMap () {
    return '<div class="map"></div>';
  }

  private function _getNavDownloads ($type) {
    $navDownloads = '
      <nav class="downloads">
        <span>Plot:</span>
        <ul class="no-style">
          <li><a href="' . $this->_getHref($type, '.gmt.gz') . '">
            <abbr title="Generic Mapping Tools">GMT</abbr> Script
          </a></li>
        </ul>
        <span>Raw Data:</span>
        <ul class="no-style">
          <li><a href="' . $this->_getHref($type, '.rneu') .'">All</a></li>
        </ul>
        <span>Detrended Data:</span>
        <ul class="no-style pipelist">
          <li><a href="' . $this->_getHref($type, '_N.data.gz') .'">North</a></li>
          <li><a href="' . $this->_getHref($type, '_E.data.gz') .'">East</a></li>
          <li><a href="' . $this->_getHref($type, '_U.data.gz') .'">Up</a></li>
        </ul>
        <span>Trended Data:</span>
        <ul class="no-style pipelist">
          <li><a href="' . $this->_getHref($type, '_N_wtrend.data.gz') .'">North</a></li>
          <li><a href="' . $this->_getHref($type, '_E_wtrend.data.gz') .'">East</a></li>
          <li><a href="' . $this->_getHref($type, '_U_wtrend.data.gz') .'">Up</a></li>
        </ul>
      </nav>';

    return $navDownloads;
  }

  private function _getNavPlots ($type) {
    $navPlots = '
      <nav class="plots ' . $type . '">
        <span>Detrended:</span>
        <ul class="no-style pipelist">
          <li><a href="' . $this->_getHref($type, '_30.png') . '">Past 30 days</a></li>
          <li><a href="' . $this->_getHref($type, '_90.png') . '">Past 90 days</a></li>
          <li><a href="' . $this->_getHref($type, '_365.png') . '">Past year</a></li>
          <li><a href="' . $this->_getHref($type, '_730.png') . '">Past 2 years</a></li>
          <li><a href="' . $this->_getHref($type, '.png') . '" class="selected">All data</a></li>
        </ul>
        <span>Trended:</span>
        <ul class="no-style pipelist">
          <li><a href="' . $this->_getHref($type, '_wtrend.png') . '">All data</a></li>
        </ul>
      </nav>';

    return $navPlots;
  }

  private function _getPath ($type) {
    return 'networks/' . $this->model->network . '/' . $this->model->station .
      '/' . $type;
  }

  private function _getTable ($type) {
    $lookup = [
      'flickernoise' => 'Flicker Noise',
      'randomwalk' => 'Random Walk',
      'rms' => 'RMS (mm)',
      'sigma' => 'Uncertainty (mm/yr)	',
      'velocity' => 'Velocity (mm/yr)	',
      'whitenoise' => 'White Noise'
    ];

    $rows = '';
    $table = '<table>';
    $types = $this->model->velocities[$type];

    foreach($types as $direction => $data) {
      $header = '<tr><td class="empty"></td>';
      $rows .= '<tr><th>' . ucfirst($direction) . '</th>';
      foreach($data as $key => $value) {
        $header .= '<th>' . $lookup[$key] . '</th>';
        $rows .= "<td>$value</td>";
      }
      $header .= '</tr>';
      $rows .= '</tr>';
    }

    $table .= $header;
    $table .= $rows;
    $table .= '</table>';

    return $table;
  }

  public function render () {
    // print '<section class="row">';
    // print $this->_getLinkList();
    // print '</section>';

    print $this->_getMap();
    print $this->_getData();
    print $this->_getDisclaimer();
    print $this->_getLinkList();
    print $this->_getCampaignList();
    print $this->_getBackLink();

    // print '<pre>';
    // print var_dump($this->model);
    // print '</pre>';
  }
}
