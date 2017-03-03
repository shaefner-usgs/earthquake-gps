<?php

/**
 * Station view
 * - creates the HTML for station.php
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class StationView {
  private $_model;

  public function __construct (Station $model) {
    $this->_baseDir = $GLOBALS['DATA_DIR'];
    $this->_baseUri = $GLOBALS['MOUNT_PATH'] . '/data';

    $this->_model = $model;
  }

  private function _getBackLink () {
    return sprintf('<p class="back">&laquo; <a href="%s/%s">Back to %s network</a></p>',
      $GLOBALS['MOUNT_PATH'],
      $this->_model->network,
      $this->_model->network
    );
  }

  private function _getCampaignList () {
    $campaignListHtml = '<h2>Campaign List</h2>';
    $networks = $this->_model->networkList;

    $campaignListHtml .= '<ul>';
    foreach ($networks as $network) {
      $campaignListHtml .= sprintf('<li><a href="%s/%s/%s">%s</a></li>',
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
    $html = '<div class="tablist">';
    $datatypes = [
      'nafixed' => 'NA-fixed',
      'itrf2008' => 'ITRF2008',
      'filtered' => 'Filtered'
    ];

    $explanation = $this->_getExplanation();

    foreach ($datatypes as $datatype => $name) {
      $baseImg = $this->_model->station . '.png';

      $dataPath = $this->_getPath($datatype);
      $downloadsHtml = $this->_getDownloads($datatype);

      $tables = [
        'Noise' => $this->_getTable('noise', $datatype),
        'Offsets' => $this->_getTable('offsets', $datatype),
        'Post-seismic' => $this->_getTable('postSeismic', $datatype),
        'Seasonal' => $this->_getTable('seasonal', $datatype),
        'Velocities' => $this->_getTable('velocities', $datatype)
      ];

      $plotsHtml = '';
      if (is_file("$this->_baseDir/$dataPath/$baseImg")) {
        $navPlots = $this->_getNavPlots($datatype);
        $image = sprintf('<div class="image">
            <img src="%s/%s/%s" class="toggle" alt="Plot showing %s data (All data)" />
          </div>',
          $this->_baseUri,
          $dataPath,
          $baseImg,
          $name
        );

        $plotsHtml = $navPlots . $image . $explanation;
      }

      $tablesHtml = '';
      foreach ($tables as $tableName => $tableData) {
        if ($tables[$tableName]) { // value is empty if no data in database
          $tablesHtml .= "<h3>$tableName</h3>$tableData";
        }
      }

      $html .= sprintf('
        <section class="panel" data-title="%s">
          <header>
            <h2>%s</h2>
          </header>
          %s
          <h3>Downloads</h3>
          %s
          %s
        </section>',
        $name,
        $name,
        $plotsHtml,
        $downloadsHtml,
        $tablesHtml
      );
    }

    $html .= '</div>';

    return $html;
  }

  private function _getDisclaimer () {
    return '<p><small>These results are preliminary. The station positions are
      unchecked and should not be used for any engineering applications. There
      may be errors in the antenna heights. The velocities are very dependent
      on the length of the span of observations. The presence of outliers
      (errant observations) sometimes contaminates the velocities.</small></p>';
  }

  private function _getDownloads ($datatype) {
    $html = '
      <nav class="downloads">
        <span>Raw Data:</span>
        <ul class="no-style">
          <li><a href="' . $this->_getHref($datatype, '.rneu') .'">All</a></li>
        </ul>
        <span>Detrended Data:</span>
        <ul class="no-style pipelist">
          <li><a href="' . $this->_getHref($datatype, '_N.data.gz') .'">North</a></li>
          <li><a href="' . $this->_getHref($datatype, '_E.data.gz') .'">East</a></li>
          <li><a href="' . $this->_getHref($datatype, '_U.data.gz') .'">Up</a></li>
        </ul>
        <span>Trended Data:</span>
        <ul class="no-style pipelist">
          <li><a href="' . $this->_getHref($datatype, '_N_wtrend.data.gz') .'">North</a></li>
          <li><a href="' . $this->_getHref($datatype, '_E_wtrend.data.gz') .'">East</a></li>
          <li><a href="' . $this->_getHref($datatype, '_U_wtrend.data.gz') .'">Up</a></li>
        </ul>
      </nav>';

    return $html;
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

  private function _getHref ($datatype, $suffix) {
    $dataPath = $this->_getPath($datatype);
    $file = $this->_model->station . $suffix;
    $href = "$this->_baseUri/$dataPath/$file";

    // Check if img exists; if not link to no-data img
    if (preg_match('/png$/', $file) && !is_file("$this->_baseDir/$dataPath/$file")) {
      $href = "#no-data";
    }

    return $href;
  }

  private function _getLinkList () {
    $html = '<h2>Station Details</h2>';
    $links = $this->_model->links;

    $html .= '<ul>';
    foreach ($links as $key => $value) {
      $html .= '<li><a href="' . $value . '">' . $key . '</a></li>';
    }
    $html .= '</ul>';

    return $html;
  }

  private function _getMap () {
    return '<div class="map"></div>';
  }

  private function _getNavPlots ($datatype) {
    $html = '
      <nav class="plots ' . $datatype . '">
        <span>Detrended:</span>
        <ul class="no-style pipelist">
          <li><a href="' . $this->_getHref($datatype, '_30.png') . '">Past 30 days</a></li>
          <li><a href="' . $this->_getHref($datatype, '_90.png') . '">Past 90 days</a></li>
          <li><a href="' . $this->_getHref($datatype, '_365.png') . '">Past year</a></li>
          <li><a href="' . $this->_getHref($datatype, '_730.png') . '">Past 2 years</a></li>
          <li><a href="' . $this->_getHref($datatype, '.png') . '" class="selected">All data</a></li>
        </ul>
        <span>Trended:</span>
        <ul class="no-style pipelist">
          <li><a href="' . $this->_getHref($datatype, '_wtrend.png') . '">All data</a></li>
        </ul>
      </nav>';

    return $html;
  }

  private function _getPath ($datatype) {
    return 'networks/' . $this->_model->network . '/' . $this->_model->station .
      '/' . $datatype;
  }

  private function _getTable ($table, $datatype, $lookupTable=NULL) {
    $components = [ // listed in display order on web page
      'N' => 'North',
      'E' => 'East',
      'U' => 'Up'
    ];
    $html = '';
    $rows = $this->_model->$table;

    if ($rows) {
      $html = '<table>';
      $trs = [];
      foreach ($rows as $fields) {
        if ($fields['datatype'] === $datatype) {
          $component = $fields['component'];
          $direction = $components[$component];
          $th = '<tr><td class="empty"></td>';
          $tr = '<tr class="' . strtolower($direction) . '">';
          $tr .= "<th>$direction</th>";

          unset( // hide these values from the table view
            $fields['component'],
            $fields['datatype'],
            $fields['id'],
            $fields['network'],
            $fields['station']
          );
          foreach ($fields as $key=>$value) {
            $th .= "<th>$key</th>";
            $tr .= "<td>$value</td>";
          }
          $th .= '</tr>';
          $tr .= '</tr>';
          $trs[$component] = $tr;
        }
      }
      $html .= $th;
      foreach ($components as $key=>$value) {
        $html .= $trs[$key];
      }
      $html .= '</table>';
    }

    // Don't send back an empty table (happens if no data for datatype)
    if ($html === '<table></table>') {
      $html = '';
    }

    return $html;
  }

  public function render () {
    print '<div class="row">';

    print '<div class="column two-of-three">';
    print $this->_getMap();
    print '</div>';

    print '<div class="column one-of-three">';
    print $this->_getLinkList();
    print $this->_getCampaignList();
    print '</div>';

    print '</div>';

    print $this->_getData();
    print $this->_getDisclaimer();
    print $this->_getBackLink();
  }
}
