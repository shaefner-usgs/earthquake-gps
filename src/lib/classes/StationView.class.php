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
    $this->_model = $model;
  }

  private function _getBackLink () {
    return sprintf('<p class="back">&laquo; <a href="%s/%s/">Back to %s network</a></p>',
      $GLOBALS['MOUNT_PATH'],
      $this->_model->network,
      $this->_model->network
    );
  }

  private function _getCampaignList () {
    $campaignListHtml = '<h2>Campaign List</h2>';
    $networks = $this->_model->networkList;

    $campaignListHtml .= '<ul>';
    foreach($networks as $network) {
      $campaignListHtml .= sprintf('<li><a href="%s/%s/%s/">%s</a></li>',
        $GLOBALS['MOUNT_PATH'],
        $network,
        $this->_model->station,
        $network
      );
    }
    $campaignListHtml .= '</ul>';

    return $campaignListHtml;
  }

  private function _getData () {
    $dataHtml = '<div class="tablist">';
    $types = [
      'nafixed' => 'NA-fixed',
      'itrf2008' => 'ITRF2008',
      'cleaned' => 'Cleaned'
    ];

    $explanation = $this->_getExplanation();

    foreach($types as $type => $name) {
      $baseDir = $GLOBALS['DATA_DIR'];
      $baseImg = $this->_model->station . '.png';
      $baseUri = $GLOBALS['MOUNT_PATH'] . '/data';
      $dataPath = $this->_getPath($type);

      $baseImgSrc = "$baseUri/$dataPath/$baseImg";

      if (is_file("$baseDir/$dataPath/$baseImg")) {
        $navDownloads = $this->_getNavDownloads($type);
        $navPlots = $this->_getNavPlots($type);
        $table = $this->_getTable($type);

        $dataHtml .= sprintf('
          <section class="panel" data-title="%s">
            <header>
              <h2>%s</h2>
            </header>
            %s
            <div class="image">
              <img src="%s" class="toggle" alt="Plot showing %s data (All data)" />
            </div>
            %s
            <h3>Downloads</h3>
            %s
            <h3>Velocities and Uncertainties</h3>
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
    $dataHtml .= '</div>';

    return $dataHtml;
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
    $baseDir = $GLOBALS['DATA_DIR'];
    $baseUri = $GLOBALS['MOUNT_PATH'] . '/data';
    $dataPath = $this->_getPath($type);
    $file = $this->_model->station . $suffix;
    $href = "$baseUri/$dataPath/$file";

    if (preg_match('/png$/', $file) && !is_file("$baseDir/$dataPath/$file")) {
      $href = "#no-data";
    }

    return $href;
  }

  private function _getLinkList () {
    $linkListHtml = '<h2>Station Details</h2>';
    $links = $this->_model->links;

    $linkListHtml .= '<ul>';
    foreach($links as $key => $value) {
      $linkListHtml .= '<li><a href="' . $value . '">' . $key . '</a></li>';
    }
    $linkListHtml .= '</ul>';

    return $linkListHtml;
  }

  private function _getMap () {
    return '<div class="map"></div>';
  }

  private function _getNavDownloads ($type) {
    $navDownloadsHtml = '
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

    return $navDownloadsHtml;
  }

  private function _getNavPlots ($type) {
    $navPlotsHtml = '
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

    return $navPlotsHtml;
  }

  private function _getPath ($type) {
    return 'networks/' . $this->_model->network . '/' . $this->_model->station .
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
    $tableHtml = '';
    if ($this->_model->velocities[$type]) {
      $components = $this->_model->velocities[$type];

      $tableHtml = '<table>';
      foreach($components as $direction => $data) {
        $header = '<tr><td class="empty"></td>';
        $rows .= '<tr><th>' . ucfirst($direction) . '</th>';
        foreach($data as $key => $value) {
          $header .= '<th>' . $lookup[$key] . '</th>';
          $rows .= "<td>$value</td>";
        }
        $header .= '</tr>';
        $rows .= '</tr>';
      }

      $tableHtml .= $header;
      $tableHtml .= $rows;
      $tableHtml .= '</table>';
    }

    return $tableHtml;
  }

  public function render () {
    print '<section class="row">';

    print '<div class="column two-of-three">';
    print $this->_getMap();
    print '</div>';

    print '<div class="column one-of-three">';
    print $this->_getLinkList();
    print $this->_getCampaignList();
    print '</div>';

    print '</section>';

    print $this->_getData();
    print $this->_getDisclaimer();
    print $this->_getBackLink();

    // print '<pre>';
    // print var_dump($this->_model);
    // print '</pre>';
  }
}
