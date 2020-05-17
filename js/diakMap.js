var map; 
var markers=Array();

$(document).ready(function() {
    application = initialize();

    
    $.fn.redraw = function(){
	  $(this).each(function(){
	    var redraw = this.offsetHeight;
	  });
    };
});


function zoomMap(i) {
    var zoom = 11;
    var center = null;
    if (i==1) { zoom=11; center = ({lat:46.769,lng:23.591}); }
    if (i==2) { zoom=10; center = ({lat:47.4984,lng:19.0411});}
    if (i==3) { zoom=7; center =  ({lat:47,lng:23});}
    if (i==4) { zoom=7; center =  ({lat:47.4984,lng:19.0411});}
    if (i==5) { zoom=7; center =  ({lat:49.84,lng:9.97});}
    if (i==6) { zoom=5; center =  ({lat:47.4984,lng:15.0411});}
    if (i==7) { zoom=3; center =  ({lat:41,lng:-42});}
    map.setCenter(center)
    map.setZoom(zoom);
    fillPoints();
}
	
	
function initialize() {
    map = new google.maps.Map(
	        document.getElementById('map_canvas'), {
	          center: new google.maps.LatLng(47, 18),
	          zoom: 5,
	          mapTypeId: google.maps.MapTypeId.ROADMAP,
	          zoomControl: true,
	          mapTypeControl: true,
	          scaleControl: true,
	          streetViewControl: true,
	          rotateControl: true,
	          fullscreenControl:false,
	          gestureHandling: 'greedy',

	       });
	//this.map.enableScrollWheelZoom();
    	map.addListener('idle', function() {
    	    fillPoints();
    	});
    	map.addListener('dragend', function() {
    	    fillPoints();
    	});
    	map.addListener('zoom_changed', function() {
    	    fillPoints();
    	});

}

    
    
function fillPoints() {
    if (map.getBounds()!=null) {
        document.getElementById("txtPerson").innerHTML='Adat keresés <img src="images/loading.gif" />';
        for(var i=0;i < markers.length; i++) {
            markers[i].setMap(null);
        }
        while(markers.length > 0) {
            markers.pop();
        }
        var SW=(map.getBounds().b);
        var NE=(map.getBounds().f);
        var url="ajax/getGeoPoints";
        url +="?lat1="+NE.b;
        url +="&lng1="+SW.b;
        url +="&lat2="+NE.f;
        url +="&lng2="+SW.f;
        $.ajax({
    	url:url,
    	type:"GET",
    	//dataType: 'json',
    	success:function(data){
    	    setMarkers(data);
    	}
        });
    }
}
	
function setMarkers(data)	{ 
    pointArr = data.split("|");
    for(var i=0;i < pointArr.length; i++) {
	if (pointArr[i]!="") {
	    var point = new Array();
	    point = pointArr[i].split(":");
	    var latlng = {lat:parseFloat(point[0]),lng:parseFloat(point[1])};
	    markers[i] = new google.maps.Marker({position:latlng,map:map,title:point[2]});
	    
	}				
    }
    document.getElementById("txtPerson").innerHTML="Osztálytárs a térképen:"+(i+1);
} 

