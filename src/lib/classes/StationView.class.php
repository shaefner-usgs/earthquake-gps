<?php

/**
 * GPS Station View
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class StationView {
  private $_baseDir,
    $_baseUri,
    $_collection,
    $_model;

  public function __construct (StationCollection $collection) {
    $this->_baseDir = $GLOBALS['DATA_DIR'];
    $this->_baseUri = $GLOBALS['MOUNT_PATH'] . '/data';
    $this->_collection = $collection;
    $this->_model = $collection->getStation($collection->selectedStation);
  }

  /**
   * Render the view
   */
  public function render () {
    print $this->_getSubtitle();

    print '<div class="row">';

    print '<div class="column two-of-three">';
    print $this->_getMap();
    print '</div>';

    print '<div class="column one-of-three details">';
    print $this->_getStationDetails();
    print $this->_getNetworks();
    print '</div>';

    print '</div>';

    print $this->_getTabbedContent();
    print $this->_getDisclaimer();
    print $this->_getBackLink();
    print $this->_getGeoJsonData();
  }

  /**
   * Get link back to Network page
   *
   * @return {String}
   */
  private function _getBackLink () {
    return sprintf('<p class="back">&laquo; <a href="%s/%s">Back to %s Network</a></p>',
      $GLOBALS['MOUNT_PATH'],
      $this->_model->network,
      $this->_model->network
    );
  }

  /**
   * Get a list of closest stations
   *
   * @return $html {String}
   */
  private function _getClosestStations () {
    $closestStations = $this->_model->getClosestStations($this->_collection);
    $html = '<ul class="closest links">';

    foreach ($closestStations as $closestStation) {
      $iconKey = $this->_model->getIconKey(
        $closestStation->stationtype,
        $closestStation->lastUpdate
      );
      $html .= sprintf('<li>
          <a href="%s/%s/%s">
            <img src="%s/img/pin-s-%s-2x.png" alt="%s station icon" />
            %s
            <small>(%s km)</small>
          </a>
        </li>',
        $GLOBALS['MOUNT_PATH'],
        $closestStation->network,
        $closestStation->station,
        $GLOBALS['MOUNT_PATH'],
        $iconKey,
        $iconKey,
        strtoupper($closestStation->station),
        round(floatval($closestStation->distance), 1)
      );
    }
    $html .= '</ul>';

    return $html;
  }

  /**
   * Get disclaimer text
   *
   * @return {String}
   */
  private function _getDisclaimer () {
    return '<p><small>These results are preliminary. The station positions are
      unchecked and should not be used for any engineering applications. There
      may be errors in the antenna heights. The velocities are very dependent
      on the length of the span of observations. The presence of outliers
      (errant observations) sometimes contaminates the velocities.</small></p>';
  }

  /**
   * Get download link
   *
   * @param $datatype {String <filtered | itrf2008 | nafixed>}
   * @param $fileType {String <text | zip>}
   * @param $fileSuffix {String}
   * @param $text {String}
   *
   * @return $html {String}
   */
  private function _getDownloadItem ($datatype, $fileType, $fileSuffix, $text) {
    $href = $this->_getHref($datatype, $fileSuffix);
    $hrefAttr = '';
    $titleAttr = '';

    if ($href) {
      $hrefAttr = ' href="' . $href . '"';
    } else {
      $titleAttr = ' title="No Data"';
    }

    $html = sprintf('<a%s%s class="%s">%s</a>',
      $hrefAttr,
      $titleAttr,
      $fileType,
      $text
    );

    return $html;
  }

  /**
   * Get a list of download links
   *
   * @param $datatype {String <filtered | itrf2008 | nafixed>}
   *
   * @return $html {String}
   */
  private function _getDownloads ($datatype) {
    $deletedHref = $this->_model->station . "/$datatype/deleted";

    $html = '
      <nav class="nav-downloads">
        <div>
          <h4>Raw Data</h4>
          <ul class="no-style downloads">
            <li>' . $this->_getDownloadItem($datatype, 'text', '.rneu', 'All') . '</li>
          </ul>
        </div>
        <div>
          <h4>Detrended Data</h4>
          <ul class="no-style downloads">
            <li>' . $this->_getDownloadItem($datatype, 'zip', '_N.data.gz', 'North') . '</li>
            <li>' . $this->_getDownloadItem($datatype, 'zip', '_E.data.gz', 'East') . '</li>
            <li>' . $this->_getDownloadItem($datatype, 'zip', '_U.data.gz', 'Up') . '</li>
          </ul>
        </div>
        <div>
          <h4>Trended Data</h4>
          <ul class="no-style downloads">
            <li>' . $this->_getDownloadItem($datatype, 'zip', '_N_wtrend.data.gz', 'North') . '</li>
            <li>' . $this->_getDownloadItem($datatype, 'zip', '_E_wtrend.data.gz', 'East') . '</li>
            <li>' . $this->_getDownloadItem($datatype, 'zip', '_U_wtrend.data.gz', 'Up') . '</li>
          </ul>
        </div>
        <div>
          <h4>Deleted Points</h4>
          <ul class="no-style downloads">
            <li><a href="' . $deletedHref .'" class="text">All</a></li>
          </ul>
        </div>
      </nav>';

    return $html;
  }

  /**
   * Get plot explanation text
   *
   * @param $datatype {String <filtered | itrf2008 | nafixed>}
   *
   * @return {String}
   */
  private function _getExplanation ($datatype) {
    $components = 'North, East and Up';
    if ($datatype === 'itrf2008') {
      $components = 'X, Y and Z';
    }

    return '<p>These plots depict the ' . $components . ' components of
      the station as a function of time.
      <a href="https://pubs.geoscienceworld.org/ssa/srl/article/88/3/916/284075/global-positioning-system-data-collection">More
      detailed explanation</a> &raquo;</p>
      <p>Dashed vertical lines show offsets (when present) due to:</p>
      <ul class="no-style">
        <li><mark class="green">Green</mark> &ndash; antenna changes from site logs</li>
        <li><mark class="red">Red</mark> &ndash; earthquakes</li>
        <li><mark class="blue">Blue</mark> &ndash; manually entered</li>
      </ul>';
  }

  /**
   * Get geoJson data to embed in html page
   *
   * @return {String}
   */
  private function _getGeoJsonData () {
    $features = [];
    $now = date(DATE_RFC2822);
    $secs = 86400; // secs in one day

    foreach ($this->_collection->stations as $station) {
      $id = intval($station->id);

      $features[] = [
        'coords' => [
          floatval($station->lon),
          floatval($station->lat)
        ],
        'id' => $id,
        'props' => [
          'days' => floor((strtotime($now) - strtotime($station->lastUpdate)) / $secs),
          'elevation' => $station->elevation,
          'lastUpdate' => $station->lastUpdate,
          'showcoords' => intval($station->showcoords),
          'station' => $station->station,
          'type' => $station->stationtype,
          'x' => $station->x,
          'y' => $station->y,
          'z' => $station->z
        ],
        'type' => 'Point'
      ];

    }

    $geoJson = getGeoJson([
      'count' => $this->_collection->count,
      'features' => $features
    ]);

    return sprintf('<script>
        var data = {
          stations: %s
        };
      </script>',
      $geoJson
    );
  }

  /**
   * Get href attribute for plot/download links
   *
   * @param $datatype {String <filtered | itrf2008 | nafixed>}
   * @param $suffix {String}
   *
   * @return $href {String}
   */
  private function _getHref ($datatype, $suffix) {
    $dataPath = $this->_getPath($datatype);
    $file = $this->_model->station . $suffix;
    $href = "$this->_baseUri/$dataPath/$file";

    // Check if file exists; if not, return '#no-data' (plots) or empty string
    if (!is_file("$this->_baseDir/$dataPath/$file")) {
      if (preg_match('/png$/', $file)) {
        $href = "#no-data";
      } else {
        $href = '';
      }
    }

    return $href;
  }

  /**
   * Get map div
   *
   * @return {String}
   */
  private function _getMap () {
    return '<div class="map"></div>';
  }

  /**
   * Get plot navigation link list
   *
   * @param $datatype {String <filtered | itrf2008 | nafixed>}
   *
   * @return $html {String}
   */
  private function _getNavPlots ($datatype) {
    $html = '
      <nav class="nav-plots ' . $datatype . '">
        <h4>Detrended</h4>
        <ul class="no-style pipelist">
          <li><a href="' . $this->_getHref($datatype, '_30.png') . '">Past 30 days</a></li>
          <li><a href="' . $this->_getHref($datatype, '_90.png') . '">Past 90 days</a></li>
          <li><a href="' . $this->_getHref($datatype, '_365.png') . '">Past year</a></li>
          <li><a href="' . $this->_getHref($datatype, '_730.png') . '">Past 2 years</a></li>
          <li><a href="' . $this->_getHref($datatype, '.png') . '" class="selected">All data</a></li>
        </ul>
        <h4>Trended</h4>
        <ul class="no-style pipelist">
          <li><a href="' . $this->_getHref($datatype, '_wtrend.png') . '">All data</a></li>
        </ul>
      </nav>';

    return $html;
  }

  /**
   * Get a list of networks that station belongs to
   *
   * @return $html {String}
   */
  private function _getNetworks () {
    $lis = '';
    $networks = $this->_model->networkList;

    $count = 0;
    foreach ($networks as $network) {
      $cssClass = '';
      $networkName = $network->network;
      $networkDisplay = $networkName;
      $networkSelected = false;
      $showNetwork = $network->show;

      if ($networkName === $this->_model->network) {
        $networkSelected = true;
      }

      if ($showNetwork || $networkSelected) { // add network to list
        $count ++;
        if ($networkSelected) {
          $cssClass = 'selected';
          if (!$showNetwork) {
            $networkDisplay .= '<small>(hidden)</small>';
          }
        }
        $lis .= sprintf('<li><a href="%s/%s/%s" class="%s">%s</a></li>',
          $GLOBALS['MOUNT_PATH'],
          $networkName,
          $this->_model->station,
          $cssClass,
          $networkDisplay
        );
      }
    }

    if ($count > 1) {
      $plurality = '';
      if ($count > 2) {
        $plurality = 's';
      }

      $html = sprintf('<nav>
          <h4>This station is in %s other network%s</h4>
          <ul class="other pipelist no-style">
            %s
          </ul>
        </nav>',
        $count - 1,
        $plurality,
        $lis
      );
    } else {
      $html = '<p>This station is not in any other networks.</p>';
    }

    return $html;
  }

  /**
   * Get offsets table
   *
   * @param $datatype {String <filtered | itrf2008 | nafixed>}
   *
   * @return $html {String}
   */
  private function _getOffsetsTable ($datatype) {
    $html = '';
    $rows = $this->_model->offsets;

    if ($rows) { // offsets exist for station
      foreach ($rows as $row) {
        if ($row->datatype === $datatype) {
          $component = $row->component;
          $date = $row->date;

          $offsets[$date]['decDate'] = $row->decdate;
          $offsets[$date]['distance'] = $row->distance_from_eq;
          $offsets[$date]['mag'] = $row->eqmagnitude;
          $offsets[$date]['type'] = $row->offsettype;
          $offsets[$date][$component . '-size'] = $row->size;
          $offsets[$date][$component . '-uncertainty'] = $row->uncertainty;
          $offsets[$date]['id'] = '';
          if ($row->eqinfo) {
            $offsets[$date]['id'] = sprintf('<a href="https://earthquake.usgs.gov/earthquakes/eventpage/%s">%s</a>',
              $row->eqinfo,
              $row->eqinfo
            );
          }
        }
      }
      if (isSet($offsets)) { // offsets exist for datatype
        $html = '<table>
          <tr>
            <td class="empty freeze"></td>
            <th>Decimal date</th>
            <th>N offset (mm)</th>
            <th>N uncertainty (mm)</th>
            <th>E offset (mm)</th>
            <th>E uncertainty (mm)</th>
            <th>U offset (mm)</th>
            <th>U uncertainty (mm)</th>
            <th>Type</th>
            <th>Earthquake magnitude</th>
            <th>Earthquake information</th>
            <th>Distance from epicenter (km)</th>
          </tr>';

        foreach ($offsets as $dateStr => $tds) {
          $html .= sprintf('<tr>
              <th class="freeze nowrap">%s</th>
              <td>%s</td>
              <td>%s</td>
              <td>%s</td>
              <td>%s</td>
              <td>%s</td>
              <td>%s</td>
              <td>%s</td>
              <td>%s</td>
              <td>%s</td>
              <td>%s</td>
              <td>%s</td>
            </tr>',
            $dateStr,
            $tds['decDate'],
            $tds['N-size'],
            $tds['N-uncertainty'],
            $tds['E-size'],
            $tds['E-uncertainty'],
            $tds['U-size'],
            $tds['U-uncertainty'],
            $tds['type'],
            $tds['mag'],
            $tds['id'],
            $tds['distance']
          );
        }

        $html .= '</table>';
      }
    }

    return $html;
  }

  /**
   * Get path for plot/download links
   *
   * @param $datatype {String <filtered | itrf2008 | nafixed>}
   *
   * @return {String}
   */
  private function _getPath ($datatype) {
    return 'networks/' . $this->_model->network . '/' . $this->_model->station .
      '/' . $datatype;
  }

  /**
   * Get postseismic table
   *
   * @param $datatype {String <filtered | itrf2008 | nafixed>}
   *
   * @return $html {String}
   */
  private function _getPostSeismicTable ($datatype) {
    $html = '';
    $rows = $this->_model->postSeismic;

    if ($rows) { // postseismic data exists for station
      foreach ($rows as $row) {
        if ($row->datatype === $datatype) {
          $component = $row->component;
          $days = $row->doy - date('z') - 1; // php starts at '0'
          $time = strtotime("+" . $days . " days");
          // use 'year' from db, and calculate 'month' and 'day' from 'doy'
          $date = $row->year . '-' . date('m-d', $time);

          $postSeismic[$date]['decDate'] = $row->decdate;
          $postSeismic[$date][$component . '-logsig'] = $row->logsig;
          $postSeismic[$date][$component . '-logsize'] = $row->logsize;
          $postSeismic[$date][$component . '-time'] = $row->time_constant;
        }
      }
      if (isSet($postSeismic)) { // postseismic data exists for datatype
        $html = '<table>
          <tr>
            <td class="empty freeze"></td>
            <th>Decimal date</th>
            <th>N log amplitude (mm)</th>
            <th>N log amplitude standard deviation (mm)</th>
            <th>N time constant (years)</th>
            <th>E log amplitude (mm)</th>
            <th>E log amplitude standard deviation (mm)</th>
            <th>E time constant (years)</th>
            <th>U log amplitude (mm)</th>
            <th>U log amplitude standard deviation (mm)</th>
            <th>U time constant (years)</th>
          </tr>';

        foreach ($postSeismic as $dateStr => $tds) {
          $html .= sprintf('<tr>
              <th class="freeze nowrap">%s</th>
              <td>%s</td>
              <td>%s</td>
              <td>%s</td>
              <td>%s</td>
              <td>%s</td>
              <td>%s</td>
              <td>%s</td>
              <td>%s</td>
              <td>%s</td>
              <td>%s</td>
            </tr>',
            $dateStr,
            $tds['decDate'],
            $tds['N-logsize'],
            $tds['N-logsig'],
            $tds['N-time'],
            $tds['E-logsize'],
            $tds['E-logsig'],
            $tds['E-time'],
            $tds['U-logsize'],
            $tds['U-logsig'],
            $tds['U-time']
          );
        }

        $html .= '</table>';
      }
    }

    return $html;
  }

  /**
   * Get station details: meta links, last update, closest stations
   *
   * @return $html {String}
   */
  private function _getStationDetails () {
    $lastUpdate = 'Unknown'; // default
    $logsheets = $this->_model->getLogSheets();
    $photos = $this->_model->getPhotos();

    if ($this->_model->lastUpdate) {
      $updateTime = strtotime($this->_model->lastUpdate);
      $lastUpdate = date('M j, Y', $updateTime);
      $numDays = date('z') - date('z', $updateTime);
      $plural = 's';

      if ($numDays >= 0 && $numDays < 31) { // show num days since update if 30 or less
        if ($numDays === 0) {
          $lastUpdate .= '<small>(today)</small>';
        } else {
          if ($numDays === 1) {
            $plural = '';
          }
          $lastUpdate .= "<small>($numDays day$plural ago)</small>";
        }
      }
    }

    $html = '<ul class="links">';
    foreach ($this->_model->linkList as $key => $arr) {
      $count = '';

      if ($key === 'Photos') {
        $count = '<small>(' . count($photos) . ')</small>';
      } else if ($key === 'Field Logs') {
        $count = '<small>(' . $this->_model->getLogSheetCount($logsheets) . ')</small>';
      }

      $html .= sprintf('<li>
          <a href="%s">
            <i class="material-icons">%s</i>%s%s
          </a>
        </li>',
        $arr->href,
        $arr->icon,
        $key,
        $count
      );
    }
    $html .= '</ul>';

    $html .= '<dl><dt>Last observation</dt>';
    $html .= '<dd class="lastUpdate">' . $lastUpdate . '</dd>';
    $html .= sprintf('',
      $updated,
      $numDaysStr
    );

    $html .= '<dt>Closest stations</dt>';
    $html .= '<dd>' . $this->_getClosestStations() . '</dd>';
    $html .= '</dl>';

    return $html;
  }

  /**
   * Get page subtitle`(Station button)
   *
   * @return {String}
   */
  private function _getSubtitle () {
    return sprintf ('<h2 class="subtitle">
        <a class="%s button">Station %s</a>
      </h2>',
      getColor($this->_model->lastUpdate),
      strtoupper($this->_model->station)
    );
  }

  /**
   * Get content in tabbed data section
   *
   * @return $html {String}
   */
  private function _getTabbedContent () {
    $html = '<div class="tablist">';
    $datatypes = [
      'nafixed' => 'NA-fixed',
      'itrf2008' => 'ITRF2008',
      'filtered' => 'Filtered'
    ];

    foreach ($datatypes as $datatype => $name) {
      $baseImg = $this->_model->station . '.png';

      $dataPath = $this->_getPath($datatype);
      $downloadsHtml = $this->_getDownloads($datatype);
      $explanation = $this->_getExplanation($datatype);

      $tables = [
        'Velocities' => $this->_getTable('velocities', $datatype, [
          'decdate' => 'Reference date',
          'doy' => 'Reference day of year',
          'int' => 'Intercept (mm)',
          'intsig' => 'Intercept standard deviation (mm)',
          'last_observation' => 'Last observation',
          'sigma' => 'Velocity standard deviation (mm/yr)',
          'velocity' => 'Velocity (mm/yr)',
          'year' => 'Reference year'
        ]),
        'Offsets' => $this->_getOffsetsTable($datatype),
        'Noise' => $this->_getTable('noise', $datatype, [
          'bpfilterelement1' => 'Lower frequency limit for band-pass (BP) filtered noise (cycle/year)',
          'bpfilterelement2' => 'Upper frequency limit for band-pass (BP) filtered noise (cycle/year) ',
          'BPamplitude' => 'Amplitude of BP filtered noise (mm)',
          'GM' => 'Amplitude of generalized Gauss-Markov noise',
          'numberofpoles' => 'Number of poles for BP filtered noise',
          'plamp1' => 'Amplitude of first power law (mm/yr^(<em>n<sub>1</sub></em>/4))',
          'plamp2' => 'Amplitude of second power law (mm/yr^(<em>n<sub>2</sub></em>/4))',
          'plexp1' => '<em>n<sub>1</sub></em> (spectral index of first power law)',
          'plexp2' => '<em>n<sub>2</sub></em> (spectral index of second power law)',
          'whitenoise' => 'White noise (mm)'
        ]),
        'Post-seismic' => $this->_getPostSeismicTable($datatype),
        'Seasonal' => $this->_getTable('seasonal', $datatype, [
          'decdate' => 'Reference date',
          'doy' => 'Reference day of year',
          'p13cosamp' => 'Amplitude of cosine (13.63-day period), mm',
          'p13cossig' => 'Standard deviation of cosine amplitude (13.63-day period)',
          'p13sinamp' => 'Amplitude of sine (13.63-day period), mm',
          'p13sinsig' => 'Standard deviation of sine amplitude (13.63-day period)',
          'p182cosamp' => 'Amplitude of cosine (180.625-day period), mm',
          'p182cossig' => 'Standard deviation of cosine amplitude (180.625-day period)',
          'p182sinamp' => 'Amplitude of sine (180.625-day period), mm',
          'p182sinsig' => 'Standard deviation of sine amplitude (180.625-day period)',
          'p365cosamp' => 'Amplitude of cosine (365.25-day period), mm',
          'p365cossig' => 'Standard deviation of cosine amplitude (365.25-day period)',
          'p365sinamp' => 'Amplitude of sine (365.25-day period), mm',
          'p365sinsig' => 'Standard deviation of sine amplitude (365.25-day period)',
          'year' => 'Reference year'
        ])
      ];

      $plotsHtml = '';
      if (is_file("$this->_baseDir/$dataPath/$baseImg")) {
        $navPlots = $this->_getNavPlots($datatype);
        $image = sprintf('<img src="%s/%s/%s" alt="Plot showing %s data (All data)" />',
          $this->_baseUri,
          $dataPath,
          $baseImg,
          $name
        );

        $plotsHtml = sprintf('<h3>Plots</h3>
          <div class="plots">
            <div class="image">%s</div>
            <div class="meta">%s%s</div>
          </div>',
          $image,
          $navPlots,
          $explanation
        );
      }

      $tablesHtml = '';
      foreach ($tables as $tableName => $tableData) {
        if ($tableData) { // value is empty if no data in database
          $tablesHtml .= sprintf('<h3>%s</h3><div class="scroll-wrapper">%s</div>',
            $tableName,
            $tableData
          );
        }
      }

      $html .= sprintf('
        <section class="panel" data-title="%s">
          <header>
            <h2>%s</h2>
          </header>
          %s
          <h3 class="clear">Downloads</h3>
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

  /**
   * Get noise, seasonal or velocities table
   *
   * @param $table {String <noise | seasonal | velocities>}
   * @param $datatype {String <filtered | itrf2008 | nafixed>}
   * @param $lookupTable {Array}
   *     key/value pairs for table headers
   *
   * @return $html {String}
   */
  private function _getTable ($table, $datatype, $lookupTable=NULL) {
    $components = [ // listed in display order on web page
      'N' => 'North',
      'E' => 'East',
      'U' => 'Up'
    ];
    $html = '';
    $rows = $this->_model->{$table};

    if ($rows) {
      $html = '<table>';
      $th = '';
      $trs = [];
      foreach ($rows as $row) {
        if ($row->datatype === $datatype) {
          $component = $row->component;
          $direction = $components[$component];
          $th = '<tr><td class="empty freeze"></td>';
          $tr = '<tr class="' . strtolower($direction) . '">';
          $tr .= '<th class="freeze">' . $direction . '</th>';

          unset( // don't include these values in the table
            $row->component,
            $row->datatype,
            $row->GM, // Jess doesn't want this field displayed
            $row->id,
            $row->network,
            $row->station
          );

          // Use select values from seasonal for velocities
          if ($table === 'velocities') {
            $seasonalRows = $this->_model->seasonal;
            foreach ($seasonalRows as $seasonalRow) {
              if ($seasonalRow->datatype === $datatype && $seasonalRow->component === $component) {
                $row->decdate = $seasonalRow->decdate;
                $row->year = $seasonalRow->year;
                $row->doy = $seasonalRow->doy;
              }
            }
          }

          foreach ($row as $key => $value) {
            $fieldName = ucfirst($key);
            if ($lookupTable[$key]) {
              $fieldName = $lookupTable[$key];
            }
            $th .= "<th>$fieldName</th>";
            $tr .= "<td>$value</td>";
          }
          $th .= '</tr>';
          $tr .= '</tr>';
          $trs[$component] = $tr;
        }
      }
      $html .= $th;
      foreach ($components as $key => $value) {
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
}
