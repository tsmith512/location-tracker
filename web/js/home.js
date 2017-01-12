(function($){
  'use strict';

  var request = new XMLHttpRequest();
  request.open('GET', '/api/location/latest', true);

  request.onload = function() {
    var locationUpdate = document.getElementById('last-location');
    var timeUpdate = document.getElementById('last-time');

    if (this.status == 200) {
      var data = JSON.parse(this.response);

      if (data.city.length) {
        // Convert the timestamp to a localized date string; display:
        var lastTime = moment.unix(data.time);
        var lastTimeOutput = lastTime.clone().tz("America/Chicago");
        timeUpdate.innerHTML = lastTimeOutput.format('dddd, MMMM Do, h:mma z');

        // Output the city name
        locationUpdate.innerHTML = data.city;

        // Set the map center to the latest record
        map.setView([data.lat, data.lon], 12);
      }
    }
  };

  request.send();
})(jQuery);
