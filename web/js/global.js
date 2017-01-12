(function($){
  'use strict';

  // Set the map height before we populate it.
  $(document).ready(function() {
    var $mapContainer = $('#map');
    var unusedHeight = ($('footer').offset().top - $mapContainer.offset().top - 90);
    if ( $mapContainer.height() < unusedHeight ) {
      $mapContainer.height(unusedHeight);
    }

    // Provide your access token
    L.mapbox.accessToken = 'pk.eyJ1IjoidHNtaXRoNTEyIiwiYSI6IlBERzc0Mk0ifQ.IBWVp4rs5wKQ_8pkLOBXUw';
    // Create a map in the div #map, globally accessible.
    window.map = L.mapbox.map('map', 'tsmith512.ee6ea9bf');
  });

})(jQuery);
