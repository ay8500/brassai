var map = null;
var osmGeocoder = null;
var marker = null;

$(document).ready(function() {
    initialize();
});

function initialize() {
    map = L.map('map_canvas').setView([centerx, centery], 11);
    L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a>',
        maxZoom: 18,
        id: 'mapbox.streets',
        accessToken: 'pk.eyJ1IjoiYXk4NTAwIiwiYSI6ImNqa3VyMzA5NDBhMTEzcXJydHIyY3dtMTYifQ.2bYqsOo9fV8tEOW-Jirxuw'
    }).addTo(map);
    L.control.scale().addTo(map);

    marker = L.marker([centerx, centery], {draggable: true}).addTo(map);
    showGeoPosition(marker.getLatLng().lat, marker.getLatLng().lng);

    marker.on('moveend', function () {
        showGeoPosition(marker.getLatLng().lat, marker.getLatLng().lng);
    });

    marker.on('drag', function () {
        showGeoPosition(marker.getLatLng().lat, marker.getLatLng().lng);
    });

    var geocoderoptions = {
        collapsed: false,
        position: 'topright',
        text: 'Keresés',
        value: "ok",
        placeholder: 'város, utca házszám',
        callback: function (results) {
            var bbox = results[0].boundingbox,
                first = new L.LatLng(bbox[0], bbox[2]),
                second = new L.LatLng(bbox[1], bbox[3]),
                bounds = new L.LatLngBounds([first, second]);
            this._map.fitBounds(bounds);
            var center = L.latLng(results[0]["lat"], results[0]["lon"]);
            marker.setLatLng(center);
            showGeoPosition(marker.getLatLng().lat, marker.getLatLng().lng);
        }
    };

    osmGeocoder = new L.Control.OSMGeocoder(geocoderoptions);

    map.addControl(osmGeocoder);


    //    marker = new google.maps.Marker({position:center,map:map,draggable: true});
    //    var infowindow = new google.maps.InfoWindow({
    //        content: diak,
    //        maxWidth: 200
    //    });
}

function doSearch(event) {
    $("#geocoder_value").val($("#addres").val());
    osmGeocoder._geocode(event);
}

function showGeoPosition(lat,lng) {
    document.geo.geolat.value = lat;
    document.geo.geolng.value = lng;
}
