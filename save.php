<?php
    /*
        Save calibration info to json lines.
    */

    include 'lib/fn.php';

    function contains($needle, $haystack) {
        return strpos($haystack, $needle) !== false;
    }

    if (isset($_POST["filename"]) && isset($_POST["points"])) {
        $filename = urldecode($_POST["filename"]);
        $points = $_POST["points"];
        
        // validate points
        if (CFG_VALIDATE) {
            $ptid = 1;
            foreach ($points as $pt) {
                if ($pt["x"] == -1 && $pt["y"] == -1) {
                    die("invalid calibration point #${ptid} location");
                    return;
                }

                $latnok = empty($pt["lat"][0]) || empty($pt["lat"][1]);
                $lonnok = empty($pt["lon"][0]) || empty($pt["lon"][1]);

                if ($latnok || $lonnok) {
                    die("invalid calibration point #${ptid} coordinates");
                    return;
                }
                $ptid++;
            }
        }

        $maps = getDoneMaps();
        $exists = FALSE;

        $obj = array(
            "name" => $filename,
            "points" => $points,
        );

        foreach ($maps as $key => $v) {
            if ($v["name"] == $filename) {
                $maps[$key] = $obj;
                $exists = TRUE;
                break;
            }
        }

        if (!$exists) {
            $maps[] = $obj;
        }

        $str = "";
        foreach ($maps as $map) {
            $str .= json_encode($map) . "\n";
        }

        file_put_contents("cmds.txt", $str);

        echo "OK";
    } else {
        die("invalid request");
    }
?>