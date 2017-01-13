(function($){
  'use strict';


  // https://github.com/Leaflet/Leaflet.heat/issues/12
  var geoJson2heat = function(geojson, intensity) {
    return geojson.features.map(function(feature) {
      return [parseFloat(feature.geometry.coordinates[1]), parseFloat(feature.geometry.coordinates[0]), intensity];
    });
  }

  $(document).ready(function(){
    // http://stackoverflow.com/questions/26629000/mapbox-issue-with-parsing-json-data-for-heatmap
    var heat = L.heatLayer([], {maxZoom: 12}).addTo(map);

    $.getJSON('/api/location/history/points', function(data) {
      L.geoJson(data, {
        onEachFeature: function(feature, layer) {
          heat.addLatLng(
            // Yes, this library takes lat/lon backwards:
            L.latLng(feature.geometry.coordinates[1],
                     feature.geometry.coordinates[0]),
            { radius: 5, blur: 5}
          );
        }
      });
    });

    $('form#adjustments input').on('change', function(e) {
      var options = {};
      options[$(this).attr('id')] = parseInt($(this).val());
      heat.setOptions(options);
    });

    // @TODO: This looks like something that should go in a database, eh?
    var presets = [{
      type: 'city',
      name: 'austin',
      radius: 35,
      blur: 65,
      lat: 30.28842096834342,
      lng: -97.74845123291017,
      zoom: 11
    },
    {
      type: 'city',
      name: 'houston',
      radius: 25,
      blur: 40,
      lat: 29.81771047425529,
      lng: -95.43170928955078,
      zoom: 11
    },
    {
      type: 'city',
      name: 'tulsa',
      radius: 35,
      blur: 70,
      lat: 36.13718154651142,
      lng: -95.94068527221681,
      zoom: 12
    },
    {
      type: 'region',
      name: 'cali',
      radius: 30,
      blur: 65,
      lat: 35.44724605551148,
      lng: -119.97619628906251,
      zoom: 7
    },
    {
      type: 'region',
      name: 'colorado',
      radius: 40,
      blur: 75,
      lat: 40.0833252155441,
      lng: -105.23391723632814,
      zoom: 9
    },
    {
      type: 'region',
      name: 'skye',
      radius: 60,
      blur: 90,
      lat: 57.436819739170076,
      lng: -6.324005126953126,
      zoom: 9
    },
    {
      type: 'zone',
      name: 'na',
      radius: 35,
      blur: 50,
      lat: 41.21172151054787,
      lng: -101.55761718750001,
      zoom: 4
    },
    {
      type: 'zone',
      name: 'eu',
      radius: 35,
      blur: 50,
      lat: 48.96579381461063,
      lng: -0.5053710937500001,
      zoom: 5
    },
    {
      type: 'obs',
      name: 'iah',
      radius: 40,
      blur: 80,
      lat: 29.985150149802575,
      lng: -95.34067511558533,
      zoom: 16
    },
    {
      type: 'obs',
      name: 'rmnpblt',
      radius: 30,
      blur: 60,
      lat: 40.31454848939765,
      lng: -105.64925193786621,
      zoom: 13
    }];

    presets.forEach(function(preset){
      $('#' + preset.type + '-' + preset.name).on('click', function(e) {
        e.preventDefault();
        $('#radius').val(preset.radius);
        $('#blur').val(preset.blur);
        heat.setOptions({radius: preset.radius, blur: preset.blur});
        map.setView({lat: preset.lat, lng: preset.lng}, preset.zoom);
      });
    });

    // Start with North America
    $('#zone-na').click();
  });

})(jQuery);
