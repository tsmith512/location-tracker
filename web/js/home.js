(function($){
  'use strict';

  $.getJSON('/api/location/latest', function(data) {
    var locationUpdate = document.getElementById;

    if (data.city.length) {
      // Convert the timestamp to a localized date string; display:
      var lastTime = new Date(data.time * 1000);
      $('#last-time').text(lastTime.toLocaleString());

      // Output the city name
      $('#last-location').text(data.city);

      // Set the map center to the latest record
      map.setView([data.lat, data.lon], 12);
    }
  });
})(jQuery);
