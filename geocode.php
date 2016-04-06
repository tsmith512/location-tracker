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

  switch ($output['status']) {
    case "OVER_QUERY_LIMIT":
    case "REQUEST_DENIED":
    case "INVALID_REQUEST":
    case "UNKNOWN_ERROR":
      return FALSE;
      break;

    case "ZERO_RESULTS":
      return NULL;
      break;

    case "OK":
      $match = isset($output['results']) ? reset($output['results']) : NULL;

      $city = isset($match['address_components'][0]['long_name']) ? $match['address_components'][0]['long_name'] : NULL;
      $full = isset($match['formatted_address']) ? $match['formatted_address'] : NULL;

      return array($city, $full);
      break;

    return NULL;
  }
var_dump($output['status']);
die();
}

function geocodeLatest() {
  global $conf;

  $mysqli = new mysqli("localhost", $conf['username'], $conf['password'], $conf['database']);
  if ($mysqli->connect_errno) { echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error; }

  $latest = $mysqli->query("SELECT * FROM `location_history` WHERE `city` IS NULL AND (`geocode_attempts` IS NULL OR `geocode_attempts` < 2) ORDER BY RAND() DESC LIMIT 3");
  $history = array();

  while ($data = $latest->fetch_assoc()) {
    // Round these to ~100 meters
    $location = getCity(array($data['lat'], $data['lon']));

    if ($location === FALSE) {
      // There was an error making the Geocoding request; probably rate limiting
      // Break out of this loop and don't make any further requests.
      var_dump("FALSE \n");
      break;
    }
    else if ($location === NULL) {
      // We got a response, but it was no results. Mark this as attempted and
      // move on.
      $query = "UPDATE `location_history` SET `geocode_attempts` = IF(`geocode_attempts` IS NULL, 1, `geocode_attempts` + 1) WHERE `id` = {$data['id']} LIMIT 1;";
      $update = $mysqli->query($query);
      var_dump("NULL \n");
    }
    else if (is_array($location) && !empty($location[0])) {
      $query = "UPDATE `location_history` SET `city` = '{$location[0]}', `full_city` = '{$location[1]}', `geocode_attempts` = IF(`geocode_attempts` IS NULL, 1, `geocode_attempts` + 1) WHERE `id` = {$data['id']} LIMIT 1;";
      $update = $mysqli->query($query);
      var_dump("UPDATED \n");
    }
    else {
      // Don't know what this would leave. We weren't rate limited, and we didn't
      // get an error that we know how to handle.
    }
  }
}

if ($conf['key'] == $_GET['key']) {
  geocodeLatest();
} else {
  header('HTTP/1.0 403 Forbidden');
}
