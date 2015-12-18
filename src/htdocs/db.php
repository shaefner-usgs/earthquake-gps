<?php

//include_once 'functions.inc.php';

include_once '../conf/config.inc.php';

$station = 'coco';
$limit = 15;

$STH = $DB->prepare("SELECT * FROM nca_gps_qualitycontrol
  WHERE `station` = :station
  ORDER BY `date` DESC
  LIMIT :limit"
);
$STH->bindValue(':station', $station, PDO::PARAM_STR);
$STH->bindValue(':limit', $limit, PDO::PARAM_INT);
$STH->execute();

?>

<table class="tabular">
	<tr>
		<th>Year</th>
		<th><abbr title="Day of Year">DOY</abbr></th>
		<th>Date</th>
		<th>Filename</th>
		<th>Possible</th>
		<th>Complete</th>
		<th>Percent</th>
		<th>MP1</th>
		<th>MP2</th>
    <th>SN1</th>
		<th>SN2</th>
		<th>Slips</th>
	</tr>

<?php

while($row = $STH->fetch(PDO::FETCH_ASSOC)) {
	printf("
		<tr>
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
			<td>%s</td>
		</tr>
		\n",
		substr($row['date'], 0, 4),
		date('z', strtotime($row['date'])) + 1,
		$row['date'],
		$row['filename'],
		$row['pos_obs'],
		$row['comp_obs'],
		$row['percentage'],
		$row['mp1'],
		$row['mp2'],
		$row['sn1'],
		$row['sn2'],
		$row['slips']
	);
}

?>

</table>
