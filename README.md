# GpsParser
Library for handling GPS coordinates in different formats

## Usage

```php
<?php
include 'lib/GpsParser.php';
include 'lib/InvalidGpsFormatException.php';
header('Content-Type: text/html; charset=utf-8');

$latitude = -12.123125345;
$longitude = 27.12312126;
$parser = new gpsParser($latitude, $longitude);

$gps = $parser->parse();
echo $gps['lat']; // -12.123125
echo $gps['lon']; // 27.123121

$gps = $parser->parse('degrees');
echo $gps['lat']; // -12° 7' 23.250"
echo $gps['lon']; // 27° 7' 23.236"

$gps = $parser->parse('noSeconds');
echo $gps['lat']; // -12° 7.3875'
echo $gps['lon']; // 27° 7.3873'
```

## Accepts different formats
* 12.543123, -12.54
* N 53.123, E 65.123
* 26 42 12, 53 23 12
* N 12 43 12, W 12 43 12
* 27° 7' 23.236", 27° 7' 23.236"
* W 27° 7' 23.236", E 27° 7' 23.236"
* For decimal point uses both "." and ","

## In default checks whether you didn't swap longitude and latitude
```php
$parser = new GpsParser('W 26.12312', 'N 12.12312');
$gps = $parser->parse();
echo $gps['lat']; // 12.123120
echo $gps['lon']; // -26.123120
```
Can be disabled with `setCheckOrder(false)`


## Can accept cardinal directions in other languages
```php
// for Czech/Slovak
$lat = 'J 26.12312';
$lon = 'V 12.12312';
$parser = new GpsParser($lat, $lon);
$parser->addCardinalDirections($north = 'S', $south = 'J', $east = 'V', $west = 'Z');
$gps = $parser->parse;
echo $gps['lat']; // -26.123120
echo $gps['lon']; // 12.123120
```
