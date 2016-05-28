var application;	//Google Mao
var mapPoints=0;
var xmlHttp;		//AJAX

$(document).ready(function() {
    if (GBrowserIsCompatible()) {
        application = new MyApplication();
        zoomMap(7);
        
        setTimeout( function(){fillPoints()},500);
    }
});


function zoomMap(i) {
    var zoom = 11;
    var center = null;
    if (i==1) { zoom=11; center = new GLatLng(46.769,23.591); }
    if (i==2) { zoom=10; center = new GLatLng(47.4984,19.0411);}
    if (i==3) { zoom=7; center = new GLatLng(47,23);}
    if (i==4) { zoom=7; center = new GLatLng(47.4984,19.0411);}
    if (i==5) { zoom=7; center = new GLatLng(49.84,9.97);}
    if (i==6) { zoom=5; center = new GLatLng(47.4984,15.0411);}
    if (i==7) { zoom=3; center = new GLatLng(41,-42);}
    application.map.setCenter(center, zoom);
    fillPoints();
}
	
	
function MyApplication() {
    if (GBrowserIsCompatible()) {
	this.map = new GMap2(document.getElementById("map_canvas"));
	this.map.enableScrollWheelZoom();
        this.map.addControl(new GSmallMapControl());
        this.map.addControl(new GMapTypeControl());
        var center = new GLatLng(41,-42);
        this.map.setCenter(center, 2);
	GEvent.bind(this.map, "dragend", this, this.onMapDragEnd);

    }
}
    
MyApplication.prototype.onMapDragEnd = function() {
	document.getElementById("txtPerson").innerHTML="Search...";
	fillPoints();
};


    
function fillPoints() {
    application.map.clearOverlays();
    var SW= new GLatLng();SW=application.map.getBounds().getSouthWest();
    var NE= new GLatLng();NE=application.map.getBounds().getNorthEast();
    var url="getGeoPoints.php";
    url +="?lat1="+SW.lat();
    url +="&lng1="+SW.lng();
    url +="&lat2="+NE.lat();
    url +="&lng2="+NE.lng();
    $.ajax({
	url:url,
	type:"GET",
	//dataType: 'json',
	success:function(data){
	    setMarkers(data);
	}
    });
}
	
function setMarkers(data)	{ 
    pointArr = data.split("|");
    for(var i=0;i < pointArr.length; i++) {
	if (pointArr[i]!="") {
	    var point = new Array();
	    point = pointArr[i].split(":");
	    latlng = new GLatLng(point[0],point[1]);
	    markerOptions = { title:point[2]};
	    marker = new GMarker(latlng,markerOptions);
	    application.map.addOverlay(marker);
	}				
    }
    document.getElementById("txtPerson").innerHTML="Osztálytárs a térképen:"+(pointArr.length-1); 
} 

