<?php

/**
 * @file
 * Contains the first set of API endpoints
 */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Geocoder\Provider\GeocoderServiceProvider;
use Traveler\Location;

$api = $app['controllers_factory'];

$api->post('/location', function (Request $request) use ($app){

  // Tasker records the data in a text file like this:
  //   1-12-17,1484250000,30.123,-95.123
  //   1-12-18,1484260000,30.456,-95.456
  // Human readable date, Unix Timestamp, Latitude, Longitude newline

  // Break up the request body by newline to get entry rows:
  $entry = explode("\n",  trim($request->getContent()));

  // Split up the entry rows by comma to get individual components
  array_walk($entry, function(&$row) { $row = explode(',', trim($row)); });

  if (count($entry) == 1) {
    $entry = reset($entry);
  }
  //  print_r($entry); die();

  // $entry[0] is a date for human use; skipped.
  $time = (isset($entry[1]) && is_numeric($entry[1])) ? (int) $entry[1]   : time();
  $lat  = (isset($entry[2]) && is_numeric($entry[2])) ? (float) $entry[2] : false;
  $lon  = (isset($entry[3]) && is_numeric($entry[3])) ? (float) $entry[3] : false;

  if ($lat && $lon) {
    // Doctrine doesn't have a Replace Into / If Dup Update command, so we prepare our own
    $sql = "REPLACE INTO location_history (`lat`, `lon`, `time`) VALUES (:lat, :lon, :time);";
    $query = $app['db']->prepare($sql);

    // On a replacement, the `id` key will be auto-updated. I think that's actually useful
    // while I work this out.
    $query->bindValue('lat', $lat);
    $query->bindValue('lon', $lon);
    $query->bindValue('time', $time);
    $query->execute();

    if ((int) $app['db']->lastInsertId()) {
      // We got back a row ID, so we know the database has this info.

      // Geocode:
      // @TODO this is a shitty way to do this:
      $location = new Location($app);
      if ($location->loadId((int) $app['db']->lastInsertId())) { $location->geocode(); }

      return new Response("Location recorded.", 201);

    } else {
      // We aren't sure the DB recorded the new info, but we have no errors
      return new Response("Location received.", 200);
    }
  } else {
    // $lat and/or $lon either weren't submitted or weren't numeric.
    // @TODO: More detail here wouldn't be a bad thing...
    return $app->abort(400, "Bad Request");
  }
})->before($keyCheck);

$api->get('/location/latest', function () use ($app) {
  $sql = 'SELECT full_city, city, timestamp, lat, lon FROM location_history WHERE city IS NOT NULL ORDER BY timestamp DESC LIMIT 1';
  $result = $app['db']->fetchAll($sql);
  return $app->json(reset($result));
});

$api->get('/location/history/line', function () use ($app) {
  $sql = 'SELECT lon, lat FROM location_history ORDER BY timestamp DESC';
  $result = $app['db']->fetchAll($sql);

  $history = array(
    'type' => 'LineString',
    'properties' => array(
      'stroke' => '#FF6633',
      'stroke-width' => 2
     ),
  );

  foreach ($result as $point) {
    $lon = $point['lon'];
    $lat = $point['lat'];

    $history['coordinates'][] = array($lon, $lat);
  }

  return $app->json($history);
});

$api->get('/location/history/points', function() use ($app) {
  $sql = 'SELECT lon, lat FROM location_history ORDER BY timestamp DESC';
  $result = $app['db']->fetchAll($sql);
  $history = array();

  foreach ($result as $point) {
    $lon = $point['lon'];
    $lat = $point['lat'];

    $history[] = array(
      'type' => 'Feature',
      'geometry' => array(
        'type' => 'Point',
        'coordinates' => array($lon, $lat),
      ),
    );
  }

  return $app->json($history);
});

$api->get('/class-test', function() use ($app) {
  $result = $app['db']->fetchAll('SELECT id FROM location_history WHERE geocode_attempts < 2 AND geocode_full_response IS NULL ORDER BY id ASC LIMIT 10');
  if (!empty($result)) {
    foreach ($result as $k => $v) {
      $location = new Location($app);
      if ($location->loadId((int) $result[$k]['id'])) { $location->geocode(); }
    }
    return true;
  } else {
    return false;
  }
});

return $api;
