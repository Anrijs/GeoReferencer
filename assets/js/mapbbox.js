Object.assign(String.prototype, {
    replaceAll(search, replace) {
        return this.split(search).join(replace);
    }
});

function romanToArabic(roman){
    if(roman == null)
        return -1;
    var totalValue = 0,
        value = 0, // Initialise!
        prev = 0;

    for(var i=0;i<roman.length;i++){
        var current = char_to_int(roman.charAt(i));

        if (current == -1) {
            return -1;
        }

        if (current > prev) {
            // Undo the addition that was done, turn it into subtraction
            totalValue -= 2 * value;
        }
        if (current !== prev) { // Different symbol?
            value = 0; // reset the sum for the new symbol
        }
        value += current; // keep adding same symbols
        totalValue += current;
        prev = current;
    }
    return totalValue;
}

function char_to_int(character) {
    switch(character){
        case 'I': return 1;
        case 'V': return 5;
        case 'X': return 10;
        case 'L': return 50;
        case 'C': return 100;
        case 'D': return 500;
        case 'M': return 1000;
        default: return -1;
    }
}

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
    return name.replaceAll(" ", "-").replaceAll("_", "-").replaceAll(".", "-");
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

    return [y2,x1,y1,x2];
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
        bbox[0] += d_lat;
    } else if (letter == "2") {  //  b / b
        bbox[0] += d_lat;
        bbox[1] += d_lon;
    } else if (letter == "4") {  //  d / g
        bbox[1] += d_lon;
    }

    var lat = bbox[0];
    var lon = bbox[1];

    return [lat, lon, lat + d_lat, lon + d_lon];
}

/*
    5k planšetes
*/
function getCoords_plansetes5k(srcname) {
    // steps in minutes
    var step_q_lat = 1.25 / 120;
    var step_q_lng = 2.5 / 120;

    var step_num_lat = 1.25 / 60;
    var step_num_lng = 2.5 / 60;

    var step_ltr_lat = 5 / 60;
    var step_ltr_lng = 10 / 60;

    var step_region_lat = 15 / 60;
    var step_region_lng = 30 / 60;

    var name = srcname.replace("_","-");
    var parts = name.split("PLANSETE-")[1].split("-");

    var hasK = parts[0].includes("K");
    var regionPt = parts[0].replace("K","");

    var region = parseInt(regionPt);
    var letter = parts[1];
    var square = parseInt(parts[2]);
    var subsq = 0;

    if (parts.length > 3) {
        var sqch = parts[3];

        switch (sqch) {
            case "1": subsq = 1; break;
            case "2": subsq = 2; break;
            case "3": subsq = 3; break;
            case "4": subsq = 4; break;


            case "I": subsq = 1; break;
            case "II": subsq = 2; break;
            case "III": subsq = 3; break;
            case "IV": subsq = 4; break;

            case "V": subsq = 5; break;
            case "VI": subsq = 6; break;
            case "VII": subsq = 7; break;
            case "VIII": subsq = 8; break;
            case "IX": subsq = 9; break;
            case "X": subsq = 10; break;
            case "XI": subsq = 11; break;
            case "XII": subsq = 12; break;
            case "XIII": subsq = 13; break;
            case "XIV": subsq = 14; break;
            case "XV": subsq = 15; break;
            case "XVI": subsq = 16; break;
            case "XVII": subsq = 17; break;
            case "XVIII": subsq = 18; break;
            case "XIX": subsq = 19; break;
            case "XX": subsq = 20; break;
            case "XXI": subsq = 21; break;
            case "XXII": subsq = 22; break;
            case "XXIII": subsq = 23; break;
            case "XXIV": subsq = 24; break;
            case "XXV": subsq = 25; break;
        }
    }

    var lat_mod = 0;
    var lng_mod = 0;

    var lng = 21.0; // + (step_region_lng * lng_mod); // 21* 0' 0"

    if (region < 8) { lng = 21.0; }
    else if (region < 15) { lng = 21.5; }
    else if (region < 21) { lng = 22.0; }
    else if (region < 27) { lng = 22.5; }
    else if (region < 32) { lng = 23.0; }
    else if (region < 35) { lng = 23.5; }
    else if (region < 43) { lng = 24.0; }

    else if (region < 52) { lng = 24.5; }
    else if (region < 61) { lng = 25.0; }
    else if (region < 71) { lng = 25.5; }
    else if (region < 81) { lng = 26.0; }
    else if (region < 90) { lng = 26.5; }
    else if (region < 99) { lng = 27.0; }
    else if (region < 107) { lng = 27.5; }
    else if (region < 112) { lng = 28.0; }
    else { lng = 28.5; console.log("UNKNOWN REGION LNG NUMBER"); }
    console.log("REGION LNG: " + region + " -> ");

    if ([43,52,61].includes(region)) { lat_mod = -4; }
    else if ([35,44,53,62,71].includes(region)) { lat_mod = -3; }
    else if ([8,15,21,36,45,54,63,72,81,90,99].includes(region)) { lat_mod = -2; }
    else if ([2,9,16,22,27,37,46,55,64,73,82,91,100].includes(region)) { lat_mod = -1; }
    else if ([3,10,17,23,28,38,47,56,65,74,83,92,101].includes(region)) { lat_mod = 0; }
    else if ([4,11,18,24,29,32,39,48,57,66,75,84,93,102].includes(region)) { lat_mod = 1; }
    else if ([5,12,19,25,30,33,40,49,58,67,76,85,94,103,109].includes(region)) { lat_mod = 2; }
    else if ([6,13,20,26,31,34,41,50,59,68,77,86,95,104,110].includes(region)) { lat_mod = 3; }
    else if ([7,14,42,51,60,69,78,87,96,105,111].includes(region)) { lat_mod = 4; }
    else if ([70,79,88,97,106].includes(region)) { lat_mod = 5; }
    else if ([80,89,98].includes(region)) { lat_mod = 6; }
    else { console.log("UNKNOWN REGION LAT NUMBER"); }

    // Bottom left for 003
    var lat = 57.0 - (step_region_lat * lat_mod); // 57* 0' 0"

    if (["a","b","c"].includes(letter)) {
        lat += (2 * step_ltr_lat);
    } else if (["d","e","f"].includes(letter)) {
        lat += step_ltr_lat;
    }

    if (["b","e","h"].includes(letter)) {
        lng += step_ltr_lng;
    } else if (["c","f","i"].includes(letter)) {
        lng += (2 * step_ltr_lng);
    }

    if ([1,2,3,4].includes(square)) {
        lat += (3 * step_num_lat);
    } else if ([5,6,7,8].includes(square)) {
        lat += (2 * step_num_lat);
    } else if ([9,10,11,12].includes(square)) {
        lat += (1 * step_num_lat);
    }


    if ([4,8,12,16].includes(square)) {
        lng += (3 * step_num_lng);
    } else if ([3,7,11,15].includes(square)) {
        lng += (2 * step_num_lng);
    } else if ([2,6,10,14].includes(square)) {
        lng += (1 * step_num_lng);
    }

    if (hasK) {
        lng -= step_num_lng;
    }


    if (subsq == 1) {
        return [lat+step_num_lat, lng, lat+step_q_lat, lng+step_num_lng-step_q_lng];
    } else if (subsq == 2) {
        return [lat+step_num_lat, lng+step_q_lng, lat+step_q_lat, lng+step_num_lng];
    } else if (subsq == 3) {
        return [lat+step_num_lat-step_q_lat, lng, lat, lng+step_num_lng-step_q_lng];
    } else if (subsq == 4) {
        return [lat+step_num_lat-step_q_lat, lng+step_q_lng, lat, lng+step_num_lng];
    }

    if (subsq > 0) {
        var step_1k_lng = step_num_lng / 5;
        var step_1k_lat = step_num_lat / 5;

        if (subsq <= 5)  { lat += (step_1k_lat * 4); }
        else if (subsq <= 10) { lat += (step_1k_lat * 3); }
        else if (subsq <= 15) { lat += (step_1k_lat * 2); }
        else if (subsq <= 20) { lat += (step_1k_lat * 1); }
        else if (subsq <= 25) { /* as is */ }

        var modu = subsq % 5;
        switch (modu) {
            case 1: break;
            case 2: lng += (step_1k_lng * 1); break;
            case 3: lng += (step_1k_lng * 2); break;
            case 4: lng += (step_1k_lng * 3); break;
            case 0: lng += (step_1k_lng * 4); break;
        }

        return [lat+step_1k_lat, lng, lat, lng+step_1k_lng];
    }

    return [lat+step_num_lat, lng, lat, lng+step_num_lng];
}

/*
    Automatic coordinate detection for USSR/Genshtab O-Grid 1:25 000 maps
*/
function getCoords_GenshtabO25k(name) {
    var bbox = getCoords_GenshtabO50k(name);

    var lat = bbox[0];
    var lon = bbox[1];

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

    return [lat, lon, lat + d_lat, lon + d_lon];
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

/*
    1 verst 42k (limited area)
*/
function getCoords_1verst42k(name) {
    var parts = fixName(name).split(".")[0].split("-");

    var chr = parts[0];
    var num = parseInt(parts[1]);

    console.log("num: " + num);

    var lat_step = 1/6;
    var lon_step = 18/60;

    var lat = 58.0;  // 4 top left
    var lon = -6.0;

    var lat_mod = 0;
    var lon_mod = 0;

    if ([32,40].includes(num)) {
        lon_mod = 3;
    } else if ([33,41,49].includes(num)) {
        lon_mod = 2;
    } else if ([34,42,50].includes(num)) {
        lon_mod = 1;
    } else if ([4,10,16,22,27,35,43,51,57].includes(num)) {
        lon = -6.0;
    }else if ([5,11,17,23,28,36,44,52,58].includes(num)) {
        lon_mod = -1;
    } else if ([6,12,18,24,29,37,45,53,59].includes(num)) {
        lon_mod = -2;
    } else if ([7,13,19,25,30,38,46,54,60].includes(num)) {
        lon_mod = -3;
    } else if ([8,14,20,26,31,39,47,55,61].includes(num)) {
        lon_mod = -4;
    } else if ([9,15,21,48,56,62].includes(num)) {
        lon_mod = -5;
    }

    if ([4,5,6,7,8,9].includes(num)) {
        lat_mod = 0;
    } else if ([10,11,12,13,14,15].includes(num)) {
        lat_mod = 1;
    } else if ([16,17,18,19,20].includes(num)) {
        lat_mod = 2;
    } else if ([22,23,24,25,26].includes(num)) {
        lat_mod = 3;
    } else if ([27,28,29,30,31].includes(num)) {
        lat_mod = 4;
    } else if ([33,34,35,36,37,38,39].includes(num)) {
        lat_mod = 5;
    } else if ([40,41,42,43,44,45,46,47,48].includes(num)) {
        lat_mod = 6;
    } else if ([49,50,51,52,53,54,55,56].includes(num)) {
        lat_mod = 7;
    } else if ([57,58,59,60,61,62].includes(num)) {
        lat_mod = 8;
    }

    lat -= lat_mod * lat_step;
    lon -= lon_mod * lon_step;

    return [lat, lon, lat - lat_step, lon + lon_step];
}

/*
    3 verst (limited area)
*/
function getCoords_3verst(name) {
    // top left coords
    var parts = fixName(name).split(".")[0].split("-");
    console.log(parts);

    // 3v Kr
    var row = parseInt(parts[0]);
    var col = parseInt(parts[1]);

    if (!row || !col) {
        // lookup values
        var pos = 0;
        for (;pos<parts.length;pos++) {
            var num = romanToArabic(parts[pos]);
            if (num >= 0) {
                row = num;
                break;
            }
        }

        for (;pos<parts.length;pos++) {
            if (!isNaN(parts[pos])) {
                col = parseInt(parts[pos]);
                break;
            }
        }
    }

    var grid = {
        6: {
            2: [[58,5.538],[21,34.191]],
            3: [[58,9.981],[22,49.240]],
            4: [[58,13.607],[24,3.212]],
            5: [[58,16.569],[25,18.395]],
            6: [[58,18.959],[26,33.217]],
        },
        7: {
            1: [[57,31.914],[20,27.825]],
            2: [[57,37.012],[21,40.896]],
            3: [[57,41.577],[22,54.791]],
            4: [[57,45.240],[24,08.094]],
            5: [[57,48.291],[25,22.108]],
            6: [[57,50.614],[26,36.430]],
            7: [[57,52.315],[27,50.799]],
        },
        8: {
            1: [[57,3.995],[20,35.241]],
            2: [[57,9.082],[21,47.380]],
            3: [[57,13.277],[23,0.240]],
            4: [[57,16.848],[24,12.935]],
            5: [[57,19.892],[25,25.774]],
            6: [[57,22.292],[26,38.840]],
            7: [[57,23.824],[27,52.686]],
        },
        9: {
            1: [[56,35.747],[20,42.616]],
            2: [[56,40.622],[21,53.894]],
            3: [[56,44.949],[23,5.627]],
            4: [[56,48.563],[24,17.704]],
            5: [[56,51.507],[25,29.405]],
            6: [[56,53.882],[26,41.735]],
            7: [[56,55.462],[27,54.533]],
            8: [[56,56.412],[29,7.022]],
        },
        10: {
            1: [[56,7.539],[20,49.908]],
            2: [[56,12.475],[22,0.252]],
            3: [[56,16.638],[23,10.941]],
            4: [[56,20.303],[24,22.390]],
            5: [[56,23.084],[25,32.984]],
            6: [[56,25.387],[26,44.588]],
            7: [[56,26.981],[27,56.387]],
            8: [[56,27.920],[29,7.902]],
        },
        11: {
            3: [[55,48.223],[23,16.202]],
            4: [[55,51.728],[24,27.082]],
            5: [[55,54.798],[25,36.504]],
            6: [[55,56.883],[26,47.399]],
            7: [[55,58.589],[27,58.199]],
            8: [[55,59.471],[29,08.793]],
        },
        12: {
            5: [[55,26.282],[25,40.009]],
            6: [[55,28.556],[26,50.160]],
            7: [[55,30.073],[28,0.012]],
        }
    };

    var pt0 = grid[row][col];
    var pt1 = grid[row][col+1];
    var pt2 = grid[row+1][col+1];
    var pt3 = grid[row+1][col];

    return [
        pt0[0], pt0[1],
        pt1[0], pt1[1],
        pt2[0], pt2[1],
        pt3[0], pt3[1]
    ];
}

function getCoords_PLBorder(name) {

    var frags = [['L1_68',
    [[55, 43.07983],
     [26, 23.20362],
     [55, 43.08997],
     [26, 25.11296],
     [55, 42.28165],
     [26, 25.12609],
     [55, 42.27151],
     [26, 23.21741]]],
   ['L1_67',
    [[55, 42.9283],
     [26, 25.11558],
     [55, 42.93795],
     [26, 27.02481],
     [55, 42.12962],
     [26, 27.03729],
     [55, 42.11998],
     [26, 25.12872]]],
   ['L1_66',
    [[55, 43.09961],
     [26, 27.02231],
     [55, 43.10876],
     [26, 28.93168],
     [55, 42.30044],
     [26, 28.94351],
     [55, 42.29129],
     [26, 27.03479]]],
   ['L1_65',
    [[55, 42.29129],
     [26, 27.03479],
     [55, 42.30044],
     [26, 28.94351],
     [55, 41.49211],
     [26, 28.95532],
     [55, 41.48296],
     [26, 27.04727]]],
   ['L1_64',
    [[55, 42.38127],
     [26, 28.94232],
     [55, 42.38992],
     [26, 30.85112],
     [55, 41.58159],
     [26, 30.86228],
     [55, 41.57294],
     [26, 28.95414]]],
   ['L1_63',
    [[55, 41.57294],
     [26, 28.95414],
     [55, 41.58159],
     [26, 30.86228],
     [55, 40.77325],
     [26, 30.87343],
     [55, 40.76461],
     [26, 28.96595]]],
   ['L1_62',
    [[55, 41.25826],
     [26, 30.86674],
     [55, 41.26641],
     [26, 32.77463],
     [55, 40.45807],
     [26, 32.78512],
     [55, 40.44992],
     [26, 30.87789]]],
   ['L1_61',
    [[55, 41.10474],
     [26, 32.77673],
     [55, 41.1124],
     [26, 34.6845],
     [55, 40.30406],
     [26, 34.69433],
     [55, 40.2964],
     [26, 32.78722]]],
   ['L1_60',
    [[55, 41.03157],
     [26, 34.68548],
     [55, 41.03873],
     [26, 36.5932],
     [55, 40.23039],
     [26, 36.60238],
     [55, 40.22323],
     [26, 34.69532]]],
   ['L1_59',
    [[55, 41.2004],
     [26, 36.59136],
     [55, 41.20707],
     [26, 38.49922],
     [55, 40.39873],
     [26, 38.50775],
     [55, 40.39206],
     [26, 36.60054]]],
   ['L1_58',
    [[55, 42.00874],
     [26, 36.58218],
     [55, 42.01542],
     [26, 38.49069],
     [55, 41.20707],
     [26, 38.49922],
     [55, 41.2004],
     [26, 36.59136]]],
   ['L1_57',
    [[55, 42.01542],
     [26, 38.49069],
     [55, 42.0216],
     [26, 40.39922],
     [55, 41.21325],
     [26, 40.40709],
     [55, 41.20707],
     [26, 38.49922]]],
   ['L1_56',
    [[55, 42.82376],
     [26, 38.48215],
     [55, 42.82995],
     [26, 40.39133],
     [55, 42.0216],
     [26, 40.39922],
     [55, 42.01542],
     [26, 38.49069]]],
   ['L1_55',
    [[55, 42.58744],
     [26, 40.3937],
     [55, 42.59313],
     [26, 42.30269],
     [55, 41.78478],
     [26, 42.30992],
     [55, 41.7791],
     [26, 40.40158]]],
   ['L1_54',
    [[55, 42.43146],
     [26, 42.30414],
     [55, 42.43665],
     [26, 44.21301],
     [55, 41.6283],
     [26, 44.21958],
     [55, 41.62311],
     [26, 42.31136]]],
   ['L1_53',
    [[55, 41.62311],
     [26, 42.31136],
     [55, 41.6283],
     [26, 44.21958],
     [55, 40.81995],
     [26, 44.22614],
     [55, 40.81476],
     [26, 42.31858]]],
   ['L1_52',
    [[55, 41.38579],
     [26, 44.22155],
     [55, 41.39049],
     [26, 46.12957],
     [55, 40.58213],
     [26, 46.13548],
     [55, 40.57744],
     [26, 44.2281]]],
   ['L1_51',
    [[55, 41.22882],
     [26, 46.13075],
     [55, 41.23302],
     [26, 48.03866],
     [55, 40.42466],
     [26, 48.0439],
     [55, 40.42046],
     [26, 46.13666]]],
   ['L1_50',
    [[55, 42.03717],
     [26, 46.12485],
     [55, 42.04138],
     [26, 48.03341],
     [55, 41.23302],
     [26, 48.03866],
     [55, 41.22882],
     [26, 46.13075]]],
   ['L1_49',
    [[55, 42.52639],
     [26, 48.03025],
     [55, 42.5301],
     [26, 49.93921],
     [55, 41.72174],
     [26, 49.94381],
     [55, 41.71803],
     [26, 48.03551]]],
   ['L1_48',
    [[55, 42.93427],
     [26, 49.93692],
     [55, 42.93749],
     [26, 51.84621],
     [55, 42.12913],
     [26, 51.85015],
     [55, 42.12592],
     [26, 49.94151]]],
   ['L1_47',
    [[55, 43.18],
     [26, 51.84503],
     [55, 43.18272],
     [26, 53.75452],
     [55, 42.37436],
     [26, 53.75781],
     [55, 42.37164],
     [26, 51.84897]]],
   ['L1_46',
    [[55, 43.98835],
     [26, 51.84108],
     [55, 43.99107],
     [26, 53.75123],
     [55, 43.18272],
     [26, 53.75452],
     [55, 43.18],
     [26, 51.84503]]],
   ['L1_45',
    [[55, 44.79729],
     [26, 52.21929],
     [55, 44.79991],
     [26, 54.13011],
     [55, 43.99156],
     [26, 54.13327],
     [55, 43.98893],
     [26, 52.22311]]],
   ['L1_44',
    [[55, 45.60648],
     [26, 52.78892],
     [55, 45.60895],
     [26, 54.70039],
     [55, 44.8006],
     [26, 54.70335],
     [55, 44.79813],
     [26, 52.79254]]],
   ['L1_43',
    [[55, 46.41562],
     [26, 53.35893],
     [55, 46.41795],
     [26, 55.27106],
     [55, 45.6096],
     [26, 55.27383],
     [55, 45.60727],
     [26, 53.36236]]],
   ['L1_42',
    [[55, 47.22447],
     [26, 53.73806],
     [55, 47.22671],
     [26, 55.65085],
     [55, 46.41836],
     [26, 55.65349],
     [55, 46.41613],
     [26, 53.74136]]],
    ['L1_41',
     [[55, 48.03282],
      [26, 53.73476],
      [55, 48.03505],
      [26, 55.64821],
      [55, 47.22671],
      [26, 55.65085],
      [55, 47.22447],
      [26, 53.73806]]],
   ['L1_40',
    [[55, 47.71171],
     [26, 55.64927],
     [55, 47.71345],
     [26, 57.56246],
     [55, 46.9051],
     [26, 57.56444],
     [55, 46.90337],
     [26, 55.65191]]],
   ['L1_39',
    [[55, 48.52006],
     [26, 55.64662],
     [55, 48.5218],
     [26, 57.56048],
     [55, 47.71345],
     [26, 57.56246],
     [55, 47.71171],
     [26, 55.64927]]],
   ['L1_38',
    [[55, 48.5218],
     [26, 57.56048],
     [55, 48.52304],
     [26, 59.47433],
     [55, 47.71469],
     [26, 59.47565],
     [55, 47.71345],
     [26, 57.56246]]],
   ['L1_37',
    [[55, 49.33014],
     [26, 57.55849],
     [55, 49.33138],
     [26, 59.47301],
     [55, 48.52304],
     [26, 59.47433],
     [55, 48.5218],
     [26, 57.56048]]],
   ['L1_36',
    [[55, 49.33138],
     [26, 59.47301],
     [55, 49.33213],
     [27, 1.38753],
     [55, 48.52378],
     [27, 1.38819],
     [55, 48.52304],
     [26, 59.47433]]],
   ['L1_35',
    [[55, 50.13973],
     [26, 59.47169],
     [55, 50.14047],
     [27, 1.38687],
     [55, 49.33213],
     [27, 1.38753],
     [55, 49.33138],
     [26, 59.47301]]],
   ['L1_34',
    [[55, 50.05964],
     [27, 1.38693],
     [55, 50.05988],
     [27, 3.30205],
     [55, 49.25154],
     [27, 3.30205],
     [55, 49.25129],
     [27, 1.38759]]],
   ['L1_33',
    [[55, 50.14072],
     [27, 3.30205],
     [55, 50.14047],
     [27, 5.21723],
     [55, 49.33213],
     [27, 5.21656],
     [55, 49.33237],
     [27, 3.30205]]],
   ['L1_32',
    [[55, 50.30214],
     [27, 5.21736],
     [55, 50.30139],
     [27, 7.13267],
     [55, 49.49305],
     [27, 7.13135],
     [55, 49.49379],
     [27, 5.2167]]],
   ['L1_31',
    [[55, 51.11048],
     [27, 5.21802],
     [55, 51.10974],
     [27, 7.13399],
     [55, 50.30139],
     [27, 7.13267],
     [55, 50.30214],
     [27, 5.21736]]],
   ['L1_30',
    [[55, 51.43307],
     [27, 7.13452],
     [55, 51.43183],
     [27, 9.05076],
     [55, 50.62349],
     [27, 9.04877],
     [55, 50.62473],
     [27, 7.1332]]],
     ['L1_29',
  [[55, 51.27016],
   [27, 9.05036],
   [55, 51.26842],
   [27, 10.96647],
   [55, 50.46008],
   [27, 10.96382],
   [55, 50.46182],
   [27, 9.04838]]],
 ['L1_28',
  [[55, 50.46182],
   [27, 9.04838],
   [55, 50.46008],
   [27, 10.96382],
   [55, 49.65174],
   [27, 10.96117],
   [55, 49.65348],
   [27, 9.04639]]],
   ['L1_27',
    [[55, 50.46008],
     [27, 10.96382],
     [55, 50.45785],
     [27, 12.87925],
     [55, 49.64951],
     [27, 12.87595],
     [55, 49.65174],
     [27, 10.96117]]],
   ['L1_26',
    [[55, 49.65046],
     [27, 12.11004],
     [55, 49.64793],
     [27, 14.02481],
     [55, 48.83959],
     [27, 14.02111],
     [55, 48.84212],
     [27, 12.10699]]],
   ['L1_25',
    [[55, 48.84117],
     [27, 12.87264],
     [55, 48.83844],
     [27, 14.78675],
     [55, 48.03009],
     [27, 14.78279],
     [55, 48.03282],
     [27, 12.86934]]],
   ['L1_24',
    [[55, 48.35343],
     [27, 14.78437],
     [55, 48.35021],
     [27, 16.69808],
     [55, 47.54186],
     [27, 16.69346],
     [55, 47.54509],
     [27, 14.78041]]],
   ['L1_23',
    [[55, 47.54509],
     [27, 14.78041],
     [55, 47.54186],
     [27, 16.69346],
     [55, 46.73352],
     [27, 16.68884],
     [55, 46.73674],
     [27, 14.77645]]],
   ['L1_22',
    [[55, 47.78437],
     [27, 16.69484],
     [55, 47.78065],
     [27, 18.60808],
     [55, 46.97231],
     [27, 18.6028],
     [55, 46.97602],
     [27, 16.69022]]],
   ['L1_21',
    [[55, 48.18482],
     [27, 18.61072],
     [55, 48.18061],
     [27, 20.52429],
     [55, 47.37226],
     [27, 20.51835],
     [55, 47.37648],
     [27, 18.60544]]],
   ['L1_20',
    [[55, 48.26144],
     [27, 20.52488],
     [55, 48.25673],
     [27, 22.43851],
     [55, 47.44839],
     [27, 22.4319],
     [55, 47.4531],
     [27, 20.51894]]],
   ['L1_19',
    [[55, 49.06978],
     [27, 20.53083],
     [55, 49.06506],
     [27, 22.44511],
     [55, 48.25673],
     [27, 22.43851],
     [55, 48.26144],
     [27, 20.52488]]],
   ['L1_18',
    [[55, 48.74173],
     [27, 22.44247],
     [55, 48.73652],
     [27, 24.35648],
     [55, 47.92819],
     [27, 24.34922],
     [55, 47.93339],
     [27, 22.43586]]],
   ['L1_17',
    [[55, 48.65569],
     [27, 24.35576],
     [55, 48.64998],
     [27, 26.26969],
     [55, 47.84165],
     [27, 26.26176],
     [55, 47.84735],
     [27, 24.34849]]],
   ['L1_16',
    [[55, 47.84735],
     [27, 24.34849],
     [55, 47.84165],
     [27, 26.26176],
     [55, 47.03332],
     [27, 26.25384],
     [55, 47.03902],
     [27, 24.34123]]],
   ['L1_15',
    [[55, 47.92248],
     [27, 26.26256],
     [55, 47.91629],
     [27, 28.17589],
     [55, 47.10795],
     [27, 28.16731],
     [55, 47.11415],
     [27, 26.25464]]],
   ['L1_14',
    [[55, 47.83545],
     [27, 28.17503],
     [55, 47.82876],
     [27, 30.08829],
     [55, 47.02043],
     [27, 30.07905],
     [55, 47.02712],
     [27, 28.16645]]],
   ['L1_13',
    [[55, 47.58626],
     [27, 30.08551],
     [55, 47.57907],
     [27, 31.99856],
     [55, 46.77075],
     [27, 31.98866],
     [55, 46.77793],
     [27, 30.07627]]],
   ['L1_12',
    [[55, 47.49824],
     [27, 31.99757],
     [55, 47.49056],
     [27, 33.91054],
     [55, 46.68223],
     [27, 33.89998],
     [55, 46.68991],
     [27, 31.98767]]],
   ['L1_11',
    [[55, 47.49056],
     [27, 33.91054],
     [55, 47.48238],
     [27, 35.82349],
     [55, 46.67406],
     [27, 35.81227],
     [55, 46.68223],
     [27, 33.89998]]],
   ['L1_10',
    [[55, 47.56321],
     [27, 35.82461],
     [55, 47.55453],
     [27, 37.73762],
     [55, 46.74622],
     [27, 37.72574],
     [55, 46.75489],
     [27, 35.8134]]]];

    for (var i=0; i<frags.length;i++) {
        var f = frags[i];
        const tag = f[0];
        if (name.includes(tag)) {
            console.log(f[1]);
            return f[1];
        }
    }

    return [];
}
