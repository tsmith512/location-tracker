<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Map Test</title>
  <link href='https://api.mapbox.com/mapbox.js/v2.2.1/mapbox.css' rel='stylesheet' />
  <link href='https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v0.4.0/MarkerCluster.css' rel='stylesheet' />
  <link href='https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v0.4.0/MarkerCluster.Default.css' rel='stylesheet' />
</head>
<body style="margin:0;padding:0;">
  <div id="map" style="width:100%;height:100vh;"></div>

  <script src='https://api.mapbox.com/mapbox.js/v2.2.1/mapbox.js'></script>
  <script src='https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v0.4.0/leaflet.markercluster.js'></script>
  <script>
    // Provide your access token
    L.mapbox.accessToken = 'pk.eyJ1IjoidHNtaXRoNTEyIiwiYSI6ImIxMzdiYWYyOTRiZGM0NzQzMmU0ZWU4ZjJmMDU0MjYzIn0.SJchcHmW4PzUmwFcADM-sQ';
    // Create a map in the div #map
    var map = L.mapbox.map('map', 'tsmith512.b3c22e74');


    // var locationHistory =
    // var myLayer = L.mapbox.featureLayer().setGeoJSON(locationHistory).addTo(map);

    L.mapbox.featureLayer()
      .loadURL('/public/api/location/history/points')
      .on('ready', function(e) {
        var clusterGroup = new L.MarkerClusterGroup();
        e.target.eachLayer(function(layer) {
          clusterGroup.addLayer(layer);
      });
      map.addLayer(clusterGroup);
      map.fitBounds(clusterGroup.getBounds());

    });

  </script>
</body>
</html>
