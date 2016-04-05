<?php

$username="maps_dev";
$password="galjp98whno3iEeU356y2yhilu";
$database="maps_dev";
$key="77QedyTiktSP1egjhGVit7vm";

  if (trim($_GET['key']) != $key) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
  }

  $location = explode(',', $_GET['location']);
  $lat = is_numeric($location[0]) ? (float) $location[0] : false;
  $lon = is_numeric($location[1]) ? (float) $location[1] : false;

  $mysqli = new mysqli("localhost", $username, $password, $database);
  if ($mysqli->connect_errno) { echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error; }

  $record = $mysqli->query("INSERT into `location_history` (`lat`, `lon`) VALUES ($lat, $lon);");

  // $latest = $mysqli->query("SELECT `time` FROM `pings` ORDER BY `time` DESC LIMIT 1");
  // $ping = $latest->fetch_assoc();

  // $time = new DateTime($ping['time']);
  // $now  = new DateTime();
  // $seconds = $now->getTimestamp() - $time->getTimestamp();

  // $internet = ($seconds < 61) ? TRUE : FALSE;
/*

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;" />
  <title>Is the Internet working?</title>
  <style type="text/css">
    body {
      text-align: center;
      color: white;
    }
    body.yes {
      background-color: #2b8a31;
    }
    body.no {
      background-color: #990000;
    }
  </style>
</head>
<body class="<?php echo ($internet) ? 'yes' : 'no'; ?>">
  <header>
    <h1><?php echo ($internet) ? 'Hooray' : 'Womp womp'; ?></h1>
    <h2><?php echo ($internet) ? 'It is working' : 'It is not working'; ?></h2>
  </header>
  <p>Last contact from 4KHQ was <?php echo $seconds; ?> seconds ago</p>
</body>
</html>
*/
