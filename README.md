# GpsParser
Library for handling GPS coordinates in different formats

## Usage

```php
<?php
include 'lib/Gps.php';
include 'lib/GpsParser.php';
include 'lib/InvalidGpsFormatException.php';
header('Content-Type: text/html; charset=utf-8');

use Teddy\Gps\Gps;
use Teddy\Gps\GpsParser;

$latitude = -12.123125345;
$longitude = 27.12312126;
$parser = new gpsParser($latitude, $longitude);
try {
    $gps = $parser->parse();
    echo implode($gps->getDecimal(), ', '); // -12.123125, 27.123121
    echo implode($gps->getDegrees(), ', '); // S 12° 7' 23.250", E 27° 7' 23.236"
    echo implode($gps->getDegrees(false), ', '); // S 12° 7.3875', E 27° 7.3873'
} catch (\Teddy\Gps\InvalidGpsFormatException $e) {
    echo $e->getMessage();
}
```

## Accepts different formats
* 12.543123, -12.54
* N 53.123, E 65.123
* 27° 7' 23", 27° 7' 23"
* W 27° 7' 23.236", E 27° 7' 23.236"
* 26 42 12, 53 23 12
* N 12 43 12, W 12 43 12
* For decimal point uses both "." and ","
* Minutes can be written with [', `, ´]
* Seconds can be written as [", ``, ´´]
* Units can be omitted

## In default checks whether you didn't swap longitude and latitude
```php
$parser = new GpsParser('W 26.12312', 'N 12.12312');
$gps = $parser->parse();
$coords = $gps->getDecimal();
echo $coords['lat']; // 12.123120
echo $coords['lon']; // -26.123120
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
$coords = $gps->getDecimal();
echo $coords['lat']; // -26.123120
echo $coords['lon']; // 12.123120
```
