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
  private $_geocode_full_response;


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

  public function loadId($id) {
    if (intval($id) > 0) {
      $this->_id = (int) $id;
      return $this->load();
    } else {
      return false;
    }
  }

  public function load() {
    $result = $this->app['db']->fetchAssoc('SELECT * FROM location_history WHERE id = ?', array((int) $this->_id));

    if (!empty($result['id'])) {
      foreach ($result as $property => $value) {
        if ($property == "geocode_attempts") {
          // @TODO: Despite a default value of 0, MySQL keeps returning NULL
          $this->_geocode_attempts = (int) $value;
        }
        else if ($property == "id") {
          $this->timestamp = (int) $value;
        }
        else if ($property == "timestamp") {
          $this->timestamp = strtotime($value);
        }
        else if ($property == "geocode_full_response") {
          $this->timestamp = (!empty($value)) ? unserialize($value) : false;
        }
        else {
          $this->{'_' . $property} = $value;
        }
      }
      // We found the entry, return the id
      return $result['id'];
    } else {
      // We didn't find that entry
      return false;
    }
    $this->debug();
  }

  public function save() {
    if ($this->_id) {
      // We have an id, so we need to update the DB
      $result = $this->app['db']->update('location_history', array(
                                                                   'city' => $this->_city,
                                                                   'full_city' => $this->_full_city,
                                                                   'geocode_attempts' => $this->_geocode_attempts,
                                                                   'geocode_full_response' => serialize($this->_geocode_full_response),
                                                                   ), array(
                                                                            'id' => (int) $this->_id,
                                                                            ));

    }
    else {
      // ID is falsey so we need to insert, then capture the ID and set it on the item
    }
  }

  public function geocode() {
    try {
      $result = $this->geocoder->reverse($this->_lat, $this->_lon);
    }
    catch (Exception $e) {
      if ($e instanceof Geocoder\Exception\NoResultException) {
        var_dump("No results");
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
    $this->_geocode_attempts++;

    // The geocode object has protected properties which serialize out
    // as prepended properties.  Remove those markings. @TODO: Really,
    // I don't want to serialize these data, but it is "expensive" to
    // make the full request and I don't yet know which parts I really
    // want to keep. Keeping everything would let me rebuild a
    // different view later.
    $full_response = array();
    foreach ((array) $result as $k => $v) {
      $k = preg_match('/^\x00(?:.*?)\x00(.+)/', $k, $matches) ? $matches[1] : $k;
      $full_response[$k] = $v;
    }

    $this->_geocode_full_response = $full_response;
    $this->save();
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
      'geocode_full_response' => $this->_geocode_full_response,
    ));
  }
}
