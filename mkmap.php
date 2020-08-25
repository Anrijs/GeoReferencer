<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>mkmap / AJ</title>

    <link rel="stylesheet" type="text/css" href="assets/css/main.css">

    <style>
        input { margin-bottom: 8px; }
    </style>
</head>
<body>
    <div id="container">
<?php

include "lib/fn.php";

$maps = getDoneMaps(!isset($_GET["existing"]));

$warnas = array();

$ext = "png";
if (isset($_GET["ext"])) {
    $ext = $_GET["ext"];
    $scale = 1.0;

    if (isset($_GET["scale"])) {
        $scale = floatval($_GET["scale"]);
    }

    $errors = array();

    echo "<a href=\"index.php\">Check status</a><br><br>";

    foreach ($maps as $map) {
        if (array_key_exists("igonre", $map)) {
            if ($map["ignore"] == "true") {
                continue;
            }
        }
        $str = generateMapFile($map, $scale);
        $str = str_replace(".tif", ".$ext", $str);

        if (CFG_VALIDATE) {
            $errs = validateCoords($map);
            foreach ($errs as $err) {
                $errors[] = $map["name"] . ": " . $err;
            }
        }

        $name = preg_replace('/\\.[^.\\s]{3,4}$/', '', $map["name"]);
        $mapname = str_replace(" ", "_", $name) . ".map";

        if (isset($_GET["dbg"])) {
            echo "<pre>"; print_r($map["dstcoords"]); echo "</pre>";
            echo "<hr>";
            echo "<pre>$str</pre>";
        } else {
            $myfile = fopen("maps/" . $mapname, "w") or die("Unable to open file!");
            fwrite($myfile, $str);
            fclose($myfile);
            echo "Created map calibration: <i><a href=\"maps/".$mapname."\">" . $mapname . "</a></i><br>\n";
        }
    }

    if (sizeof($errors) > 0) {
        echo "<pre>" . implode("\n",$errors) . "</pre>";
    }
} else { ?>
    <h3>Generate .map files</h3><br>
    <form method="get">
        <label for="ext">Image file extension</label><br>
        <input type="text" id="ext" name="ext"><br>
        <label for="scale">Image scale</label><br>
        <input type="number" id="scale" name="scale" value="1.0"><br>
        <input type="checkbox" id="existing" name="existing" checked><label for="existing">Only for existing images</label><br>
        <input type="submit">
    </form>
<?php } ?>
</div>
</body>
</html>