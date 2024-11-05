<?php

include 'config.php';

// Url to https://github.com/Anrijs/GeoTIFF-Tiler
$ch = curl_init(CFG_GDALINFO_PATH);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$server_output = curl_exec($ch);
header('Content-Type: application/json');
echo $server_output;

curl_close ($ch);
?>
