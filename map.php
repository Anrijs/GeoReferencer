<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Calibration map</title>
    <link rel="stylesheet" href="assets/css/leaflet.css" />
    <script src="assets/js/leaflet.js"></script>
    <script src="border.js"></script>
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
var lyr = L.tileLayer("https://geotiff.anrijs.lv/corona/{z}/{x}/{y}.png", { maxNativeZoom: 15, detectRetina: false})

baseMaps["OSM"].addTo(map);

var overlayMaps = {
    "CORONA preview": L.tileLayer("https://geotiff.anrijs.lv/corona3/{z}/{x}/{y}.png", { maxNativeZoom: 15, detectRetina: false}),
    "NARA preview": L.tileLayer("https://tiles.anrijs.lv/aerofoto_1940/{z}/{x}/{y}.png", { maxNativeZoom: 17, detectRetina: false}),
    "NARA preview veer2": L.tileLayer("https://geotiff.anrijs.lv/aerover3/{z}/{x}/{y}.png", { maxNativeZoom: 17, detectRetina: false}),
 };

L.control.layers(baseMaps, overlayMaps).addTo(map);

$('.leaflet-container').css('cursor','crosshair');

<?php 
  require_once 'lib/fn.php';

  $done = getDoneMaps(True);
  $boxnum = 0;
  $extra = "";
  foreach ($done as $map) {

    $polypts = array();
    foreach($map["points"] as $pt) {
      $ptlat = $pt["dstlat"][0] + ($pt["dstlat"][1] / 60);
      $ptlon = $pt["dstlon"][0] + ($pt["dstlon"][1] / 60);

      $polypts[] = "[".$ptlat.",".$ptlon."]";
    }

    $calibtool = "calibrate.php";
    if (array_key_exists("corners", $map) && $map["corners"]) {
        $calibtool = "calibrate2.php";
    }

    echo "var popupText = '<b>" . $map["name"] . "</b><br><a href=\"#\" onclick=\"bbox".$boxnum.".removeFrom(map);\">Hide</a> | <a href=\"" . $calibtool . "?map=" . $map["name"] . "\">Edit</a>';"; 
    echo "var bbox" . $boxnum . " = L.polygon([";
    echo implode(",",$polypts);
    echo"], {fillOpacity: 0.05, weight: 1.5 " . $extra . "}).bindTooltip(\"" . $map["name"] . "\", {direction:'center'}).bindPopup(popupText).addTo(map);";

    ++$boxnum;
  }
?>

function showBorder() {
  L.geoJSON(geoborder, {color: "white", weight: 2}).addTo(map);
}

function showSquares() {
        var xrange = [20,29];
        var yrange = [55,59];

        L.polyline([
            [yrange[0],xrange[0]],
            [yrange[0],xrange[1]],
            [yrange[1],xrange[1]],
            [yrange[1],xrange[0]],
            [yrange[0],xrange[0]],
        ], {color: 'white', opacity: 0.75, weight: 2}).addTo(map);

        for (var x=xrange[0];x<xrange[1];x++) {
            L.polyline([
                [yrange[0],x],
                [yrange[1],x],
            ], {color: 'white', opacity: 0.75, weight: 2}).addTo(map);

            L.polyline([
                [yrange[0],x+0.5],
                [yrange[1],x+0.5],
            ], {color: 'white', opacity: 0.75, weight: 1}).addTo(map);
        }

        for (var y=yrange[0];y<yrange[1];y++) {
            L.polyline([
                [y,xrange[0]],
                [y,xrange[1]],
            ], {color: 'white', opacity: 0.75, weight: 2}).addTo(map);
        }
    }

</script>

</html>

 

