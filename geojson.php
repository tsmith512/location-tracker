<?php

$username="maps_dev";
$password="galjp98whno3iEeU356y2yhilu";
$database="maps_dev";

$mysqli = new mysqli("localhost", $username, $password, $database);
if ($mysqli->connect_errno) { echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error; }

switch ($_GET['q']) {
  case 'points':
    $latest = $mysqli->query("SELECT * FROM `location_history` ORDER BY `timestamp` DESC");
    $history = array();

    while ($data = $latest->fetch_assoc()) {
      // Round these to ~100 meters
      $lon = $data['lon']; // round($data['lon'], 3);
      $lat = $data['lat']; // round($data['lat'], 3);

      $history[] = array(
        'type' => 'Feature',
        'geometry' => array(
          'type' => 'Point',
          'coordinates' => array($lon, $lat),
        ),
        'properties' => array(
          'marker-color' => '#0066CC',
          'marker-size' => 'small',
          'marker-symbol' => '1',
        ),
      );
    }
    break;

  case 'cartodb':
    $latest = $mysqli->query("SELECT * FROM `location_history` ORDER BY `timestamp` DESC");
    $history = array();

    while ($data = $latest->fetch_assoc()) {

      $history[] = array(
        'lon' => $data['lon'],
        'lat' => $data['lat'],
        'time' => $data['timestamp'],
      );
    }
    break;

  default:
    # code...
    break;
}


header("content-type: application/json");
print json_encode($history);
