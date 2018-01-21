var map = null;
var geocoder = null;
var marker = null;

$(document).ready(function() {
    initialize();
});

function initialize() {
      if (GBrowserIsCompatible()) {
        map = new GMap2(document.getElementById("map_canvas"));
		map.enableScrollWheelZoom();
        map.addControl(new GSmallMapControl());
        map.addControl(new GMapTypeControl());
        center = new GLatLng(centerx,centery);
        map.setCenter(center, 10);

        marker = new GMarker(center, {draggable: true});
        if(null!=document.geo) {
		document.geo.geolat.value=center.lat();
		document.geo.geolng.value=center.lng();
        }

        GEvent.addListener(marker, "dragstart", function() {
          map.closeInfoWindow();
        });

        GEvent.addListener(marker, "dragend", function() {
          marker.openInfoWindowHtml(diak);
		  var position = marker.getLatLng();
		  document.geo.geolat.value=position.lat();
		  document.geo.geolng.value=position.lng();
		  fieldChanged(); 
        });

        map.addOverlay(marker);
        
        geocoder = new GClientGeocoder();
       }
    }
    
    function doSearch() {
      var addres = document.getElementById("addres").value;
      if (geocoder) {
        geocoder.getLatLng(
          addres,
          function(point) {
            if (!point) {
              alert(addres + " Nem találtam semmit.");
            } else {
              map.setCenter(point, 13);
              marker.setLatLng(point);
			  document.geo.geolat.value=point.lat();
			  document.geo.geolng.value=point.lng();
            }
          }
        );
      }
    }
       
