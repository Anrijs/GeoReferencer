/*
    gget filename witohut dir path
*/
function baseName(str, ext) {
    var base = new String(str).substring(str.lastIndexOf('/') + 1); 
    if(ext && base.lastIndexOf(".") != -1) base = base.substring(0, base.lastIndexOf("."));
    return base;
}

/*
    Prepare filename for getCoords_* functions
*/
function fixName(name) {
    return name.replace(" ", "-").replace("_", "-").replace(".", "-");
}

/*
    Automatic coordinate detection for USSR/Genshtab O-Grid 1:100 000 maps
*/
function getCoords_GenshtabO100k(name) {
    var parts = fixName(name).split("-");
    var nom_number = parts[0];
    var col_number = parseInt(parts[1]);

    var Low_mil_lat = (nom_number.toUpperCase().charCodeAt(0) - 65) * 4.0; // O-35-... 44
    var Lef_mil_lon = (col_number - 31.0)*6;

    var nl = parseInt(parts[2]);

    var rw = Math.floor((nl - 1) / 12);

    var x1 =  Lef_mil_lon + Math.floor(nl - rw * 12 - 1) * 0.5;
    var y1 =  (Low_mil_lat) + 4 - (rw + 1) * (1.0/3);
    var x2 = x1 + 0.5;
    var y2 = y1 + 1/3.0;

    return [x1,y1,x2,y2];
}

/*
    Automatic coordinate detection for USSR/Genshtab O-Grid 1:50 000 maps
*/
function getCoords_GenshtabO50k(name) {
    var bbox = getCoords_GenshtabO100k(name);

    var parts = fixName(name).split("-");

    var col_number = parseInt(parts[1]);

    var d_lat = 1.0/6;
    var d_lon = 0.25;
    var letter = parts[3];

    if (letter == "1") {         //  a / a
        bbox[1] += d_lat;
    } else if (letter == "2") {  //  b / b
        bbox[1] += d_lat;
        bbox[0] += d_lon;
    } else if (letter == "4") {  //  d / g
        bbox[0] += d_lon;
    }

    var lat = bbox[1];
    var lon = bbox[0];

    return [lon, lat, lon + d_lon, lat + d_lat];
}

/*
    Automatic coordinate detection for USSR/Genshtab O-Grid 1:25 000 maps
*/
function getCoords_GenshtabO25k(name) {
    var bbox = getCoords_GenshtabO50k(name);

    var lat = bbox[1];
    var lon = bbox[0];

    var parts = fixName(name).split("-");

    var col_number = parseInt(parts[1]);
    var zone = col_number - 30;
    var epsg = 28400 + zone;

    var d_lat = 1.0/12;
    var d_lon = 0.125;
    var letter = parts[4]; 

    if (letter == "1") {         //  a / a
        lat += d_lat;
    } else if (letter == "2") {  //  b / b
        lat += d_lat;
        lon += d_lon;
    } else if (letter == "4") {  //  d / g
        lon += d_lon;
    }

    return [lon, lat, lon + d_lon, lat + d_lat];
}

/*
    Automatic coordinate detection for West. Osteuropa 1:25 000 maps (limited area)
    http://igrek.amzp.pl/maplist.php?cat=GERRUS025WWI
*/
function getCoords_GerRus25WWI(name) {
    var parts = fixName(name).split("-");

    var row = parts[0].toUpperCase();
    var col = parseInt(parts[1]);
    var grid = parts[2].toUpperCase();

    // top left (north-west) corner
    var lat = 0;
    var lon = -9.45; // at col 9

    var d_lat = 0.25; // 15 minutes
    var d_lon = 0.45; // 27 minutes

    var d_lat_sub = d_lat / 3.0; // 5 minutes
    var d_lon_sub = 0.15; // 9 minutes

    switch (row) {
        case "0":    lat = 58; break;
        case "I":    lat = 58 - d_lat; break;
        case "II":   lat = 58 - d_lat - d_lat; break;
        case "III":  lat = 58 - d_lat - d_lat - d_lat; break;
        case "IV":   lat = 57; break;
        case "V":    lat = 57 - d_lat; break;
        case "VI":   lat = 57 - d_lat - d_lat; break;
        case "VII":  lat = 57 - d_lat - d_lat - d_lat; break;
        case "VIII": lat = 56; break;
        case "IX":   lat = 56 - d_lat; break;
        case "X":    lat = 56 - d_lat - d_lat; break;
    }

    lon += (col - 9) * d_lon;

    switch (grid) {
        case "A": break; // leave as is
        case "B": lon += d_lon_sub; break;
        case "C": lon += 2*d_lon_sub; break;
        case "D": lat -= d_lat_sub; break;
        case "E": lat -= d_lat_sub; lon += d_lon_sub; break;
        case "F": lat -= d_lat_sub; lon += 2*d_lon_sub; break;
        case "G": lat -= 2*d_lat_sub; break;
        case "H": lat -= 2*d_lat_sub; lon += d_lon_sub; break;
        case "I": lat -= 2*d_lat_sub; lon += 2*d_lon_sub;break;
    }

    return [lat, lon, lat - d_lat_sub, lon + d_lon_sub];
}

function getCoords_GerRus86k(name) {
    var parts = fixName(name).split("-");

    var row = parts[0].toUpperCase();
    var col = parseInt(parts[1]);

    // top left (north-west) corner
    var lat = 0;
    var lon = -9.45; // at col 9

    var d_lat = 0.25; // 15 minutes
    var d_lon = 0.45; // 27 minutes

    switch (row) {
        case "0":    lat = 58; break;
        case "I":    lat = 58 - d_lat; break;
        case "II":   lat = 58 - d_lat - d_lat; break;
        case "III":  lat = 58 - d_lat - d_lat - d_lat; break;
        case "IV":   lat = 57; break;
        case "V":    lat = 57 - d_lat; break;
        case "VI":   lat = 57 - d_lat - d_lat; break;
        case "VII":  lat = 57 - d_lat - d_lat - d_lat; break;
        case "VIII": lat = 56; break;
        case "IX":   lat = 56 - d_lat; break;
        case "X":    lat = 56 - d_lat - d_lat; break;
    }

    lon += (col - 9) * d_lon;

    console.log("lon: " + lon + " | lon_d: " + d_lon);

    return [lat, lon, lat - d_lat, lon + d_lon];
}

/*
    Custom 300K coords
    Used for http://igrek.amzp.pl/mapindex.php?cat=ME300JOINT1

    A-1 is corner of R58 S57 Windau – Riga (split in 8x8 fragments) 
*/
function getCoords_Custom300k(name) {
    var parts = fixName(name).split("-");

    var lon = 38;
    var lat = 58;

    var d_lat = 0.25;
    var d_lon = 0.5;

    var offsetLat = (parts[0].toUpperCase().charCodeAt(0) - 65) * d_lat;
    var offsetLon = (parseInt(parts[1]) - 1) * d_lon;

    lat = lat - offsetLat;
    lon = lon + offsetLon;

    return [lat, lon, lat - d_lat, lon + d_lon];
}
