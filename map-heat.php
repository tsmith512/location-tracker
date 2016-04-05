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
  <script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-heat/v0.1.3/leaflet-heat.js'></script>
  <script src="https://code.jquery.com/jquery-2.2.2.min.js"></script>
  <script>
    // Provide your access token
    L.mapbox.accessToken = 'pk.eyJ1IjoidHNtaXRoNTEyIiwiYSI6ImIxMzdiYWYyOTRiZGM0NzQzMmU0ZWU4ZjJmMDU0MjYzIn0.SJchcHmW4PzUmwFcADM-sQ';
    // Create a map in the div #map
    var map  = L.mapbox.map('map', 'tsmith512.b3c22e74');

    // https://github.com/Leaflet/Leaflet.heat/issues/12
    geoJson2heat = function(geojson, intensity) {
      return geojson.features.map(function(feature) {
        return [parseFloat(feature.geometry.coordinates[1]), parseFloat(feature.geometry.coordinates[0]), intensity];
      });
    }

    // http://stackoverflow.com/questions/26629000/mapbox-issue-with-parsing-json-data-for-heatmap
    var heat = L.heatLayer([], { maxZoom: 12 }).addTo(map);
    $.getJSON('/public/api/location/history/points', function(data) {
      var geojson = L.geoJson(data, {
        onEachFeature: function(feature, layer) {
          heat.addLatLng(L.latLng(feature.geometry.coordinates[1], feature.geometry.coordinates[0]), { radius: 5, blur: 5});
        }
      });
    });

    map.on('zoomend', function() {
      var z = map.getZoom(); // Between 1 (out) and 22 (in)
      heat.setOptions({radius: (Math.abs(z-10)+20), blur: ((Math.abs(z-12)*2)+20) });
    });

  </script>
</body>
</html>
