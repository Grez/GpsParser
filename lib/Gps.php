<?php

namespace Teddy\Gps;


class Gps
{

    /** @var float */
    protected $lat = 0.0;

    /** @var float */
    protected $lon = 0.0;


    /**
     * @param float $latitude
     * @param float $longitude
     */
    public function __construct($latitude, $longitude)
    {
        $this->lat = round($latitude, 6);
        $this->lon = round($longitude, 6);
    }

    /**
     * @return array[string]
     */
    public function getDecimal()
    {
        return array(
            'lat' => number_format($this->lat, 6, '.', ','),
            'lon' => number_format($this->lon, 6, '.', ','),
        );
    }

    /**
     * @param bool $seconds
     * @return array[string]
     */
    public function getDegrees($seconds = true)
    {
        if ($seconds) {
            return array(
                'lat' => $this->decToDegrees($this->lat, 'lat'),
                'lon' => $this->decToDegrees($this->lon, 'lon'),
            );
        } else {
            return array(
                'lat' => $this->decToDegreesNoSeconds($this->lat, 'lat'),
                'lon' => $this->decToDegreesNoSeconds($this->lon, 'lon'),
            );
        }
    }

    /**
     * @param float $decimal
     * @param enum lat|lon $type
     * @return string
     */
    protected function decToDegrees($decimal, $type)
    {
        if($decimal < 0) {
            $hemisphere = ($type == 'lat') ? 'S' : 'W';
        } else {
            $hemisphere = ($type == 'lat') ? 'N' : 'E';
        }

        $abs = abs($decimal);
        $degrees = floor($abs);
        $minutes = floor(($abs - $degrees) * 60);
        $seconds = ((($abs - $degrees) * 60) - $minutes) * 60;
        return $hemisphere . ' ' . $degrees . '° ' . $minutes . '\' ' . number_format($seconds, 3, '.', ',') . '"';
    }

    /**
     * @param float $decimal
     * @param enum lat|lon $type
     * @return string
     */
    protected function decToDegreesNoSeconds($decimal, $type)
    {
        if($decimal < 0) {
            $hemisphere = ($type == 'lat') ? 'S' : 'W';
        } else {
            $hemisphere = ($type == 'lat') ? 'N' : 'E';
        }

        $abs = abs($decimal);
        $abs = abs($abs);
        $degrees = floor($abs);
        $minutes = ($abs - $degrees) * 60;
        return $hemisphere . ' ' . $degrees . '° ' . number_format($minutes, 4, '.', ',') . '\'';
    }

}
