var map = null;
var geocoder = null;
var marker = null;

$(document).ready(function() {
    initialize();
});

function initialize() {
    center = new google.maps.LatLng(centerx, centery);
    map = new google.maps.Map(
	        document.getElementById('map_canvas'), {
	          center: center,
	          zoom: 12,
	          mapTypeId: google.maps.MapTypeId.ROADMAP,
	          zoomControl: true,
	          mapTypeControl: true,
	          scaleControl: true,
	          streetViewControl: true,
	          rotateControl: true,
	          fullscreenControl:false,
	          gestureHandling: 'greedy',

       });
    	
        marker = new google.maps.Marker({position:center,map:map,draggable: true});
        var infowindow = new google.maps.InfoWindow({
            content: diak,
            maxWidth: 200
        });
        
        marker.addListener('click', function() {
            infowindow.open(map, marker);
          });
        
        marker.addListener('dragend', function() {
            infowindow.open(map, marker);
            var position = marker.getPosition();
            document.geo.geolat.value=position.lat();
            document.geo.geolng.value=position.lng();
            fieldChanged(); 
        });

        
        if(null!=document.geo) {
		document.geo.geolat.value=center.lat();
		document.geo.geolng.value=center.lng();
        }

        geocoder = new google.maps.Geocoder();
    }
    
    function doSearch() {
      var address = document.getElementById("addres").value;
      geocoder.geocode({'address': address}, function(results, status) {
          if (status === 'OK') {
            center = results[0].geometry.location
            map.setCenter(center);
            map.setZoom(12);
            marker.setPosition(center);
            document.geo.geolat.value=center.lat();
            document.geo.geolng.value=center.lng();
          } else {
            alert('Nem találtam semmit!  ' + status);
          }
      });
    }
       
