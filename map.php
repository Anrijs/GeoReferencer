<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Calibration map</title>
    <link rel="stylesheet" href="assets/css/leaflet.css" />
    <script src="assets/js/leaflet.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>

    <style>
        body {
            padding: 0;
            margin: 0;
        }
        html, body, #map {
            height: 100%;
            width: 100%;
            background-color: #000;
            font-family: helvetica;
        }
    </style>
</head>
<body>
    <div id="map"></div>
</body>
<script type="text/javascript">

var map_osm = L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
  maxZoom: 22,
  maxNativeZoom: 18,
});

var map_esri = L.tileLayer('//server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 22,
        maxNativeZoom: 18,
    });

var baseMaps = {
    "OSM": map_osm,
    "ESRI Sat": map_esri
};

var map = L.map('map', {maxZoom:22}).setView([56.75,24], 8);

baseMaps["OSM"].addTo(map);

var overlayMaps = {
  // add your own ovelays...
};

L.control.layers(baseMaps, overlayMaps).addTo(map);

$('.leaflet-container').css('cursor','crosshair');

<?php 
  require_once 'lib/fn.php';

  $done = getDoneMaps();
  $boxnum = 0;
  $extra = "";
  foreach ($done as $map) {

    $polypts = array();
    foreach($map["points"] as $pt) {
      $ptlat = $pt["dstlat"][0] + ($pt["dstlat"][1] / 60);
      $ptlon = $pt["dstlon"][0] + ($pt["dstlon"][1] / 60);

      $polypts[] = "[".$ptlat.",".$ptlon."]";
    }

    echo "var popupText = '<b>" . $map["name"] . "</b><br><a href=\"#\" onclick=\"bbox".$boxnum.".removeFrom(map);\">Hide</a> | <a href=\"calibrate.php?map=" . $map["name"] . "\">Edit</a>';"; 
    echo "var bbox" . $boxnum . " = L.polygon([";
    echo implode(",",$polypts);
    echo"], {fillOpacity: 0.05, weight: 1.5 " . $extra . "}).bindTooltip(\"" . $map["name"] . "\", {direction:'center'}).bindPopup(popupText).addTo(map);";

    ++$boxnum;
  }
?>

</script>

</html>

 

