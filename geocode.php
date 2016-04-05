<?php

include 'config.php';


function getCity($latlng) {
  global $conf;

  $ch = curl_init();
  $url = ('https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $latlng[0] . ',' . $latlng[1] . '&result_type=locality&key=' . $conf['gmaps_api_key']);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, false);
  curl_setopt($ch, CURLOPT_URL, $url);

  $result = curl_exec($ch);
  $output = json_decode($result, true);
  curl_close($ch);

  $match = isset($output['results']) ? reset($output['results']) : false;

  $city = isset($match['address_components'][0]['long_name']) ? $match['address_components'][0]['long_name'] : NULL;
  $full = isset($match['formatted_address']) ? $match['formatted_address'] : NULL;

  return array($city, $full);
}

function geocodeLatest() {
  global $conf;

  $mysqli = new mysqli("localhost", $conf['username'], $conf['password'], $conf['database']);
  if ($mysqli->connect_errno) { echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error; }

  $latest = $mysqli->query("SELECT * FROM `location_history` WHERE `city` IS NULL ORDER BY `timestamp` DESC LIMIT 20");
  $history = array();

  while ($data = $latest->fetch_assoc()) {
    // Round these to ~100 meters
    $location = getCity(array($data['lat'], $data['lon']));

    if (!empty($location[0])) {
      $query = "UPDATE `location_history` SET `city` = '{$location[0]}', `full_city` = '{$location[1]}' WHERE `id` = {$data['id']} LIMIT 1;";
      $update = $mysqli->query($query);
    }
  }
}

if ($conf['key'] == $_GET['key']) {
  geocodeLatest();
} else {
  header('HTTP/1.0 403 Forbidden');
}
