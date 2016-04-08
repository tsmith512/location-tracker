<?php

namespace Traveler;
use Silex\Application;

class Location {
  private $_app;

  private $_id;
  private $_timestamp;
  private $_lat;
  private $_lon;
  private $_city;
  private $_full_city;
  private $_geocode_attempts;


  public function __construct(Application $app) {
    // This way we can reuse the geocoder instance that's already set up
    $this->_app = $app;
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
    }
  }

  public function load() {
    $result = $this->_app['db']->fetchAssoc('SELECT * FROM location_history WHERE id = ?', array((int) 37094));

    if (!empty($result['id'])) {
      foreach ($result as $property => $value) {
        $this->{'_' . $property} = $value;
      }
    }
    $this->debug();
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
