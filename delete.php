<?php
    require_once "lib/fn.php";

    if (CFG_FILE_MODIFY_ALLOWED) {
        if (isset($_GET["map"])) {
            $map = basename($_GET["map"]);

            $pname = preg_replace('/\\.[^.\\s]{3,4}$/', '', $map);

            $maps = getMaps();
            $dots = getDotMaps();

            $mapdir = dirname(__FILE__) . "/maps/";

            foreach ($maps as $m) {
                if ($m == $map) {
                    unlink($mapdir . $m);
                    echo "$pname.map removed<br>";
                    break;
                }
            }

            foreach ($dots as $m) {
                if ($m == $pname . ".map") {
                    unlink($mapdir . $m);
                    echo "$pname.map removed<br>";
                    break;
                }
            }

            header("Location: index.php");
            
        } else {
            die("Map name missing");
        }
    } else {
        die("File modifying is disabled");
    }
?>