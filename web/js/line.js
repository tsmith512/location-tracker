(function($){
  $(document).ready(function(){
    window.tripLine = L.mapbox.featureLayer()
      .loadURL('/api/location/history/line')
      .addTo(map);

    var updateView = function() {
      map.fitBounds(tripLine.getBounds());
      var mileage = turf.lineDistance(tripLine.getGeoJSON(), 'miles');
      $('#miles').text(mileage.toLocaleString());
    }

    tripLine.on('ready', updateView);

    $.getJSON('/api/trips', function(data) {
      if (data.length) {
        data.forEach(function(trip) {
          $('#trip-list').append($('<option/>').val(trip.id).text(trip.label));
        });
      }
    });

    $('#trip-list').on('change', function(e){
      var tripId = $('option:selected', this).val();

      if (tripId < 1) {
        $.getJSON('/api/location/history/line', function(data) {
          tripLine.setGeoJSON(data);
          updateView();
        });
      } else {
        $.getJSON('/api/trips/' + tripId, function(data) {
          tripLine.setGeoJSON(data.line);
          updateView();
        });
      }
    });
  });
})(jQuery);
