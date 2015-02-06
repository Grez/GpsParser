<?php

class GpsParser
{
    protected $lat = 0;
    protected $lon = 0;
    protected $checkOrder = true;
    protected $cardinalDirections = array(
        array(
            'north' => 'N',
            'south' => 'S',
            'east' => 'E',
            'west' => 'W',
        ),
    );

    public function __construct($latitude, $longitude)
    {
        $this->lat = $latitude;
        $this->lon = $longitude;

        // Debug purposes
        $this->_lat = $latitude;
        $this->_lon = $longitude;
    }

    /**
     * Adds cardinal directions for another language (eg. SJVZ for Czech)
     * @param char $north
     * @param char $south
     * @param char $east
     * @param char $west
     * @return null
     */
    public function addCardinalDirections($north, $south, $east, $west)
    {
        $this->cardinalDirections[] = array(
            'north' => $north,
            'south' => $south,
            'east' => $east,
            'west' => $west,
        );
    }

    /**
     * Should we check whether we really got "lat;lon" and not "lon;lat"?
     * @param bool $checkOrder
     */
    public function setCheckOrder($checkOrder)
    {
        $this->checkOrder = (bool) $checkOrder;
    }

    /**
     * @param string decimal|degrees|noSeconds $format
     * @return array
     * @throws InvalidGpsFormatException
     * @throws InvalidArgumentException
     */
    public function parse($format = 'decimal')
    {
        if(!$this->canonize()) {
            throw new InvalidGpsFormatException('Unable to parse GPS coordinates. Your input: "' . $this->_lat . ';' . $this->_lon . '"');
        }

        switch($format) {
            case 'decimal':
                return array(
                    'lat' => number_format($this->lat, 6, '.', ','),
                    'lon' => number_format($this->lon, 6, '.', ','),
                );
                break;
            case 'degrees':
                return array(
                    'lat' => $this->decToDegrees($this->lat),
                    'lon' => $this->decToDegrees($this->lon),
                );
                break;
            case 'noSeconds':
                return array(
                    'lat' => $this->decToDegreesNoSeconds($this->lat),
                    'lon' => $this->decToDegreesNoSeconds($this->lon),
                );
                break;
        }

        throw new InvalidArgumentException('This output format isn\'t supported. Typo?');
    }

    /**
     * Delete hemisphere [NSWE] and replaces it with sign
     * @param char $north
     * @param char $south
     * @param char $east
     * @param char $west
     * @return null
     */
    protected function deleteHemisphere($north = 'N', $south = 'S', $east = 'E', $west = 'W')
    {
        // Hemisphere is in the beginning
        $first_lat = substr($this->lat, 0, 1);
        $first_lon = substr($this->lon, 0, 1);

        // [SW] hemisphere -> negative, negative
        if($first_lat == $south && $first_lon == $west) {
            $this->lat = '-' . trim(substr($this->lat, 1));
            $this->lon = '-' . trim(substr($this->lon, 1));
        }

        // [SE] hemisphere -> negative, positive
        if($first_lat == $south && $first_lon == $east) {
            $this->lat = '-' . trim(substr($this->lat, 1));
            $this->lon = trim(substr($this->lon, 1));
        }

        // [NW] hemisphere -> positive, negative
        if($first_lat == $north && $first_lon == $west) {
            $this->lat = trim(substr($this->lat, 1));
            $this->lon = '-' . trim(substr($this->lon, 1));
        }

        // [NE] hemisphere -> negative, negative
        if($first_lat == $north && $first_lon == $east) {
            $this->lat = trim(substr($this->lat, 1));
            $this->lon = trim(substr($this->lon, 1));
        }


        // Hemisphere is in the end
        $last_lat = substr($this->lat, -1);
        $last_lon = substr($this->lon, -1);
        
        // [SW] hemisphere -> negative, negative
        if($last_lat == $south && $last_lon == $west) {
            $this->lat = '-' . trim(substr($this->lat, 0, -1));
            $this->lon = '-' . trim(substr($this->lon, 0, -1));
        }

        // [SE] hemisphere -> negative, positive
        if($last_lat == $south && $last_lon == $east) {
            $this->lat = '-' . trim(substr($this->lat, 0, -1));
            $this->lon = trim(substr($this->lon, 0, -1));
        }

        // [NW] hemisphere -> positive, negative
        if($last_lat == $north && $last_lon == $west) {
            $this->lat = trim(substr($this->lat, 0, -1));
            $this->lon = '-' . trim(substr($this->lon, 0, -1));
        }

        // [NE] hemisphere -> negative, negative
        if($last_lat == $north && $last_lon == $east) {
            $this->lat = trim(substr($this->lat, 0, -1));
            $this->lon = trim(substr($this->lon, 0, -1));
        }
    }

    /**
     * Format ex.: 24.123123, 24.123123, 24, -12.2, 12.1
     * @param string
     * @return float|false
     */
    protected function parseDecimal($string)
    {
        $pattern = '/^(?P<deg>[-]?(\d+)(\.\d+)?)$/';
        $match = preg_match($pattern, $string, $matches);
        if($match && $matches['deg'] <= 180 && $matches['deg'] >= -180) {
            return number_format($matches['deg'], 6, '.', ',');
        } else {
            return false;
        }
    }

    /**
     * Format ex.: N 49° 03.690, N 49° 6’ 1.7’’
     * Minutes allowed as [',`,´]
     * Seconds allowed as [",´´,``]
     */
    protected function parseDegrees($string)
    {
        $string = str_replace(array('``', '´´'), '"', $string);
        $pattern = '/(*UTF8)^(?P<deg>[-]?\d+(\.\d+)?)[° ]{0,2}((?P<min>\d+(\.\d+)?)[\'`´ ]{0,2}((?P<sec>\d+(\.\d+)?)["]?)?)?$/';
        $match = preg_match($pattern, $string, $matches);

        if($match) {
            if($matches['deg'] > 180 || $matches['deg'] < -180) {
                throw new InvalidGpsFormatException('GPS must be in range <-180,180>. Your input: "' . $this->_lat . ';' . $this->_lon . '"');
            }

            $decimal = $matches['deg'];

            if (array_key_exists('min', $matches)) {
                $decimal += $matches['min'] / 60;
            }

            if (array_key_exists('sec', $matches)) {
                $decimal += $matches['sec'] / 3600;
            }

            return $decimal;
        }

        return false;
    }

    /**
     * Check whether someone didn't switch latitude with longitude
     */
    protected function checkOrder()
    {
        foreach($this->cardinalDirections as $cd) {
            if($this->isLon($this->lat, $cd['east'], $cd['west']) && $this->isLat($this->lon, $cd['north'], $cd['south'])) {
                list($this->lat, $this->lon) = array($this->lon, $this->lat); //swap 'em :)
            }
        }
    }

    /**
     * Does string start/end with N or S?
     * @param string $value
     * @return bool
     */
    protected function isLat($value, $north = 'N', $south = 'S')
    {
        $pattern = '/((^[' . $north . $south . ']{1}(.*?))|(.*?)[' . $north . $south . ']{1}$)/';
        $match = preg_match($pattern, $value, $matches);
        return (bool) $match;
    }

    /**
     * Does string start/end with N or S?
     * @param string $value
     * @return bool
     */
    protected function isLon($value, $east = 'E', $west = 'W')
    {
        $pattern = '/((^[' . $east . $west . ']{1}(.*?))|(.*?)[' . $east . $west . ']{1}$)/';
        $match = preg_match($pattern, $value, $matches);
        return (bool) $match;
    }

    /**
     * Tries to canonize GPS coordinates into decimal format
     * @return bool
     */
    protected function canonize()
    {
        $this->lat = str_replace(',', '.', $this->lat);
        $this->lon = str_replace(',', '.', $this->lon);

        if($this->checkOrder) {
            $this->checkOrder();
        }

        foreach($this->cardinalDirections as $cd) {
            $this->deleteHemisphere($cd['north'], $cd['south'], $cd['east'], $cd['west']);
        }

        $lat = $this->parseDecimal($this->lat);
        $lon = $this->parseDecimal($this->lon);
        if($lat !== false && $lon !== false) {
            $this->lat = $lat;
            $this->lon = $lon;
            return true;
        }

        $lat = $this->parseDegrees($this->lat);
        $lon = $this->parseDegrees($this->lon);
        if($lat !== false && $lon !== false) {
            $this->lat = $lat;
            $this->lon = $lon;
            return true;
        }

        return false;
    }

    /**
     * @param $int
     * @return string
     */
    protected function decToDegrees($int)
    {
        $negative = ($int < 0);
        $int = abs($int);
        $degrees = floor($int);
        $minutes = floor(($int - $degrees) * 60);
        $seconds = ((($int - $degrees) * 60) - $minutes) * 60;
        return (($negative) ? '-' : '') . $degrees . '° ' . $minutes . '\' ' . number_format($seconds, 3, '.', ',') . '"';
    }

    /**
     * @param $int
     * @return string
     */
    protected function decToDegreesNoSeconds($int)
    {
        $negative = ($int < 0);
        $int = abs($int);
        $degrees = floor($int);
        $minutes = ($int - $degrees) * 60;
        return (($negative) ? '-' : '') . $degrees . '° ' . number_format($minutes, 4, '.', ',') . '\'';
    }

}