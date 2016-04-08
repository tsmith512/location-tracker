<?php

namespace Traveler;
use Silex\Application;

class Location {
  private $_app;

  private $_lat;
  private $_lon;

  public function __construct(Application $app) {
    // This way we can reuse the geocoder instance that's already set up
    $this->_app = $app;
  }

  public function setCoords($lat, $lon) {
    $this->_lat = (float) $lat;
    $this->_lon = (float) $lon;

    $this->debug();
  }

  private function debug() {
    var_dump(array(
      'latitude' => $this->_lat,
      'longitude' => $this->_lon,
    ));
  }
}
