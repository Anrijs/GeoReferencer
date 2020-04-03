<?php

require_once dirname(__FILE__) . '/../config.php';

/*
    Returns list of valid map images
*/
function getMaps() {
    $maps = array();
    $files = scandir("maps");
    foreach ($files as $f) {
        $parts = explode(".", $f);
        $ext = strtolower(end($parts));

        if ($ext == "png" || $ext == "jpg" || $ext == "gif") {
            $maps[] = $f;
        }
    }

    return $maps;
}

/*
    Returns list of uncalibrated map images
*/
function getIncompleteMaps() {
    $maps = getMaps();
    $done = getDoneMaps();

    $incomplete = array();

    foreach ($maps as $map) {
        $isDone = FALSE;
        foreach ($done as $d) {
            if ($d["name"] == $map) {
                $isDone = TRUE;
                break;
            }
        }
        if (!$isDone) {
            $incomplete[] = $map;
        }
    }

    return $incomplete;
}

/*
    Returns .map calibration file list
*/
function getDotMaps() {
    $maps = array();
    $files = scandir("maps");
    foreach ($files as $f) {
        $parts = explode(".", $f);
        $ext = end($parts);

        if ($ext == "map") {
            $maps[] = $f;
        }
    }

    return $maps;
}

function migrateDoneMaps($version) {
    echo "Migrate done maps $version<br>";
    if ($version == 1) {
        $str = file_get_contents("cmds.txt");
        $lines = explode("\n",$str);

        $donestr = "";

        foreach ($lines as $ln) {
            $obj = json_decode($ln, TRUE);

            $points = $obj["points"];
            $coords = $obj["coords"];

            $count = min(sizeof($points), sizeof($coords));
            $newobj = array("name" => $obj["name"]);
            $newpoints = array();

            for ($i=0;$i<$count;$i++) {
                $newpoints[] = array(
                    "x" => $points[$i][0],
                    "y" => $points[$i][1],
                    "lat" => array($coords[$i][0], $coords[$i][1]),
                    "lon" => array($coords[$i][2], $coords[$i][3])
                );
            }

            $newobj["points"] = $newpoints;
            $donestr .= json_encode($newobj) . "\n";
        }

        file_put_contents(dirname(__FILE__) . "/../cmds.txt", $donestr);
    }
}

/*
    Returns calibrated map list with calibration
*/
function getDoneMaps() {
    $done = array();
    $maps = getMaps();

    $str = file_get_contents("cmds.txt");
    $lines = explode("\n",$str);

    foreach ($lines as $ln) {
        if (strlen($ln) < 5) continue;
        $obj = json_decode($ln, TRUE);

        if (array_key_exists("coords", $obj)) {
            migrateDoneMaps(1);
            return getDoneMaps();
        }

        if (!in_array($obj["name"],$maps)) continue;

        $dstcoords = array();

        foreach ($obj["points"] as $key => $c) {
            $coord = $c;

            $dstlat = array($c["lat"][0],$c["lat"][1]);
            $dstlon = array($c["lon"][0],$c["lon"][1]);

            if (CFG_COORD_FORMAT == "D") { // must be degree decimals, right?
                $dstlat[1] = floatval("0." . $dstlat[1]) * 60;
                $dstlon[1] = floatval("0." . $dstlon[1]) * 60;
            }
            
            $inv = $dstlon[0] < 0;

            $dstlon[0] += CFG_MAP_MERIDIAN_DD;
            
            if ($inv xor $dstlon[0] < 0) {
                $dstlon[1] = -$dstlon[1];
            }

            $dstlon[1] += CFG_MAP_MERIDIAN_MM;

            if ($dstlon[1] >= 60) {
                $dstlon[1] -= 60;
                $dstlon[0] -= 1;
            } else if ($dstlon[1] < 0) {
                $dstlon[1] += 60;
                $dstlon[0] -= 1;
            }

            $c["dstlat"] = $dstlat;
            $c["dstlon"] = $dstlon;

            $obj["points"][$key] = $c;
            //$coord[3] = abs($coord[3]);

        }

        $done[] = $obj;
    }
    return $done;
}

function validateCoords($map) {
    $coords = array();

    foreach ($map["points"] as $c) {
        $lat = $c["dstlat"][0];
        $lon = $c["dstlon"][0];

        $latmm = $c["dstlat"][1] / 60;
        $lonmm = $c["dstlon"][1] / 60;

        $lat += ($lat >= 0) ? $latmm : -$latmm;
        $lon += ($lon >= 0) ? $lonmm : -$lonmm;

        $coords[] = array($lat,$lon);
    }

    $errors = array();

    if ($coords[0][0] < $coords[3][0]) $errors[] = "[Error] Pt1 Should be above Pt3";
    if ($coords[1][1] < $coords[0][1]) $errors[] = "[Error] Pt2 Should be left to Pt2";
    if ($coords[1][0] < $coords[2][0]) $errors[] = "[Error] Pt2 Should be above Pt3";
    if ($coords[2][1] < $coords[3][1]) $errors[] = "[Error] Pt4 Should be left to Pt3";

    return $errors;
}

function getCmds() {
    $maps = getDoneMaps();

    $cmds = array();

    foreach ($maps as $map) {
        $cmd = getToPngCmd($map);
        $cmds[] = $cmd;
    }
    
    return $cmds;
}

function getToTifCmd($map) {
    $mapname = preg_replace('/\\.[^.\\s]{3,4}$/', '', $map["name"]);
    $sane = str_replace('`','',$mapname);
    $sane = str_replace("'","",$sane);
    $sane = str_replace("@","-",$sane);

    return "gdal_translate -of GTiff " . escapeshellarg($mapname . ".map") . " " . escapeshellarg($sane . ".tif");
}

function getToPngCmd($map, $srcname=FALSE, $dstname=FALSE) {
    $name = $map["name"];

    if ($srcname) {
        $name = $srcname;
    }

    $cmd = "convert " . escapeshellarg($name);

    $pts = "";

    $map["points"][0]["x"] = $map["points"][0]["x"] - CFG_PNG_PADDING;
    $map["points"][0]["y"] = $map["points"][0]["y"] - CFG_PNG_PADDING;
    $map["points"][1]["x"] = $map["points"][1]["x"] + CFG_PNG_PADDING;
    $map["points"][1]["y"] = $map["points"][1]["y"] - CFG_PNG_PADDING;
    $map["points"][2]["x"] = $map["points"][2]["x"] + CFG_PNG_PADDING;
    $map["points"][2]["y"] = $map["points"][2]["y"] + CFG_PNG_PADDING;
    $map["points"][3]["x"] = $map["points"][3]["x"] - CFG_PNG_PADDING;
    $map["points"][3]["y"] = $map["points"][3]["y"] + CFG_PNG_PADDING;
    
    foreach ($map["points"] as $pt) {
        $pts .= $pt["x"] . "," . $pt["y"] . " ";
    }

    $mask = "\\( +clone -fill Black -colorize 100 -fill White -draw \"polygon " . $pts . "\" \\)";
    $ext = "-alpha off -compose CopyOpacity -composite +repage -quality 1"; // quality = 1 because we dont need compression


    if ($dstname) {
        $mapname = $dstname;
    } else {
        $mapname = preg_replace('/\\.[^.\\s]{3,4}$/', '', $map["name"]);
    }
    
    $cmd .= ' ' . $mask . ' ' . $ext . ' ' . escapeshellarg($mapname . '.png');

    return $cmd;
}

function oziPointLine($num, $x, $y, $latdd, $latmm, $pole, $londd, $lonmm, $hemi) {
    $fnum = $num;
    if($num < 10) $fnum = "0" . $num;   
    return "Point$fnum,xy, $x, $y,in, deg, $latdd, ".number_format((float) $latmm, 4, '.', '').", $pole, $londd, ".number_format((float) $lonmm, 4, '.', '').",$hemi, grid,   ,           ,           ,$pole";
}

function oziMPXYLine($num, $x, $y) {
    return "MMPXY,$num,$x,$y";
}

function oziMPLLLine($num, $latdd, $latmm, $pole, $londd, $lonmm, $hemi) {
    $mmlat = $latdd + ($latmm / 60);
    $mmlon = $londd + ($lonmm / 60);
    
    if ($pole == "S") $mmlat = -$mmlat;
    if ($hemi == "W") $mmlon = -$mmlon;

    return "MMPLL,$num,$mmlat,$mmlon";
}

// doneMap, lut
function generateMapFile($map) {
    $template = 'OziExplorer Map Data File Version 2.2
{FILENAME}
{FILENAME}
1 ,Map Code,
'.CFG_MAP_DATUM.',WGS 84,   0.0000,   0.0000,WGS 84
Reserved 1
Reserved 2
Magnetic Variation,,,E
Map Projection,Latitude/Longitude,PolyCal,No,AutoCalOnly,No,BSBUseWPX,No
{POINTS}
Projection Setup,,,,,,,,,,
Map Feature = MF ; Map Comment = MC     These follow if they exist
Track File = TF      These follow if they exist
Moving Map Parameters = MM?    These follow if they exist
MM0,Yes
MMPNUM,{MPNUMCOUNT}
{MPNUMS}
';

    $cut = true;

    $name = preg_replace('/\\.[^.\\s]{3,4}$/', '', $map["name"]);

    $str = str_replace("{FILENAME}",$name.".tif",$template);

    $mmpadding = 0;
    $mmstr = $mmtemplate;

    $ptlines = array();
    $mpxylines = array();
    $mplllines = array();

    $i = 0;
    while (++$i <= 4) {
        $latdd = $map["points"][$i-1]["dstlat"][0];
        $latmm = $map["points"][$i-1]["dstlat"][1];
        $londd = $map["points"][$i-1]["dstlon"][0];
        $lonmm = $map["points"][$i-1]["dstlon"][1];

        $pole = "N";
        $hemi = "E";

        if ($latdd < 0) {
            $pole = "S";
            $latdd = -$latdd;
        }

        if ($londd < 0) {
            $hemi = "W";
            $londd = -$londd;
        }

        $ptx = $map["points"][$i-1]["x"];
        $pty = $map["points"][$i-1]["y"];

        // Calibration points
        $ptlines[] = oziPointLine($i,$ptx,$pty,$latdd,$latmm,$pole,$londd,$lonmm,$hemi);
        // MMPNUMS
        $mpxylines[] = oziMPXYLine($i,$ptx,$pty);
        $mplllines[] = oziMPLLLine($i,$latdd,$latmm,$pole,$londd,$lonmm,$hemi);
    }

    $mpsstr = implode("\n",$mpxylines) . "\n" . implode("\n",$mplllines);
    $ptsstr = implode("\n",$ptlines);

    $str = str_replace("{MPNUMCOUNT}",sizeof($mpxylines),$str);
    $str = str_replace("{MPNUMS}",$mpsstr,$str);
    $str = str_replace("{POINTS}",$ptsstr.".tif",$str);

    return $str;
}

/*
    Functions to get map coordinates by map id/name
*/

function fixName($name) {
    // fix _ and whitespaces
    $name = str_replace("_", "-", $name);
    $name = str_replace(" ", "-", $name);
    $name = str_replace(".", "-", $name);

    return $name;
}

function getLatLon100k($name) {
    $parts = explode("-",fixName($name));
    $nom_number = $parts[0];
    $col_number = intval($parts[1]);

    $Low_mil_lat = (ord(strtoupper($nom_number)) - 65) * 4.0; // O-35-... 44
    $Lef_mil_lon = ($col_number - 31.0)*6;

    $nl = intval($parts[2]);

    $rw = floor(($nl - 1) / 12);

    $x1 = $Lef_mil_lon + floor($nl - $rw * 12 - 1) * 0.5;
    $y1 = ($Low_mil_lat) + 4 - ($rw + 1) * (1.0/3);
    $x2 = $x1 + 0.5;
    $y2 = $y1 + 1/3.0;

    return array($x1,$y1,$x2,$y2);
}

function getLatLon50k($name) {
    $bbox = getLatLon100k($name);

    $parts = explode("-",fixName($name));

    $col_number = intval($parts[1]);

    $d_lat = 1.0/6;
    $d_lon = 0.25;
    $letter = $parts[3];

    if ($letter == "1") {         //  a / a
        $bbox[1] += $d_lat;
    } else if ($letter == "2") {  //  b / b
        $bbox[1] += $d_lat;
        $bbox[0] += $d_lon;
    } else if ($letter == "4") {  //  d / g
        $bbox[0] += $d_lon;
    }

    return $bbox;
}

function getLatLon25k($name) {
    $bbox = getLatLon50k($name);

    $lat = $bbox[1];
    $lon = $bbox[0];

    $parts = explode("-",fixName($name));

    $col_number = intval($parts[1]);
    
    $d_lat = 1.0/12;
    $d_lon = 0.125;
    $letter = $parts[4]; 

    if ($letter == "1") {         //  a / a
        $lat += $d_lat;
    } else if ($letter == "2") {  //  b / b
        $lat += $d_lat;
        $lon += $d_lon;
    } else if ($letter == "4") {  //  d / g
        $lon += $d_lon;
    }

    return array($lon, $lat, $lon + $d_lon, $lat + $d_lat);
}

?>
