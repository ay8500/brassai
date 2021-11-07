var map; 
var markers=Array();

$(document).ready(function() {
    application = initialize();
    fillPoints();
    
    $.fn.redraw = function(){
	  $(this).each(function(){
	    var redraw = this.offsetHeight;
	  });
    };
});


function zoomMap(i) {
    var zoom = 11;
    var center = null;
    if (i==1) { zoom=11; center = [46.769,23.591]; }
    if (i==2) { zoom=10; center = [47.4984,19.0411];}
    if (i==3) { zoom=7; center =  [47,23];}
    if (i==4) { zoom=7; center =  [47.4984,19.0411];}
    if (i==5) { zoom=7; center =  [49.84,9.97];}
    if (i==6) { zoom=5; center =  [47.4984,15.0411];}
    if (i==6) { zoom=5; center =  [47.4984,15.0411];}
    if (i==7) { zoom=3; center =  [41,-42];}
    map.setView(center,zoom,{animate:false});
    fillPoints();
}
	
	
function initialize() {
    map= L.map('map_canvas').setView([47, 18], 5);
    L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
    attribution: '© <a href="https://www.mapbox.com/about/maps/">Mapbox</a> © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> <strong><a href="https://www.mapbox.com/map-feedback/" target="_blank">Improve this map</a></strong>',
        tileSize: 512,
        maxZoom: 18,
        zoomOffset: -1,
        id: 'mapbox/streets-v11',
	    accessToken: 'pk.eyJ1IjoiYXk4NTAwIiwiYSI6ImNqa3VyMzA5NDBhMTEzcXJydHIyY3dtMTYifQ.2bYqsOo9fV8tEOW-Jirxuw'
    }).addTo(map);
    L.control.scale().addTo(map);
    
    map.on('moveend', function() {
        fillPoints();
    });
    map.on('zoomend', function() {
        fillPoints();
    });

    var geocoderoptions  = {
        collapsed: false,
        position: 'topright',
        text: 'Keresés',
        value:"ok",
        placeholder: 'város, utca házszám',
        callback: function (results) {
            var bbox = results[0].boundingbox,
                first = new L.LatLng(bbox[0], bbox[2]),
                second = new L.LatLng(bbox[1], bbox[3]),
                bounds = new L.LatLngBounds([first, second]);
            this._map.fitBounds(bounds);
        }
    };

    var osmGeocoder = new L.Control.OSMGeocoder(geocoderoptions );

    map.addControl(osmGeocoder);
}

    
    
function fillPoints() {
    if (map.getBounds()!=null) {
        document.getElementById("txtPerson").innerHTML='Adat keresés <img src="images/loading.gif" />';
        for(var i=0;i < markers.length; i++) {
            markers[i].remove();
        }
        while(markers.length > 0) {
            markers.pop();
        }
        var url="ajax/getGeoPoints";
        url +="?lat2="+map.getBounds().getNorth();
        url +="&lng1="+map.getBounds().getWest();
        url +="&lat1="+map.getBounds().getSouth();
        url +="&lng2="+map.getBounds().getEast();
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
	    var latlng = {lat:parseFloat(point[0]),lon:parseFloat(point[1])};
	    markers[i] = L.marker(latlng,{title:point[2],riseOnHover:true}).addTo(map);
	    
	}				
    }
    document.getElementById("txtPerson").innerHTML="Kolozsvári tanár és véndiák a térképen:"+(i+1);
}



