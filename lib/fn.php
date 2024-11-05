<?php

require_once dirname(__FILE__) . '/../config.php';

/*
    Returns list of valid map images
*/
function getMaps() {
    $maps = array();
    $files = scandir("maps");
    natsort($files);
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
function getDoneMaps($missing=False) {
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


        $obj["missing"] = "0";

        if (!in_array($obj["name"],$maps)) {
            if ($missing) {
                $obj["missing"] = "1";
            } else {
                continue;
            }
        }

        $dstcoords = array();

        foreach ($obj["points"] as $key => $c) {
            $coord = $c;

            $dstlat = array(floatval($c["lat"][0]),floatval($c["lat"][1]));
            $dstlon = array(floatval($c["lon"][0]),floatval($c["lon"][1]));

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

function getCmds($scale=1.0) {
    $maps = getDoneMaps();

    $cmds = array();

    foreach ($maps as $map) {
        $cmd = getToPngCmd($map, $scale);
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

function getToPngCmd($map, $scale=1.0, $srcname=FALSE, $dstname=FALSE) {
    $name = $map["name"];

    if ($srcname) {
        $name = $srcname;
    }

    $cmd = "convert " . escapeshellarg($name);

    $pts = "";

    $padding = CFG_PNG_PADDING;

    $map["points"][0]["x"] = ($map["points"][0]["x"] * $scale) - $padding;
    $map["points"][0]["y"] = ($map["points"][0]["y"] * $scale) - $padding;
    $map["points"][1]["x"] = ($map["points"][1]["x"] * $scale) + $padding;
    $map["points"][1]["y"] = ($map["points"][1]["y"] * $scale) - $padding;
    $map["points"][2]["x"] = ($map["points"][2]["x"] * $scale) + $padding;
    $map["points"][2]["y"] = ($map["points"][2]["y"] * $scale) + $padding;
    $map["points"][3]["x"] = ($map["points"][3]["x"] * $scale) - $padding;
    $map["points"][3]["y"] = ($map["points"][3]["y"] * $scale) + $padding;

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

function getWarpCmd($map, $scale=1.0, $srcname=FALSE, $dstname=FALSE) {
    $name = $map["name"];
    if (!isset($map["corners"]) || !isset($map["warp"]) || sizeof($map["warp"]) < 4) {
        return "# missing warp data for $name";
    }
    if ($srcname) {
        $name = $srcname;
    }
    $h = $map["warp"]["srch"];
    $w = $map["warp"]["srcw"];
    $defpts = array(
        array(0,0),
        array($w-1,0),
        array($w-1,$h-1),
        array(0,$h-1),
        array($h-1,0),
        array($h-1,$w-1),
        array(0,$w-1)
    );

    $padding = CFG_PNG_PADDING;
    $blur =    CFG_PNG_PADDING;
    
    $cutline = "";
    $isdef = true;
    foreach ($map["cutline"] as $cut) {
        if (!in_array($cut, $defpts)) {
            $isdef = false;
        }

        $cutx = $cut[1];
        $cuty = $cut[0];

        if ($cutx < ($w/2)) {
            $cutx += $padding;
        } else {
            $cutx -= $padding;
        }

        if ($cuty < ($h/2)) {
            $cuty += $padding;
        } else {
            $cuty -= $padding;
        }

        $cutline .= $cutx . ',' . $cuty. ' ';
    }
    
    $maskcmd = " \( +clone -fill Black -colorize 100 -fill White -draw \"polygon ".$cutline." \"    \) -alpha off -compose CopyOpacity -composite +repage";
    if ($isdef) {
        $maskcmd = "";
    }
    $cmd = "convert " . escapeshellarg($name) . $maskcmd . " +profile \"icc\" -colorspace srgb -type TrueColorAlpha -virtual-pixel background -background transparent +distort Perspective ";
    $distort = "";
    $gcps = "";
    $offsetx = intval($map["warp"]["offsetx"]);
    $offsety = intval($map["warp"]["offsety"]);
    $pt1lat = intval($map["points"][0]["lat"][0]) + (floatval($map["points"][0]["lat"][1]) / 60);
    $pt1lon = intval($map["points"][0]["lon"][0]) + (floatval($map["points"][0]["lon"][1]) / 60);    
    $pt2lat = intval($map["points"][1]["lat"][0]) + (floatval($map["points"][1]["lat"][1]) / 60);
    $pt2lon = intval($map["points"][1]["lon"][0]) + (floatval($map["points"][1]["lon"][1]) / 60);    
    $pt3lat = intval($map["points"][2]["lat"][0]) + (floatval($map["points"][2]["lat"][1]) / 60);
    $pt3lon = intval($map["points"][2]["lon"][0]) + (floatval($map["points"][2]["lon"][1]) / 60);    
    $pt4lat = intval($map["points"][3]["lat"][0]) + (floatval($map["points"][3]["lat"][1]) / 60);
    $pt4lon = intval($map["points"][3]["lon"][0]) + (floatval($map["points"][3]["lon"][1]) / 60);
    $x1 = ($map["warp"]["points"][0]["dst"][0] - $offsetx) * $scale;
    $y1 = ($map["warp"]["points"][0]["dst"][1] - $offsety) * $scale;
    $x2 = ($map["warp"]["points"][($firstid + 1) % 4]["dst"][0] - $offsetx) * $scale;
    $y2 = ($map["warp"]["points"][($firstid + 1) % 4]["dst"][1] - $offsety) * $scale;
    $x3 = ($map["warp"]["points"][($firstid + 2) % 4]["dst"][0] - $offsetx) * $scale;
    $y3 = ($map["warp"]["points"][($firstid + 2) % 4]["dst"][1] - $offsety) * $scale;
    $x4 = ($map["warp"]["points"][($firstid + 3) % 4]["dst"][0] - $offsetx) * $scale;
    $y4 = ($map["warp"]["points"][($firstid + 3) % 4]["dst"][1] - $offsety) * $scale;
    $gcps .= "-gcp " . $x1 . " " . $y1 . " " . $pt1lon . " " . $pt1lat . " ";
    $gcps .= "-gcp " . $x2 . " " . $y2 . " " . $pt2lon . " " . $pt2lat . " ";
    $gcps .= "-gcp " . $x3 . " " . $y3 . " " . $pt3lon . " " . $pt3lat . " ";
    $gcps .= "-gcp " . $x4 . " " . $y4 . " " . $pt4lon . " " . $pt4lat . " ";
    foreach ($map["warp"]["points"] as $pt) {
        $distort .= ($pt["src"][0] * $scale) . "," . ($pt["src"][1] * $scale) . " ";
        $distort .= (($pt["dst"][0] - $offsetx) * $scale) . "," . (($pt["dst"][1] - $offsety) * $scale) . " ";
    }
    if ($dstname) {
        $mapname = $dstname;
    } else {
        $mapname = preg_replace('/\\.[^.\\s]{3,4}$/', '', $map["name"]);
    }
    
    $cmd .= " '" . $distort . "' -quality 1 " . escapeshellarg($mapname . '.png');
    $cmd2 = "gdal_translate -of GTiff " . $gcps .  " " . escapeshellarg($mapname . '.png') . " " .escapeshellarg($mapname . '.tif');
    
    return array($cmd, $cmd2);
}
/**
 * Calculates the great-circle distance between two points, with
 * the Haversine formula.
 * @param float $latitudeFrom Latitude of start point in [deg decimal]
 * @param float $longitudeFrom Longitude of start point in [deg decimal]
 * @param float $latitudeTo Latitude of target point in [deg decimal]
 * @param float $longitudeTo Longitude of target point in [deg decimal]
 * @param float $earthRadius Mean earth radius in [m]
 * @return float Distance between points in [m] (same as earthRadius)
 */
function haversineGreatCircleDistance(
    $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
    // convert from degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);
    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;
    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
    cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius;
}

// Works only if corner mode is enabled
function cmpMapArea($a, $b) {
    $area = 0;
    
    if ($a["corners"] == "false" || $b["corners"] == "false") {
        return -1;
    }
    $lat0 = floatval($a["points"][0]["dstlat"][0]) + (floatval($a["points"][0]["dstlat"][1]) / 60);
    $lon0 = floatval($a["points"][0]["dstlon"][0]) + (floatval($a["points"][0]["dstlon"][1]) / 60);
    $lat1 = floatval($a["points"][1]["dstlat"][0]) + (floatval($a["points"][1]["dstlat"][1]) / 60);
    $lon1 = floatval($a["points"][1]["dstlon"][0]) + (floatval($a["points"][1]["dstlon"][1]) / 60);
    $lat2 = floatval($a["points"][2]["dstlat"][0]) + (floatval($a["points"][2]["dstlat"][1]) / 60);
    $lon2 = floatval($a["points"][2]["dstlon"][0]) + (floatval($a["points"][2]["dstlon"][1]) / 60);
    $lat3 = floatval($a["points"][3]["dstlat"][0]) + (floatval($a["points"][3]["dstlat"][1]) / 60);
    $lon3 = floatval($a["points"][3]["dstlon"][0]) + (floatval($a["points"][3]["dstlon"][1]) / 60);
    $top = haversineGreatCircleDistance($lat0, $lon0, $lat1, $lon1);
    $right = haversineGreatCircleDistance($lat1, $lon1, $lat2, $lon2);
    $bottom = haversineGreatCircleDistance($lat2, $lon2, $lat3, $lon3);
    $left = haversineGreatCircleDistance($lat3, $lon3, $lat0, $lon0);
    $maxtb = max($top, $bottom);
    $maxlr = max($left, $right);
    $areaa = $maxlr * $maxtb;
////////
    $lat0 = floatval($b["points"][0]["dstlat"][0]) + (floatval($b["points"][0]["dstlat"][1]) / 60);
    $lon0 = floatval($b["points"][0]["dstlon"][0]) + (floatval($b["points"][0]["dstlon"][1]) / 60);
    $lat1 = floatval($b["points"][1]["dstlat"][0]) + (floatval($b["points"][1]["dstlat"][1]) / 60);
    $lon1 = floatval($b["points"][1]["dstlon"][0]) + (floatval($b["points"][1]["dstlon"][1]) / 60);
    $lat2 = floatval($b["points"][2]["dstlat"][0]) + (floatval($b["points"][2]["dstlat"][1]) / 60);
    $lon2 = floatval($b["points"][2]["dstlon"][0]) + (floatval($b["points"][2]["dstlon"][1]) / 60);
    $lat3 = floatval($b["points"][3]["dstlat"][0]) + (floatval($b["points"][3]["dstlat"][1]) / 60);
    $lon3 = floatval($b["points"][3]["dstlon"][0]) + (floatval($b["points"][3]["dstlon"][1]) / 60);
    $top = haversineGreatCircleDistance($lat0, $lon0, $lat1, $lon1);
    $right = haversineGreatCircleDistance($lat1, $lon1, $lat2, $lon2);
    $bottom = haversineGreatCircleDistance($lat2, $lon2, $lat3, $lon3);
    $left = haversineGreatCircleDistance($lat3, $lon3, $lat0, $lon0);
    $maxtb = max($top, $bottom);
    $maxlr = max($left, $right);
    $areab = $maxlr * $maxtb;
    return $areab - $areaa;
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

// doneMap, lut0
function generateMapFile($map, $scale=1.0, $warped=True) {
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

    $postfix = "";
    if (isset($map['postfix'])) {
        $postfix = $map['postfix'];
        if (strlen($postfix) < 1) {
            $postfix = "";
        } else {
            $postfix = "_" . $postfix;
        }
    }

    $str = str_replace("{FILENAME}",$name . $postfix . ".tif",$template);

    $mmpadding = 0;
    $mmstr = $mmtemplate;

    $ptlines = array();
    $mpxylines = array();
    $mplllines = array();

    $corners = $map["corners"] == "true" && $warped;
    $offsetx = 0;
    $offset = 0;
    if ($corners) {
        $offsetx = intval($map["warp"]["offsetx"]);
        $offsety = intval($map["warp"]["offsety"]);
    }

    // anchors
    $lata = 0;
    $lona = 0;
    $latstep = 0.0;
    $lonstep = 0.0;
    
    $i = 0;
    while (++$i <= 4) {
        $xyarr = $corners ? $map["warp"]["points"] : $map["points"];
        if (!array_key_exists($i-1,$xyarr)) {
            continue;
        }

        $latdd = $map["points"][$i-1]["dstlat"][0];
        $latmm = $map["points"][$i-1]["dstlat"][1];
        $londd = $map["points"][$i-1]["dstlon"][0];
        $lonmm = $map["points"][$i-1]["dstlon"][1];

        $lat = $latdd + ($latmm / 60);
        $lon = $londd + ($lonmm / 60);

        $latdt = $lat - (($lat - $lata) * $latstep);
        $londt = $lon - (($lon - $lona) * $lonstep);

        $latdt += CFG_OFFSET_LAT;
        $londt += CFG_OFFSET_LON;

        $latdd = floor($latdt);
        $latmm = ($latdt - $latdd) * 60;
        $londd = floor($londt);
        $lonmm = ($londt - $londd) * 60;

        $map["points"][$i-1]["dstlat"][0] = $latdd;
        $map["points"][$i-1]["dstlat"][1] = $latmm;
        $map["points"][$i-1]["dstlon"][0] = $londd;
        $map["points"][$i-1]["dstlon"][1] = $lonmm;

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

        $ptx = $map["points"][$i-1]["x"] * $scale;
        $pty = $map["points"][$i-1]["y"] * $scale;

        if ($corners) {
            $ptx = ($map["warp"]["points"][$i-1]["dst"][0] - $offsetx) * $scale;
            $pty = ($map["warp"]["points"][$i-1]["dst"][1] - $offsety) * $scale;
        }

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
    $str = str_replace("{POINTS}",$ptsstr,$str);
    
    return $str;
}

function generateGcpFile($map, $scale=1.0) {
    $lines = array();
    $lines[] = '#CRS: GEOGCS["WGS 84",DATUM["WGS_1984",SPHEROID["WGS 84",6378137,298.257223563,AUTHORITY["EPSG","7030"]],AUTHORITY["EPSG","6326"]],PRIMEM["Greenwich",0,AUTHORITY["EPSG","8901"]],UNIT["degree",0.0174532925199433,AUTHORITY["EPSG","9122"]],AUTHORITY["EPSG","4326"]]';
    $lines[] = 'mapX,mapY,pixelX,pixelY,enable';

    $i = 0;
    while (++$i <= 4) {
        $xyarr = $corners ? $map["warp"]["points"] : $map["points"];
        if (!array_key_exists($i-1,$xyarr)) {
            continue;
        }

        $latdd = $map["points"][$i-1]["dstlat"][0];
        $latmm = $map["points"][$i-1]["dstlat"][1];

        $londd = $map["points"][$i-1]["dstlon"][0];
        $lonmm = $map["points"][$i-1]["dstlon"][1];

        $lat = $latdd + ($latmm / 60);
        $lon = $londd + ($lonmm / 60);

        $ptx = $map["points"][$i-1]["x"] * $scale;
        $pty = $map["points"][$i-1]["y"] * $scale;

        if ($corners) {
            $ptx = ($map["warp"]["points"][$i-1]["dst"][0] - $offsetx) * $scale;
            $pty = ($map["warp"]["points"][$i-1]["dst"][1] - $offsety) * $scale;
        }

        $pty = -$pty;

        $lines[] = "$lon,$lat,$ptx,$pty,1";
    }

    return implode("\n", $lines);
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