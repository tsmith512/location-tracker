<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Map Image Builder</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
  <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</head>
<body>

<form class="form-horizontal">
<fieldset>

<!-- Form Name -->
<legend>Fetch Mapbox Image</legend>

<!-- Text input-->
<div class="control-group">
  <label class="control-label" for="lat">Latitude</label>
  <div class="controls">
    <input id="lat" name="lat" type="text" value="" class="input-xlarge">
    
  </div>
</div>

<!-- Text input-->
<div class="control-group">
  <label class="control-label" for="lon">Longitude</label>
  <div class="controls">
    <input id="lon" name="lon" type="text" value="" class="input-xlarge">
    
  </div>
</div>

<!-- Text input-->
<div class="control-group">
  <label class="control-label" for="z">Zoom</label>
  <div class="controls">
    <input id="z" name="z" type="text" value="" class="input-xlarge">
    
  </div>
</div>

<!-- Text input-->
<div class="control-group">
  <label class="control-label" for="width">Width</label>
  <div class="controls">
    <input id="width" name="width" type="text" value="800" class="input-xlarge">
    
  </div>
</div>

<!-- Text input-->
<div class="control-group">
  <label class="control-label" for="height">Height</label>
  <div class="controls">
    <input id="height" name="height" type="text" value="800" class="input-xlarge">
    
  </div>
</div>

<!-- Multiple Radios (inline) -->
<div class="control-group">
  <label class="control-label" for="format">Format</label>
  <div class="controls">
    <label class="radio inline" for="format-0">
      <input type="radio" name="format" id="format-0" value="png" checked="checked">
      png
    </label>
    <label class="radio inline" for="format-1">
      <input type="radio" name="format" id="format-1" value="jpg">
      jpg
    </label>
  </div>
</div>

<!-- Text input-->
<div class="control-group">
  <label class="control-label" for="mapid">Map ID</label>
  <div class="controls">
    <input id="mapid" name="mapid" type="text" value="tsmith512.b3c22e74" class="input-xlarge">
    
  </div>
</div>

<!-- Text input-->
<div class="control-group">
  <label class="control-label" for="access">Access Token</label>
  <div class="controls">
    <input id="access" name="access" type="text" <?php /* value="pk.eyJ1IjoidHNtaXRoNTEyIiwiYSI6IlBERzc0Mk0ifQ.IBWVp4rs5wKQ_8pkLOBXUw" */ ?> class="input-xlarge">

  </div>
</div>

<!-- Button (Double) -->
<div class="control-group">
  <label class="control-label" for="get"></label>
  <div class="controls">
    <button id="get" name="get" value="get" class="btn btn-success">Fetch</button>
  </div>
</div>

</fieldset>
</form>

<div>
  <?php
    if ($_GET['get'] == 'get') {
      $url = "https://api.mapbox.com/v4/{$_GET['mapid']}/pin-m-star+0099FF({$_GET['lon']},{$_GET['lat']})/{$_GET['lon']},{$_GET['lat']},{$_GET['z']}/{$_GET['width']}x{$_GET['height']}.{$_GET['format']}?access_token={$_GET['access']}";
      echo "<img src='{$url}'>";
    }
  ?>
</div>

<script>
  if ("geolocation" in navigator) {
    navigator.geolocation.getCurrentPosition(function(position) {
      document.getElementById('lat').value = position.coords.latitude;
      document.getElementById('lon').value = position.coords.longitude;
      document.getElementById('z').value = 8;
    });
  }
</script>
</body>
</html>
