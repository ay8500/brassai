<?PHP 	include_once("sessionManager.php"); 
//Change scool year and class if parameters are there 
if (isset($_GET['scoolYear'])) {
	$_SESSION['scoolYear']=$_GET['scoolYear'];
} 
if (isset($_GET['scoolClass']))  {
	$_SESSION['scoolClass']=$_GET['scoolClass'];	
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
 <head>
   <title>A diakok a vílág térképén</title>
   <meta http-equiv="content-type" content="text/html; charset=UTF8">
   <link rel="stylesheet" type="text/css" href="./menu.css">
   <meta name="robots" content="index,follow" />
   <meta name="geo.placename" content="Kolozsvár" />
   <meta name="geo.position" content="46.771919;23.592248" />
   <meta name="author" content="Levente Maier">
   <meta name="description" content="A kolozsvari Brassai Samuel líceum diakjai a vílág térképén">
   <meta name="keywords" content="Brassai Sámuel iskola líceum Kolozsvár Cluj Klausenburg diák diákok térkép">
   <script type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAAt_D9PjCp6KIewCC6DftsBTV4tYwmYR0tDWyEKlffNzbwkWE4hTrbEDIZOQBwqdYefOLpNQ7swehXg" ></script>
 
	<script type="text/javascript">
	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-20252557-2']);
	  _gaq.push(['_trackPageview']);

	  (function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	</script>

 
	<link rel="stylesheet" type="text/css" href="ddsmoothmenu.css" />
	<link rel="stylesheet" type="text/css" href="ddsmoothmenu-v.css" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
	<script type="text/javascript" src="js/ddsmoothmenu.js"></script>
	<script type="text/javascript">
	  ddsmoothmenu.init({	mainmenuid: 'smoothmenu', orientation: 'v', classname: 'ddsmoothmenu-v', contentsource: "markup" })
	</script>

   <script type="text/javascript">
	var application;	//Google Mao
	var mapPoints=0;
	var xmlHttp;		//AJAX
   
    function zoomMap(i) {
		if (i==1) { var zoom=11; var center = new GLatLng(46.769,23.591); }
		if (i==2) { var zoom=10; var center = new GLatLng(47.4984,19.0411);}
		if (i==3) { var zoom=7; var center = new GLatLng(47,23);}
		if (i==4) { var zoom=7; var center = new GLatLng(47.4984,19.0411);}
		if (i==5) { var zoom=7; var center = new GLatLng(49.84,9.97);}
		if (i==6) { var zoom=5; var center = new GLatLng(47.4984,15.0411);}
		if (i==7) { var zoom=2; var center = new GLatLng(41,-42);}
		application.map.setCenter(center, zoom);
		fillPoints();
	}
	
	function centerMap(lat,lng) {
		var zoom=15; 
		var center = new GLatLng(lat,lng); 
		application.map.setCenter(center, zoom);
		fillPoints();
	}
	
    function MyApplication() {
      if (GBrowserIsCompatible()) {
		 <?PHP
		include_once("data.php");
		$geolat = array();
		$geolng = array();
		$xmin=180;$xmax=-180;$ymin=180;$ymax=-180;
		$i=0;
		for ($l=1;$l<=getDataSize();$l++) {
			$d=getPerson($l);
			if ($d["geolat"]!="") {
				$geolat[$i]=$d["geolat"];
				$geolng[$i]=$d["geolng"];
				if ($geolat[$i]>$xmax) $xmax=$geolat[$i];
				if ($geolat[$i]<$xmin) $xmin=$geolat[$i];
				if ($geolng[$i]>$ymax) $ymax=$geolng[$i];
				if ($geolng[$i]<$ymin) $ymin=$geolng[$i];
				$i++;
			}
			$xcenter=($xmax+$xmin) / 2;
			$ycenter=($ymax+$ymin) / 2;
		}
		?>
        this.map = new GMap2(document.getElementById("map_canvas"));
		this.map.enableScrollWheelZoom();
        this.map.addControl(new GSmallMapControl());
        this.map.addControl(new GMapTypeControl());
        var center = new GLatLng(<?PHP echo($xcenter.','.$ycenter); ?>);
        this.map.setCenter(center, 2);
        GEvent.bind(this.map, "dragend", this, this.onMapDragEnd);
        
      }
    }
    
    MyApplication.prototype.onMapDragEnd = function() {
		document.getElementById("txtPerson").innerHTML="Search...";
		fillPoints();
    }
    
    function initialize() {
      if (GBrowserIsCompatible()) {
        application = new MyApplication();
        application.onMapDragEnd();
      }
    }
    
    function fillPoints()
	{ 
		xmlHttp=GetXmlHttpObject();
		if (xmlHttp==null)
		 {
		 alert ("Browser does not support HTTP Request");
		 return;
		 }
		application.map.clearOverlays();
		SW= new GLatLng();SW=application.map.getBounds().getSouthWest();
		NE= new GLatLng();NE=application.map.getBounds().getNorthEast();
		var url="getGeoPoints.php";
		url=url+"?lat1="+SW.lat();
		url=url+"&lng1="+SW.lng();
		url=url+"&lat2="+NE.lat();
		url=url+"&lng2="+NE.lng();
		xmlHttp.onreadystatechange=setMarkers; 
		xmlHttp.open("GET",url,true);
		xmlHttp.send(null);
	}
	
	function setMarkers()
	{ 
		if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete") { 
	 		ret=xmlHttp.responseText;
	 		var pointArr = new Array();
	 		pointArr = ret.split("|");
	 		for(var i=0;i < pointArr.length; i++)
			{
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
	}

    
    function GetXmlHttpObject()
	{
		var xmlHttp=null;
		try
		 {
		 // Firefox, Opera 8.0+, Safari
		 xmlHttp=new XMLHttpRequest();
		 }
		catch (e)
		 {
		 //Internet Explorer
		 try
		  {
		  xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		  }
		 catch (e)
		  {
		  xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
		  }
		 }
		return xmlHttp;
	}
	
	
function getLocation() {
  if (!window.google || !google.gears) {
    location.href = "http://gears.google.com/?action=install&name=A kolozsvari Brassai Sámuel líceum vén diákjai&message=Google Gears telepítése szükséges&icon_src=http://brassai.blue-l.de/favicon.jpg&return=http://brassai.blue-l.de/worldmap.php";
    return;
  }

  addStatus('Valyon merre lehetsz...');

  function successCallback(p) {
    var address = p.gearsAddress.city + ', '
                  + p.gearsAddress.region + ', '
                  + p.gearsAddress.country + '  '
                  + p.gearsAddress.postalCode + '  '
                  //+ p.gearsAddress.street + '  '
                  //+ p.gearsAddress.streetNumber + '  '
                  //+ 'Altitude:'+ p.altitude + '  '
                  + '('+ p.latitude + ', '
                  + p.longitude + ')'
                  + ' Pontosság:'+ p.accuracy + ' meter'
                  ;

    clearStatus();
    addStatus('A te geometriai helyed: ' + address);
    centerMap(p.latitude,p.longitude);
  				latlng = new GLatLng(p.latitude,p.longitude);
				marker = new GMarker(latlng,markerOptions);
				application.map.addOverlay(marker);
  }

  function errorCallback(err) {
    var msg = 'Error retrieving your location: ' + err.message;
    setError(msg);
  }

  try {
    var geolocation = google.gears.factory.create('beta.geolocation');
    geolocation.getCurrentPosition(successCallback,
                                   errorCallback,
                                   { enableHighAccuracy: true,
                                     gearsRequestAddress: true });
  } catch (e) {
    setError('Error using Geolocation API: ' + e.message);
    return;
  }

}
	
    </script>
 </head>
<body onload="initialize()" onunload="GUnload()"> 
<?PHP 
$googleMap = true;
include("homemenu.php"); 

?>
<h2 class="sub_title">Merre szóródtak szét az osztálytársak:</h2>
<table align="center" class="pannel" style="width:700px">
<tr><td>
<?PHP
if ( !isset($_SESSION['USER']) || $_SESSION['USER']="" || $_SESSION['USER']=0) { 
	echo('<div style="text-align:center;font-size:12px">Mivel a weblap látogatója anonim a koordináták véletlenszerüen el vannak kb. 10 km el tólva. Jelenkezz be a pontos poziciók megtekintéséhez.</div>');
}
?>

<div align="center" style="text-align:center;">
	<div id="map_canvas" style="width: 600px; height: 400px; text-align:center"></div>
	<br/>
	Térkép részletek: 
	<a href="javascript:zoomMap(1);">Kolozsvár</a>
	<a href="javascript:zoomMap(2);">Budapest</a>
	<a href="javascript:zoomMap(3);">Erdély</a>
	<a href="javascript:zoomMap(4);">Magyarország</a>
	<a href="javascript:zoomMap(5);">Németország</a>
	<a href="javascript:zoomMap(6);">Europa</a>
	<a href="javascript:zoomMap(7);">Az egész világ</a>
	<br/>&nbsp;
	<div id="txtPerson">Osztálytárs a térképen:</div>
</div>
</td></tr>
<tr><td id="status"></td></tr>

</td>
</tr>
</table>


</table>
</td>
</tr>
</table>
</body>
</html>
