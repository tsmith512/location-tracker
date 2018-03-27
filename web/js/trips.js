(function($){
  $(document).ready(function(){
    $.getJSON('/api/trips', function(data) {
      if (data.length) {

        var tripTable = document.getElementById("triplist");

        data.forEach(function(trip) {
          var row = tripTable.insertRow(-1);

          // To ensure the right order, select props each instead of iterating
          ['id', 'label', 'machine_name', 'starttime', 'endtime'].forEach(function(prop, index) {

            var cell = row.insertCell(index);

            // Append a text node to the cell
            var value = document.createTextNode(trip[prop]);
            cell.appendChild(value);
          });
        });
      }
    });
  });
})(jQuery);
