<?php
/*
    Meridian offsets:
        Ferro:   - 17° 39.767'
        Pulkovo: + 30° 19.6'

    Autocoords presets:
        getCoords_GenshtabO25k
        getCoords_GenshtabO50k
        getCoords_GenshtabO100k
        getCoords_GerRus25WWI
*/



define("CFG_PNG_PADDING", 0);
define("CFG_MAP_DATUM", "WGS 84");
define("CFG_MAP_MERIDIAN_DD", 0); // degrees offset
define("CFG_MAP_MERIDIAN_MM", 0); // minutes offset, sign same as DD (decimal)
define("CFG_FILE_MODIFY_ALLOWED", True); // allow to upload and delete map images via web
define("CFG_AUTOCOORDS", False); // js function name for coordinate detection by file name. False to disable
define("CFG_PROJECT_DIR", ""); // active project dir (relative to this file)
define("CFG_COORD_FORMAT", "DM"); // D - dd.ddd |other - dd mm.mmm
define("CFG_VALIDATE", True); // Should save validate positions

?>
