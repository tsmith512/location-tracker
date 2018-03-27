(function($){
  $(document).ready(function(){
    $.getJSON('/api/trips', function(data) {
      if (data.length) {

        var tripTable = document.getElementById("triplist");

        data.forEach(function(trip) {
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
        });
      }
    });

    $('#trip-form').on("submit", function(){
      var newTrip = {
        id: null,
        machine_name: document.getElementById("trip_machine_name").value,
        starttime: document.getElementById("trip_start").value,
        endtime: document.getElementById("trip_end").value,
        label: document.getElementById("trip_label").value
      }

      console.log(newTrip);
    });
  });
})(jQuery);
