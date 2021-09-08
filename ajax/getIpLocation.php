<?php
include_once '../config.class.php';
include_once Config::$lpfw.'ltools.php';

header('Content-Type: application/json');

$ip=getParam("ip");

$ret = json_decode(file_get_contents("http://ip-api.com/json/".$ip));

$ret->x = json_decode(file_get_contents("http://api.ipapi.com/".$ip."?access_key=68ae26e798c7aaef5446488d3ecd36ef&output=json&fields=country_name,city,zip"));

echo json_encode($ret);

/*
https://localhost/brassai/ajax/getIpLocation?ip=192.77.237.9

status: "success",
country: "United States",
countryCode: "US",
region: "CA",
regionName: "California",
city: "San Francisco",
zip: "94105",
lat: 37.7852,
lon: -122.3874,
timezone: "America/Los_Angeles",
isp: "Webpass Inc",
org: "",
as: "AS19165 Webpass Inc.",
query: "192.77.237.95"
x: {
    country_name: "United States",
    city: "San Francisco",
    zip: "94107",
    location: {
    country_flag: "http://assets.ipapi.com/flags/us.svg"
}
}
}
*/
?>
