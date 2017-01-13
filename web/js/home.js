(function($){
  'use strict';

  $.getJSON('/api/location/latest', function(data) {
    var locationUpdate = document.getElementById;

    if (data.city.length) {
      // Convert the timestamp to a localized date string; display:
      var lastTime = moment.unix(data.time);
      var lastTimeOutput = lastTime.clone().tz("America/Chicago");
      $('#last-time').text(lastTimeOutput.format('dddd, MMMM Do, h:mma z'));

      // Output the city name
      $('#last-location').text(data.city);

      // Set the map center to the latest record
      map.setView([data.lat, data.lon], 12);
    }
  });
})(jQuery);
