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
  preg_replace('/[\r\n]+/', "\n", $entries);
  $entries = explode("\n",  trim($request->getContent()));
  // Split up the entry rows by comma to get individual components
  array_walk($entries, function(&$entry) { $entry = explode(',', trim($entry)); });

  // Variables
  $total = count($entries);
  $created = 0;

  foreach ($entries as $entry) {

    // $entry[0] is a date for human use; skipped.
    $time = (isset($entry[1]) && is_numeric($entry[1])) ? (int) $entry[1]   : time();
    $lat  = (isset($entry[2]) && is_numeric($entry[2])) ? (float) $entry[2] : false;
    $lon  = (isset($entry[3]) && is_numeric($entry[3])) ? (float) $entry[3] : false;

    if ($lat && $lon) {
      // Doctrine doesn't have a Replace Into / If Dup Update command, so we prepare our own.
      // `time` is a unique int unixtime. @TODO: Still including `timestamp` for now, should
      // consolidate to one or the other. Unique index on TIMESTAMP field seemed weird.
      $sql = "REPLACE INTO location_history (`lat`, `lon`, `time`, `timestamp`) VALUES (:lat, :lon, :time, FROM_UNIXTIME(:time));";
      $query = $app['db']->prepare($sql);

      // On a replacement, the `id` key will be auto-updated. I think that's actually useful
      // while I work this out.
      $query->bindValue('lat', $lat);
      $query->bindValue('lon', $lon);
      $query->bindValue('time', $time);
      $query->execute();

      if ((int) $app['db']->lastInsertId()) {
        // We got back a row ID, so we know the database has this info.
        $created++;

        // Geocode:
        // @TODO this is a shitty way to do this:
        $location = new Location($app);
        if ($location->loadId((int) $app['db']->lastInsertId()) && $total < 11) { $location->geocode(); }

      } else {
        // We aren't sure the DB recorded the new info, but we have no errors
      }
    } else {
      // $lat and/or $lon either weren't submitted or weren't numeric.
      // @TODO: More detail here wouldn't be a bad thing...
      // @TODO: Also, earlier rows would have already been inserted, so this would
      //        kill a batch in the middle. What should the client do?
      return $app->abort(400, "Bad Request: Row contained malformed coordinates");
    }
  }

  if ($created == $total) {
    return new Response("Location recorded.", 201);
  } else {
    // So there were no rows missing coordinates (throws 400), but we didn't
    // get database confirmation on every row. @TODO: What do do about that?
    return new Response("Location received.", 200);
  }

})->before($keyCheck);


$api->get('/location/latest', function () use ($app) {
  $sql = 'SELECT full_city, city, time, lat, lon FROM location_history WHERE city IS NOT NULL ORDER BY time DESC LIMIT 1';
  $result = $app['db']->executeQuery($sql);
  $point = $result->fetch();

  // Check to see if this location stamp is during any trips and include
  // them in the result. (This is a safety feature for the blog abstraction)
  $sql = "SELECT * FROM trips WHERE starttime <= {$point['time']} AND endtime >= {$point['time']}";
  $trips = $app['db']->fetchAll($sql);


  $history = array(
    'full_city' => $point['full_city'],
    'city' => $point['city'],
    'time' => $point['time'],
    'lat' => $point['lat'],
    'lon' => $point['lon'],
    'trips' => $trips,
  );

  return $app->json($history);
});

$api->get('/location/history/line', function () use ($app) {
  $sql = 'SELECT lon, lat FROM location_history ORDER BY time DESC';
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

$api->get('/location/history/timestamp/{time}', function($time) use ($app) {
  // Make sure no one is being sneaky, this should be a number.
  if (!intval($time)) {
    return $app->abort(400, "Bad Request: Timestamp should be numeric (unix formatted timestamp of seconds)");
  }

  $sql = 'SELECT lon, lat, city, full_city, `time`, ABS(`time` - :time) AS `difference` FROM location_history ORDER BY `difference` ASC LIMIT 1;';
  $result = $app['db']->executeQuery($sql, array('time' => (int) $time));
  $point = $result->fetch();

  // I mean, it's just math, if there's any history, there's a nearest point,
  // but let's cover our bases.
  if (empty($point)) {
    return $app->abort(404, "No Result");
  }

  // Look up relevant trips from the _location's_ time, not the request
  // time, just to be on the safe side.
  $sql = "SELECT * FROM trips WHERE starttime <= {$point['time']} AND endtime >= {$point['time']}";
  $trips = $app['db']->fetchAll($sql);


  $history = array(
    'full_city' => $point['full_city'],
    'city' => $point['city'],
    'time' => $point['time'],
    'lat' => $point['lat'],
    'lon' => $point['lon'],
    'trips' => $trips,
  );

  return $app->json($history);
});

$api->get('/trips', function() use ($app) {
  $sql = 'SELECT * FROM trips ORDER BY starttime ASC';
  $result = $app['db']->fetchAll($sql);
  return $app->json($result);
});

$api->post('/trips/create', function(Request $request) use ($app) {
  $submitted = json_decode($request->getContent());

  $trip = array(
    'id' => null,
    // @TODO: Sanitize the machine name
    'machine_name' => substr($submitted->machine_name, 0, 50),
    'starttime' => (int) $submitted->starttime,
    'endtime' => (int) $submitted->endtime,
    'label' => substr($submitted->label, 0, 255)
  );

  if (!$trip['starttime'] || !$trip['endtime']) {
    return new Response("Please provide valid start and end timestamps", 400);
  }

  if (!$trip['machine_name'] || !$trip['label']) {
    return new Response("Please provide a valid machine name and label", 400);
  }

  $app['db']->insert('trips', $trip);

  if ($app['db']->lastInsertId()) {
    $trip['id'] = $app['db']->lastInsertId();
    return new Response(json_encode($trip), 201);
  } else {
    // @TODO: It'd be great to wrap this and catch the error
    return new Response("Unknown error inserting new trip", 500);
  }

  // return new Response("Location recorded.", 201);
});

$api->get('/trips/{id}', function($id) use ($app) {
  // Make sure no one is being sneaky, this should be a number.
  if (!intval($id)) {
    return $app->abort(400, "Bad Request: Trip ID should be numeric");
  }

  $sql = "SELECT * FROM trips WHERE id = ? LIMIT 1";
  $trip = $app['db']->fetchAssoc($sql, array((int) $id));

  if (!$trip) {
    return $app->abort(404, "Not Found: Trip ID not found");
  }

  $sql = "SELECT lon, lat FROM location_history WHERE time >= {$trip['starttime']} AND time <= {$trip['endtime']} ORDER BY time DESC";
  $result = $app['db']->fetchAll($sql);

  $line = array(
    'type' => 'LineString',
    'properties' => array(
      'stroke' => '#FF6633',
      'stroke-width' => 2
     ),
  );

  foreach ($result as $point) {
    $lon = $point['lon'];
    $lat = $point['lat'];

    $line['coordinates'][] = array($lon, $lat);
  }

  $trip['line'] = $line;

  return $app->json($trip);
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
