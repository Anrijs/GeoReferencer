# GeoReferencer

## Installation
1. Coppy files to webserver
2. Make directory `maps`
3. Create `config.php` file (see `example.config.php`)

## Usage
1. Open in web browser
2. Upload map images (20 max at once) or manually copy to `maps` directory
3. Click **Start calibrating maps** to calibrate all or pick indvidual map from list by clicking **[check]** next to name
4. Place pointers on map corners in order:
```
 1      2
  +----+
  |    |
  |    |
  +----+
 4      3
```
5. Save map
6. When maps are calibrated, validate placement in overview map by clicking **View calibration bbox in map**
7. Click **Generate .map files** to make OZI calibration files. They will be stored in `maps` direcory

## Tips and tricks
1. Use shortcuts for faster calibration. (click info button in calibration screen)
2. Enable auto-coords if map file names uses pattern like O-System (example: O-35-091-3-1.jpg). You can set auto coords function in `config.php` file

## Additional features
Click on **Show commands** to generate useful commands: 
1. **src Image -> PNG commands** - Commands to remove borders. 
2. **src MAP -> TIF commands** - generate GoeTIFF files from OZI map files

## This is still a work-in progress tool
