<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
  'db.options' => array(
    'driver'   => 'pdo_mysql',
    'host'     => 'localhost',
    'dbname'   => 'maps_dev',
    'user'     => 'maps_dev',
    'password' => 'galjp98whno3iEeU356y2yhilu',
  ),
));

$app->get('/', function(){
  return new Response("Hello world");
});

$app->get('/api/location/latest', function () use ($app) {
  $sql = 'SELECT full_city, city, timestamp FROM location_history WHERE city IS NOT NULL ORDER BY timestamp DESC LIMIT 1';
  $test = $app['db']->fetchAll($sql);
  return $app->json(reset($test));
});
