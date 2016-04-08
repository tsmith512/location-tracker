<?php

namespace Traveler;
use Silex\Application;

class Location {
  private $app;
  private $geocoder;

  private $_id;
  private $_timestamp;
  private $_lat;
  private $_lon;
  private $_city;
  private $_full_city;
  private $_geocode_attempts;


  public function __construct(Application $app) {
    // This way we can reuse the geocoder instance that's already set up
    $this->app = $app;
    $this->geocoder = $app['geocoder'];
  }

  public function setCoords($lat, $lon) {
    $this->_lat = (float) $lat;
    $this->_lon = (float) $lon;

    $this->debug();
  }

  public function setId($id) {
    if (intval($id) > 0) {
      $this->_id = (int) $id;
      $this->load();
      $this->geocode();
      $this->debug();
    }
  }

  public function load() {
    $result = $this->app['db']->fetchAssoc('SELECT * FROM location_history WHERE id = ?', array((int) 37094));

    if (!empty($result['id'])) {
      foreach ($result as $property => $value) {
        $this->{'_' . $property} = $value;
      }
    }
    $this->debug();
  }

  private function geocode() {
    try {
      $result = $this->geocoder->reverse($this->_lat, $this->_lon);

    var_dump($location);
    }
    catch (Exception $e) {
      if ($e instanceof Geocoder\Exception\NoResultException) {
        var_dump("No results");
        $this->_geocode_attempts++;
        return null;
      }
      else if ($e instanceof Geocoder\Exception\QuotaExceededException) {
        return false;
      }
      else {
        var_dump("something else happened");
        return false;
      }
    }

    $this->_city = $result->getCity() ?: false;
    $this->_full_city = implode(', ', array($result->getCity(), $result->getRegionCode(), $result->getCountryCode()));

  }

  private function debug() {
    var_dump(array(
      'id' => $this->_id,
      'timestamp' => $this->_timestamp,
      'latitude' => $this->_lat,
      'longitude' => $this->_lon,
      'city' => $this->_city,
      'full_city' => $this->_full_city,
      'geocode_attempts' => $this->_geocode_attempts,
    ));
  }
}
