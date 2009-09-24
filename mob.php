<?php
/*
 * Mobile version of the site
 * 
 * Simplified layout and no javascript
 *
 */

$file = file_get_contents('data.json');

$data = json_decode($file, true);
?>



<html>
<head>
  <title>Wellington real-time weather data</title>
</head>
<body>
<h2>Wellington real-time weather data</h2>
<table border="1" cellspacing="0" cellpadding="5">
 <thead>
  <tr>
   <th rowspan="2">Location</th>
   <th colspan="4">Wind</th>
   <th rowspan="2">Temp.</th>
   <th rowspan="2">Pressure</th>
   <th rowspan="2">Observation Time</th>
   <th rowspan="2">Source</th>
  </tr>
  <tr>
   <th>Speed</th>
   <th colspan="2">Direction</th>
   <th>Gust</th>
 </thead>
 <tbody id="tablebody">
<?php
foreach ($data as $row) {
   $site = ($row['name']) ? $row['name'] : 'Unknown';
   $comment = ($row['comment']) ? '<small>'.$row['comment'].'</small>' : '';
   $link = ($row['link']) ? '<a href="'.$row['link'].'">Source</a>' : '&mdash;';

   $temp = ($row['reading']['temp'] !== false) ? $row['reading']['temp'].'&deg;C' : '&mdash;';
   $pressure = ($row['reading']['pressure'] !== false) ? $row['reading']['pressure'].' mb' : '&mdash;';
   $windSpeed = ($row['reading']['windSpeed'] !== false) ? (round(10.0*$row['reading']['windSpeed'])/10.0).' kts' : '&mdash;';
   $windGust = ($row['reading']['windGust'] !== false) ? (round(10.0*$row['reading']['windGust'])/10.0).' kts' : '&mdash;';
   if ($row['reading']['windDir'] !== false) {
       $windDir = $row['reading']['windDir'].'&deg;';
       $windCardinal = ($row['reading']['windCardinal']) ? $row['reading']['windCardinal'] : '&mdash;';
   } else {
       $windDir = '&mdash;';
       $windCardinal = '&mdash;';
   }
   $obsTime = $row['reading']['obsTime'];
   $obsTimeFormatted = ($row['reading']['obsTimeFormatted']) ? $row['reading']['obsTimeFormatted'] : 'Obs time unknown';
   
   print '<tr><td>'.$site.'</td>';
   print '<td>'.$windSpeed.'</td><td>'.$windDir.'</td><td>'.$windCardinal.'</td>';
   print '<td>'.$windGust.'</td><td>'.$temp.'</td><td>'.$pressure.'</td><td>'.$obsTimeFormatted.'</td>';
   print '<td>'.$link.'</td></tr>';


}
?>
 </tbody>
</table>
<p>This page is for mobile phones. The full version of this page is <a href="kiting.html">here</a>.</p>
</body>
</html>