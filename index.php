<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>status / AJ</title>

    <script
        src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3ea830fc8d.js" crossorigin="anonymous"></script>
    <script src="assets/js/mapbbox.js"></script>
    <script src="assets/js/cookieman.js"></script>

    <link rel="stylesheet" type="text/css" href="assets/css/main.css">

    <style>
        .code {
            padding: 2px;
            background: #ddd;
            border: solid 1px #888;
            border-radius: 2px;
            font-size: 0.9em;
        }

        pre {
            white-space: pre-wrap;       /* Since CSS 2.1 */
            white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
            white-space: -pre-wrap;      /* Opera 4-6 */
            white-space: -o-pre-wrap;    /* Opera 7 */
            word-wrap: break-word;       /* Internet Explorer 5.5+ */
        }
        
        li {
            padding: 4px;
        }

        table {
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
        }

        td,th {
            padding: 4px;
        }

        .txt-yes {
            color: #4CAF50;
        }

        .txt-no {
            color: #f44336;
        }

        pre {
            background-color: #eee;
            font-family: 'Courier New', Courier, monospace;
            padding: 12px;
            font-size: 14px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div id="container">
        <?php 
            include 'lib/fn.php';
            // get all
            
            $maps = getMaps();
            $done = getDoneMaps();
            $dotmaps = getDotMaps();

            $donecount = sizeof($done) . "/" . sizeof($maps);

            echo "<h3><a href=\"calibrate.php\">Start calibrating maps</a></h3><br>";
            echo "<a href=\"mkmap.php\">Generate .map files</a><br><br>";
            /*if (TrueCFG_FILE_MODIFY_ALLOWED)*/ echo "<a href=\"upload.php\">Upload map images</a><br><br>";
            echo "<a href=\"map.php\">View calibration bbox in map</a><br>";

            echo "<hr class=\"alt\">";

            echo "<h3>Status <small>($donecount Calibrated)</small></h3><br>";
            echo "<table>\n";
            echo "<tr>\n";

            echo "<th>Name</th>\n";
            echo "<th>Original</th>\n";
            echo "<th>Calib</th>\n";
            echo "<th>.map</th>\n";
            /*if (TrueCFG_FILE_MODIFY_ALLOWED)*/ echo "<th></th>\n";

            echo "</tr>\n";

            $strY = "<b class=\"txt-yes\">Y</b>";
            $strN = "<b class=\"txt-no\">N</b>";

            $readyPngCmds = array();
            $readTifCmds = array();

            foreach ($maps as $map) {
                $name = preg_replace('/\\.[^.\\s]{3,4}$/', '', $map);

                $don = FALSE;
                $dot = FALSE;

                foreach ($done as $d) {
                    $dname = preg_replace('/\\.[^.\\s]{3,4}$/', '', $d["name"]);
                    if ($dname == $name) {
                        $don = $d;
                        break;
                    }
                }

                $name2 = str_replace(" ", "_", $name);
                foreach ($dotmaps as $d) {
                    $pname = preg_replace('/\\.[^.\\s]{3,4}$/', '', $d);
                    if ($pname == $name2) {
                        $dot = $d;
                        break;
                    }
                }

                $origurl = "<a href=\"maps/$map\" download>Download</a>";                
                $doturl = "<a href=\"maps/$dot\" class=\"txt-yes\">.map</a>";
                $editurl = "<a href=\"calibrate.php?map=$map\">check</a>";
                $rmurl = "<a onclick=\"return confirm('Confirm $map delete');\" href=\"delete.php?map=$map\">Delete</a>";

                echo "<tr>\n";


                echo "<td>" . $name . " <small>[" . $editurl . "]</small></td>\n";
                echo "<td>$origurl</td>\n";                
                echo "<td>" . ($don ? $strY : $strN) . "</td>\n";
                echo "<td>" . ($dot ? $doturl : " - ") . "</td>\n";
                /*if (TrueCFG_FILE_MODIFY_ALLOWED)*/ echo "<td><small>[" . $rmurl . "]</small></td>\n";

                echo "</tr>\n";
            }

            echo "</table>";

            echo "<hr class=\"alt\">";

            $dformat = "dd mm.mmm";
            if (CFG_COORD_FORMAT == "D") {
                $dformat = "dd.ddd";
            }

            echo "<h3>Configuration</h3>";
            echo "<div><dl class=\"inline-flex\">";
            echo "<dt>Map Datum</dt><dd><span class=\"ddspan\"></span>" . CFG_MAP_DATUM . "</dd>";
            echo "<dt>Meridian</dt><dd><span class=\"ddspan\"></span>" . CFG_MAP_MERIDIAN_DD . "° " . str_replace("-","",strval(CFG_MAP_MERIDIAN_MM)) . "'</dd>";
            echo "<dt>PNG Cut padding</dt><dd><span class=\"ddspan\"></span>" . CFG_PNG_PADDING . "px</dd>";
            echo "<dt>Degree format</dt><dd><span class=\"ddspan\"></span>" . $dformat . "</dd>";
            echo "<dt>File upload/delete</dt><dd><span class=\"ddspan\"></span>" . (CFG_FILE_MODIFY_ALLOWED ? "Allowed" : "Disabled") . "</dd>";
            echo "<dt>Validate points on save</dt><dd><span class=\"ddspan\"></span>" . (CFG_VALIDATE ? "Allowed" : "Disabled") . "</dd>";
            echo "</dl></div>";
            echo "<small>Edit in config.php</small>";

            echo "<hr class=\"alt\">";

            if (isset($_GET["cmds"])) {
                foreach ($done as $don) {
                    $name = preg_replace('/\\.[^.\\s]{3,4}$/', '', $don["name"]);
                    $parts = explode(" ",$name);
                    $sane = $parts[0];
    
                    $readyPngCmds[] = getToPngCmd($don, FALSE, "pngs/" . $sane);
                    $readTifCmds[] = getToTifCmd($don);
                }

                echo "<a href=\"?\">Hide commands</a><br><br>";

                echo "<h3>src Image -> PNG commands <small>(Cut borders)</small></h3>";
                echo "<pre>";
                foreach ($readyPngCmds as $cmd) {
                    echo $cmd . "\n";
                }
                echo "</pre><br>";

                echo "<h3>src MAP -> TIF commands</h3>";
                echo "<pre>";
                foreach ($readTifCmds as $cmd) {
                    echo $cmd . "\n";
                }
                echo "</pre><br>";
            } else {
                echo "<a href=\"?cmds\">Show commands</a><br><br>";
            }
        ?>
    </div>
</body>
</html>