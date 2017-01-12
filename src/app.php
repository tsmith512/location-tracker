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

// api.php contains all api endpoints for now to clean up this file
$app->mount('/api', include 'api.php');
