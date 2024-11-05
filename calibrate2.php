<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        include "lib/fn.php";

        $pointerCount = 3;
        $isSaved = 0;

        $map = FALSE;
        $left = 0;

        $incompleteMaps = getIncompleteMaps();
        
        if (isset($_GET["map"])) {
            $map = "maps/" . $_GET["map"];
        } else {
            $maps = $incompleteMaps;
            $left = sizeof($maps);
            if (sizeof($maps) < 1) {
                $nomaps = TRUE;
            } else {
                if (isset($_GET["sq"])) {
                    $map = "maps/" . $maps[0];
                } else if (isset($_GET["rsq"])) {
                    $map = "maps/" . $maps[sizeof($maps)-1];
                } else {
                    $map = "maps/" . urldecode($maps[array_rand($maps)]); // $maps[0];
                }
            }
        }
    
        $pt1xy = "-1,-1";
        $pt2xy = "-1,-1";
        $pt3xy = "-1,-1";
        $pt4xy = "-1,-1";

        $pt1yx = "-1,-1";
        $pt2yx = "-1,-1";
        $pt3yx = "-1,-1";
        $pt4yx = "-1,-1";

        $pt1latlon = "0,0";
        $pt2latlon = "0,0";
        $pt3latlon = "0,0";
        $pt4latlon = "0,0";


        $pt1lat = array("","");
        $pt1lon = array("","");
        $pt2lat = array("","");
        $pt2lon = array("","");
        $pt3lat = array("","");
        $pt3lon = array("","");

        $pt4lat = array("","");
        $pt4lon = array("","");

        $prevMap = "";
        $nextMap = "";

        $allmaps = getMaps();
        natsort($allmaps);
        $allmaps = array_values($allmaps);

        // get next and prev map
        if (isset($_GET["map"])) {
            foreach($allmaps as $key => $m) {
                if ($m == $_GET["map"]) {
                    $nextId = $key-1;
                    $prevId = $key+1;

                    if ($prevId < 0) $prevId = sizeof($allmaps);
                    if ($nextId >= sizeof($allmaps)) $nextId = 0;

                    $prevMap = $allmaps[$prevId];
                    $nextMap = $allmaps[$nextId];
                    break;
                }
            }
        } else {
            $nextId = array_rand($incompleteMaps);
            $prevId = array_rand($incompleteMaps);

            $nextMap = $incompleteMaps[$nextId];
            $prevMap = $incompleteMaps[$prevId];
        }

        $defcutline = "[0,0],[0, imgw-1],[imgh-1,imgw-1],[imgh-1,0]";

        if (isset($_GET["map"])) {
            $name = urldecode($_GET["map"]);

            $done = getDoneMaps();
            foreach ($done as $d) {
                if ($d["name"] == $name) {
                    $pt1xy = $d["points"][0]["x"] . "," . $d["points"][0]["y"];
                    $pt2xy = $d["points"][1]["x"] . "," . $d["points"][1]["y"];
                    $pt3xy = $d["points"][2]["x"] . "," . $d["points"][2]["y"];
                    if (sizeof($d["points"]) >= 4) $pt4xy = $d["points"][3]["x"] . "," . $d["points"][3]["y"];

                    $pt1yx = $d["points"][0]["y"] . "," . $d["points"][0]["x"];
                    $pt2yx = $d["points"][1]["y"] . "," . $d["points"][1]["x"];
                    $pt3yx = $d["points"][2]["y"] . "," . $d["points"][2]["x"];
                    if (sizeof($d["points"]) >= 4) $pt4yx = $d["points"][3]["y"] . "," . $d["points"][3]["x"];

                    $pt1lat = array($d["points"][0]["lat"][0], $d["points"][0]["lat"][1]);
                    $pt1lon = array($d["points"][0]["lon"][0], $d["points"][0]["lon"][1]);
                    $pt2lat = array($d["points"][1]["lat"][0], $d["points"][1]["lat"][1]);
                    $pt2lon = array($d["points"][1]["lon"][0], $d["points"][1]["lon"][1]);
                    $pt3lat = array($d["points"][2]["lat"][0], $d["points"][2]["lat"][1]);
                    $pt3lon = array($d["points"][2]["lon"][0], $d["points"][2]["lon"][1]);
                    if (sizeof($d["points"]) >= 4) $pt4lat = array($d["points"][3]["lat"][0], $d["points"][3]["lat"][1]);
                    if (sizeof($d["points"]) >= 4) $pt4lon = array($d["points"][3]["lon"][0], $d["points"][3]["lon"][1]);

                    $pt1latlon = (floatval($d["points"][0]["lat"][0]) + (floatval($d["points"][0]["lat"][1]) / 60.0)) . ", " . (floatval($d["points"][0]["lon"][0]) + (floatval($d["points"][0]["lon"][1]) / 60.0));
                    $pt2latlon = (floatval($d["points"][1]["lat"][0]) + (floatval($d["points"][1]["lat"][1]) / 60.0)) . ", " . (floatval($d["points"][1]["lon"][0]) + (floatval($d["points"][1]["lon"][1]) / 60.0));
                    $pt3latlon = (floatval($d["points"][2]["lat"][0]) + (floatval($d["points"][2]["lat"][1]) / 60.0)) . ", " . (floatval($d["points"][2]["lon"][0]) + (floatval($d["points"][2]["lon"][1]) / 60.0));
                    if (sizeof($d["points"]) >= 4) $pt4latlon = (floatval($d["points"][3]["lat"][0]) + (floatval($d["points"][3]["lat"][1]) / 60.0)) . ", " . (floatval($d["points"][3]["lon"][0]) + (floatval($d["points"][3]["lon"][1]) / 60.0));

                    $corners = array_key_exists("corners", $d) ? $d["corners"] == "true" : FALSE;
                    $isSaved = true;

                    if (array_key_exists("cutline", $d) && $d["cutline"]) {
                        $cuts = array();
                        foreach ($d["cutline"] as $c) {
                            $cuts[] = "[" . $c[0] . "," . $c[1] . "]";
                        }
                        $defcutline = implode(",", $cuts);
                    }
                    break;
                }
            }
        }

        $deflatlon = $pt1latlon;
        $defz = 14;
        if ($deflatlon == "0,0") {
            $deflatlon = "56.75,24";
            $defz = 8;
        }
        
        ?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>calib | <?=$map?></title>


    <script
        src="assets/js/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"></script>
    <script src="assets/js/fa.js" crossorigin="anonymous"></script>
    <script src="assets/js/mapbbox.js"></script>
    <script src="assets/js/cookieman.js"></script>
    <script src="assets/js/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@turf/turf@5/turf.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/0.4.2/leaflet.draw.js"></script>
    <script src="assets/js/leaflet.geometryutil.js"></script>
    <script src="assets/js/leaflet.snap.js"></script>

    <script src="assets/js/L.ImageTransform.js"></script>

    <link rel="stylesheet" type="text/css" href="assets/css/main.css">
    <link rel="stylesheet" type="text/css" href="assets/css/calibrate.css">
    <link rel="stylesheet" type="text/css" href="assets/css/leaflet.css">
</head>
<body>
    <?php
        if (!$map) {
            die("<div style=\"padding: 12px;\">All maps calibrated. Add more to /maps directory or <a href=\"index.php\">check calibration</a></div></body></html>");
        }

        $imgsz = getimagesize($map);
    ?>

    <div style="height: 100%">
    <div id="image-map"></div>
    <div id="map-map"></div>
    </div>
    <div id="toolbar">
        <div id="btn1" onClick="activateMarker(1);" class="btn btn-active">1</div>
        <div id="btn2" onClick="activateMarker(2);" class="btn">2</div>
        <div id="btn3" onClick="activateMarker(3);" class="btn">3</div>
        <?php if ($pointerCount >= 4) { ?>
        <div id="btn4" onClick="activateMarker(4);" class="btn">4</div>
        <?php } ?>
        <br>
        <div id="btnOverlay" onClick="togglePreview();" class="btn"><i class="fas fa-layer-group"></i></div>
        <div id="btnMode" onClick="toggleMode();" class="btn"><i class="fas fa-expand"></i></div>
        <br>
        <div id="btnCutline" onClick="toggleCutlineTool();" class="btn"><i class="fas fa-cut"></i></div>
        <br>
        <div id="btnImageToggle" onClick="toggleImageMap();" class="btn btn-active"><i class="fas fa-image"></i></div>
        <br>
        <span style="font-size: 9px;">Overlays</span>
        <div id="btnOverlayVectorToggle" onClick="toggleOverlayVectors();" class="btn btn-active"><i class="fas fa-vector-square"></i></div>
        <div id="btnOverlayImageToggle" onClick="toggleOverlayImages();" class="btn"><i class="fas fa-images"></i></div>
        <br>
        <div id="cmdSace" onClick="saveCmd()" class="btn <?php if($isSaved) echo "btn-saved" ?>"><i class="fas fa-save"></i></div>
        <div id="cmdHelp" onClick="helpShow()" class="btn"><i class="fas fa-info-circle"></i></div>
        <div id="cmdSettings" onClick="settingsShow()" class="btn"><i class="fas fa-cog"></i></div>
    </div>

    <div id="mapname"><?=basename($map);?> / <?=$left;?> remaining | <span id="stats"></span></div>
    
    <div id="help">
        <b> [Q] </b> - Select next point<br>
        <b> [W], [A], [S], [D] </b> - Move calibration point<br>
        <b> [Shift] </b> </b> - Move pont by 10px<br>
        <b> [E] </b> - Export<br>
        <b> [M] </b> - Toggle overlay visibility<br>
        <b> [,], [.] </b> - Change overlay opacity<br>
        <hr>
        <b> [I], [J], [K], [L] </b> - Set anchor offset (for corner mode)<br>
        <b> [O] </b> - Reset anchor offset<br>
        <br>
        <span onClick="helpHide();" class="btn-micro clickable"><b>Close</b></span>
    </div>

    <div id="settings">
        <b>Uzstādījumi</b><br>
        <input id="autoCoord" type="checkbox"> <label for="autoCoord">- Fill coordinates by map name</label>
        <br>
        <br>
        <span onClick="settingsHide();" class="btn-micro clickable"><b>Close</b></span>
    </div>
</body>
<script>
    var imgCrs = L.extend({}, L.CRS.Simple, {
        transformation: new L.Transformation(1, 0, 1, 0)
    }); 

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

    var map = L.map('map-map', {maxZoom:22}).setView([<?=$deflatlon?>], <?=$defz?>);

    var overlayMaps = {
        "Genshtab 1:10k (C)": L.tileLayer("https://maps.anrijs.lv/tiles/genshtab_10k_c/{z}/{x}/{y}.png", { maxNativeZoom: 16, detectRetina: false}),
        "Genshtab 1:10k (O)": L.tileLayer("https://maps.anrijs.lv/tiles/genshtab_10k_o/{z}/{x}/{y}.png", { maxNativeZoom: 16, detectRetina: false}),
        "Genshtab 1:10k (Pilsetas)": L.tileLayer("https://maps.anrijs.lv/tiles/genshtab_10k_pilsetas/{z}/{x}/{y}.png", { maxNativeZoom: 16, detectRetina: false}),

        "Genshtab 1:25k (1963)": L.tileLayer("https://maps.anrijs.lv/tiles/PSRS_42g_25k/{z}/{x}/{y}.png", { maxNativeZoom: 15, detectRetina: false}),
        "Genshtab 1:25k (1986)": L.tileLayer("https://maps.anrijs.lv/tiles/PSRS_63g_25k/{z}/{x}/{y}.png", { maxNativeZoom: 15, detectRetina: false}),

        "Latvijas Armijas 1:75k (1920-1940)": L.tileLayer("https://maps.anrijs.lv/tiles/LVARM_40_75k/{z}/{x}/{y}.png", { maxNativeZoom: 13, detectRetina: false}),
        "1883_Karte_der_stadt_Riga": L.tileLayer("https://tiles.anrijs.lv/1883_Karte_der_stadt_Riga/{z}/{x}/{y}.png", { maxNativeZoom: 17, detectRetina: false}),
        "rig2": L.tileLayer("https://geotiff.anrijs.lv/rig2/{z}/{x}/{y}.png", { maxNativeZoom: 17, detectRetina: false}),

        "LVM DVM":        L.tileLayer("https://wms.anrijs.lv/wmts/lvm_dsm/osm_grid/{z}/{x}/{y}.png", { maxNativeZoom: 18, detectRetina: false}),
        "LVM Vainagi":    L.tileLayer("https://wms.anrijs.lv/wmts/lvm/GLOBAL_MERCATOR/{z}/{x}/{y}.png", { maxNativeZoom: 18, detectRetina: false}),
        "LVM Topo 1:50k": L.tileLayer("https://wms.anrijs.lv/wmts/lvm_topo50/GLOBAL_MERCATOR/{z}/{x}/{y}.png", { maxNativeZoom: 18, detectRetina: false}),
        
        "CORONA preview": L.tileLayer("https://tiles.anrijs.lv/corona/{z}/{x}/{y}.png", { maxNativeZoom: 15, detectRetina: false}),
        "NARA preview": L.tileLayer("https://tiles.anrijs.lv/aerofoto_1940/{z}/{x}/{y}.png", { maxNativeZoom: 17, detectRetina: false}),

        "080 preview": L.tileLayer("https://geotiff.anrijs.lv/80s/{z}/{x}/{y}.png", { maxNativeZoom: 15, detectRetina: false}),

        // https://home.dodies.lv/tiles/tiles-kdwr/z13/2518/4650.png
        "KDWR": L.tileLayer("https://home.dodies.lv/tiles/tiles-kdwr/z{z}/{y}/{x}.png", { maxNativeZoom: 15, detectRetina: false}),
     };

    baseMaps["ESRI Sat"].addTo(map);

    L.control.layers(baseMaps, overlayMaps).addTo(map);

    $('.leaflet-container').css('cursor','crosshair');
    $('#image-map').css('cursor','crosshair');

    var mkOffsetX = 16;
    var mkOffsetY = 16;

    // Setting cookies
    var autoCoord = getCookie("autoCoord") == "true";

    if (autoCoord) {
        autofillCoords()
    }

    var active = 1;
    var activemap = 0; // 0 - image, 1 - map
    var shiftDown = false;
    var ctrlDown = false;

    function helpShow() {
        $("#help").show();
    }

    function helpHide() {
        $("#help").hide();
    }

    function settingsShow() {
        $("#settings").show();
    }

    function settingsHide() {
        $("#settings").hide();
    }

    helpHide();
    settingsHide();

    var pointer1icon = L.icon({
        iconUrl: 'assets/img/pointer1.png',
        iconSize:     [33, 33],
        iconAnchor:   [17, 17],
        popupAnchor:  [33, 33]
    });

    var pointer2icon = L.icon({
        iconUrl: 'assets/img/pointer2.png',
        iconSize:     [33, 33],
        iconAnchor:   [17, 17],
        popupAnchor:  [33, 33]
    });

    var pointer3icon = L.icon({
        iconUrl: 'assets/img/pointer3.png',
        iconSize:     [33, 33],
        iconAnchor:   [17, 17],
        popupAnchor:  [33, 33]
    })

    var pointer4icon = L.icon({
        iconUrl: 'assets/img/pointer4.png',
        iconSize:     [33, 33],
        iconAnchor:   [17, 17],
        popupAnchor:  [33, 33]
    });

    var imageMap = L.map('image-map', {
        crs: imgCrs,
        minZoom: -4
    });

    var imgw = <?=$imgsz[0];?>;
    var imgh = <?=$imgsz[1];?>;

    var cutting = false;

    var boundsA = [[0,0], [imgh,imgw]];

    var imageSrc = L.imageOverlay('<?=$map;?>', boundsA).addTo(imageMap);
    imageMap.fitBounds(boundsA);

    var defCutlineStyle = {color: 'orange', fillOpacity: 0.0, opacity: 0.5, weight: 1.5};
    var activeCutlineStyle = {color: 'red', fillOpacity: 0.05, opacity: 0.75, weight: 2.5, icon: new L.DivIcon({
            iconSize: new L.Point(10, 10),
            className: 'leaflet-div-icon leaflet-editing-icon my-own-class'
        })}

    var cutlinePolygon = new L.Polygon([
			<?= $defcutline; ?>
        ], defCutlineStyle);
        
    cutlinePolygon.on('edit', function() {
	 
    });


    imageMap.addLayer(cutlinePolygon);

    var pt1src = L.marker([<?=$pt1yx?>], {icon: pointer1icon}).addTo(imageMap);
    var pt1dst = L.marker([<?=$pt1latlon?>], {icon: pointer1icon}).addTo(map);
    var pt2src = L.marker([<?=$pt2yx?>], {icon: pointer2icon}).addTo(imageMap);
    var pt2dst = L.marker([<?=$pt2latlon?>], {icon: pointer2icon}).addTo(map);
    var pt3src = L.marker([<?=$pt3yx?>], {icon: pointer3icon}).addTo(imageMap);
    var pt3dst = L.marker([<?=$pt3latlon?>], {icon: pointer3icon}).addTo(map);

<?php if ($pointerCount >= 4) { ?>
    var pt4src = L.marker([<?=$pt4yx?>], {icon: pointer4icon}).addTo(imageMap);
    var pt4dst = L.marker([<?=$pt4latlon?>], {icon: pointer4icon}).addTo(map);
<?php } ?>

    imageMap.on('click', function(e) {
        if (cutting) {
            return;
        }

        switch (active) {
            case 1: pt1src.setLatLng(new L.latLng(Math.round(e.latlng.lat), Math.round(e.latlng.lng))); break;
            case 2: pt2src.setLatLng(new L.latLng(Math.round(e.latlng.lat), Math.round(e.latlng.lng))); break;
            case 3: pt3src.setLatLng(new L.latLng(Math.round(e.latlng.lat), Math.round(e.latlng.lng))); break;
<?php if ($pointerCount >= 4) { ?>
            case 4: pt4src.setLatLng(new L.latLng(Math.round(e.latlng.lat), Math.round(e.latlng.lng))); break;
<?php } ?>
        }
    });

    imageMap.on('contextmenu',function(e){
        if (!cutting) {
            return;
        }

        var pos = e.latlng;
        pos.lat = Math.round(pos.lat);
        pos.lng = Math.round(pos.lng);

        var latlngs = cutlinePolygon.getLatLngs()[0];
        var closestId = 0;
        var closesDist = -1;

        for (var i=0;i<latlngs.length;i++) {
            var d = latlngs[i];
            var dx = Math.abs(pos.lng - d.lng);
            var dy = Math.abs(pos.lat - d.lat);

            var dist = Math.abs(Math.sqrt((dx*dx) + (dy*dy)));
            if (closesDist == -1 || dist < closesDist) {
                closesDist = dist;
                closestId = i;
            }
        }

        latlngs[closestId].lat = pos.lat;
        latlngs[closestId].lng = pos.lng;

        cutlinePolygon.setLatLngs([latlngs]);

        cutlinePolygon.editing.updateMarkers();
    });

    map.on('click', function(e) {
        if (externalPolygon) { 
            return;
        }
        switch (active) {
            case 1: pt1dst.setLatLng(e.latlng); break;
            case 2: pt2dst.setLatLng(e.latlng); break;
            case 3: pt3dst.setLatLng(e.latlng); break;
<?php if ($pointerCount >= 4) { ?>
            case 4: pt4dst.setLatLng(e.latlng); break;
<?php } ?>
        }
    });

    function toggleCutlineTool() {
        cutting = !cutting;
        
        if (cutting) {
            $("#btnCutline").addClass("btn-active");
            cutlinePolygon.editing.enable();
            cutlinePolygon.setStyle(activeCutlineStyle);
        } else {
            $("#btnCutline").removeClass("btn-active");
            cutlinePolygon.editing.disable();
            cutlinePolygon.setStyle(defCutlineStyle);
        }
    }

    function haversineDistance(coords1, coords2) {
        function toRad(x) {
            return x * Math.PI / 180;
        }

        var lon1 = coords1[0];
        var lat1 = coords1[1];

        var lon2 = coords2[0];
        var lat2 = coords2[1];

        var R = 6371000; // km

        var x1 = lat2 - lat1;
        var dLat = toRad(x1);
        var x2 = lon2 - lon1;
        var dLon = toRad(x2)
        var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        var d = R * c;

        return d;
    }

    function distanceLat(anchor, target) {
        var dist = haversineDistance(
            [anchor.getLatLng().lat, anchor.getLatLng().lng],
            [target.getLatLng().lat, anchor.getLatLng().lng]
        );

        if (anchor.getLatLng().lat < target.getLatLng().lat) {
            return -dist;
        }

        return dist;
    }

    function distanceLng(anchor, target) {
        var dist = haversineDistance(
            [anchor.getLatLng().lat, anchor.getLatLng().lng],
            [anchor.getLatLng().lat, target.getLatLng().lng]
        );

        if (anchor.getLatLng().lng > target.getLatLng().lng) {
            return -dist;
        }

        return dist;
    }

    function toDM(dd) {
        var dec = Math.floor(dd);
        var min = (dd - dec) * 60;

        return [dec, min];
    }

    function findHighestMarkerId(markers, ignorelist) {
        var top = -1;
        for (var i=0;i<markers.length;i++) {
            if (ignorelist.includes(i)) {
                continue;
            }

            if (top == -1) {
                top = i;
            }

            if (markers[i].getLatLng().lat > markers[top].getLatLng().lat) {
                top = i;
            }
        }

        return top;
    }

    function saveCmd() {
        toggleSnap(false); // prevent from snaping on offset reset
        for (var i=0;i<4;i++) {
            if (polygonOffsetLat[i] != 0 || polygonOffsetLon[i] != 0) {
                setPolygonOffset(i,0,0);
            }
        }

        var image = $("#image")[0];

        var obje = [];
        var warp = {};

        var byCorners = false;
        if (anchorMarkers && anchorMarkers.length > 0) {
            /* match markers
               1        2
                +------+
                |      |
                |      |
                +------+
               3        4
            */

            var top1 = findHighestMarkerId(anchorMarkers, []);
            var top2 = findHighestMarkerId(anchorMarkers, [top1]);

            var topLeftId = (anchorMarkers[top1].getLatLng().lng < anchorMarkers[top2].getLatLng().lng) ? top1 : top2;
            var topRightId = (topLeftId + 1) % 4;

            var bottomRightId = (topLeftId + 2) % 4;
            var bottomLeftId = (topLeftId + 3) % 4;

            console.log("Order (1-4):  " + topLeftId + ", " + topRightId + ", " + bottomRightId + ", " + bottomLeftId);

            switch (topLeftId) {
                case 0: console.log("0 degrees roatation"); break;
                case 1: console.log("270 degrees CW roatation"); break;
                case 2: console.log("180 degrees CW roatation"); break;
                case 3: console.log("90 degrees CW roatation"); break;
            }

            
            byCorners = true;
            obje = [
                {
                    "x":0,
                    "y":0,
                    "lat":toDM(anchorMarkers[0].getLatLng().lat),
                    "lon":toDM(anchorMarkers[0].getLatLng().lng)
                },
                {
                    "x":imgw,
                    "y":0,
                    "lat":toDM(anchorMarkers[1].getLatLng().lat),
                    "lon":toDM(anchorMarkers[1].getLatLng().lng)
                },
                {
                    "x":imgw,
                    "y":imgh,
                    "lat":toDM(anchorMarkers[2].getLatLng().lat),
                    "lon":toDM(anchorMarkers[2].getLatLng().lng)
                },
                {
                    "x":0,
                    "y":imgh,
                    "lat":toDM(anchorMarkers[3].getLatLng().lat),
                    "lon":toDM(anchorMarkers[3].getLatLng().lng)
                }
            ];

            var left = haversineDistance(
                [anchorMarkers[topLeftId].getLatLng().lat, anchorMarkers[topLeftId].getLatLng().lng],
                [anchorMarkers[bottomLeftId].getLatLng().lat, anchorMarkers[bottomLeftId].getLatLng().lng]
            );

            var right =  haversineDistance(
                [anchorMarkers[topRightId].getLatLng().lat, anchorMarkers[topRightId].getLatLng().lng],
                [anchorMarkers[bottomRightId].getLatLng().lat, anchorMarkers[bottomRightId].getLatLng().lng]
            );

            var top =  haversineDistance(
                [anchorMarkers[topLeftId].getLatLng().lat, anchorMarkers[topLeftId].getLatLng().lng],
                [anchorMarkers[topRightId].getLatLng().lat, anchorMarkers[topRightId].getLatLng().lng]
            );

            var bottom =  haversineDistance(
                [anchorMarkers[bottomLeftId].getLatLng().lat, anchorMarkers[bottomLeftId].getLatLng().lng],
                [anchorMarkers[bottomRightId].getLatLng().lat, anchorMarkers[bottomRightId].getLatLng().lng]
            );

            var hdist = Math.min(left, right);
            var wdist = Math.min(top, bottom);

            var hmod = imgh / hdist;
            var wmod = imgw / wdist;

            if (topLeftId == 1 || topLeftId == 3) {
                wmod = imgh / wdist;
                hmod = imgw / hdist;
            }

            var deltas = [
                [0,0],
                [Math.round(distanceLng(anchorMarkers[0], anchorMarkers[1]) * wmod), Math.round(distanceLat(anchorMarkers[0], anchorMarkers[1]) * hmod)],
                [Math.round(distanceLng(anchorMarkers[0], anchorMarkers[2]) * wmod), Math.round(distanceLat(anchorMarkers[0], anchorMarkers[2]) * hmod)],
                [Math.round(distanceLng(anchorMarkers[0], anchorMarkers[3]) * wmod), Math.round(distanceLat(anchorMarkers[0], anchorMarkers[3]) * hmod)]
            ];

            var offsetx = Math.min(
                deltas[0][0],
                deltas[1][0],
                deltas[2][0],
                deltas[3][0]
            );

            var offsety = Math.min(
                deltas[0][1],
                deltas[1][1],
                deltas[2][1],
                deltas[3][1]
            )

            var line1 = turf.lineString([
                [anchorMarkers[topRightId].getLatLng().lat, anchorMarkers[topRightId].getLatLng().lng],
                [anchorMarkers[bottomLeftId].getLatLng().lat, anchorMarkers[bottomLeftId].getLatLng().lng]
            ]);

            var line2 = turf.lineString([
                [anchorMarkers[topLeftId].getLatLng().lat, anchorMarkers[topLeftId].getLatLng().lng],
                [anchorMarkers[bottomRightId].getLatLng().lat, anchorMarkers[bottomRightId].getLatLng().lng]
            ]);

            var intersects = turf.lineIntersect(line1, line2);
            var cxcoord = intersects.features[0].geometry["coordinates"]

            var clat = cxcoord[0];
            var clon = cxcoord[1];

            warp = {
                "srch": imgh,
                "srcw": imgw,
                "offsetx": offsetx,
                "offsety": offsety,
                "firstid": topLeftId,
                "clat": clat,
                "clon": clon,
                "points": [
                    {
                        "src": [0,0],
                        "dst": deltas[0]
                    },
                    {
                        "src": [imgw-1,0],
                        "dst": deltas[1]
                    },
                    {
                        "src": [imgw-1,imgh-1],
                        "dst": deltas[2]
                    },
                    {
                        "src": [0,imgh-1],
                        "dst": deltas[3]
                    }
                ],
            }

        } else { // not corners
            obje = [
                {
                    "x":pt1src.getLatLng().lng,
                    "y":pt1src.getLatLng().lat,
                    "lat":toDM(pt1dst.getLatLng().lat),
                    "lon":toDM(pt1dst.getLatLng().lng)
                },
                {
                    "x":pt2src.getLatLng().lng,
                    "y":pt2src.getLatLng().lat,
                    "lat":toDM(pt2dst.getLatLng().lat),
                    "lon":toDM(pt2dst.getLatLng().lng)
                },
                {
                    "x":pt3src.getLatLng().lng,
                    "y":pt3src.getLatLng().lat,
                    "lat":toDM(pt3dst.getLatLng().lat),
                    "lon":toDM(pt3dst.getLatLng().lng)
                },
<?php if ($pointerCount >= 4) { ?>
                {
                    "x":pt4src.getLatLng().lng,
                    "y":pt4src.getLatLng().lat,
                    "lat":toDM(pt4dst.getLatLng().lat),
                    "lon":toDM(pt4dst.getLatLng().lng)
                },
<?php } ?>
            ];
        }

        var cutline = [];
        var cutlinePts = cutlinePolygon.getLatLngs()[0];
        for (var i=0;i<cutlinePts.length;i++) {
            var o = cutlinePts[i];

            var lat = Math.max(0, Math.min(o.lat, imgh-1));
            var lng = Math.max(0, Math.min(o.lng, imgw-1));

            cutline.push([lat, lng]);
        }
        
        $.post( "save.php", { 'points': obje, 'filename': baseName("<?=$map?>", false), "corners": byCorners, "warp": warp, "cutline": cutline}, function( data ) {
            if (data == "OK") {
                location.reload();
                window.scrollTo(0, 0);
            } else {
                alert(data);
            }
        });
    }
    
    function deactivateMarker() {
        $("#btn1").removeClass("btn-active");
        $("#btn2").removeClass("btn-active");
        $("#btn3").removeClass("btn-active");
<?php if ($pointerCount >= 4) { ?>
        $("#btn4").removeClass("btn-active");
<?php } ?>
        active = 0;
    }

    function mvMarker(dir) {
        if (active == 0) return;

        var m = 0;

        if (activemap == 0)  { // src / image
            switch (active) {
                case 1: m = pt1src; break;
                case 2: m = pt2src; break;
                case 3: m = pt3src; break;
<?php if ($pointerCount >= 4) { ?>
                case 4: m = pt4src; break;
<?php } ?>
            }
            var pos = m.getLatLng();
            var step = shiftDown ? 10 : 1;
            switch (dir) {
                case 1: pos.lat -= step; break; // up
                case 2: pos.lng -= step; break; // left
                case 3: pos.lat += step; break; // down
                case 4: pos.lng += step; break; // right
            }
            m.setLatLng(pos);
        } else {
            switch (active) {
                case 1: m = pt1pos; break;
                case 2: m = pt2pos; break;
                case 3: m = pt3pos; break;
<?php if ($pointerCount >= 4) { ?>
                case 4: m = pt4pos; break;
<?php } ?>
            }
        }
    }

    function activateMarker(id) {
        deactivateMarker(0);

        var image = $("#image")[0];

        if (id < 0 || id > 4) {
            active = 0;
        } else {
            active = id;
            var wx = 0;
            var wy = 0;

            var w = window.innerWidth / 2;
            var h = window.innerHeight / 2;

           switch (active) {
               case 1: 
                   $("#btn1").addClass("btn-active"); 
                   //wx = pt1pos[0] == -1 ? 0 : pt1pos[0] - w; 
                   //wy = pt1pos[1] == -1 ? 0 : pt1pos[1] - h;
                   break;
               case 2: 
                   $("#btn2").addClass("btn-active");
                   //wx = (pt2pos[0] == -1 ? image.width - pt1pos[0] : pt2pos[0]) - w; 
                   //wy = (pt2pos[1] == -1 ? pt1pos[1]               : pt2pos[1]) - h;
                   break;
               case 3: 
                   $("#btn3").addClass("btn-active");
                   //wx = (pt3pos[0] == -1 ? pt2pos[0] : pt3pos[0]) - w; 
                   //wy = (pt3pos[1] == -1 ? image.height - pt2pos[1] : pt3pos[1]) - h;
                   break;
<?php if ($pointerCount >= 4) { ?>
                case 4: 
                   $("#btn4").addClass("btn-active");
                   //wx = (pt3pos[0] == -1 ? pt2pos[0] : pt3pos[0]) - w; 
                   //wy = (pt3pos[1] == -1 ? image.height - pt2pos[1] : pt3pos[1]) - h;
                   break;
<?php } ?>
           }

            //window.scrollTo(wx, wy);
        }

        console.log("activated " + active);
    }

    function nextMarker() {
        if (active >= 4 || active <= 0) {
            activateMarker(1);
        } else {
            activateMarker(active+1);            
        }
    }

    function autofillCoords() {
        <?php if (CFG_AUTOCOORDS) { ?>
        var filename = baseName("<?=$map;?>", false);
        var bbox = <?=CFG_AUTOCOORDS?>(filename);

        // 3 0 1 2
        var lat1 = bbox[0];
        var lon1 = bbox[1];
        var lat2 = bbox[2];
        var lon2 = bbox[3];

        var lat1neg = lat1 < 0;
        var lon1neg = lon1 < 0;
        var lat2neg = lat2 < 0;
        var lon2neg = lon2 < 0;

        lat1 = Math.abs(lat1);
        lon1 = Math.abs(lon1);
        lat2 = Math.abs(lat2);
        lon2 = Math.abs(lon2);

        var lat1dd = Math.floor(lat1);
        var lon1dd = Math.floor(lon1);
        var lat2dd = Math.floor(lat2);
        var lon2dd = Math.floor(lon2);

        var lat1mm = ((lat1 - lat1dd)*60).toFixed(4);
        var lon1mm = ((lon1 - lon1dd)*60).toFixed(4);
        var lat2mm = ((lat2 - lat2dd)*60).toFixed(4);
        var lon2mm = ((lon2 - lon2dd)*60).toFixed(4);

        if (lat1neg) lat1dd = -lat1dd;
        if (lon1neg) lon1dd = -lon1dd;
        if (lat2neg) lat2dd = -lat2dd;
        if (lon2neg) lon2dd = -lon2dd;

        $("#pointer1latdd")[0].value = lat1dd;
        $("#pointer1londd")[0].value = lon1dd;
        $("#pointer1latmm")[0].value = lat1mm;
        $("#pointer1lonmm")[0].value = lon1mm;

        $("#pointer2latdd")[0].value = lat1dd;
        $("#pointer2londd")[0].value = lon2dd;
        $("#pointer2latmm")[0].value = lat1mm;
        $("#pointer2lonmm")[0].value = lon2mm;

        $("#pointer3latdd")[0].value = lat2dd;
        $("#pointer3londd")[0].value = lon2dd;
        $("#pointer3latmm")[0].value = lat2mm;
        $("#pointer3lonmm")[0].value = lon2mm;

        $("#pointer4latdd")[0].value = lat2dd;
        $("#pointer4londd")[0].value = lon1dd;
        $("#pointer4latmm")[0].value = lat2mm;
        $("#pointer4lonmm")[0].value = lon1mm;
        <?php } else { echo "/* disabled */\n"; } ?>
    }

    /* ---------------------------------------------- */

    document.addEventListener("keyup", function(event) {
        switch (event.keyCode) {
            case 16: shiftDown = false; break;
            case 17: ctrlDown = false; break;
        }
    });

    document.addEventListener("keydown", function(event) {
        switch (event.keyCode) {
            case 9: toggleImageMap(); break;
            case 16: shiftDown = true; break;
            case 17: ctrlDown = true; break;

            case 49: activateMarker(1); break; // 1
            case 50: activateMarker(2); break; // 2
            case 51: activateMarker(3); break; // 3
<?php if ($pointerCount >= 4) { ?>
            case 52: activateMarker(4); break; // 4
<?php } ?>
            case 81: $("input").blur(); nextMarker(); break; // q

            //case 48: deactivateMarker(); break; // 0F
            case 27: deactivateMarker(); break; // esc

            // wasd
            case 87: $("input").blur(); mvMarker(1); break; // w
            case 65: $("input").blur(); mvMarker(2); break; // a
            case 83: $("input").blur(); mvMarker(3); break; // s
            case 68: $("input").blur(); mvMarker(4); break; // d

            // case 90: togglePreview(); break; // z

            case 69: saveCmd(); // e

            case 188: previewOpacity(-0.1); break; // ,
            case 190: previewOpacity(0.1); break; // . 
            case 77: previewOpacity(0); break; // m - because firefox uses / as quick search
            case 191: previewOpacity(0); break; // /

            case 67: toggleCutlineTool(); break; // d

            // ijkl
            case 73: setAllPolygonOffset(getOffsetStep(), 0); break; // i
            case 74: setAllPolygonOffset(0, getOffsetStep()); break; // j
            case 75: setAllPolygonOffset(-getOffsetStep(), 0); break; // k
            case 76: setAllPolygonOffset(0, -getOffsetStep()); break; // l
            case 79: resetPolygonOffset(0,0); break; // o

            case 33: var loc = window.location.href.split("?")[0]; window.location.href = loc + "?map=" + "<?=$prevMap;?>"; break; // pg down
            case 34: var loc = window.location.href.split("?")[0]; window.location.href = loc + "?map=" + "<?=$nextMap;?>"; break; // pg up
        }
    });

    function getOffsetStep() {
        var step = 0.001;
        switch (map.getZoom()) {
            case 16: step = 0.0005; break;
            case 17: step = 0.0001; break;
            case 18: step = 0.00005; break;
            case 19: step = 0.000001; break;
        }

        return step;
    }

    var trOpacity = 0.6;
    function previewOpacity(step) {
        if (step == 0) {
            step = trOpacity < 0.5 ? 1 : -1;    
        }

        trOpacity += step;

        if (trOpacity < 0.1) {
            trOpacity = 0.1;
        } else if (trOpacity > 1.0) {
            trOpacity = 1.0;
        }

        uptateStatsText();

        transformedImage.setOpacity(trOpacity);
    }
    
    function putImage() {
        var src = document.getElementById("select_image");
        var target = document.getElementById("image");
        showImage(src, target);
    }

    $(function(){
        $("#autoCoord")[0].checked = autoCoord;
        $("#autoCoord").change(function() {
            autoCoord = this.checked
            console.log("Auto Coord cookie set: " + autoCoord);
            setCookie("autoCoord", autoCoord, 30);

            if (autoCoord) {
                autofillCoords();
            }
        });

<?php if ($corners) { ?>
        var callback = function() {
            anchorMarkers[3].setLatLng(L.latLng(<?=$pt4latlon?>));
            updateAnchors();
        } 
        toggleMode(callback);
<?php } ?>
    });

    var transformedImage = null;
    var imageCorners = null;

    var externalPolygon = null;
    var anchorMarkers = null;

    var opOrigin = null;

    var centerMarker = null;

    var onAnchorDragEnd = function() {
        opOrigin = null;
    }

    var updateAnchors = function(anchorId=-1, info=null) {
        if (ctrlDown && anchorId != -1 && info != null) {
            if (opOrigin == null) {
                opOrigin = [
                    polygonOffsetLat[anchorId],
                    polygonOffsetLon[anchorId]
                ];
            }

            var deltaLat = info.latlng.lat - info.oldLatLng.lat;
            var deltaLng = info.latlng.lng - info.oldLatLng.lng;

            //console.log("mv: " + info.oldLatLng.lat + " -> " + info.latlng.lat);

            setPolygonOffset(
                anchorId,
                opOrigin[0] + deltaLat,
                opOrigin[1] + deltaLng
            );
        }

        anchors = anchorMarkers.map(function(marker){ return marker.getLatLng(); })

        var imgAnchors = [];
        for (var i=0; i<anchors.length;i++) {
            var anch = [parseFloat(anchors[i].lat), parseFloat(anchors[i].lng)];

            testval = anchors[i];

            switch (i) {
                case 0:
                    anch[0] -= polygonOffsetLat[i];
                    anch[1] -= polygonOffsetLon[i];
                    break;
                case 1:
                    anch[0] -= polygonOffsetLat[i];
                    anch[1] -= polygonOffsetLon[i];
                    break;
                case 2:
                    anch[0] -= polygonOffsetLat[i];
                    anch[1] -= polygonOffsetLon[i];
                    break;
                case 3:
                    anch[0] -= polygonOffsetLat[i];
                    anch[1] -= polygonOffsetLon[i];
                    break;
            }

            imgAnchors[i] = anch;
        }

        // var line1 = turf.lineString([
        //     [parseFloat(anchors[0].lat), parseFloat(anchors[0].lng)],
        //     [parseFloat(anchors[2].lat), parseFloat(anchors[2].lng)]
        // ]);

        // var line2 = turf.lineString([
        //     [parseFloat(anchors[1].lat), parseFloat(anchors[1].lng)],
        //     [parseFloat(anchors[3].lat), parseFloat(anchors[3].lng)]
        // ]);

        // var intersects = turf.lineIntersect(line1, line2);
        // var cxcoord = intersects.features[0].geometry["coordinates"]

        // if (centerMarker == null) {
        //     centerMarker = L.marker(cxcoord);
        //     //centerMarker.addTo(map);
        // }

        // centerMarker.setLatLng(L.latLng(cxcoord[0],cxcoord[1]));

        transformedImage.setAnchors(imgAnchors);
        externalPolygon.setLatLngs(anchors);
    }
    

    var polygonOffsetLat = [0,0,0,0];
    var polygonOffsetLon = [0,0,0,0];


    function resetPolygonOffset() {
        setPolygonOffset(0,0,0);
        setPolygonOffset(1,0,0);
        setPolygonOffset(2,0,0);
        setPolygonOffset(3,0,0);
    }

    function setAllPolygonOffset(lat, lon) {
        setPolygonOffset(0, polygonOffsetLat[0] + lat, polygonOffsetLon[0] + lon);
        setPolygonOffset(1, polygonOffsetLat[1] - lat, polygonOffsetLon[1] + lon);
        setPolygonOffset(2, polygonOffsetLat[2] - lat, polygonOffsetLon[2] - lon);
        setPolygonOffset(3, polygonOffsetLat[3] + lat, polygonOffsetLon[3] - lon);
    }

    function setPolygonOffset(id, offsetLat, offsetLon) {
        if (!anchorMarkers) {
            return;
        }

        var deltaLat = offsetLat - polygonOffsetLat[id];
        var deltaLon = offsetLon - polygonOffsetLon[id];

        var latlng = anchorMarkers[id].getLatLng();
        switch (id) {
            case 0:
                latlng.lat += deltaLat;
                latlng.lng += deltaLon;
                break;
            case 1:
                latlng.lat += deltaLat;
                latlng.lng += deltaLon;
                break;
            case 2:
                latlng.lat += deltaLat;
                latlng.lng += deltaLon;
                break;
            case 3:
                latlng.lat += deltaLat;
                latlng.lng += deltaLon;
                break;
        }

        anchorMarkers[id].setLatLng(latlng)
        

        polygonOffsetLat[id] = offsetLat;
        polygonOffsetLon[id] = offsetLon;

        uptateStatsText();

        updateAnchors();
    }

    function uptateStatsText() {
        $("#stats").text("Opacity: " + trOpacity + " | Polygon marker offset: " + polygonOffsetLat[0] + ", " + polygonOffsetLon[0]);
    }

    var testval = 0;
    var bboxes = [];

    function toggleMode(callback=null) {
        var callback2 = function() {
            if (!externalPolygon) {
                externalPolygon = L.polygon(imageCorners, {fill: false}).addTo(map);
                var anchorId = 0;
                anchorMarkers = imageCorners.map(function(anchor) {
                    // Do scaling...?

                    anchor[0] = parseFloat(anchor[0]);
                    anchor[1] = parseFloat(anchor[1]);

                    switch (anchorId) {
                        case 0:
                            anchor[0] -= polygonOffsetLat[anchorId];
                            anchor[1] += polygonOffsetLon[anchorId];
                            break;
                        case 1:
                            anchor[0] -= polygonOffsetLat[anchorId];
                            anchor[1] -= polygonOffsetLon[anchorId];
                            break;
                        case 2:
                            anchor[0] += polygonOffsetLat[anchorId];
                            anchor[1] -= polygonOffsetLon[anchorId];
                            break;
                        case 3:
                            anchor[0] += polygonOffsetLat[anchorId];
                            anchor[1] += polygonOffsetLon[anchorId];
                            break;
                    }

                    var staticId = anchorId++;
                    return L.marker(anchor, {draggable: true}).addTo(map).on('dragend', onAnchorDragEnd).on('drag', function(info) { updateAnchors(staticId, info); });
                })

                if (bboxes.length > 0) {
                    for (var i=0;i<anchorMarkers.length;i++) {
                        var marker = anchorMarkers[i];
                        marker.snapediting = new L.Handler.MarkerSnap(map, marker);
                        for(var j=0;j<bboxes.length;j++) {
                            marker.snapediting.addGuideLayer(bboxes[j]);
                        }
                        marker.snapediting.enable();
                    }
                }

                $("#btnMode").addClass("btn-active");
            } else {
                externalPolygon.removeFrom(map);;
                externalPolygon = null;

                if (anchorMarkers) {
                    for (i=0;i<anchorMarkers.length;i++) {
                        anchorMarkers[i].removeFrom(map);
                    }
                    anchorMarkers = null;
                }

                $("#btnMode").removeClass("btn-active"); 
            }

            if (callback) callback();
        }

        if (!transformedImage) {
            togglePreview(callback2);
        } else {
            callback2();
        }        
    }

    var anchorMarkerIcon = new L.Icon({
        iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    var overlayVectors = [];
    var overlayImages = [];

    var overlayVectorsEnabled = true;
    var overlayImagesEnabled = false;


    function toggleOverlayVectors() {
        overlayVectorsEnabled = !overlayVectorsEnabled;
        
        if (overlayVectorsEnabled) {
            $("#btnOverlayVectorToggle").addClass("btn-active");
            for (var i=0; i<overlayVectors.length; i++) {
                var lyr = overlayVectors[i];
                lyr.addTo(map);
            }
        } else {
            $("#btnOverlayVectorToggle").removeClass("btn-active");
            for (var i=0; i<overlayVectors.length; i++) {
                var lyr = overlayVectors[i];
                lyr.removeFrom(map);
            }
        }
    }

    function toggleOverlayImages() {
        overlayImagesEnabled = !overlayImagesEnabled;
        
        if (overlayImagesEnabled) {
            $("#btnOverlayImageToggle").addClass("btn-active");
            for (var i=0; i<overlayImages.length; i++) {
                var lyr = overlayImages[i];
                lyr.addTo(map);
            }
        } else {
            $("#btnOverlayImageToggle").removeClass("btn-active");
            for (var i=0; i<overlayImages.length; i++) {
                var lyr = overlayImages[i];
                lyr.removeFrom(map);
            }
        }
    }

    function toggleImageMap() {
        var activate = $("#image-map").css("width") == "0px";

        if (activate) {
            $("#image-map").css('width',"50%");
            $("#map-map").css('left',"50%");
            $("#map-map").css('width',"50%");
            $("#btnImageToggle").addClass("btn-active"); 
        } else {
            $("#image-map").css('width',"0%");
            $("#map-map").css('left',"0%");
            $("#map-map").css('width',"100%");
            $("#btnImageToggle").removeClass("btn-active"); 
        }

        map.invalidateSize();
    }

    function togglePreview(callback=null) {
        if (externalPolygon) {
            alert("Disable corner calibration mode first");
            return;
        }

        if (transformedImage) {
            transformedImage.removeFrom(map);
            transformedImage = null;

            $("#btnOverlay").removeClass("btn-active"); 
            return;
        }

        var obje = [
            {
                "x":pt1src.getLatLng().lng,
                "y":pt1src.getLatLng().lat,
                "lat":toDM(pt1dst.getLatLng().lat),
                "lon":toDM(pt1dst.getLatLng().lng)
            },
            {
                "x":pt2src.getLatLng().lng,
                "y":pt2src.getLatLng().lat,
                "lat":toDM(pt2dst.getLatLng().lat),
                "lon":toDM(pt2dst.getLatLng().lng)
            },
            {
                "x":pt3src.getLatLng().lng,
                "y":pt3src.getLatLng().lat,
                "lat":toDM(pt3dst.getLatLng().lat),
                "lon":toDM(pt3dst.getLatLng().lng)
            },
            <?php if ($pointerCount >= 4) { ?>
            {
               "x":pt4src.getLatLng().lng,
                "y":pt4src.getLatLng().lat,
                "lat":toDM(pt4dst.getLatLng().lat),
                "lon":toDM(pt4dst.getLatLng().lng)
            },
            <?php } ?>
        ];

        $.post( "gdalinfo.php", { 'points': obje, 'filename': baseName("<?=$map?>", false), 'h': imgh, 'w': imgw}, function( data ) {
            var imageUrl = "<?=$map?>";
            imageCorners = [
                [data["cornerCoordinates"]["upperLeft"][1], data["cornerCoordinates"]["upperLeft"][0]], 
                [data["cornerCoordinates"]["upperRight"][1], data["cornerCoordinates"]["upperRight"][0]], 

                [data["cornerCoordinates"]["lowerRight"][1], data["cornerCoordinates"]["lowerRight"][0]], 
                [data["cornerCoordinates"]["lowerLeft"][1], data["cornerCoordinates"]["lowerLeft"][0]], 
            ];

            transformedImage = L.imageTransform(imageUrl, imageCorners, { opacity: trOpacity, disableSetClip: false }).addTo(map);

            if (callback) callback();

            $("#btnOverlay").addClass("btn-active"); 
        });
    }

    <?php
        function strContains($needle, $haystack) {
            return strpos($haystack, $needle) !== false;
        }

        function getMapNum($str) {
            $num = "";

            $chars = str_split($str);
            foreach ($chars as $ch) {
                if (is_numeric($ch)) {
                    $num .= $ch;
                } else {
                    if (!empty($num)) {
                        break;
                    }
                }
            }

            return $num;
        }

        $jsonStr = file_get_contents("tmp/out.json");
        $json = json_decode($jsonStr, true)["coords"];

        // print_r($json);

        echo "// map = " . $map . "\n";

        $mapname = basename($map);
        $mapname = getMapNum($mapname);

        foreach ($json as $j) {
            $jname = $j["name"];
            
            $jname = getMapNum($jname);

            echo "// " . $mapname . " contains " . $jname . " (".$j["name"].")\n";

            if (isset($_GET["all"]) || strContains($jname, $mapname) || (isset($_GET["alt"]) && strContains($jname, $_GET["alt"]))) {
                $polypts = [];
                foreach ($j["coords"] as $c) {
                    $polypts[] = "[" . implode(",", $c) . "]";
                }

                echo "var polyline = L.polyline([";
                echo implode(",", $polypts);
                echo"], {color: 'red', opacity: 0.75, weight: 1.5}).bindTooltip(\"" . $j["name"] . "\", {direction:'center'});\n";
                echo "polyline.addTo(map);\n";
                echo "overlayVectors.push(polyline);\n";
            }
        }
        
        $mission = explode("_", $_GET["map"])[0];
        $missionTag = "_" . explode("_", $_GET["map"])[1] . "_";
        
        $done = getDoneMaps();
        $boxnum = 0;

        $missionBboxes = array();
        $otherMissionBboxes = array();

        foreach ($done as $map) {             
            $polypts = array();
            foreach($map["points"] as $pt) {
                $ptlat = $pt["dstlat"][0] + ($pt["dstlat"][1] / 60);
                $ptlon = $pt["dstlon"][0] + ($pt["dstlon"][1] / 60);

                $polypts[] = "[".$ptlat.",".$ptlon."]";
            }

            $bboxNames[] = "bbox_" . $boxnum;

            $htmlstr = "var popupText = '<b>" . $map["name"] . "</b><br><a href=\"#\" onclick=\"bbox".$boxnum.".removeFrom(map);\">Hide</a> | <a href=\"calibrate2.php?map=" . $map["name"] . "\">Edit</a><br><a href=\"#\" onclick=\"loadFragment(bbox".$boxnum.");\">Load</a>';"; 
            $htmlstr .= "var bbox" . $boxnum . " = L.polygon([";
            $htmlstr .= implode(",",$polypts);
            $htmlstr .= "], {fillOpacity: 0.05 ,weight: 1.5}); bbox" . $boxnum . ".bindTooltip(\"" . $map["name"] . "\", {direction:'center'}).bindPopup(popupText).addTo(map);\n";

            if (true || (strpos($map["name"], $missionTag)) && $map["name"] != $_GET["map"]) {
                $htmlstr .= "bboxes.push(bbox".$boxnum.");\n";
                $htmlstr .= "overlayVectors.push(bbox".$boxnum.");\n";
            } else {
                $htmlstr .= "// not tag: " . $missionTag . "\n";
                $htmlstr .= "overlayVectors.push(bbox".$boxnum.");\n";
            }

            ++$boxnum;

            if (False && !(strpos($map["name"], $mission) === 0)) {
                $otherMissionBboxes[] = $htmlstr;                
            } else {
                $missionBboxes[] = $htmlstr;
            }
        }

        foreach ($missionBboxes as $bbox) {
            echo $bbox;
        }

        if (sizeof($missionBboxes) == 0 && isset($_GET["all"])) {
            foreach ($otherMissionBboxes as $bbox) {
                echo $bbox;
            }
        }

        
    ?>

    function loadFragment(bboxobj) {
        imgCorners = bboxobj.getLatLngs()[0];
        imgUrl = "maps/" + bboxobj.getTooltip().getContent();
        transformedImg = L.imageTransform(imgUrl, imgCorners, { opacity: trOpacity, disableSetClip: false }).addTo(map);
        overlayImages.push(transformedImg);

        if (!overlayImagesEnabled) {
            toggleOverlayImages();
        }
    }

    function toggleSnap(state) {
        if (anchorMarkers && anchorMarkers.length > 0) {
            for (var i=0; i<anchorMarkers.length;i++) {
                var marker = anchorMarkers[i];
                if (!marker.snapediting) {
                    continue;
                }
                if (state) marker.snapediting.enable();
                else try { marker.snapediting.disable(); } catch(err) { console.log(err); }
            }
        }
    }

    function showSquares(xoffset) {
        
        var xrange = [20+xoffset,29+xoffset];
        var yrange = [54,60];

        L.polyline([
            [yrange[0],xrange[0]],
            [yrange[0],xrange[1]],
            [yrange[1],xrange[1]],
            [yrange[1],xrange[0]],
            [yrange[0],xrange[0]],
        ], {color: 'red', opacity: 0.75, weight: 2}).addTo(map);

        for (var x=xrange[0];x<xrange[1];x++) {
            let steps = 3;
            for (var l=1;l<steps;l++) {
                L.polyline([
                    [yrange[0],x+(l*(1/steps))],
                    [yrange[1],x+(l*(1/steps))],
                ], {color: 'orange', opacity: 0.75, weight: 1}).addTo(map);
            }

            L.polyline([
                [yrange[0],x],
                [yrange[1],x],
            ], {color: 'red', opacity: 0.75, weight: 3}).addTo(map);

            L.polyline([
                [yrange[0],x+0.5],
                [yrange[1],x+0.5],
            ], {color: 'red', opacity: 0.75, weight: 1}).addTo(map);
        }

        for (var y=yrange[0];y<yrange[1];y++) {
            let steps = 6;
            for (var l=1;l<steps;l++) {
                L.polyline([
                    [y+(l*(1/steps)),xrange[0]],
                    [y+(l*(1/steps)),xrange[1]],
                ], {color: 'orange', opacity: 0.75, weight: 1}).addTo(map);
            }

            L.polyline([
                [y,xrange[0]],
                [y,xrange[1]],
            ], {color: 'red', opacity: 0.75, weight: 3}).addTo(map);


            L.polyline([
                [y+0.5,xrange[0]],
                [y+0.5,xrange[1]],
            ], {color: 'red', opacity: 0.75, weight: 1}).addTo(map);

        }
    }

    function drawGrid(lat0, lat1, lon0, lon1) {
        // latgitude lines
        var latPts = [];
        var latPtsQ = [];
        var latPtsT = [];

        let ot = 1/3;
        let tt = 2/3;

        for (y=lat0;y<lat1;y++) {
            let pos = y % 2 == 0;
            
            if (pos) {
                latPts.push(new L.LatLng(y, lon0));
                latPts.push(new L.LatLng(y, lon1));

                latPtsQ.push(new L.LatLng(y+0.25, lon0));
                latPtsQ.push(new L.LatLng(y+0.25, lon1));
                latPtsQ.push(new L.LatLng(y+0.50, lon1));
                latPtsQ.push(new L.LatLng(y+0.50, lon0));
                latPtsQ.push(new L.LatLng(y+0.75, lon0));
                latPtsQ.push(new L.LatLng(y+0.75, lon1));
            } else {
                latPts.push(new L.LatLng(y, lon1));
                latPts.push(new L.LatLng(y, lon0));

                latPtsQ.push(new L.LatLng(y+0.25, lon1));
                latPtsQ.push(new L.LatLng(y+0.25, lon0));
                latPtsQ.push(new L.LatLng(y+0.50, lon0));
                latPtsQ.push(new L.LatLng(y+0.50, lon1));
                latPtsQ.push(new L.LatLng(y+0.75, lon1));
                latPtsQ.push(new L.LatLng(y+0.75, lon0));
            }

            latPtsT.push(new L.LatLng(y+ot, lon0));
            latPtsT.push(new L.LatLng(y+ot, lon1));
            latPtsT.push(new L.LatLng(y+tt, lon1));
            latPtsT.push(new L.LatLng(y+tt, lon0));
        }

        var latPolyline = new L.Polyline(latPts, {
            color: 'red',
            weight: 3,
            opacity: 0.5,
            smoothFactor: 1
        });
        latPolyline.addTo(map);

        var latPolylineQ = new L.Polyline(latPtsQ, {
            color: 'white',
            weight: 2,
            opacity: 0.5,
            smoothFactor: 1
        });
        latPolylineQ.addTo(map);

        var latPolylineT = new L.Polyline(latPtsT, {
            color: 'blue',
            weight: 2,
            opacity: 0.5,
            smoothFactor: 1
        });
        latPolylineT.addTo(map);

        // longitude lines
        var lonPts = [];
        var lonPtsQ = [];
        var lonPtsT = [];


        let lonOffsetMM = <?=CFG_MAP_MERIDIAN_MM?>;
        let lonOffsetDD = <?=CFG_MAP_MERIDIAN_DD?>;

        for (x=lon0;x<lon1;x++) {
            let pos = x % 2 == 0;
            
            var lon = x;
            if (lonOffsetDD > 0) {
                lon -= lonOffsetMM/60;
            } else {
                lon += lonOffsetMM/60;
            }
 
            if (pos) {
                lonPts.push(new L.LatLng(lat0, lon));
                lonPts.push(new L.LatLng(lat1, lon));

                lonPtsQ.push(new L.LatLng(lat0, lon+0.25));
                lonPtsQ.push(new L.LatLng(lat1, lon+0.25));
                lonPtsQ.push(new L.LatLng(lat1, lon+0.50));
                lonPtsQ.push(new L.LatLng(lat0, lon+0.50));
                lonPtsQ.push(new L.LatLng(lat0, lon+0.75));
                lonPtsQ.push(new L.LatLng(lat1, lon+0.75));
            } else {
                lonPts.push(new L.LatLng(lat1, lon));
                lonPts.push(new L.LatLng(lat0, lon));

                lonPtsQ.push(new L.LatLng(lat1, lon+0.25));
                lonPtsQ.push(new L.LatLng(lat0, lon+0.25));
                lonPtsQ.push(new L.LatLng(lat0, lon+0.50));
                lonPtsQ.push(new L.LatLng(lat1, lon+0.50));
                lonPtsQ.push(new L.LatLng(lat1, lon+0.75));
                lonPtsQ.push(new L.LatLng(lat0, lon+0.75));
            }

            lonPtsT.push(new L.LatLng(lat1, lon+ot));
            lonPtsT.push(new L.LatLng(lat0, lon+ot));
            lonPtsT.push(new L.LatLng(lat0, lon+tt));
            lonPtsT.push(new L.LatLng(lat1, lon+tt));
        }

        var lonPolyline = new L.Polyline(lonPts, {
            color: 'red',
            weight: 3,
            opacity: 0.5,
            smoothFactor: 1
        });
        lonPolyline.addTo(map);

        var lonPolylineQ = new L.Polyline(lonPtsQ, {
            color: 'white',
            weight: 2,
            opacity: 0.5,
            smoothFactor: 1
        });
        lonPolylineQ.addTo(map);

        var lonPolylineT = new L.Polyline(lonPtsT, {
            color: 'blue',
            weight: 2,
            opacity: 0.5,
            smoothFactor: 1
        });
        lonPolylineT.addTo(map);
    }
</script>
</html>