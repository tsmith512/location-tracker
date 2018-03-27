(function($){
  $(document).ready(function(){

    function populateTripTable(trip) {
      console.log(trip);
      var tripTable = document.getElementById("triplist");
      var row = tripTable.insertRow(-1);

      // To ensure the right order, select props each instead of iterating
      ['id', 'label', 'machine_name', 'starttime', 'endtime'].forEach(function(prop, index) {

        // Create the cell
        var cell = row.insertCell(index);

        // If we're dealing with a time, let's make it easy to read
        if (prop.indexOf('time') > -1) {
          var date = new Date(trip[prop] * 1000);
          var value = document.createTextNode(
            date.toLocaleString()
          );
        }
        else {
          // Append a text node to the cell
          var value = document.createTextNode(trip[prop]);
        }

        cell.appendChild(value);
      });
    }

    $.getJSON('/api/trips', function(data) {
      if (data.length) {
        data.forEach(populateTripTable);
      }
    });

    $('#trip-form').on("submit", function(e){
      e.preventDefault();

      var newTrip = {
        id: null,
        machine_name: document.getElementById("trip_machine_name").value,
        starttime: document.getElementById("trip_start").value,
        endtime: document.getElementById("trip_end").value,
        label: document.getElementById("trip_label").value
      }

      $.ajax({
        type: "POST",
        url: "/api/trips/create",
        data: JSON.stringify(newTrip),
        success: function () {
          $('#trip-form input').val("");
        }
      }).done(function (data) {
        populateTripTable(JSON.parse(data));
      });
    });
  });
})(jQuery);
