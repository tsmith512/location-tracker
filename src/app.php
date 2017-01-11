<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Geocoder\Provider\GeocoderServiceProvider;
use Traveler\Location;

$app->register(new \Ronanchilvers\Silex\Provider\YamlConfigServiceProvider(__DIR__ . '/../config.yml'));

$app->register(new GeocoderServiceProvider());
// Override the default provider to use Google Maps instead
$app['geocoder.provider'] = $app->share(function () use ($app) {
  return new \Geocoder\Provider\GoogleMapsProvider(
    $app['geocoder.adapter'],       // The HTTP adapter; use the default
    null, // Locale
    null, // Region
    true, // Use SSL (boolean)
    $app['config']['keys']['gmaps'] // API Key
  );
});

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
  'db.options' => array(
    'driver'   => 'pdo_mysql',
    'host'     => $app['config']['db']['host'] ?: 'localhost',
    'dbname'   => $app['config']['db']['schema'],
    'user'     => $app['config']['db']['username'],
    'password' => $app['config']['db']['password'],
  ),
));

$keyCheck = function (Request $request) use ($app) {
  if ($request->query->get('key') !== $app['config']['keys']['access']) {
    return $app->abort(403, "Unauthorized");
  }
};

$app->get('/', function () use ($app) {
  return $app['twig']->render('home.twig', array('option' => 'home'));
});

$app->get('/line', function () use ($app) {
  return $app['twig']->render('line.twig', array('option' => 'line'));
});

$app->get('/heat', function () use ($app) {
  return $app['twig']->render('heat.twig', array('option' => 'heat'));
});


$app->post('/api/location', function (Request $request) use ($app){
  // This is the %LOC parameter from Tasker
  $location = explode(',', $request->request->get('location'));
  $lat = is_numeric($location[0]) ? (float) $location[0] : false;
  $lon = (isset($location[1]) && is_numeric($location[1])) ? (float) $location[1] : false;

  if ($lat && $lon) {
    $app['db']->insert('location_history', array(
      'lat' => $lat,
      'lon' => $lon,
    ));

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

$app->get('/api/location/latest', function () use ($app) {
  $sql = 'SELECT full_city, city, timestamp, lat, lon FROM location_history WHERE city IS NOT NULL ORDER BY timestamp DESC LIMIT 1';
  $result = $app['db']->fetchAll($sql);
  return $app->json(reset($result));
});

$app->get('/api/location/history/line', function () use ($app) {
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

$app->get('/api/location/history/points', function() use ($app) {
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

$app->get('/api/class-test', function() use ($app) {
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
