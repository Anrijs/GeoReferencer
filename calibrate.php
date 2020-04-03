<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        include "lib/fn.php";

        $pointerCount = 4;

        $map = FALSE;
        $left = 0;
        
        if (isset($_GET["map"])) {
            $map = "maps/" . $_GET["map"];
        } else {
            $maps = getIncompleteMaps();
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
    
        $pt1xy = "-100,-1";
        $pt2xy = "-100,-1";
        $pt3xy = "-100,-1";
        $pt4xy = "-100,-1";

        $pt1lat = array("","");
        $pt1lon = array("","");
        $pt2lat = array("","");
        $pt2lon = array("","");
        $pt3lat = array("","");
        $pt3lon = array("","");
        $pt4lat = array("","");
        $pt4lon = array("","");

        if (isset($_GET["map"])) {
            $name = urldecode($_GET["map"]);

            $done = getDoneMaps();
            foreach ($done as $d) {
                if ($d["name"] == $name) {
                    $pt1xy = $d["points"][0]["x"] . "," . $d["points"][0]["y"];
                    $pt2xy = $d["points"][1]["x"] . "," . $d["points"][1]["y"];
                    $pt3xy = $d["points"][2]["x"] . "," . $d["points"][2]["y"];
                    $pt4xy = $d["points"][3]["x"] . "," . $d["points"][3]["y"];

                    $pt1lat = array($d["points"][0]["lat"][0], $d["points"][0]["lat"][1]);
                    $pt1lon = array($d["points"][0]["lon"][0], $d["points"][0]["lon"][1]);
                    $pt2lat = array($d["points"][1]["lat"][0], $d["points"][1]["lat"][1]);
                    $pt2lon = array($d["points"][1]["lon"][0], $d["points"][1]["lon"][1]);
                    $pt3lat = array($d["points"][2]["lat"][0], $d["points"][2]["lat"][1]);
                    $pt3lon = array($d["points"][2]["lon"][0], $d["points"][2]["lon"][1]);
                    $pt4lat = array($d["points"][3]["lat"][0], $d["points"][3]["lat"][1]);
                    $pt4lon = array($d["points"][3]["lon"][0], $d["points"][3]["lon"][1]);
                    break;
                }
            }
        }
        
        ?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>cutter / AJ</title>


    <script
        src="assets/js/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"></script>
    <script src="assets/js/fa.js" crossorigin="anonymous"></script>
    <script src="assets/js/mapbbox.js"></script>
    <script src="assets/js/cookieman.js"></script>
    
    <link rel="stylesheet" type="text/css" href="assets/css/main.css">

    <style>
        #image {
            image-rendering: optimizeSpeed;             /* STOP SMOOTHING, GIVE ME SPEED  */
            image-rendering: -moz-crisp-edges;          /* Firefox                        */
            image-rendering: -o-crisp-edges;            /* Opera                          */
            image-rendering: -webkit-optimize-contrast; /* Chrome (and eventually Safari) */
            image-rendering: pixelated;                 /* Chrome */
            image-rendering: optimize-contrast;         /* CSS3 Proposed                  */
            -ms-interpolation-mode: nearest-neighbor;   /* IE8+                           */
        }
    </style>
</head>
<body>
    <?php
        if (!$map) {
            die("<div style=\"padding: 12px;\">All maps calibrated. Add more to /maps directory or <a href=\"index.php\">check calibration</a></div></body></html>");
        }
    ?>
    <div id="toolbar">
        <div id="btn1" onClick="activateMarker(1);" class="btn btn-active">1</div>
        <div id="btn2" onClick="activateMarker(2);" class="btn">2</div>
        <div id="btn3" onClick="activateMarker(3);" class="btn">3</div>
        <div id="btn4" onClick="activateMarker(4);" class="btn">4</div>
        <br>
        <div id="cmdSace" onClick="saveCmd()" class="btn"><i class="fas fa-save"></i></div>
        <div id="cmdHelp" onClick="helpShow()" class="btn"><i class="fas fa-info-circle"></i></div>
        <div id="cmdSettings" onClick="settingsShow()" class="btn"><i class="fas fa-cog"></i></div>
    </div>

    <div id="mapname"><?=basename($map);?> / <?=$left;?> remaining</div>
    
    <img id="image" src="<?=$map;?>" class="clickables">​
    <canvas id="canvas" width='250px' height='250px'></canvas>

    <div id="help">
        <b> [Q] </b> - Select next point<br>
        <b> [W], [A], [S], [D] </b> - Move calibration point<br>
        <b> [Shift] </b> </b> - Move pont by 10px<br>
        <b> [E] </b> - Export<br>
        <br>
        <span onClick="helpHide();" class="btn-micro clickable"><b>Close</b></span>
    </div>

    <div id="settings">
        <b>Uzstādījumi</b><br>
        <input id="autoNext" type="checkbox"> <label for="autoNext">- Automatically change point</label>
        <br>
        <input id="autoCoord" type="checkbox"> <label for="autoCoord">- Fill coordinates by map name</label>
        <br>
        <br>
        <span onClick="settingsHide();" class="btn-micro clickable"><b>Close</b></span>
    </div>

<?php
        // Install pointers
        $i = 0;
        while (++$i <= $pointerCount) {
            $ptlat = array("","");
            $ptlon = array("",""); 

            if ($i == 1) {
                $ptlat = $pt1lat;
                $ptlon = $pt1lon; 
            } if ($i == 2) {
                $ptlat = $pt2lat;
                $ptlon = $pt2lon; 
            } if ($i == 3) {
                $ptlat = $pt3lat;
                $ptlon = $pt3lon; 
            } if ($i == 4) {
                $ptlat = $pt4lat;
                $ptlon = $pt4lon; 
            }

            echo "<img src=\"assets/img/pointer${i}.png\" id=\"pointer${i}\" class=\"clickables\">\n";
            echo "<div class=\"pointer-coords\" id=\"pointer${i}coords\">\n";
            echo "<div>\n";
            echo     "<b>Lat: </b><br>";
            echo     "<input value=\"${ptlat[0]}\" type=\"number\" id=\"pointer${i}latdd\" style=\"width: 36px;\" placeholder=\"dd\">°";
            echo     "<input value=\"${ptlat[1]}\" id=\"pointer${i}latmm\" style=\"width: 72px;\" placeholder=\"mm.m\">'";
            echo "</div>";
            echo "<div>\n";
            echo     "<b>Lon: </b><br>";
            echo     "<input value=\"${ptlon[0]}\" type=\"number\" id=\"pointer${i}londd\" style=\"width: 36px;\" placeholder=\"dd\">°";
            echo     "<input value=\"${ptlon[1]}\" id=\"pointer${i}lonmm\" style=\"width: 72px;\" placeholder=\"mm.m\">'";
            echo "</div>\n";
            echo "</div>\n\n";
        }
?>
</body>
<script>
    var mkOffsetX = 16;
    var mkOffsetY = 16;

    // Setting cookies
    var autoNext = getCookie("autoNext") == "true";
    var autoCoord = getCookie("autoCoord") == "true";

    if (autoCoord) {
        autofillCoords()
    }

    var active = 1;
    var shiftDown = false;

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

    var pt1pos = [<?=$pt1xy;?>];
    var pt2pos = [<?=$pt2xy;?>];
    var pt3pos = [<?=$pt3xy;?>];
    var pt4pos = [<?=$pt4xy;?>];

    var mapimg = document.getElementById("image");
    var ctx = false;
    var ctxscale = 1;
    var ctxheight = 250;
    var ctxwidth = 250;

    function loadCtx() {
        var c = document.getElementById("canvas");
        ctx = c.getContext("2d");

        if (mapimg.width > mapimg.height) {
            ctxscale = mapimg.width / 250;
            ctxheight = mapimg.height / ctxscale              
        } else {
            ctxscale = mapimg.height / 250;
            ctxwidth = mapimg.width / ctxscale  
        }

        canvas.width = ctxwidth;
        canvas.height = ctxheight;

        c.addEventListener('click',function(evt){
            var x = (evt.offsetX * ctxscale) - (window.innerWidth / 2);
            var y = (evt.offsetY * ctxscale) - (window.innerHeight / 2);

            window.scrollTo(x,y);
        },false);
    }

    function updateMinimap() {
        if (!ctx) {
            loadCtx();
        }

        ctx.fillStyle = "#EEEEEE";
        ctx.fillRect(0, 0, ctxwidth, ctxheight);

        ctx.drawImage(mapimg, 0, 0, ctxwidth, ctxheight);

        ctx.fillStyle = "#FF0000";
        ctx.beginPath();
        ctx.arc(pt1pos[0] / ctxscale, (pt1pos[1] / ctxscale), 2, 0, 2 * Math.PI, false);
        ctx.fill();

        ctx.beginPath();
        ctx.arc(pt2pos[0] / ctxscale, (pt2pos[1] / ctxscale), 2, 0, 2 * Math.PI, false);
        ctx.fill();

        ctx.beginPath();
        ctx.arc(pt3pos[0] / ctxscale, (pt3pos[1] / ctxscale), 2, 0, 2 * Math.PI, false);
        ctx.fill();

        ctx.beginPath();
        ctx.arc(pt4pos[0] / ctxscale, (pt4pos[1] / ctxscale), 2, 0, 2 * Math.PI, false);
        ctx.fill();

        var wpsw = window.innerWidth / ctxscale;
        var wpsh = window.innerHeight / ctxscale;

        var x1 = window.pageXOffset / ctxscale;
        var y1 = window.pageYOffset / ctxscale;

        ctx.strokeStyle = "#FF0000";
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(x1,y1);
        ctx.lineTo(x1+wpsw,y1);
        ctx.lineTo(x1+wpsw,y1+wpsh);
        ctx.lineTo(x1, y1+wpsh);
        ctx.closePath();
        ctx.stroke();
    }

    function updatePoints() {
        var p1m = $("#pointer1");
        var p1c = $("#pointer1coords");

        var p2m = $("#pointer2");
        var p2c = $("#pointer2coords");

        var p3m = $("#pointer3");
        var p3c = $("#pointer3coords");

        var p4m = $("#pointer4");
        var p4c = $("#pointer4coords");

        $('#pointer1').css({top: pt1pos[1]-mkOffsetY, left: pt1pos[0]-mkOffsetX, position:'absolute'});
        $('#pointer1coords').css({top: pt1pos[1]-mkOffsetY, left: pt1pos[0]+mkOffsetX+48, position:'absolute'});

        $('#pointer2').css({top: pt2pos[1]-mkOffsetY, left: pt2pos[0]-mkOffsetX, position:'absolute'});
        $('#pointer2coords').css({top: pt2pos[1]-mkOffsetY, left: pt2pos[0]+mkOffsetX+48, position:'absolute'});

        $('#pointer3').css({top: pt3pos[1]-mkOffsetY, left: pt3pos[0]-mkOffsetX, position:'absolute'});
        $('#pointer3coords').css({top: pt3pos[1]-mkOffsetY, left: pt3pos[0]+mkOffsetX+48, position:'absolute'});

        $('#pointer4').css({top: pt4pos[1]-mkOffsetY, left: pt4pos[0]-mkOffsetX, position:'absolute'});
        $('#pointer4coords').css({top: pt4pos[1]-mkOffsetY, left: pt4pos[0]+mkOffsetX+48, position:'absolute'});

        updateMinimap();
    }

    function saveCmd() {
        var image = $("#image")[0];

        var obje = [
            {"x":pt1pos[0],"y":pt1pos[1],"lat":[$("#pointer1latdd")[0].value, $("#pointer1latmm")[0].value],"lon":[$("#pointer1londd")[0].value, $("#pointer1lonmm")[0].value]},
            {"x":pt2pos[0],"y":pt2pos[1],"lat":[$("#pointer2latdd")[0].value, $("#pointer2latmm")[0].value],"lon":[$("#pointer2londd")[0].value, $("#pointer2lonmm")[0].value]},
            {"x":pt3pos[0],"y":pt3pos[1],"lat":[$("#pointer3latdd")[0].value, $("#pointer3latmm")[0].value],"lon":[$("#pointer3londd")[0].value, $("#pointer3lonmm")[0].value]},
            {"x":pt4pos[0],"y":pt4pos[1],"lat":[$("#pointer4latdd")[0].value, $("#pointer4latmm")[0].value],"lon":[$("#pointer4londd")[0].value, $("#pointer4lonmm")[0].value]},
        ];
        
        $.post( "save.php", { 'points': obje, 'filename': baseName(image.src, false)}, function( data ) {
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
        $("#btn4").removeClass("btn-active");
        active = 0;
    }

    function mvMarker(dir) {
        if (active == 0) return;

        var m = 0;

        switch (active) {
            case 1: m = pt1pos; break;
            case 2: m = pt2pos; break;
            case 3: m = pt3pos; break;
            case 4: m = pt4pos; break;
        }

        var step = shiftDown ? 10 : 1;

        switch (dir) {
            case 1: m[1] -= step; break; // up
            case 2: m[0] -= step; break; // left
            case 3: m[1] += step; break; // down
            case 4: m[0] += step; break; // right
        }

        updatePoints();
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
                    wx = pt1pos[0] == -1 ? 0 : pt1pos[0] - w; 
                    wy = pt1pos[1] == -1 ? 0 : pt1pos[1] - h;
                    break;
                case 2: 
                    $("#btn2").addClass("btn-active");
                    wx = (pt2pos[0] == -1 ? image.width - pt1pos[0] : pt2pos[0]) - w; 
                    wy = (pt2pos[1] == -1 ? pt1pos[1]               : pt2pos[1]) - h;
                    break;
                case 3: 
                    $("#btn3").addClass("btn-active");
                    wx = (pt3pos[0] == -1 ? pt2pos[0] : pt3pos[0]) - w; 
                    wy = (pt3pos[1] == -1 ? image.height - pt2pos[1] : pt3pos[1]) - h;
                    break;
                case 4: 
                    $("#btn4").addClass("btn-active");
                    wx = (pt4pos[0] == -1 ? pt1pos[0] : pt4pos[0]) - w; 
                    wy = (pt4pos[1] == -1 ? pt3pos[1] : pt4pos[1]) - h;
                    break;
            }

            window.scrollTo(wx, wy);
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
            case 16: shiftDown = false;
        }
    });

    document.addEventListener("keydown", function(event) {
        switch (event.keyCode) {
            case 16: shiftDown = true; break;
            //case 49: activateMarker(1); break; // 1
            //case 50: activateMarker(2); break; // 2
            //case 51: activateMarker(3); break; // 3
            //case 52: activateMarker(4); break; // 4

            case 81: $("input").blur(); nextMarker(); break; // q

            //case 48: deactivateMarker(); break; // 0
            case 27: deactivateMarker(); break; // esc

            // wasd
            case 87: $("input").blur(); mvMarker(1); break; // w
            case 65: $("input").blur(); mvMarker(2); break; // a
            case 83: $("input").blur(); mvMarker(3); break; // s
            case 68: $("input").blur(); mvMarker(4); break; // d

            case 69: saveCmd(); // s
        }
    });

    function showImage(src, target) {
        var fr = new FileReader();

        fr.onload = function(){
            target.src = fr.result;
        }
        fr.readAsDataURL(src.files[0]);
    }
    
    function putImage() {
        var src = document.getElementById("select_image");
        var target = document.getElementById("image");
        showImage(src, target);
    }

    $(function(){
        $(".clickables").click(function(e) {
            switch (active) {
                case 1: 
                    pt1pos[0] = e.pageX;
                    pt1pos[1] = e.pageY;
                    $("#btn1").addClass("btn-set");
                    break;
                case 2: 
                    pt2pos[0] = e.pageX;
                    pt2pos[1] = e.pageY;
                    $("#btn2").addClass("btn-set");
                    break;
                case 3: 
                    pt3pos[0] = e.pageX;
                    pt3pos[1] = e.pageY;
                    $("#btn3").addClass("btn-set");
                    break;
                case 4: 
                    pt4pos[0] = e.pageX;
                    pt4pos[1] = e.pageY;
                    $("#btn4").addClass("btn-set"); 
                    break;
            }

            if (autoNext && active != 0) {
                active++;
                activateMarker(active);
            }

            updatePoints();
        });

        $("#autoNext")[0].checked = autoNext;
        $("#autoNext").change(function() {
            autoNext = this.checked
            console.log("Auto Next cookie set: " + autoNext);
            setCookie("autoNext", autoNext, 30);
        });

        $("#autoCoord")[0].checked = autoCoord;
        $("#autoCoord").change(function() {
            autoCoord = this.checked
            console.log("Auto Coord cookie set: " + autoCoord);
            setCookie("autoCoord", autoCoord, 30);

            if (autoCoord) {
                autofillCoords();
            }
        });

        updatePoints();
    });

    mapimg.onload = function() {
        updateMinimap();
    };

    window.onscroll = function() {
        updateMinimap();
    };

    window.onresize = function() {
        updateMinimap();
    };
</script>
</html>